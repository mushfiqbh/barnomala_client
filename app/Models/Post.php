<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PostArtifact;

class Post extends Model
{
    public const NOTICE = 'notice';
    public const NEWS = 'news';

    public const POST_TYPES = [
        'notice' => 'Notice',
        'news' => 'News',
        'document' => 'Document',
        'admission_form' => 'Admission Form',
        'other_forms' => 'Other Forms',
        'class_routine' => 'Class Routine',
        'exam_routine' => 'Exam Routine',
        'syllabus' => 'Syllabus',
        'magazine' => 'Magazine',
        'board_result' => 'Board Results',
    ];

    protected $fillable = [
        'type',
        'source_type',
        'source_id',
        'legacy_id',
        'title',
        'content',
        'summary',
        'description',
        'class_label',
        'published_at',
        'image_json',
        'is_active',
        'is_urgent',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'legacy_id' => 'integer',
        'published_at' => 'date',
        'image_json' => 'array',
        'is_active' => 'boolean',
        'is_urgent' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function artifacts(): HasMany
    {
        return $this->hasMany(PostArtifact::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeNotices(Builder $query): Builder
    {
        return $query->where('type', self::NOTICE)->where('source_type', self::NOTICE);
    }

    public function scopeNews(Builder $query): Builder
    {
        return $query->where('type', self::NEWS)->where('source_type', self::NEWS);
    }

    public function scopeDownloads(Builder $query): Builder
    {
        return $query->where('source_type', 'download');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_json['url'] ?? asset('images/default-news.png');
    }

    public static function typeLabel(?string $type): string
    {
        if (!$type) {
            return 'Uncategorized';
        }

        return self::POST_TYPES[$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    public static function typeIcon(?string $type): string
    {
        return match ($type) {
            self::NOTICE => 'fa-flag',
            self::NEWS => 'fa-newspaper',
            'document' => 'fa-file-text',
            'admission_form' => 'fa-file-signature',
            'other_forms' => 'fa-file-alt',
            'class_routine' => 'fa-calendar-week',
            'exam_routine' => 'fa-calendar-check',
            'syllabus' => 'fa-book',
            'magazine' => 'fa-newspaper',
            'board_result' => 'fa-scroll',
            default => 'fa-file-download',
        };
    }

    /**
     * Types that map to the "download" source (i.e. everything except notice/news).
     *
     * @return array<string, string>
     */
    public static function downloadTypes(): array
    {
        return array_diff_key(self::POST_TYPES, [self::NOTICE => 0, self::NEWS => 0]);
    }
}
