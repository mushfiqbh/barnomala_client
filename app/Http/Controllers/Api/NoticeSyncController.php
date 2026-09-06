<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostArtifact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NoticeSyncController extends Controller
{
    public function sync(Request $request)
    {
        $noticesData = $request->input('notices', []);

        if (!is_array($noticesData)) {
            $noticesData = json_decode($request->getContent(), true)['notices'] ?? [];
        }
        
        // Preload all notices in one query (avoid N+1)
        $ids = collect($noticesData)->pluck('id')->filter()->all();
        $existingNotices = Post::notices()->whereIn('source_id', $ids)->get()->keyBy('source_id');

        $summary = [
            'updated' => 0,
            'deleted' => 0,
            'failed' => 0
        ];

        try {
            DB::beginTransaction();
            foreach ($noticesData as $notice) {
                // Find existing notice by ID
                $sourceId = $notice['id'] ?? null;
                $noticeModel = $existingNotices[$sourceId] ?? null;

                // If only ID is provided → delete
                if (count($notice) === 1) {
                    if ($noticeModel) {
                        $noticeModel->artifacts()->delete();
                        $noticeModel->delete();
                        $summary['deleted']++;
                    }
                    continue;
                }

                // Prepare data once
                $data = [
                    'title' => $notice['title'] ?? null,
                    'content' => $notice['content'] ?? null,
                    'published_at' => $notice['published_at'] ?? null,
                    'is_active' => $notice['is_active'] ?? true,
                    'is_urgent' => $notice['is_urgent'] ?? false,
                ];

                if ($noticeModel) {
                    $noticeModel->update($data);
                } else {
                    $noticeModel = Post::create($data + [
                        'type' => Post::NOTICE,
                        'source_type' => Post::NOTICE,
                        'source_id' => $sourceId,
                    ]);
                }

                // Sync artifacts if provided
                if (!empty($notice['artifacts']) && is_array($notice['artifacts'])) {
                    $this->syncArtifacts($noticeModel, $notice['artifacts']);
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
            Log::error('Notice Sync Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function syncArtifacts(Post $notice, array $artifacts)
    {
        $processedIds = [];

        foreach ($artifacts as $artifact) {
            // If only id is provided, delete the artifact
            if (isset($artifact['id']) && count($artifact) === 1) {
                PostArtifact::where('source_type', Post::NOTICE)
                    ->where('source_id', $artifact['id'])
                    ->where('post_id', $notice->id)
                    ->delete();
                continue;
            }

            // Upsert artifact by id
            if (isset($artifact['id'])) {
                $artifactModel = PostArtifact::updateOrCreate(
                    ['source_type' => Post::NOTICE, 'source_id' => $artifact['id']],
                    [
                        'post_id' => $notice->id,
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
                    'post_id' => $notice->id,
                    'source_type' => Post::NOTICE,
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
        $notices = Post::notices()->with('artifacts')->orderBy('published_at', 'desc')->paginate($perPage);
        return response()->json([
            'status' => 'success',
            'data' => $notices->items(),
            'pagination' => [
                'current_page' => $notices->currentPage(),
                'per_page' => $notices->perPage(),
                'total' => $notices->total(),
                'last_page' => $notices->lastPage(),
            ]
        ]);
    }
}
