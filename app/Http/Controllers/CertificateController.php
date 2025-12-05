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
            $angle = isset($gradient['angle']) ? (float)$gradient['angle'] : 0; // Угол в градусах

            // Преобразуем угол в радианы
            $angleRad = deg2rad($angle);

            // Вычисляем длину диагонали для покрытия всего изображения
            $diagonal = sqrt($width * $width + $height * $height);

            // Центр изображения
            $centerX = $width / 2;
            $centerY = $height / 2;

            // Вычисляем координаты начала и конца градиента
            $x1 = $centerX - cos($angleRad) * $diagonal / 2;
            $y1 = $centerY - sin($angleRad) * $diagonal / 2;
            $x2 = $centerX + cos($angleRad) * $diagonal / 2;
            $y2 = $centerY + sin($angleRad) * $diagonal / 2;

            // Для каждого пикселя вычисляем расстояние от линии градиента
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    // Вычисляем проекцию точки на линию градиента
                    $dx = $x2 - $x1;
                    $dy = $y2 - $y1;
                    $length = sqrt($dx * $dx + $dy * $dy);

                    if ($length > 0) {
                        $px = $x - $x1;
                        $py = $y - $y1;
                        $dot = ($px * $dx + $py * $dy) / ($length * $length);
                        $ratio = max(0, min(1, $dot));
                    } else {
                        $ratio = 0;
                    }

                    $r = (int)($color1['r'] + ($color2['r'] - $color1['r']) * $ratio);
                    $g = (int)($color1['g'] + ($color2['g'] - $color1['g']) * $ratio);
                    $b = (int)($color1['b'] + ($color2['b'] - $color1['b']) * $ratio);

                    $pixelColor = imagecolorallocate($image, $r, $g, $b);
                    imagesetpixel($image, $x, $y, $pixelColor);
                }
            }
        }
    }

    /**
     * Добавить элементы (текст, фигуры, изображения)
     */
    private function addTextElements($image, $elements, $user, $course, $program)
    {
        foreach ($elements as $element) {
            $type = $element['type'] ?? 'text';

            if ($type === 'text') {
                $this->addTextElement($image, $element, $user, $course, $program);
            } elseif ($type === 'line') {
                $this->addLineElement($image, $element);
            } elseif ($type === 'circle') {
                $this->addCircleElement($image, $element);
            } elseif ($type === 'square') {
                $this->addSquareElement($image, $element);
            } elseif ($type === 'rectangle') {
                $this->addRectangleElement($image, $element);
            } elseif ($type === 'trapezoid') {
                $this->addTrapezoidElement($image, $element);
            } elseif ($type === 'image') {
                $this->addImageElement($image, $element);
            }
        }
    }

    /**
     * Добавить текстовый элемент
     */
    private function addTextElement($image, $element, $user, $course, $program)
    {
        $text = $this->replacePlaceholders($element['text'] ?? '', $user, $course, $program);
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $size = $element['size'] ?? 24;
        $color = $element['color'] ?? '#000000';
        $font = $element['font'] ?? 'Arial';
        $letterSpacing = $element['letterSpacing'] ?? 0;

        $rgb = $this->hexToRgb($color);
        $textColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);

        $fontSize = max(1, min(5, (int)($size / 10)));

        if ($letterSpacing > 0) {
            $currentX = $x;
            $textLength = mb_strlen($text, 'UTF-8');

            for ($i = 0; $i < $textLength; $i++) {
                $char = mb_substr($text, $i, 1, 'UTF-8');
                imagestring($image, $fontSize, $currentX, $y, $char, $textColor);
                $charWidth = imagefontwidth($fontSize);
                $currentX += $charWidth + $letterSpacing;
            }
        } else {
            imagestring($image, $fontSize, $x, $y, $text, $textColor);
        }
    }

    /**
     * Добавить линию
     */
    private function addLineElement($image, $element)
    {
        $x1 = $element['x1'] ?? 0;
        $y1 = $element['y1'] ?? 0;
        $x2 = $element['x2'] ?? 0;
        $y2 = $element['y2'] ?? 0;
        $lineWidth = $element['lineWidth'] ?? 2;
        $color = $element['color'] ?? '#000000';

        $rgb = $this->hexToRgb($color);
        $lineColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);

        imagesetthickness($image, $lineWidth);
        imageline($image, $x1, $y1, $x2, $y2, $lineColor);
        imagesetthickness($image, 1);
    }

    /**
     * Добавить круг
     */
    private function addCircleElement($image, $element)
    {
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $radius = $element['radius'] ?? 50;
        $fillColor = $element['fillColor'] ?? '#000000';
        $strokeColor = $element['strokeColor'] ?? '#000000';
        $strokeWidth = $element['strokeWidth'] ?? 2;
        $filled = $element['filled'] ?? true;

        $fillRgb = $this->hexToRgb($fillColor);
        $strokeRgb = $this->hexToRgb($strokeColor);
        $fillColorRes = imagecolorallocate($image, $fillRgb['r'], $fillRgb['g'], $fillRgb['b']);
        $strokeColorRes = imagecolorallocate($image, $strokeRgb['r'], $strokeRgb['g'], $strokeRgb['b']);

        if ($filled) {
            imagefilledellipse($image, $x, $y, $radius * 2, $radius * 2, $fillColorRes);
        }

        if ($strokeWidth > 0) {
            imagesetthickness($image, $strokeWidth);
            imageellipse($image, $x, $y, $radius * 2, $radius * 2, $strokeColorRes);
            imagesetthickness($image, 1);
        }
    }

    /**
     * Добавить квадрат
     */
    private function addSquareElement($image, $element)
    {
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $size = $element['size'] ?? 100;
        $fillColor = $element['fillColor'] ?? '#000000';
        $strokeColor = $element['strokeColor'] ?? '#000000';
        $strokeWidth = $element['strokeWidth'] ?? 2;
        $filled = $element['filled'] ?? true;

        $fillRgb = $this->hexToRgb($fillColor);
        $strokeRgb = $this->hexToRgb($strokeColor);
        $fillColorRes = imagecolorallocate($image, $fillRgb['r'], $fillRgb['g'], $fillRgb['b']);
        $strokeColorRes = imagecolorallocate($image, $strokeRgb['r'], $strokeRgb['g'], $strokeRgb['b']);

        if ($filled) {
            imagefilledrectangle($image, $x, $y, $x + $size, $y + $size, $fillColorRes);
        }

        if ($strokeWidth > 0) {
            imagesetthickness($image, $strokeWidth);
            imagerectangle($image, $x, $y, $x + $size, $y + $size, $strokeColorRes);
            imagesetthickness($image, 1);
        }
    }

    /**
     * Добавить прямоугольник
     */
    private function addRectangleElement($image, $element)
    {
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $width = $element['width'] ?? 200;
        $height = $element['height'] ?? 100;
        $fillColor = $element['fillColor'] ?? '#000000';
        $strokeColor = $element['strokeColor'] ?? '#000000';
        $strokeWidth = $element['strokeWidth'] ?? 2;
        $filled = $element['filled'] ?? true;

        $fillRgb = $this->hexToRgb($fillColor);
        $strokeRgb = $this->hexToRgb($strokeColor);
        $fillColorRes = imagecolorallocate($image, $fillRgb['r'], $fillRgb['g'], $fillRgb['b']);
        $strokeColorRes = imagecolorallocate($image, $strokeRgb['r'], $strokeRgb['g'], $strokeRgb['b']);

        if ($filled) {
            imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $fillColorRes);
        }

        if ($strokeWidth > 0) {
            imagesetthickness($image, $strokeWidth);
            imagerectangle($image, $x, $y, $x + $width, $y + $height, $strokeColorRes);
            imagesetthickness($image, 1);
        }
    }

    /**
     * Добавить трапецию
     */
    private function addTrapezoidElement($image, $element)
    {
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $topWidth = $element['topWidth'] ?? 200;
        $bottomWidth = $element['bottomWidth'] ?? 300;
        $height = $element['height'] ?? 100;
        $fillColor = $element['fillColor'] ?? '#000000';
        $strokeColor = $element['strokeColor'] ?? '#000000';
        $strokeWidth = $element['strokeWidth'] ?? 2;
        $filled = $element['filled'] ?? true;

        $topLeftX = $x;
        $topRightX = $x + $topWidth;
        $bottomLeftX = $x + ($topWidth - $bottomWidth) / 2;
        $bottomRightX = $bottomLeftX + $bottomWidth;

        $fillRgb = $this->hexToRgb($fillColor);
        $strokeRgb = $this->hexToRgb($strokeColor);
        $fillColorRes = imagecolorallocate($image, $fillRgb['r'], $fillRgb['g'], $fillRgb['b']);
        $strokeColorRes = imagecolorallocate($image, $strokeRgb['r'], $strokeRgb['g'], $strokeRgb['b']);

        $points = [
            $topLeftX, $y,
            $topRightX, $y,
            $bottomRightX, $y + $height,
            $bottomLeftX, $y + $height
        ];

        if ($filled) {
            imagefilledpolygon($image, $points, 4, $fillColorRes);
        }

        if ($strokeWidth > 0) {
            imagesetthickness($image, $strokeWidth);
            imagepolygon($image, $points, 4, $strokeColorRes);
            imagesetthickness($image, 1);
        }
    }

    /**
     * Добавить изображение
     */
    private function addImageElement($image, $element)
    {
        // Примечание: Для полной поддержки изображений в элементах потребуется
        // сохранение загруженных изображений на сервере и их загрузка при генерации
        // Сейчас это заглушка - изображения элементов не будут отображаться в финальном сертификате
        // Для реализации нужно:
        // 1. Сохранять загруженные изображения в storage/app/public/certificate-elements/
        // 2. Сохранять путь к изображению в imageData вместо base64
        // 3. Загружать изображение при генерации сертификата

        // Временная реализация: если imageData содержит путь к файлу
        if (isset($element['imageData']) && !empty($element['imageData'])) {
            $imagePath = storage_path('app/public/certificate-elements/' . $element['imageData']);
            if (file_exists($imagePath)) {
                $sourceImage = imagecreatefromstring(file_get_contents($imagePath));
                if ($sourceImage) {
                    $x = $element['x'] ?? 0;
                    $y = $element['y'] ?? 0;
                    $width = $element['width'] ?? 100;
                    $height = $element['height'] ?? 100;

                    $sourceWidth = imagesx($sourceImage);
                    $sourceHeight = imagesy($sourceImage);

                    imagecopyresampled($image, $sourceImage, $x, $y, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
                    imagedestroy($sourceImage);
                }
            }
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
