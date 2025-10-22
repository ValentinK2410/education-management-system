<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'program_id',
        'instructor_id',
        'code',
        'credits',
        'duration',
        'schedule',
        'location',
        'prerequisites',
        'learning_outcomes',
        'is_active',
    ];

    protected $casts = [
        'prerequisites' => 'array',
        'learning_outcomes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the program that owns the course.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the instructor that teaches the course.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Scope a query to only include active courses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
