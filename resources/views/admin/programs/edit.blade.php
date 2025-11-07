@extends('layouts.admin')

@section('title', 'Редактировать программу')
@section('page-title', 'Редактировать образовательную программу')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Редактировать образовательную программу
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.programs.update', $program) }}" method="POST" onsubmit="enablePriceField()">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название программы *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $program->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Код программы</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $program->code) }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="institution_id" class="form-label">Учебное заведение *</label>
                            <select class="form-select @error('institution_id') is-invalid @enderror" 
                                    id="institution_id" name="institution_id" required>
                                <option value="">Выберите учебное заведение</option>
                                @foreach($institutions as $institution)
                                    <option value="{{ $institution->id }}" 
                                            {{ old('institution_id', $program->institution_id) == $institution->id ? 'selected' : '' }}>
                                        {{ $institution->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('institution_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание программы</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description', $program->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Продолжительность (месяцы)</label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                           id="duration" name="duration" value="{{ old('duration', $program->duration) }}" min="0">
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="credits" class="form-label">Количество кредитов</label>
                                    <input type="number" class="form-control @error('credits') is-invalid @enderror" 
                                           id="credits" name="credits" value="{{ old('credits', $program->credits) }}" min="0">
                                    @error('credits')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="degree_level" class="form-label">Уровень степени</label>
                                    <select class="form-select @error('degree_level') is-invalid @enderror"
                                            id="degree_level" name="degree_level">
                                        <option value="">Выберите уровень</option>
                                        @foreach(\App\Models\Program::degreeLevelOptions() as $value => $label)
                                            <option value="{{ $value }}" {{ old('degree_level', $program->degree_level) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('degree_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="language" class="form-label">Язык обучения *</label>
                                    <select class="form-select @error('language') is-invalid @enderror" 
                                            id="language" name="language" required>
                                        <option value="">Выберите язык</option>
                                        <option value="ru" {{ old('language', $program->language ?? 'ru') == 'ru' ? 'selected' : '' }}>Русский</option>
                                        <option value="en" {{ old('language', $program->language) == 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Локация</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $program->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tuition_fee" class="form-label">Стоимость обучения</label>
                                    <input type="number" class="form-control @error('tuition_fee') is-invalid @enderror" 
                                           id="tuition_fee" name="tuition_fee" value="{{ old('tuition_fee', $program->tuition_fee) }}" 
                                           min="0" step="0.01">
                                    @error('tuition_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Поля оплаты -->
                        <h5 class="mb-3">Настройки оплаты</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <!-- Скрытое поле для отправки false когда чекбокс не отмечен -->
                                        <input type="hidden" name="is_paid" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid" 
                                               value="1" {{ old('is_paid', $program->is_paid) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_paid">
                                            Платная программа
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Валюта</label>
                                    <select class="form-select @error('currency') is-invalid @enderror" 
                                            id="currency" name="currency">
                                        <option value="RUB" {{ old('currency', $program->currency) == 'RUB' ? 'selected' : '' }}>Рубль (RUB)</option>
                                        <option value="USD" {{ old('currency', $program->currency) == 'USD' ? 'selected' : '' }}>Доллар (USD)</option>
                                        <option value="EUR" {{ old('currency', $program->currency) == 'EUR' ? 'selected' : '' }}>Евро (EUR)</option>
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Цена программы</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" value="{{ old('price', $program->price) }}" 
                                               min="0" step="0.01" placeholder="0.00">
                                        <span class="input-group-text" id="currency-display">
                                            {{ old('currency', $program->currency) ?? 'RUB' }}
                                        </span>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Оставьте пустым для бесплатной программы</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $program->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Программа активна
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Назад к списку
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Функция для включения поля цены перед отправкой формы
function enablePriceField() {
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.disabled = false;
        console.log('Price field enabled before submit, value:', priceInput.value);
    }
    return true; // Продолжаем отправку формы
}

document.addEventListener('DOMContentLoaded', function() {
    const currencySelect = document.getElementById('currency');
    const currencyDisplay = document.getElementById('currency-display');
    const isPaidCheckbox = document.getElementById('is_paid');
    const priceInput = document.getElementById('price');

    // Обновление отображения валюты
    currencySelect.addEventListener('change', function() {
        currencyDisplay.textContent = this.value;
    });

    // Управление полем цены в зависимости от чекбокса "Платная программа"
    isPaidCheckbox.addEventListener('change', function() {
        if (this.checked) {
            priceInput.disabled = false;
            priceInput.placeholder = '0.00';
        } else {
            priceInput.disabled = true;
            priceInput.value = '';
            priceInput.placeholder = 'Бесплатная программа';
        }
    });

    // Инициализация состояния при загрузке страницы
    if (!isPaidCheckbox.checked) {
        priceInput.disabled = true;
        priceInput.placeholder = 'Бесплатная программа';
    }

    // Перед отправкой формы включаем все поля, чтобы они отправились
    document.querySelector('form').addEventListener('submit', function(e) {
        // Включаем поле цены перед отправкой
        priceInput.disabled = false;
        
        // Также убеждаемся, что все скрытые поля отправляются
        const hiddenField = document.querySelector('input[name="is_paid"][type="hidden"]');
        if (hiddenField && !isPaidCheckbox.checked) {
            hiddenField.value = '0';
        }
        
        console.log('Form submitting with price:', priceInput.value, 'is_paid:', isPaidCheckbox.checked);
    });
});
</script>
@endsection
