<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'legacy_id',
        'staff_code',
        'name',
        'department',
        'designation',
        'type',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'photo',
        'national_id',
        'religion',
        'blood_group',
        'marital_status',
        'present_address',
        'permanent_address',
        'joining_date',
        'leaving_date',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'leaving_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
