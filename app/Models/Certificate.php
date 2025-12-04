<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'certificate_template_id',
        'certificate_number',
        'course_id',
        'program_id',
        'image_path',
        'data',
        'issued_at',
    ];

    protected $casts = [
        'data' => 'array',
        'issued_at' => 'datetime',
    ];

    /**
     * Получить пользователя
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить шаблон сертификата
     */
    public function template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    /**
     * Получить курс
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Получить программу
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Генерировать уникальный номер сертификата
     */
    public static function generateCertificateNumber()
    {
        do {
            $number = 'CERT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)) . '-' . date('Y');
        } while (static::where('certificate_number', $number)->exists());

        return $number;
    }
}
