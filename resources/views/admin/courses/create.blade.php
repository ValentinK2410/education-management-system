@extends('layouts.admin')

@section('title', 'Создать курс')
@section('page-title', 'Создать курс')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>Создать курс
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название курса *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Код курса</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code') }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject_id" class="form-label">Предмет *</label>
                                    <select class="form-select @error('subject_id') is-invalid @enderror"
                                            id="subject_id" name="subject_id" required>
                                        <option value="">Выберите предмет</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}"
                                                    {{ old('subject_id', $selectedSubjectId ?? '') == $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                                @if($subject->code)
                                                    ({{ $subject->code }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Предмет объединяет несколько курсов одной тематики</div>
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
                                                    {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
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
                                      id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Обложка курса</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Рекомендуемый размер: 1200x600px. Форматы: JPG, PNG, GIF, WebP. Максимальный размер: 2MB</div>
                            <div id="image-preview" class="mt-3" style="display: none;">
                                <img id="preview-img" src="" alt="Предпросмотр" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="credits" class="form-label">Количество кредитов</label>
                                    <input type="number" class="form-control @error('credits') is-invalid @enderror"
                                           id="credits" name="credits" value="{{ old('credits') }}" min="0">
                                    @error('credits')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="hours" class="form-label">Часы обучения</label>
                                    <input type="number" class="form-control @error('hours') is-invalid @enderror"
                                           id="hours" name="hours" value="{{ old('hours') }}" min="0">
                                    @error('hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">Семестр</label>
                                    <input type="number" class="form-control @error('semester') is-invalid @enderror"
                                           id="semester" name="semester" value="{{ old('semester') }}" min="1" max="12">
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
                                        <!-- Скрытое поле для отправки false когда чекбокс не отмечен -->
                                        <input type="hidden" name="is_paid" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid"
                                               value="1" {{ old('is_paid') ? 'checked' : '' }}>
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
                                        <option value="RUB" {{ old('currency', 'RUB') == 'RUB' ? 'selected' : '' }}>Рубль (RUB)</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>Доллар (USD)</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>Евро (EUR)</option>
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
                                               id="price" name="price" value="{{ old('price') }}"
                                               min="0" step="0.01" placeholder="0.00" disabled>
                                        <span class="input-group-text" id="currency-display">RUB</span>
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
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
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
                                <i class="fas fa-save me-2"></i>Создать курс
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
    priceInput.disabled = true;
    priceInput.placeholder = 'Бесплатный курс';

    // Предпросмотр изображения
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });
});
</script>
@endsection
