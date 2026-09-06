<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostArtifact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PostSyncController extends Controller
{
    /**
     * Unified sync for every post in the `posts` table.
     *
     * The body is a single `posts` array. Each item carries its own `type`
     * (notice, news, document, admission_form, ...) — the value the form
     * already uses on the client. Items with an unknown or missing `type`
     * are counted as `failed` and skipped.
     */
    public function sync(Request $request)
    {
        $payload = $this->decode($request);

        $summary = [
            'updated' => 0,
            'deleted' => 0,
            'failed' => 0,
        ];

        $items = $payload['posts'];

        if (empty($items)) {
            return response()->json([
                'status' => 'success',
                'summary' => $summary,
            ]);
        }

        $allowedTypes = array_keys(Post::POST_TYPES);

        // Group valid items by type so we can preload each bucket in one query.
        $byType = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                $summary['failed']++;
                continue;
            }

            $type = $item['type'] ?? null;
            if (!$type || !in_array($type, $allowedTypes, true)) {
                $summary['failed']++;
                continue;
            }

            $byType[$type][] = $item;
        }

        try {
            DB::beginTransaction();

            foreach ($byType as $type => $bucket) {
                $existing = $this->loadExistingBySourceId($type, $bucket);

                foreach ($bucket as $item) {
                    $sourceId = $item['id'] ?? null;

                    // Deletion marker: body contains only `id` (+ `type`).
                    $isDeletion = $sourceId !== null
                        && count(array_diff_key($item, array_flip(['id', 'type']))) === 0;

                    if ($isDeletion) {
                        $model = $existing[$sourceId] ?? null;
                        if ($model) {
                            $model->artifacts()->delete();
                            $model->delete();
                            $summary['deleted']++;
                        }
                        continue;
                    }

                    $data = $this->mapFields($type, $item);

                    $model = $this->upsertPost($existing[$sourceId] ?? null, $data, $type, $sourceId);

                    if (!empty($item['artifacts']) && is_array($item['artifacts'])) {
                        $this->syncArtifacts($model, $type, $item['artifacts']);
                    }

                    $summary['updated']++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Post Sync Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /* ---------------------------------------------------------------- */
    // ---------- shared helpers ---------------------------------------
    /* ---------------------------------------------------------------- */

    private function decode(Request $request): array
    {
        $payload = $request->all();

        if (empty($payload)) {
            $decoded = json_decode($request->getContent(), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $posts = $payload['posts'] ?? [];

        return ['posts' => is_array($posts) ? $posts : []];
    }

    /**
     * Map incoming item fields onto the `posts` table for a given type.
     * Each branch only whitelists the columns it knows how to fill.
     */
    private function mapFields(string $type, array $item): array
    {
        return match ($type) {
            Post::NOTICE => [
                'title' => $item['title'] ?? null,
                'content' => $item['content'] ?? null,
                'published_at' => $item['published_at'] ?? null,
                'is_active' => $item['is_active'] ?? true,
                'is_urgent' => $item['is_urgent'] ?? false,
            ],

            Post::NEWS => [
                'title' => $item['title'] ?? null,
                'summary' => $item['summary'] ?? null,
                'content' => $item['content'] ?? null,
                'published_at' => $item['published_at'] ?? null,
                'image_json' => $item['image_json'] ?? null,
                'is_active' => $item['is_active'] ?? true,
                'is_featured' => $item['is_featured'] ?? false,
            ],

            // All "download" flavours (document, admission_form, other_forms,
            // class_routine, exam_routine, syllabus, magazine, board_result)
            // share the same column set.
            default => [
                'title' => $item['title'] ?? null,
                'description' => $item['description'] ?? null,
                'class_label' => $item['class_label'] ?? null,
                'published_at' => $item['published_at'] ?? null,
                'is_active' => $item['is_active'] ?? true,
                'sort_order' => $item['sort_order'] ?? 0,
            ],
        };
    }

    private function loadExistingBySourceId(string $sourceType, array $items): array
    {
        $ids = collect($items)
            ->pluck('id')
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($ids)) {
            return [];
        }

        return Post::query()
            ->where('source_type', $sourceType)
            ->whereIn('source_id', $ids)
            ->get()
            ->keyBy('source_id')
            ->all();
    }

    private function upsertPost(?Post $existing, array $data, string $type, ?int $sourceId): Post
    {
        if ($existing) {
            $existing->fill($data)->save();
            return $existing;
        }

        return Post::create($data + [
            'type' => $type,
            'source_type' => $type,
            'source_id' => $sourceId,
        ]);
    }

    private function syncArtifacts(Post $post, string $sourceType, array $artifacts): void
    {
        foreach ($artifacts as $artifact) {
            if (!is_array($artifact)) {
                continue;
            }

            // Body contains only `id` → drop it.
            $isArtifactDeletion = isset($artifact['id'])
                && count(array_diff_key($artifact, array_flip(['id']))) === 0;

            if ($isArtifactDeletion) {
                PostArtifact::query()
                    ->where('post_id', $post->id)
                    ->where('source_type', $sourceType)
                    ->where('source_id', $artifact['id'])
                    ->delete();
                continue;
            }

            $payload = [
                'post_id' => $post->id,
                'source_type' => $sourceType,
                'file_path' => $artifact['file_path'] ?? null,
                'file_name' => $artifact['file_name'] ?? null,
                'file_type' => $artifact['file_type'] ?? null,
                'file_size' => $artifact['file_size'] ?? null,
            ];

            if (isset($artifact['id'])) {
                PostArtifact::updateOrCreate(
                    ['source_type' => $sourceType, 'source_id' => $artifact['id']],
                    $payload
                );
            } else {
                PostArtifact::create($payload);
            }
        }
    }

    /* ---------------------------------------------------------------- */
    // ---------- read endpoints (GET /api/v1/posts) -------------------
    /* ---------------------------------------------------------------- */

    /**
     * GET /api/v1/posts
     *
     * Paginated list across every post type.
     *
     * Query params (all optional):
     *   - type        : filter by post type (must be in Post::POST_TYPES).
     *                   Supports comma-separated lists, e.g. "notice,news".
     *   - is_active   : 1 / 0 / true / false.
     *   - search      : case-insensitive substring on `title` (and `description`
     *                   for download types).
     *   - class_label : exact match (download types).
     *   - featured    : 1 / 0 (news only).
     *   - urgent      : 1 / 0 (notice only).
     *   - per_page    : items per page, default 15, max 100.
     *   - page        : page number, default 1.
     *   - order_by    : "published_at" (default), "sort_order", or "created_at".
     *   - direction   : "desc" (default) or "asc".
     */
    public function index(Request $request)
    {
        $allowedTypes = array_keys(Post::POST_TYPES);

        $validated = $request->validate([
            'type' => ['nullable', 'string'],
            'is_active' => ['nullable'],
            'search' => ['nullable', 'string', 'max:255'],
            'class_label' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable'],
            'urgent' => ['nullable'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'order_by' => ['nullable', 'string', Rule::in(['published_at', 'sort_order', 'created_at', 'updated_at'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $types = $this->resolveTypes($validated['type'] ?? null, $allowedTypes);

        if ($validated['type'] ?? null) {
            $unknown = array_diff(
                array_map('trim', explode(',', $validated['type'])),
                $allowedTypes
            );
            if (!empty($unknown)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unknown post type(s): ' . implode(', ', $unknown),
                    'allowed' => $allowedTypes,
                ], 422);
            }
        }

        $perPage = (int) ($validated['per_page'] ?? 15);
        $orderBy = $validated['order_by'] ?? 'published_at';
        $direction = $validated['direction'] ?? 'desc';

        $query = Post::query()->with('artifacts');

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        if (array_key_exists('is_active', $validated)) {
            $query->where('is_active', $this->toBool($validated['is_active']));
        }

        if (!empty($validated['search'])) {
            $needle = '%' . $validated['search'] . '%';
            $query->where(function ($q) use ($needle) {
                $q->where('title', 'like', $needle)
                  ->orWhere('content', 'like', $needle)
                  ->orWhere('description', 'like', $needle);
            });
        }

        if (!empty($validated['class_label'])) {
            $query->where('class_label', $validated['class_label']);
        }

        if (array_key_exists('featured', $validated)) {
            $query->where('is_featured', $this->toBool($validated['featured']));
        }

        if (array_key_exists('urgent', $validated)) {
            $query->where('is_urgent', $this->toBool($validated['urgent']));
        }

        $query->orderBy($orderBy, $direction)
              ->orderBy('id', 'desc');

        $posts = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $posts->items(),
            'meta' => [
                'allowed_types' => $allowedTypes,
                'type_labels' => Post::POST_TYPES,
            ],
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/posts/{type}
     *
     * Paginated list scoped to a single post type.
     * Accepts the same query params as `index` minus `type`.
     */
    public function indexByType(Request $request, string $type)
    {
        if (!in_array($type, array_keys(Post::POST_TYPES), true)) {
            return response()->json([
                'status' => 'error',
                'message' => "Unknown post type: {$type}",
                'allowed' => array_keys(Post::POST_TYPES),
            ], 404);
        }

        $request->merge(['type' => $type]);

        return $this->index($request);
    }

    /**
     * GET /api/v1/posts/{type}/{id}
     *
     * `id` may be either the local primary key or the `source_id` from the
     * originating system. We try the local id first and fall back to
     * `source_id` so callers don't need to track our internal ids.
     */
    public function show(Request $request, string $type, string $id)
    {
        if (!in_array($type, array_keys(Post::POST_TYPES), true)) {
            return response()->json([
                'status' => 'error',
                'message' => "Unknown post type: {$type}",
                'allowed' => array_keys(Post::POST_TYPES),
            ], 404);
        }

        $post = Post::query()
            ->with('artifacts')
            ->where('type', $type)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('source_id', $id);
            })
            ->first();

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => "Post not found: type={$type}, id={$id}",
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $post,
        ]);
    }

    /* ---------------------------------------------------------------- */
    // ---------- read helpers ------------------------------------------
    /* ---------------------------------------------------------------- */

    /**
     * Parse the `type` query param into an array. Supports comma-separated
     * values. Returns `null` when no filter was requested (meaning: all
     * types allowed).
     */
    private function resolveTypes(?string $raw, array $allowed): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $types = array_values(array_filter(array_map('trim', explode(',', $raw))));
        $types = array_values(array_intersect($types, $allowed));

        return $types ?: null;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}