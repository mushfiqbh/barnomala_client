<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffSyncController extends Controller
{
    public function sync(Request $request)
    {
        $staffData = $request->input('staff', []);
        
        if (!is_array($staffData)) {
            $staffData = json_decode($request->getContent(), true)['staff'] ?? [];
        }

        $summary = [
            'updated' => 0,
            'deleted' => 0,
            'failed' => 0
        ];

        try {
            DB::beginTransaction();
            foreach ($staffData as $item) {
                if (!isset($item['id'])) {
                    $summary['failed']++;
                    continue;
                }

                $legacyId = $item['id'];

                // If body has only id, delete it
                if (count($item) === 1) {
                    $deleted = Staff::where('legacy_id', $legacyId)->delete();
                    if ($deleted) $summary['deleted']++;
                    continue;
                }

                // Map data from request to staff model attributes.
                // `type` mirrors the Committee model and lets the public
                // Incharges page filter on a stable column rather than
                // fuzzy-matching department/designation strings.
                //
                // `legacy_id` is the stable primary identifier from the
                // source system, so we match on that for upserts. Backfill
                // it for rows previously imported before the column existed.
                $staffCode = $item['staff_code'] ?? (string) $legacyId;

                $existing = Staff::where('legacy_id', $legacyId)->first();
                if (!$existing) {
                    $existing = Staff::where('staff_code', $staffCode)->first();
                }

                $data = [
                    'legacy_id' => $legacyId,
                    'staff_code' => $staffCode,
                    'name' => $item['name'] ?? 'Unknown',
                    'department' => $item['department'] ?? null,
                    'designation' => $item['designation'] ?? null,
                    'type' => $item['type'] ?? null,
                    'gender' => $item['gender'] ?? null,
                    'date_of_birth' => $item['date_of_birth'] ?? null,
                    'phone' => $item['phone'] ?? null,
                    'email' => $item['email'] ?? null,
                    'photo' => $item['photo'] ?? null,
                    'national_id' => $item['national_id'] ?? null,
                    'religion' => $item['religion'] ?? null,
                    'blood_group' => $item['blood_group'] ?? null,
                    'marital_status' => $item['marital_status'] ?? null,
                    'present_address' => $item['present_address'] ?? null,
                    'permanent_address' => $item['permanent_address'] ?? null,
                    'joining_date' => $item['joining_date'] ?? null,
                    'leaving_date' => $item['leaving_date'] ?? null,
                    'status' => $item['status'] ?? 'active',
                ];

                if ($existing) {
                    $existing->fill($data)->save();
                } else {
                    Staff::create($data);
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
            Log::error('Staff Sync Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
