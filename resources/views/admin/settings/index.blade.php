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
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
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

                        @foreach($groups as $groupKey => $groupName)
                        <div class="settings-group">
                            <div class="settings-group-header">
                                <i class="fas fa-{{ $groupKey === 'moodle' ? 'graduation-cap' : ($groupKey === 'sso' ? 'key' : 'cog') }} me-2"></i>
                                {{ $groupName }}
                            </div>
                            <div class="settings-group-body">
                                @if(isset($settings[$groupKey]) && $settings[$groupKey]->count() > 0)
                                    @foreach($settings[$groupKey] as $setting)
                                    <div class="setting-item">
                                        <label class="setting-label">
                                            {{ $setting->label ?? $setting->key }}
                                        </label>
                                        @if($setting->description)
                                        <div class="setting-description">
                                            {{ $setting->description }}
                                        </div>
                                        @endif
                                        
                                        @if($setting->key === 'system_logo')
                                            <div class="mb-3">
                                                @php
                                                    $currentLogo = $setting->value;
                                                    $logoExists = $currentLogo && \Storage::disk('public')->exists($currentLogo);
                                                @endphp
                                                @if($logoExists)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $currentLogo) }}" alt="Текущий логотип" style="max-height: 64px; max-width: 200px; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px;">
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
                                                    class="form-control" 
                                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp">
                                                <small class="text-muted">Рекомендуемый размер: до 200x64px. Форматы: JPEG, PNG, GIF, SVG, WebP. Максимальный размер: 2MB</small>
                                            </div>
                                        @elseif($setting->type === 'text')
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
</script>
@endpush
@endsection

