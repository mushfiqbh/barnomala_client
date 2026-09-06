<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommitteeSyncController extends Controller
{
    public function sync(Request $request)
    {
        $committeesData = $request->input('committees', []);
        
        if (!is_array($committeesData)) {
            $committeesData = json_decode($request->getContent(), true)['committees'] ?? [];
        }

        $summary = [
            'updated' => 0,
            'deleted' => 0,
            'failed' => 0
        ];

        try {
            DB::beginTransaction();
            foreach ($committeesData as $item) {
                if (!isset($item['id'])) {
                    $summary['failed']++;
                    continue;
                }

                $legacyId = $item['id'];
                
                // If body has only id, delete it
                if (count($item) === 1) {
                    $deleted = Committee::where('id', $legacyId)->delete();
                    if ($deleted) $summary['deleted']++;
                    continue;
                }

                // Map data from request to Committee model attributes.
                // The outbound payload uses `type` (see SyncCommitteeToSchoolJob),
                // which maps to the local `type` column.
                $data = [
                    'id' => $legacyId,
                    'type' => $item['type'] ?? $item['committee_type'] ?? 'general',
                    'name' => $item['name'] ?? 'Unknown',
                    'session' => $item['session'] ?? null,
                    'description' => $item['description'] ?? null,
                    'order_index' => $item['order_index'] ?? 0,
                    'status' => $item['status'] ?? 'active',
                    'note' => $item['note'] ?? null,
                ];

                $committee = Committee::updateOrCreate(
                    ['id' => $legacyId],
                    $data
                );

                // Sync members if provided. Whitelist only known fillable fields
                // so unknown payload keys (e.g. nested relations) cannot leak in.
                if (isset($item['members']) && is_array($item['members'])) {
                    foreach ($item['members'] as $memberItem) {
                        if (!isset($memberItem['id'])) continue;

                        CommitteeMember::updateOrCreate(
                            ['id' => $memberItem['id']],
                            array_intersect_key($memberItem, array_flip([
                                'name',
                                'designation',
                                'father_name',
                                'mother_name',
                                'phone',
                                'email',
                                'photo',
                                'joining_date',
                                'leaving_date',
                                'is_active',
                            ])) + ['committee_id' => $committee->id]
                        );
                    }
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
            Log::error('Committee Sync Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
