@extends('layouts.admin')

@section('title', 'Редактировать курс')
@section('page-title', 'Редактировать курс')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Редактировать курс
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.courses.update', $course) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название курса *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $course->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Код курса</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code', $course->code) }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="program_id" class="form-label">Образовательная программа *</label>
                                    <select class="form-select @error('program_id') is-invalid @enderror"
                                            id="program_id" name="program_id" required>
                                        <option value="">Выберите программу</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}"
                                                    {{ old('program_id', $course->program_id) == $program->id ? 'selected' : '' }}>
                                                {{ $program->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('program_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="instructor_id" class="form-label">Преподаватель</label>
                                    <select class="form-select @error('instructor_id') is-invalid @enderror"
                                            id="instructor_id" name="instructor_id">
                                        <option value="">Выберите преподавателя</option>
                                        @foreach($instructors as $instructor)
                                            <option value="{{ $instructor->id }}"
                                                    {{ old('instructor_id', $course->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                                {{ $instructor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('instructor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание курса</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="credits" class="form-label">Количество кредитов</label>
                                    <input type="number" class="form-control @error('credits') is-invalid @enderror"
                                           id="credits" name="credits" value="{{ old('credits', $course->credits) }}" min="0">
                                    @error('credits')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="hours" class="form-label">Часы обучения</label>
                                    <input type="number" class="form-control @error('hours') is-invalid @enderror"
                                           id="hours" name="hours" value="{{ old('hours', $course->hours) }}" min="0">
                                    @error('hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">Семестр</label>
                                    <input type="number" class="form-control @error('semester') is-invalid @enderror"
                                           id="semester" name="semester" value="{{ old('semester', $course->semester) }}" min="1" max="12">
                                    @error('semester')
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
                                        <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid"
                                               value="1" {{ old('is_paid', $course->is_paid) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_paid">
                                            Платный курс
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Валюта</label>
                                    <select class="form-select @error('currency') is-invalid @enderror"
                                            id="currency" name="currency">
                                        <option value="RUB" {{ old('currency', $course->currency) == 'RUB' ? 'selected' : '' }}>Рубль (RUB)</option>
                                        <option value="USD" {{ old('currency', $course->currency) == 'USD' ? 'selected' : '' }}>Доллар (USD)</option>
                                        <option value="EUR" {{ old('currency', $course->currency) == 'EUR' ? 'selected' : '' }}>Евро (EUR)</option>
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
                                    <label for="price" class="form-label">Цена курса</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price', $course->price) }}"
                                               min="0" step="0.01" placeholder="0.00">
                                        <span class="input-group-text" id="currency-display">
                                            {{ old('currency', $course->currency) ?? 'RUB' }}
                                        </span>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Оставьте пустым для бесплатного курса</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', $course->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Курс активен
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
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
document.addEventListener('DOMContentLoaded', function() {
    const currencySelect = document.getElementById('currency');
    const currencyDisplay = document.getElementById('currency-display');
    const isPaidCheckbox = document.getElementById('is_paid');
    const priceInput = document.getElementById('price');

    // Обновление отображения валюты
    currencySelect.addEventListener('change', function() {
        currencyDisplay.textContent = this.value;
    });

    // Управление полем цены в зависимости от чекбокса "Платный курс"
    isPaidCheckbox.addEventListener('change', function() {
        if (this.checked) {
            priceInput.disabled = false;
            priceInput.placeholder = '0.00';
        } else {
            priceInput.disabled = true;
            priceInput.value = '';
            priceInput.placeholder = 'Бесплатный курс';
        }
    });

    // Инициализация состояния при загрузке страницы
    if (!isPaidCheckbox.checked) {
        priceInput.disabled = true;
        priceInput.placeholder = 'Бесплатный курс';
    }
});
</script>
@endsection
