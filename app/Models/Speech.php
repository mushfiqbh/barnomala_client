<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Speech extends Model
{
    protected $fillable = ['name', 'title', 'speech', 'image_json', 'row_index', 'column_index', 'colspan', 'is_active'];

    protected $casts = [
        'image_json' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image_json['url'] ?? asset('images/teacher.png');
    }
}
