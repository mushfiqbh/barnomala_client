<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostArtifact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsSyncController extends Controller
{
    public function sync(Request $request)
    {
        $newsData = $request->input('news', []);

        if (!is_array($newsData)) {
            $newsData = json_decode($request->getContent(), true)['news'] ?? [];
        }

        // Preload all news in one query (avoid N+1)
        $ids = collect($newsData)->pluck('id')->filter()->all();
        $existingNews = Post::news()->whereIn('source_id', $ids)->get()->keyBy('source_id');

        $summary = [
            'updated' => 0,
            'deleted' => 0,
            'failed' => 0
        ];

        try {
            DB::beginTransaction();
            foreach ($newsData as $news) {
                // Find existing news by ID
                $sourceId = $news['id'] ?? null;
                $newsModel = $existingNews[$sourceId] ?? null;

                // If only ID is provided → delete
                if (count($news) === 1) {
                    if ($newsModel) {
                        $newsModel->artifacts()->delete();
                        $newsModel->delete();
                        $summary['deleted']++;
                    }
                    continue;
                }

                // Prepare data once
                $data = [
                    'title' => $news['title'] ?? null,
                    'summary' => $news['summary'] ?? null,
                    'content' => $news['content'] ?? null,
                    'published_at' => $news['published_at'] ?? null,
                    'image_json' => $news['image_json'] ?? null,
                    'is_active' => $news['is_active'] ?? true,
                    'is_featured' => $news['is_featured'] ?? false,
                ];

                if ($newsModel) {
                    $newsModel->update($data);
                } else {
                    $newsModel = Post::create($data + [
                        'type' => Post::NEWS,
                        'source_type' => Post::NEWS,
                        'source_id' => $sourceId,
                    ]);
                }

                // Sync artifacts if provided
                if (!empty($news['artifacts']) && is_array($news['artifacts'])) {
                    $this->syncArtifacts($newsModel, $news['artifacts']);
                }

                $summary['updated']++;
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('News Sync Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function syncArtifacts(Post $news, array $artifacts)
    {
        $processedIds = [];

        foreach ($artifacts as $artifact) {
            // If only id is provided, delete the artifact
            if (isset($artifact['id']) && count($artifact) === 1) {
                PostArtifact::where('source_type', Post::NEWS)
                    ->where('source_id', $artifact['id'])
                    ->where('post_id', $news->id)
                    ->delete();
                continue;
            }

            // Upsert artifact by id
            if (isset($artifact['id'])) {
                $artifactModel = PostArtifact::updateOrCreate(
                    ['source_type' => Post::NEWS, 'source_id' => $artifact['id']],
                    [
                        'post_id' => $news->id,
                        'file_path' => $artifact['file_path'] ?? null,
                        'file_name' => $artifact['file_name'] ?? null,
                        'file_type' => $artifact['file_type'] ?? null,
                        'file_size' => $artifact['file_size'] ?? null,
                    ]
                );
                $processedIds[] = $artifactModel->id;
            } else {
                // Create new artifact without id
                $artifactModel = PostArtifact::create([
                    'post_id' => $news->id,
                    'source_type' => Post::NEWS,
                    'file_path' => $artifact['file_path'] ?? null,
                    'file_name' => $artifact['file_name'] ?? null,
                    'file_type' => $artifact['file_type'] ?? null,
                    'file_size' => $artifact['file_size'] ?? null,
                ]);
                $processedIds[] = $artifactModel->id;
            }
        }
    }

    public function index()
    {
        $perPage = request()->get('per_page', 15);
        $news = Post::news()->with('artifacts')->orderBy('published_at', 'desc')->paginate($perPage);
        return response()->json([
            'status' => 'success',
            'data' => $news->items(),
            'pagination' => [
                'current_page' => $news->currentPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
                'last_page' => $news->lastPage(),
            ]
        ]);
    }
}
