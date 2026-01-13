<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Показать страницу системных настроек
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $groups = [
            'general' => 'Общие настройки',
            'moodle' => 'Настройки Moodle',
            'sso' => 'Настройки SSO',
        ];
        
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        return view('admin.settings.index', compact('settings', 'groups'));
    }

    /**
     * Сохранить настройки
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'additional_lines' => 'nullable|array',
            'additional_lines.*.text' => 'nullable|string|max:255',
            'additional_lines.*.font_size' => 'nullable|numeric|min:0.5|max:2',
            'additional_lines.*.opacity' => 'nullable|numeric|min:0|max:1',
        ]);
        
        // Обработка синхронизации цвета кнопки виртуального класса
        if ($request->has('settings') && isset($request->input('settings')['virtual_class_button_color'])) {
            $colorValue = $request->input('settings')['virtual_class_button_color'];
            // Если есть текстовое поле цвета, используем его значение
            if ($request->has('settings') && isset($request->input('settings')['virtual_class_button_color_text'])) {
                $colorTextValue = $request->input('settings')['virtual_class_button_color_text'];
                if (!empty($colorTextValue)) {
                    $colorValue = $colorTextValue;
                }
            }
            $request->merge(['settings' => array_merge($request->input('settings'), ['virtual_class_button_color' => $colorValue])]);
        }

        // Обработка загрузки логотипа
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('system', 'public');
            $setting = Setting::where('key', 'system_logo')->first();
            if ($setting) {
                // Удаляем старый логотип, если он существует
                if ($setting->value && \Storage::disk('public')->exists($setting->value)) {
                    \Storage::disk('public')->delete($setting->value);
                }
                $setting->value = $logoPath;
                $setting->save();
            }
        }

        // Обработка удаления логотипа
        if ($request->has('delete_logo') && $request->delete_logo === '1') {
            $setting = Setting::where('key', 'system_logo')->first();
            if ($setting && $setting->value) {
                if (\Storage::disk('public')->exists($setting->value)) {
                    \Storage::disk('public')->delete($setting->value);
                }
                $setting->value = null;
                $setting->save();
            }
        }

        // Обработка дополнительных строк
        if ($request->has('additional_lines')) {
            $additionalLines = [];
            foreach ($request->input('additional_lines', []) as $line) {
                if (!empty($line['text'])) {
                    $additionalLines[] = [
                        'text' => $line['text'],
                        'font_size' => $line['font_size'] ?? '0.875',
                        'opacity' => $line['opacity'] ?? '0.9',
                    ];
                }
            }
            
            $setting = Setting::where('key', 'system_brand_additional_lines')->first();
            if ($setting) {
                $setting->value = json_encode($additionalLines);
                $setting->save();
            }
        }

        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            if ($setting) {
                // Приводим значение к нужному типу
                $castedValue = $this->castValue($value, $setting->type);
                
                // Если тип json, кодируем в JSON
                if ($setting->type === 'json') {
                    $castedValue = json_encode($castedValue);
                }
                
                $setting->value = $castedValue;
                $setting->save();
            }
        }

        // Очищаем кэш настроек
        Cache::forget('settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Настройки успешно сохранены');
    }

    /**
     * Привести значение к нужному типу
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function castValue($value, string $type)
    {
        if ($value === null || $value === '') {
            return null;
        }

        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }
}
