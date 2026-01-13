@extends('layouts.admin')

@section('title', 'Системные настройки')
@section('page-title', 'Системные настройки')

@push('styles')
<style>
    .settings-group {
        margin-bottom: 2rem;
    }

    .settings-group-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem 0.5rem 0 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .settings-group-body {
        background: white;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 0.5rem 0.5rem;
        padding: 1.5rem;
    }

    [data-theme="dark"] .settings-group-body {
        background: var(--card-bg, #334155);
        border-color: var(--border-color, #475569);
        color: var(--text-color, #e2e8f0);
    }

    .setting-item {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    [data-theme="dark"] .setting-item {
        border-bottom-color: var(--border-color, #475569);
    }

    .setting-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .setting-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    [data-theme="dark"] .setting-label {
        color: var(--text-color, #e2e8f0);
    }

    .setting-description {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.75rem;
    }

    [data-theme="dark"] .setting-description {
        color: var(--text-color, #94a3b8);
        opacity: 0.8;
    }

    .form-control {
        background-color: white;
        border-color: #e2e8f0;
        color: #1e293b;
    }

    [data-theme="dark"] .form-control {
        background-color: var(--card-bg, #334155);
        border-color: var(--border-color, #475569);
        color: var(--text-color, #e2e8f0);
    }

    [data-theme="dark"] .form-control:focus {
        background-color: var(--card-bg, #334155);
        border-color: #6366f1;
        color: var(--text-color, #e2e8f0);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }

    [data-theme="dark"] .form-control::placeholder {
        color: var(--text-color, #94a3b8);
        opacity: 0.6;
    }

    [data-theme="dark"] .form-check-label {
        color: var(--text-color, #e2e8f0);
    }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }

    [data-theme="dark"] .card {
        background-color: var(--card-bg, #334155) !important;
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    [data-theme="dark"] .card-header {
        background-color: var(--card-bg, #334155) !important;
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    [data-theme="dark"] .card-body {
        background-color: var(--card-bg, #334155) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    [data-theme="dark"] .card.border-0 {
        background-color: var(--card-bg, #334155) !important;
    }

    [data-theme="dark"] .card.border-0 .card-header.bg-white {
        background-color: var(--card-bg, #334155) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    [data-theme="dark"] .card.border-0 .card-header.border-bottom {
        border-color: var(--border-color, #475569) !important;
    }

    [data-theme="dark"] .text-muted {
        color: var(--text-color, #94a3b8) !important;
        opacity: 0.8;
    }

    [data-theme="dark"] .text-danger {
        color: #f87171 !important;
    }

    [data-theme="dark"] h4, 
    [data-theme="dark"] h5, 
    [data-theme="dark"] .h4, 
    [data-theme="dark"] .h5 {
        color: var(--text-color, #e2e8f0) !important;
    }

    .brand-preview {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-preview-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
    }

    .brand-preview-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .brand-preview-logo img {
        object-fit: contain;
    }

    .brand-preview-text {
        color: white;
        font-weight: 700;
        text-align: center;
    }

    .brand-preview-line {
        color: white;
        font-size: 0.875rem;
        text-align: center;
        opacity: 0.9;
    }

    .additional-line-item {
        background: #f8f9fa;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    [data-theme="dark"] .additional-line-item {
        background: var(--card-bg, #334155);
        border-color: var(--border-color, #475569);
    }

    .additional-line-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .size-control-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .preview-container {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 20px !important;
        align-self: flex-start !important;
        margin-top: 0 !important;
        z-index: 10;
    }
    
    @media (min-width: 992px) {
        .preview-container {
            position: -webkit-sticky !important;
            position: sticky !important;
            top: 20px !important;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        
        .settings-row {
            display: flex;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row settings-row">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h4 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Системные настройки
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.store') }}" id="settingsForm" enctype="multipart/form-data">
                        @csrf

                        @php
                            $brandingSettings = isset($settings['general']) ? $settings['general']->filter(function($s) {
                                return in_array($s->key, ['system_name', 'system_logo', 'system_logo_icon', 'system_brand_text_size', 'system_logo_width', 'system_logo_height', 'system_brand_additional_lines']);
                            }) : collect();
                            $otherGeneralSettings = isset($settings['general']) ? $settings['general']->filter(function($s) {
                                return !in_array($s->key, ['system_name', 'system_logo', 'system_logo_icon', 'system_brand_text_size', 'system_logo_width', 'system_logo_height', 'system_brand_additional_lines']);
                            }) : collect();
                        @endphp

                        @if($brandingSettings->count() > 0)
                        <div class="settings-group">
                            <div class="settings-group-header">
                                <i class="fas fa-palette me-2"></i>
                                Настройки брендинга
                            </div>
                            <div class="settings-group-body">
                                @foreach($brandingSettings as $setting)
                                    @if($setting->key === 'system_logo')
                                        <div class="setting-item">
                                            <label class="setting-label">
                                                {{ $setting->label ?? $setting->key }}
                                            </label>
                                            @if($setting->description)
                                            <div class="setting-description">
                                                {{ $setting->description }}
                                            </div>
                                            @endif
                                            <div class="mb-3">
                                                @php
                                                    $currentLogo = $setting->value;
                                                    $logoExists = $currentLogo && \Storage::disk('public')->exists($currentLogo);
                                                @endphp
                                                @if($logoExists)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $currentLogo) }}" alt="Текущий логотип" id="currentLogoPreview" style="max-height: 64px; max-width: 200px; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px;">
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="delete_logo" value="1" id="delete_logo">
                                                        <label class="form-check-label" for="delete_logo">
                                                            Удалить текущий логотип
                                                        </label>
                                                    </div>
                                                @endif
                                                <input 
                                                    type="file" 
                                                    name="logo" 
                                                    id="logoInput"
                                                    class="form-control" 
                                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp">
                                                <small class="text-muted">Рекомендуемый размер: до 200x64px. Форматы: JPEG, PNG, GIF, SVG, WebP. Максимальный размер: 2MB</small>
                                            </div>
                                        </div>
                                    @elseif($setting->key === 'system_brand_additional_lines')
                                        <div class="setting-item">
                                            <label class="setting-label">
                                                {{ $setting->label ?? $setting->key }}
                                            </label>
                                            @if($setting->description)
                                            <div class="setting-description">
                                                {{ $setting->description }}
                                            </div>
                                            @endif
                                            <div id="additionalLinesContainer">
                                                @php
                                                    // Для типа json Setting уже декодирует значение, но здесь мы работаем напрямую с $setting->value
                                                    $linesValue = $setting->value ?? '[]';
                                                    if (is_string($linesValue)) {
                                                        $lines = json_decode($linesValue, true);
                                                    } else {
                                                        $lines = is_array($linesValue) ? $linesValue : [];
                                                    }
                                                    if (!is_array($lines)) $lines = [];
                                                @endphp
                                                @foreach($lines as $index => $line)
                                                    <div class="additional-line-item" data-line-index="{{ $index }}">
                                                        <div class="additional-line-item-header">
                                                            <strong>Строка {{ $index + 1 }}</strong>
                                                            <button type="button" class="btn btn-sm btn-danger remove-line-btn" data-index="{{ $index }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" 
                                                               name="additional_lines[{{ $index }}][text]" 
                                                               class="form-control mb-2" 
                                                               value="{{ $line['text'] ?? '' }}" 
                                                               placeholder="Текст строки"
                                                               data-preview-target="line-{{ $index }}">
                                                        <div class="size-control-group">
                                                            <div>
                                                                <label class="small">Размер шрифта (rem)</label>
                                                                <input type="number" 
                                                                       name="additional_lines[{{ $index }}][font_size]" 
                                                                       class="form-control form-control-sm" 
                                                                       value="{{ $line['font_size'] ?? '0.875' }}" 
                                                                       step="0.1"
                                                                       min="0.5"
                                                                       max="2"
                                                                       data-preview-size="line-{{ $index }}">
                                                            </div>
                                                            <div>
                                                                <label class="small">Прозрачность (0-1)</label>
                                                                <input type="number" 
                                                                       name="additional_lines[{{ $index }}][opacity]" 
                                                                       class="form-control form-control-sm" 
                                                                       value="{{ $line['opacity'] ?? '0.9' }}" 
                                                                       step="0.1"
                                                                       min="0"
                                                                       max="1"
                                                                       data-preview-opacity="line-{{ $index }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary mt-2" id="addLineBtn">
                                                <i class="fas fa-plus me-1"></i>Добавить строку
                                            </button>
                                            <input type="hidden" name="settings[{{ $setting->key }}]" id="additionalLinesInput" value="{{ $setting->value ?? '[]' }}">
                                        </div>
                                    @elseif(in_array($setting->key, ['system_logo_width', 'system_logo_height']))
                                        <div class="setting-item">
                                            <label class="setting-label">
                                                {{ $setting->label ?? $setting->key }}
                                            </label>
                                            @if($setting->description)
                                            <div class="setting-description">
                                                {{ $setting->description }}
                                            </div>
                                            @endif
                                            <div class="size-control-group">
                                                <input 
                                                    type="number" 
                                                    name="settings[{{ $setting->key }}]" 
                                                    class="form-control" 
                                                    value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                    placeholder="Введите число"
                                                    data-preview-size="{{ $setting->key === 'system_logo_width' ? 'logo-width' : 'logo-height' }}"
                                                    min="16"
                                                    max="200"
                                                    step="1">
                                            </div>
                                        </div>
                                    @elseif($setting->key === 'system_brand_text_size')
                                        <div class="setting-item">
                                            <label class="setting-label">
                                                {{ $setting->label ?? $setting->key }}
                                            </label>
                                            @if($setting->description)
                                            <div class="setting-description">
                                                {{ $setting->description }}
                                            </div>
                                            @endif
                                            <input 
                                                type="number" 
                                                name="settings[{{ $setting->key }}]" 
                                                class="form-control" 
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                placeholder="Введите число"
                                                data-preview-size="text-size"
                                                step="0.1"
                                                min="0.8"
                                                max="3"
                                                style="max-width: 200px;">
                                        </div>
                                    @else
                                        <div class="setting-item">
                                            <label class="setting-label">
                                                {{ $setting->label ?? $setting->key }}
                                            </label>
                                            @if($setting->description)
                                            <div class="setting-description">
                                                {{ $setting->description }}
                                            </div>
                                            @endif
                                            <input 
                                                type="text" 
                                                name="settings[{{ $setting->key }}]" 
                                                class="form-control" 
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                placeholder="Введите значение"
                                                data-preview-text="{{ $setting->key === 'system_name' ? 'name' : '' }}">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @foreach($groups as $groupKey => $groupName)
                            @if($groupKey === 'general' && $otherGeneralSettings->count() > 0)
                                <div class="settings-group">
                                    <div class="settings-group-header">
                                        <i class="fas fa-cog me-2"></i>
                                        {{ $groupName }}
                                    </div>
                                    <div class="settings-group-body">
                                        @foreach($otherGeneralSettings as $setting)
                                        <div class="setting-item">
                                            <label class="setting-label">
                                                {{ $setting->label ?? $setting->key }}
                                            </label>
                                            @if($setting->description)
                                            <div class="setting-description">
                                                {{ $setting->description }}
                                            </div>
                                            @endif
                                            
                                            @if($setting->type === 'text')
                                            <textarea 
                                                name="settings[{{ $setting->key }}]" 
                                                class="form-control" 
                                                rows="3"
                                                placeholder="Введите значение">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                        @elseif($setting->type === 'boolean')
                                            <div class="form-check form-switch">
                                                <input 
                                                    class="form-check-input" 
                                                    type="checkbox" 
                                                    name="settings[{{ $setting->key }}]" 
                                                    value="1"
                                                    id="setting_{{ $setting->key }}"
                                                    {{ old('settings.' . $setting->key, $setting->value) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="setting_{{ $setting->key }}">
                                                    {{ $setting->value ? 'Включено' : 'Выключено' }}
                                                </label>
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <input 
                                                type="number" 
                                                name="settings[{{ $setting->key }}]" 
                                                class="form-control" 
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                placeholder="Введите число">
                                        @else
                                            <input 
                                                type="text" 
                                                name="settings[{{ $setting->key }}]" 
                                                class="form-control" 
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                placeholder="Введите значение">
                                        @endif
                                        
                                        @error('settings.' . $setting->key)
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-muted mb-0">Настройки для этой группы отсутствуют</p>
                                @endif
                            </div>
                        </div>
                        @endforeach

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>
                                Сохранить настройки
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm preview-container" style="position: sticky; top: 20px; align-self: flex-start;">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Предпросмотр
                    </h5>
                </div>
                <div class="card-body">
                    <div class="brand-preview" id="brandPreview" style="min-height: 120px;">
                        <div class="brand-preview-content">
                            <div class="brand-preview-logo" id="previewLogo" style="display: flex; align-items: center; gap: 0.5rem;">
                                <img id="previewLogoImg" src="" alt="" style="display: none; max-width: 32px; max-height: 32px; object-fit: contain;">
                                <i id="previewLogoIcon" class="fas fa-graduation-cap" style="font-size: 16px;"></i>
                            </div>
                            <div class="brand-preview-text" id="previewText" style="font-size: 1.5rem; font-weight: 700; color: white; margin-top: 0.5rem;">EduManage</div>
                            <div id="previewAdditionalLines" style="margin-top: 0.5rem;"></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Предпросмотр обновляется автоматически при изменении настроек
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Обработка переключателей boolean
    document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.textContent = this.checked ? 'Включено' : 'Выключено';
        });
    });

    // Предпросмотр в реальном времени
    @php
        $lines = [];
        $additionalLinesSetting = $brandingSettings->firstWhere('key', 'system_brand_additional_lines');
        if ($additionalLinesSetting) {
            $linesValue = $additionalLinesSetting->value ?? '[]';
            // Проверяем, является ли значение строкой (JSON) или уже массивом
            if (is_string($linesValue)) {
                $lines = json_decode($linesValue, true);
            } else {
                $lines = is_array($linesValue) ? $linesValue : [];
            }
            if (!is_array($lines)) $lines = [];
        }
        $logoSetting = $brandingSettings->firstWhere('key', 'system_logo');
        $logoExists = $logoSetting && $logoSetting->value && \Storage::disk('public')->exists($logoSetting->value);
        $currentLogo = $logoSetting ? $logoSetting->value : null;
    @endphp
    let lineCounter = {{ count($lines) }};
    let currentLogoUrl = @json($logoExists && $currentLogo ? asset('storage/' . $currentLogo) : null);

    // Обновление предпросмотра
    function updatePreview() {
        console.log('Обновление предпросмотра...');
        
        // Название системы
        const nameInput = document.querySelector('input[name="settings[system_name]"]');
        const previewText = document.getElementById('previewText');
        if (nameInput && previewText) {
            previewText.textContent = nameInput.value || 'EduManage';
            console.log('Название обновлено:', nameInput.value);
        }

        // Размер текста
        const textSizeInput = document.querySelector('input[name="settings[system_brand_text_size]"]');
        if (textSizeInput && previewText) {
            const fontSize = (textSizeInput.value || '1.5') + 'rem';
            previewText.style.fontSize = fontSize;
            console.log('Размер текста обновлен:', fontSize);
        }

        // Логотип
        const logoWidthInput = document.querySelector('input[name="settings[system_logo_width]"]');
        const logoHeightInput = document.querySelector('input[name="settings[system_logo_height]"]');
        const logoImg = document.getElementById('previewLogoImg');
        const logoIcon = document.getElementById('previewLogoIcon');
        
        if (logoWidthInput && logoHeightInput) {
            const width = logoWidthInput.value || '32';
            const height = logoHeightInput.value || '32';
            
            if (currentLogoUrl && logoImg) {
                logoImg.style.display = 'block';
                logoIcon.style.display = 'none';
                logoImg.style.maxWidth = width + 'px';
                logoImg.style.maxHeight = height + 'px';
                console.log('Логотип обновлен, размер:', width + 'x' + height);
            } else if (logoIcon) {
                logoImg.style.display = 'none';
                logoIcon.style.display = 'block';
                logoIcon.style.fontSize = (parseInt(height) / 2) + 'px';
                console.log('Иконка обновлена, размер:', (parseInt(height) / 2) + 'px');
            }
        }

        // Дополнительные строки
        updateAdditionalLinesPreview();
    }

    // Обновление предпросмотра дополнительных строк
    function updateAdditionalLinesPreview() {
        const container = document.getElementById('previewAdditionalLines');
        container.innerHTML = '';
        
        document.querySelectorAll('.additional-line-item').forEach(function(item) {
            const textInput = item.querySelector('input[data-preview-target]');
            const sizeInput = item.querySelector('input[data-preview-size]');
            const opacityInput = item.querySelector('input[data-preview-opacity]');
            
            if (textInput && textInput.value) {
                const lineDiv = document.createElement('div');
                lineDiv.className = 'brand-preview-line';
                lineDiv.textContent = textInput.value;
                lineDiv.style.fontSize = (sizeInput ? sizeInput.value : '0.875') + 'rem';
                lineDiv.style.opacity = opacityInput ? opacityInput.value : '0.9';
                container.appendChild(lineDiv);
            }
        });
    }

    // Обработка загрузки нового логотипа
    const logoInput = document.getElementById('logoInput');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    currentLogoUrl = e.target.result;
                    const logoImg = document.getElementById('previewLogoImg');
                    const logoIcon = document.getElementById('previewLogoIcon');
                    logoImg.src = currentLogoUrl;
                    logoImg.style.display = 'block';
                    logoIcon.style.display = 'none';
                    updatePreview();
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Обработка удаления логотипа
    const deleteLogoCheckbox = document.getElementById('delete_logo');
    if (deleteLogoCheckbox) {
        deleteLogoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                currentLogoUrl = null;
                const logoImg = document.getElementById('previewLogoImg');
                const logoIcon = document.getElementById('previewLogoIcon');
                logoImg.style.display = 'none';
                logoIcon.style.display = 'block';
            } else {
                const currentLogo = @json($logoExists ? asset('storage/' . $currentLogo) : null);
                if (currentLogo) {
                    currentLogoUrl = currentLogo;
                    const logoImg = document.getElementById('previewLogoImg');
                    const logoIcon = document.getElementById('previewLogoIcon');
                    logoImg.src = currentLogoUrl;
                    logoImg.style.display = 'block';
                    logoIcon.style.display = 'none';
                }
            }
            updatePreview();
        });
    }

    // Добавление новой строки
    const addLineBtn = document.getElementById('addLineBtn');
    if (addLineBtn) {
        addLineBtn.addEventListener('click', function() {
            const container = document.getElementById('additionalLinesContainer');
            const index = lineCounter++;
            const lineHtml = `
                <div class="additional-line-item" data-line-index="${index}">
                    <div class="additional-line-item-header">
                        <strong>Строка ${index + 1}</strong>
                        <button type="button" class="btn btn-sm btn-danger remove-line-btn" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <input type="text" 
                           name="additional_lines[${index}][text]" 
                           class="form-control mb-2" 
                           value="" 
                           placeholder="Текст строки"
                           data-preview-target="line-${index}">
                    <div class="size-control-group">
                        <div>
                            <label class="small">Размер шрифта (rem)</label>
                            <input type="number" 
                                   name="additional_lines[${index}][font_size]" 
                                   class="form-control form-control-sm" 
                                   value="0.875" 
                                   step="0.1"
                                   min="0.5"
                                   max="2"
                                   data-preview-size="line-${index}">
                        </div>
                        <div>
                            <label class="small">Прозрачность (0-1)</label>
                            <input type="number" 
                                   name="additional_lines[${index}][opacity]" 
                                   class="form-control form-control-sm" 
                                   value="0.9" 
                                   step="0.1"
                                   min="0"
                                   max="1"
                                   data-preview-opacity="line-${index}">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', lineHtml);
            
            // Добавляем обработчики событий для новой строки
            attachLineEventListeners(container.lastElementChild);
            updatePreview();
        });
    }

    // Удаление строки
    function attachLineEventListeners(lineElement) {
        const textInput = lineElement.querySelector('input[data-preview-target]');
        const sizeInput = lineElement.querySelector('input[data-preview-size]');
        const opacityInput = lineElement.querySelector('input[data-preview-opacity]');
        const removeBtn = lineElement.querySelector('.remove-line-btn');
        
        if (textInput) {
            textInput.addEventListener('input', updatePreview);
        }
        if (sizeInput) {
            sizeInput.addEventListener('input', updatePreview);
        }
        if (opacityInput) {
            opacityInput.addEventListener('input', updatePreview);
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                lineElement.remove();
                updatePreview();
                updateAdditionalLinesInput();
            });
        }
    }

    // Обновление скрытого поля с данными дополнительных строк
    function updateAdditionalLinesInput() {
        const lines = [];
        document.querySelectorAll('.additional-line-item').forEach(function(item) {
            const textInput = item.querySelector('input[data-preview-target]');
            const sizeInput = item.querySelector('input[data-preview-size]');
            const opacityInput = item.querySelector('input[data-preview-opacity]');
            
            if (textInput && textInput.value) {
                lines.push({
                    text: textInput.value,
                    font_size: sizeInput ? sizeInput.value : '0.875',
                    opacity: opacityInput ? opacityInput.value : '0.9'
                });
            }
        });
        
        const hiddenInput = document.getElementById('additionalLinesInput');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(lines);
        }
    }

    // Инициализация обработчиков событий для всех полей
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Инициализация предпросмотра...');
        
        // Обработчики для основных полей настроек
        document.querySelectorAll('input[name^="settings["]').forEach(function(input) {
            input.addEventListener('input', function() {
                console.log('Изменено поле:', input.name, 'значение:', input.value);
                updatePreview();
            });
            input.addEventListener('change', function() {
                console.log('Изменено поле (change):', input.name, 'значение:', input.value);
                updatePreview();
            });
        });

        // Обработчики для полей размеров логотипа
        const logoWidthInput = document.querySelector('input[name="settings[system_logo_width]"]');
        const logoHeightInput = document.querySelector('input[name="settings[system_logo_height]"]');
        const textSizeInput = document.querySelector('input[name="settings[system_brand_text_size]"]');
        const nameInput = document.querySelector('input[name="settings[system_name]"]');
        
        if (logoWidthInput) {
            logoWidthInput.addEventListener('input', updatePreview);
            logoWidthInput.addEventListener('change', updatePreview);
        }
        if (logoHeightInput) {
            logoHeightInput.addEventListener('input', updatePreview);
            logoHeightInput.addEventListener('change', updatePreview);
        }
        if (textSizeInput) {
            textSizeInput.addEventListener('input', updatePreview);
            textSizeInput.addEventListener('change', updatePreview);
        }
        if (nameInput) {
            nameInput.addEventListener('input', updatePreview);
            nameInput.addEventListener('change', updatePreview);
        }

        // Обработчики для существующих дополнительных строк
        document.querySelectorAll('.additional-line-item').forEach(function(item) {
            attachLineEventListeners(item);
        });

        // Обновление скрытого поля перед отправкой формы
        const form = document.getElementById('settingsForm');
        if (form) {
            form.addEventListener('submit', function() {
                updateAdditionalLinesInput();
            });
        }

        // Инициализация предпросмотра
        @if($logoExists && $currentLogo)
            const logoImg = document.getElementById('previewLogoImg');
            if (logoImg) {
                logoImg.src = @json(asset('storage/' . $currentLogo));
                logoImg.style.display = 'block';
                logoImg.style.maxWidth = '32px';
                logoImg.style.maxHeight = '32px';
                const logoIcon = document.getElementById('previewLogoIcon');
                if (logoIcon) logoIcon.style.display = 'none';
                currentLogoUrl = @json(asset('storage/' . $currentLogo));
            }
        @endif
        
        // Инициализация дополнительных строк в предпросмотре
        @if(!empty($lines))
            @foreach($lines as $index => $line)
                @if(!empty($line['text']))
                    const previewLinesContainer = document.getElementById('previewAdditionalLines');
                    if (previewLinesContainer) {
                        const lineDiv = document.createElement('div');
                        lineDiv.className = 'brand-preview-line';
                        lineDiv.textContent = @json($line['text'] ?? '');
                        lineDiv.style.fontSize = @json($line['font_size'] ?? '0.875') + 'rem';
                        lineDiv.style.opacity = @json($line['opacity'] ?? '0.9');
                        lineDiv.style.color = 'rgba(255,255,255,' + @json($line['opacity'] ?? '0.9') + ')';
                        previewLinesContainer.appendChild(lineDiv);
                    }
                @endif
            @endforeach
        @endif
        
        // Вызываем обновление предпросмотра после инициализации
        setTimeout(function() {
            updatePreview();
            console.log('Предпросмотр инициализирован');
        }, 100);
    });
</script>
@endpush
@endsection

