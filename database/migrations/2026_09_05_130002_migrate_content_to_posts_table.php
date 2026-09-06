<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('notices') ||
            ! Schema::hasTable('news') ||
            ! Schema::hasTable('notice_artifacts') ||
            ! Schema::hasTable('news_artifacts')
        ) {
            return;
        }

        DB::transaction(function (): void {
            $postIds = [
                'notice' => [],
                'news' => [],
            ];

            foreach (DB::table('notices')->orderBy('id')->get() as $notice) {
                $postIds['notice'][$notice->id] = DB::table('posts')->insertGetId([
                    'type' => 'notice',
                    'source_type' => 'notice',
                    'source_id' => $notice->id,
                    'legacy_id' => $notice->legacy_id,
                    'title' => $notice->title,
                    'content' => $notice->content,
                    'published_at' => $notice->published_at,
                    'is_active' => $notice->is_active,
                    'is_urgent' => $notice->is_urgent,
                    'created_at' => $notice->created_at,
                    'updated_at' => $notice->updated_at,
                ]);
            }

            foreach (DB::table('news')->orderBy('id')->get() as $news) {
                $postIds['news'][$news->id] = DB::table('posts')->insertGetId([
                    'type' => 'news',
                    'source_type' => 'news',
                    'source_id' => $news->id,
                    'legacy_id' => $news->legacy_id,
                    'title' => $news->title,
                    'summary' => $news->summary,
                    'content' => $news->content,
                    'published_at' => $news->published_at,
                    'image_json' => $news->image_json,
                    'is_active' => $news->is_active,
                    'is_featured' => $news->is_featured,
                    'created_at' => $news->created_at,
                    'updated_at' => $news->updated_at,
                ]);
            }

            $this->copyArtifacts('notice_artifacts', 'notice_id', 'notice', $postIds['notice']);
            $this->copyArtifacts('news_artifacts', 'news_id', 'news', $postIds['news']);

            $this->assertCount('notices', count($postIds['notice']), 'notice');
            $this->assertCount('news', count($postIds['news']), 'news');

            $sourceArtifactCount = DB::table('notice_artifacts')->count()
                + DB::table('news_artifacts')->count();
            $postArtifactCount = DB::table('post_artifacts')->count();

            if ($sourceArtifactCount !== $postArtifactCount) {
                throw new RuntimeException("Post artifact migration count mismatch: expected {$sourceArtifactCount}, got {$postArtifactCount}.");
            }
        });
    }

    private function copyArtifacts(string $table, string $foreignKey, string $sourceType, array $postIds): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach (DB::table($table)->orderBy('id')->get() as $artifact) {
            $postId = $postIds[$artifact->{$foreignKey}] ?? null;

            if ($postId === null) {
                throw new RuntimeException("Unable to map {$table} row {$artifact->id} to a post.");
            }

            DB::table('post_artifacts')->insert([
                'post_id' => $postId,
                'source_type' => $sourceType,
                'source_id' => $artifact->id,
                'file_path' => $artifact->file_path,
                'file_name' => $artifact->file_name,
                'file_type' => $artifact->file_type,
                'file_size' => $artifact->file_size,
                'created_at' => $artifact->created_at,
                'updated_at' => $artifact->updated_at,
            ]);
        }
    }

    private function assertCount(string $table, int $expected, string $sourceType): void
    {
        $actual = DB::table('posts')->where('source_type', $sourceType)->count();

        if ($actual !== $expected) {
            throw new RuntimeException("Post migration count mismatch for {$table}: expected {$expected}, got {$actual}.");
        }
    }

    public function down(): void
    {
        throw new RuntimeException('The content merge is destructive and cannot be rolled back without a database backup.');
    }
};