@extends('layouts.admin')

@section('title', 'Создать событие')

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Создать событие
                </h1>
                <p class="text-muted mb-0">Добавьте новое событие или мероприятие</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Назад к списку
            </a>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Основная информация</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Название -->
                            <div class="mb-4">
                                <label for="title" class="form-label">
                                    <strong>Название события</strong> <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" 
                                       placeholder="Введите название события"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Краткое описание -->
                            <div class="mb-4">
                                <label for="description" class="form-label">
                                    <strong>Краткое описание</strong>
                                </label>
                                <textarea name="description" 
                                          id="description" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" 
                                          placeholder="Краткое описание события (до 500 символов)">{{ old('description') }}</textarea>
                                <div class="form-text">Максимум 500 символов</div>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Подробное содержание -->
                            <div class="mb-4">
                                <label for="content" class="form-label">
                                    <strong>Подробное содержание</strong>
                                </label>
                                <textarea name="content" 
                                          id="content" 
                                          class="form-control @error('content') is-invalid @enderror" 
                                          rows="8" 
                                          placeholder="Подробное описание события, программа, требования к участникам и т.д.">{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Даты -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">
                                        <strong>Дата и время начала</strong> <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           name="start_date" 
                                           id="start_date" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           value="{{ old('start_date') }}" 
                                           required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">
                                        <strong>Дата и время окончания</strong>
                                    </label>
                                    <input type="datetime-local" 
                                           name="end_date" 
                                           id="end_date" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Место проведения -->
                            <div class="mb-4">
                                <label for="location" class="form-label">
                                    <strong>Место проведения</strong>
                                </label>
                                <input type="text" 
                                       name="location" 
                                       id="location" 
                                       class="form-control @error('location') is-invalid @enderror" 
                                       value="{{ old('location') }}" 
                                       placeholder="Адрес или название места проведения">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Изображение -->
                            <div class="mb-4">
                                <label for="image" class="form-label">
                                    <strong>Изображение события</strong>
                                </label>
                                <input type="file" 
                                       name="image" 
                                       id="image" 
                                       class="form-control @error('image') is-invalid @enderror" 
                                       accept="image/*">
                                <div class="form-text">
                                    Поддерживаемые форматы: JPEG, PNG, JPG, GIF. Максимальный размер: 2MB
                                </div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Кнопки -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Отмена
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Создать событие
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Настройки публикации -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Настройки публикации</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_published" 
                                   id="is_published" 
                                   value="1" 
                                   {{ old('is_published') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                <strong>Опубликовать событие</strong>
                            </label>
                            <div class="form-text">Событие будет видно на сайте</div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_featured" 
                                   id="is_featured" 
                                   value="1" 
                                   {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                <strong>Рекомендуемое событие</strong>
                            </label>
                            <div class="form-text">Будет выделено на главной странице</div>
                        </div>
                    </div>
                </div>

                <!-- Дополнительные настройки -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Дополнительные настройки</h5>
                    </div>
                    <div class="card-body">
                        <!-- Максимальное количество участников -->
                        <div class="mb-3">
                            <label for="max_participants" class="form-label">
                                <strong>Максимальное количество участников</strong>
                            </label>
                            <input type="number" 
                                   name="max_participants" 
                                   id="max_participants" 
                                   class="form-control @error('max_participants') is-invalid @enderror" 
                                   value="{{ old('max_participants') }}" 
                                   min="1" 
                                   placeholder="Не ограничено">
                            @error('max_participants')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Цена -->
                        <div class="mb-3">
                            <label for="price" class="form-label">
                                <strong>Цена участия</strong>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       name="price" 
                                       id="price" 
                                       class="form-control @error('price') is-invalid @enderror" 
                                       value="{{ old('price') }}" 
                                       min="0" 
                                       step="0.01" 
                                       placeholder="0.00">
                                <select name="currency" 
                                        class="form-select @error('currency') is-invalid @enderror">
                                    <option value="RUB" {{ old('currency', 'RUB') == 'RUB' ? 'selected' : '' }}>₽</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>$</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>€</option>
                                </select>
                            </div>
                            <div class="form-text">Оставьте пустым для бесплатного события</div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ссылка на регистрацию -->
                        <div class="mb-3">
                            <label for="registration_url" class="form-label">
                                <strong>Ссылка на регистрацию</strong>
                            </label>
                            <input type="url" 
                                   name="registration_url" 
                                   id="registration_url" 
                                   class="form-control @error('registration_url') is-invalid @enderror" 
                                   value="{{ old('registration_url') }}" 
                                   placeholder="https://example.com/register">
                            @error('registration_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Автоматическое заполнение времени окончания
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', function() {
        if (!endDateInput.value) {
            // Устанавливаем время окончания на 2 часа позже времени начала
            const startDate = new Date(this.value);
            startDate.setHours(startDate.getHours() + 2);
            
            // Форматируем дату для input[type="datetime-local"]
            const year = startDate.getFullYear();
            const month = String(startDate.getMonth() + 1).padStart(2, '0');
            const day = String(startDate.getDate()).padStart(2, '0');
            const hours = String(startDate.getHours()).padStart(2, '0');
            const minutes = String(startDate.getMinutes()).padStart(2, '0');
            
            endDateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    });
});
</script>
@endsection
