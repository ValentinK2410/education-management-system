<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
// Используем GD напрямую

class CertificateController extends Controller
{
    /**
     * Генерировать сертификат для курса
     */
    public function generateForCourse(Request $request, Course $course)
    {
        $user = auth()->user();

        // Проверяем, завершен ли курс
        $userCourse = $user->courses()->where('course_id', $course->id)->first();
        if (!$userCourse || $userCourse->pivot->status !== 'completed') {
            return redirect()->back()->with('error', 'Курс не завершен.');
        }

        // Проверяем, не выдан ли уже сертификат
        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingCertificate) {
            return redirect()->route('certificates.show', $existingCertificate);
        }

        // Получаем шаблон
        $template = CertificateTemplate::getDefault('course');
        if (!$template) {
            $template = CertificateTemplate::forCourses()->active()->first();
        }

        if (!$template) {
            return redirect()->back()->with('error', 'Шаблон сертификата не найден.');
        }

        // Генерируем сертификат
        $certificate = $this->createCertificate($user, $template, $course, null);

        return redirect()->route('certificates.show', $certificate)
            ->with('success', 'Сертификат успешно сгенерирован.');
    }

    /**
     * Генерировать сертификат для программы
     */
    public function generateForProgram(Request $request, Program $program)
    {
        $user = auth()->user();

        // Проверяем, завершена ли программа
        $userProgram = $user->programs()->where('program_id', $program->id)->first();
        if (!$userProgram || $userProgram->pivot->status !== 'completed') {
            return redirect()->back()->with('error', 'Программа не завершена.');
        }

        // Проверяем, не выдан ли уже сертификат
        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('program_id', $program->id)
            ->first();

        if ($existingCertificate) {
            return redirect()->route('certificates.show', $existingCertificate);
        }

        // Получаем шаблон
        $template = CertificateTemplate::getDefault('program');
        if (!$template) {
            $template = CertificateTemplate::forPrograms()->active()->first();
        }

        if (!$template) {
            return redirect()->back()->with('error', 'Шаблон сертификата не найден.');
        }

        // Генерируем сертификат
        $certificate = $this->createCertificate($user, $template, null, $program);

        return redirect()->route('certificates.show', $certificate)
            ->with('success', 'Сертификат успешно сгенерирован.');
    }

    /**
     * Создать сертификат
     */
    private function createCertificate($user, CertificateTemplate $template, $course = null, $program = null)
    {
        // Генерируем изображение сертификата
        $imagePath = $this->generateCertificateImage($user, $template, $course, $program);

        // Создаем запись сертификата
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'certificate_template_id' => $template->id,
            'certificate_number' => Certificate::generateCertificateNumber(),
            'course_id' => $course ? $course->id : null,
            'program_id' => $program ? $program->id : null,
            'image_path' => $imagePath,
            'data' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'course_name' => $course ? $course->name : null,
                'program_name' => $program ? $program->name : null,
                'completed_at' => $course
                    ? ($user->courses()->where('course_id', $course->id)->first()->pivot->completed_at ?? now())
                    : ($user->programs()->where('program_id', $program->id)->first()->pivot->completed_at ?? now()),
            ],
            'issued_at' => now(),
        ]);

        return $certificate;
    }

    /**
     * Генерировать изображение сертификата
     */
    private function generateCertificateImage($user, CertificateTemplate $template, $course = null, $program = null)
    {
        try {
            // Создаем изображение нужного размера
            $image = imagecreatetruecolor($template->width, $template->height);

            // Устанавливаем фон
            if ($template->background_type === 'color') {
                $this->applyColorBackground($image, $template->background_color);
            } elseif ($template->background_type === 'image' && $template->background_image) {
                $this->applyImageBackground($image, $template->background_image, $template->width, $template->height);
            } elseif ($template->background_type === 'gradient' && $template->background_gradient) {
                $this->applyGradientBackground($image, $template->background_gradient, $template->width, $template->height);
            } else {
                // Белый фон по умолчанию
                $white = imagecolorallocate($image, 255, 255, 255);
                imagefill($image, 0, 0, $white);
            }

            // Добавляем текстовые элементы
            if ($template->text_elements) {
                $this->addTextElements($image, $template->text_elements, $user, $course, $program);
            }

            // Сохраняем изображение
            Storage::disk('public')->makeDirectory('certificates');
            $filename = 'cert_' . time() . '_' . uniqid() . '.jpg';
            $path = 'certificates/' . $filename;
            $fullPath = storage_path('app/public/' . $path);

            imagejpeg($image, $fullPath, $template->quality);
            imagedestroy($image);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Ошибка генерации сертификата: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Применить цветной фон
     */
    private function applyColorBackground($image, $color)
    {
        $rgb = $this->hexToRgb($color);
        $bgColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
        imagefill($image, 0, 0, $bgColor);
    }

    /**
     * Применить фоновое изображение
     */
    private function applyImageBackground($image, $imagePath, $width, $height)
    {
        $backgroundPath = storage_path('app/public/' . $imagePath);
        if (file_exists($backgroundPath)) {
            $backgroundInfo = getimagesize($backgroundPath);
            if ($backgroundInfo) {
                $sourceImage = null;
                switch ($backgroundInfo[2]) {
                    case IMAGETYPE_JPEG:
                        $sourceImage = imagecreatefromjpeg($backgroundPath);
                        break;
                    case IMAGETYPE_PNG:
                        $sourceImage = imagecreatefrompng($backgroundPath);
                        break;
                    case IMAGETYPE_GIF:
                        $sourceImage = imagecreatefromgif($backgroundPath);
                        break;
                }

                if ($sourceImage) {
                    imagecopyresampled($image, $sourceImage, 0, 0, 0, 0, $width, $height, $backgroundInfo[0], $backgroundInfo[1]);
                    imagedestroy($sourceImage);
                }
            }
        }
    }

    /**
     * Применить градиентный фон
     */
    private function applyGradientBackground($image, $gradient, $width, $height)
    {
        if (isset($gradient['colors']) && count($gradient['colors']) >= 2) {
            $color1 = $this->hexToRgb($gradient['colors'][0]);
            $color2 = $this->hexToRgb($gradient['colors'][1]);

            for ($y = 0; $y < $height; $y++) {
                $ratio = $y / $height;
                $r = (int)($color1['r'] + ($color2['r'] - $color1['r']) * $ratio);
                $g = (int)($color1['g'] + ($color2['g'] - $color1['g']) * $ratio);
                $b = (int)($color1['b'] + ($color2['b'] - $color1['b']) * $ratio);

                $lineColor = imagecolorallocate($image, $r, $g, $b);
                imageline($image, 0, $y, $width, $y, $lineColor);
            }
        }
    }

    /**
     * Добавить текстовые элементы
     */
    private function addTextElements($image, $elements, $user, $course, $program)
    {
        foreach ($elements as $element) {
            $text = $this->replacePlaceholders($element['text'] ?? '', $user, $course, $program);
            $x = $element['x'] ?? 0;
            $y = $element['y'] ?? 0;
            $size = $element['size'] ?? 24;
            $color = $element['color'] ?? '#000000';

            $rgb = $this->hexToRgb($color);
            $textColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);

            // Используем встроенный шрифт (можно заменить на imagettftext для кастомных шрифтов)
            $fontSize = max(1, min(5, (int)($size / 10))); // Размер шрифта 1-5
            imagestring($image, $fontSize, $x, $y, $text, $textColor);
        }
    }

    /**
     * Заменить плейсхолдеры в тексте
     */
    private function replacePlaceholders($text, $user, $course, $program)
    {
        $replacements = [
            '{user_name}' => $user->name,
            '{user_email}' => $user->email,
            '{course_name}' => $course ? $course->name : '',
            '{program_name}' => $program ? $program->name : '',
            '{date}' => now()->format('d.m.Y'),
            '{year}' => now()->format('Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Преобразовать hex в RGB
     */
    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Показать сертификат
     */
    public function show(Certificate $certificate)
    {
        // Проверяем права доступа
        if (auth()->id() !== $certificate->user_id && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        return view('certificates.show', compact('certificate'));
    }

    /**
     * Скачать сертификат
     */
    public function download(Certificate $certificate)
    {
        // Проверяем права доступа
        if (auth()->id() !== $certificate->user_id && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $path = storage_path('app/public/' . $certificate->image_path);

        if (!file_exists($path)) {
            abort(404, 'Файл сертификата не найден.');
        }

        return response()->download($path, 'certificate_' . $certificate->certificate_number . '.jpg');
    }
}
