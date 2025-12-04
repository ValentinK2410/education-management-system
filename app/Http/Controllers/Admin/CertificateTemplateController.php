<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateTemplateController extends Controller
{
    /**
     * Отобразить список всех шаблонов
     */
    public function index()
    {
        $templates = CertificateTemplate::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.certificate-templates.index', compact('templates'));
    }

    /**
     * Показать форму создания шаблона
     */
    public function create()
    {
        return view('admin.certificate-templates.create');
    }

    /**
     * Сохранить новый шаблон
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:course,program',
            'description' => 'nullable|string',
            'width' => 'required|integer|min:100|max:5000',
            'height' => 'required|integer|min:100|max:5000',
            'quality' => 'required|integer|min:1|max:100',
            'background_type' => 'required|in:color,image,gradient',
            'background_color' => 'required_if:background_type,color|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'background_gradient' => 'nullable|string',
            'text_elements_json' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        // Если выбран шаблон по умолчанию, снимаем флаг с других
        if ($data['is_default']) {
            CertificateTemplate::where('type', $data['type'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        // Обработка загрузки фонового изображения
        if ($request->hasFile('background_image')) {
            Storage::disk('public')->makeDirectory('certificate-templates');
            $image = $request->file('background_image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('certificate-templates', $imageName, 'public');
            $data['background_image'] = $imagePath;
        }

        // Парсинг градиента
        if ($request->has('background_gradient') && $request->background_type === 'gradient' && !empty($request->background_gradient)) {
            $gradientData = json_decode($request->background_gradient, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['background_gradient'] = $gradientData;
            }
        } elseif ($request->background_type === 'gradient') {
            // Если градиент не передан, но тип градиент, создаем пустой
            $data['background_gradient'] = null;
        }

        // Парсинг элементов текста
        if ($request->has('text_elements_json') && !empty($request->text_elements_json)) {
            $textElementsData = json_decode($request->text_elements_json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['text_elements'] = $textElementsData;
            } else {
                $data['text_elements'] = [];
            }
        } elseif ($request->has('text_elements')) {
            $textElementsData = json_decode($request->text_elements, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['text_elements'] = $textElementsData;
            } else {
                $data['text_elements'] = [];
            }
        } else {
            $data['text_elements'] = [];
        }

        // Очистка неиспользуемых полей фона
        if ($data['background_type'] !== 'color') {
            unset($data['background_color']);
        } else {
            // Убеждаемся, что цвет установлен
            if (empty($data['background_color'])) {
                $data['background_color'] = '#ffffff';
            }
        }

        if ($data['background_type'] !== 'image') {
            unset($data['background_image']);
        }

        if ($data['background_type'] !== 'gradient') {
            unset($data['background_gradient']);
        } else {
            // Если градиент не установлен, создаем дефолтный
            if (empty($data['background_gradient'])) {
                $data['background_gradient'] = ['colors' => ['#ffffff', '#f0f0f0']];
            }
        }

        // Убеждаемся, что text_elements установлен
        if (empty($data['text_elements'])) {
            $data['text_elements'] = [];
        }

        $template = CertificateTemplate::create($data);

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Шаблон сертификата успешно создан.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Ошибка создания шаблона сертификата: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Произошла ошибка при создании шаблона: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Показать форму редактирования шаблона
     */
    public function edit(CertificateTemplate $certificateTemplate)
    {
        return view('admin.certificate-templates.edit', compact('certificateTemplate'));
    }

    /**
     * Обновить шаблон
     */
    public function update(Request $request, CertificateTemplate $certificateTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:course,program',
            'description' => 'nullable|string',
            'width' => 'required|integer|min:100|max:5000',
            'height' => 'required|integer|min:100|max:5000',
            'quality' => 'required|integer|min:1|max:100',
            'background_type' => 'required|in:color,image,gradient',
            'background_color' => 'required_if:background_type,color|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'background_gradient' => 'nullable|string',
            'text_elements_json' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        // Если выбран шаблон по умолчанию, снимаем флаг с других
        if ($data['is_default']) {
            CertificateTemplate::where('type', $data['type'])
                ->where('id', '!=', $certificateTemplate->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        // Обработка загрузки фонового изображения
        if ($request->hasFile('background_image')) {
            Storage::disk('public')->makeDirectory('certificate-templates');

            // Удаляем старое изображение
            if ($certificateTemplate->background_image && Storage::disk('public')->exists($certificateTemplate->background_image)) {
                Storage::disk('public')->delete($certificateTemplate->background_image);
            }

            $image = $request->file('background_image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('certificate-templates', $imageName, 'public');
            $data['background_image'] = $imagePath;
        } elseif ($request->has('remove_background_image')) {
            if ($certificateTemplate->background_image && Storage::disk('public')->exists($certificateTemplate->background_image)) {
                Storage::disk('public')->delete($certificateTemplate->background_image);
            }
            $data['background_image'] = null;
        } else {
            unset($data['background_image']);
        }

        // Парсинг градиента
        if ($request->has('background_gradient') && $request->background_type === 'gradient' && !empty($request->background_gradient)) {
            $gradientData = json_decode($request->background_gradient, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['background_gradient'] = $gradientData;
            }
        } elseif ($request->background_type === 'gradient') {
            // Если градиент не передан, но тип градиент, сохраняем текущий или null
            if (!isset($data['background_gradient'])) {
                $data['background_gradient'] = $certificateTemplate->background_gradient;
            }
        }

        // Парсинг элементов текста
        if ($request->has('text_elements_json') && !empty($request->text_elements_json)) {
            $textElementsData = json_decode($request->text_elements_json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['text_elements'] = $textElementsData;
            }
        } elseif ($request->has('text_elements')) {
            $textElementsData = json_decode($request->text_elements, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['text_elements'] = $textElementsData;
            }
        }

        // Очистка неиспользуемых полей фона
        if ($data['background_type'] !== 'color') {
            unset($data['background_color']);
        }
        if ($data['background_type'] !== 'image' && !$request->hasFile('background_image') && !$request->has('remove_background_image')) {
            unset($data['background_image']);
        }
        if ($data['background_type'] !== 'gradient') {
            unset($data['background_gradient']);
        }

        $certificateTemplate->update($data);

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Шаблон сертификата успешно обновлен.');
    }

    /**
     * Удалить шаблон
     */
    public function destroy(CertificateTemplate $certificateTemplate)
    {
        // Удаляем фоновое изображение
        if ($certificateTemplate->background_image && Storage::disk('public')->exists($certificateTemplate->background_image)) {
            Storage::disk('public')->delete($certificateTemplate->background_image);
        }

        $certificateTemplate->delete();

        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Шаблон сертификата успешно удален.');
    }
}
