<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'width',
        'height',
        'quality',
        'background_type',
        'background_color',
        'background_image',
        'background_gradient',
        'text_elements',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'text_elements' => 'array',
        'background_gradient' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'quality' => 'integer',
    ];

    /**
     * Получить сертификаты, созданные по этому шаблону
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Получить активные шаблоны
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить шаблоны для курсов
     */
    public function scopeForCourses($query)
    {
        return $query->where('type', 'course');
    }

    /**
     * Получить шаблоны для программ
     */
    public function scopeForPrograms($query)
    {
        return $query->where('type', 'program');
    }

    /**
     * Получить шаблон по умолчанию для типа
     */
    public static function getDefault($type)
    {
        return static::where('type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}
