<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Speech;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpeechSyncController extends Controller
{
    public function sync(Request $request)
    {
        $speechesData = $request->input('speeches', []);

        if (!is_array($speechesData)) {
            $speechesData = json_decode($request->getContent(), true)['speeches'] ?? [];
        }

        // Preload all speeches in one query (avoid N+1)
        $ids = collect($speechesData)->pluck('id')->filter()->all();
        $existingSpeeches = Speech::whereIn('id', $ids)->get()->keyBy('id');

        $summary = [
            'updated' => 0,
            'deleted' => 0,
            'failed' => 0
        ];

        try {
            DB::beginTransaction();
            foreach ($speechesData as $speech) {
                // Find existing speech by ID
                $speechModel = $existingSpeeches[$speech['id'] ?? null] ?? null;

                // If only ID is provided → delete
                if (count($speech) === 1) {
                    if ($speechModel) {
                        $speechModel->delete();
                        $summary['deleted']++;
                    }
                    continue;
                }

                // Prepare data once
                $data = [
                    'name' => $speech['name'] ?? null,
                    'title' => $speech['title'] ?? null,
                    'speech' => $speech['speech'] ?? null,
                    'image_json' => $speech['image_json'] ?? null,
                    'row_index' => $speech['row_index'] ?? 1,
                    'column_index' => $speech['column_index'] ?? 1,
                    'colspan' => $speech['colspan'] ?? 1,
                    'is_active' => $speech['is_active'] ?? true,
                ];

                if ($speechModel) {
                    $speechModel->update($data);
                } else {
                    Speech::create($data);
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
            Log::error('Speech Sync Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $perPage = request()->get('per_page', 15);
        $speeches = Speech::orderBy('row_index', 'asc')
            ->orderBy('column_index', 'asc')
            ->paginate($perPage);
        return response()->json([
            'status' => 'success',
            'data' => $speeches->items(),
            'pagination' => [
                'current_page' => $speeches->currentPage(),
                'per_page' => $speeches->perPage(),
                'total' => $speeches->total(),
                'last_page' => $speeches->lastPage(),
            ]
        ]);
    }
}
