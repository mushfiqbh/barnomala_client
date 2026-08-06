<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OptionSyncController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'options' => ['nullable', 'array'],
        ]);

        $locksOption = Option::where('option_key', 'transfer.locks.json')->first();
        $locks = $locksOption ? json_decode($locksOption->option_value, true) : [];

        $optionsPayload = $this->normalizeOptions($validated['options'] ?? []);

        // Filter out locked demographics options if locked
        if (!empty($locks['demographics'])) {
            $optionsPayload = array_filter($optionsPayload, function ($item) {
                return !str_starts_with($item['option_key'], 'institute.demographics.') && 
                       !str_starts_with($item['option_key'], 'institute.stats.');
            });
        }

        DB::transaction(function () use ($optionsPayload): void {
            $this->upsertOptions($optionsPayload);
        });

        return response()->json([
            'synced' => true,
            'counts' => [
                'options' => count($optionsPayload),
            ],
        ]);
    }

    protected function normalizeOptions(array $options): array
    {
        $rows = [];

        foreach ($options as $key => $value) {
            if (! is_string($key) || $key === '') {
                throw ValidationException::withMessages([
                    'options' => 'Options must be an object keyed by option name.',
                ]);
            }

            // Skip zero-valued institute stats (e.g. student/staff counts of 0)
            if (str_starts_with($key, 'institute.stats.') && $this->isZeroValue($value)) {
                continue;
            }

            [$optionValue, $valueType] = $this->serializeOptionValue($value);

            $rows[] = [
                'option_key' => $key,
                'option_value' => $optionValue,
                'value_type' => $valueType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }

    protected function upsertOptions(array $rows): void
    {
        Option::query()->upsert(
            $rows,
            ['option_key'],
            ['option_value', 'value_type', 'updated_at']
        );
    }

    protected function serializeOptionValue(mixed $value): array
    {
        return match (true) {
            is_array($value) => [json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'json'],
            is_bool($value) => [$value ? '1' : '0', 'boolean'],
            is_int($value) => [(string) $value, 'integer'],
            $value === null => [null, 'string'],
            default => [(string) $value, 'string'],
        };
    }

    protected function isZeroValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value === false;
        }

        return is_numeric($value) && (float) $value === 0.0;
    }
}
