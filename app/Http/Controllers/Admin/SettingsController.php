<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        ]);

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
