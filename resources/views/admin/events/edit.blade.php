@extends('layouts.admin')

@section('title', 'Редактировать событие - ' . $event->title)

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Редактировать событие
                </h1>
                <p class="text-muted mb-0">{{ $event->title }}</p>
            </div>
            <div>
                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-outline-primary me-2">
                    <i class="fas fa-eye me-1"></i>
                    Просмотр
                </a>
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Назад к списку
                </a>
            </div>
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

                        <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <!-- Название -->
                            <div class="mb-4">
                                <label for="title" class="form-label">
                                    <strong>Название события</strong> <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $event->title) }}" 
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
                                          placeholder="Краткое описание события (до 500 символов)">{{ old('description', $event->description) }}</textarea>
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
                                          placeholder="Подробное описание события, программа, требования к участникам и т.д.">{{ old('content', $event->content) }}</textarea>
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
                                           value="{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}" 
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
                                           value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d\TH:i') : '') }}">
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
                                       value="{{ old('location', $event->location) }}" 
                                       placeholder="Адрес или название места проведения">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Текущее изображение -->
                            @if($event->image)
                                <div class="mb-4">
                                    <label class="form-label">
                                        <strong>Текущее изображение</strong>
                                    </label>
                                    <div class="mb-3">
                                        <img src="{{ Storage::url($event->image) }}" 
                                             alt="{{ $event->title }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px; max-height: 200px;">
                                    </div>
                                </div>
                            @endif

                            <!-- Новое изображение -->
                            <div class="mb-4">
                                <label for="image" class="form-label">
                                    <strong>{{ $event->image ? 'Заменить изображение' : 'Изображение события' }}</strong>
                                </label>
                                <input type="file" 
                                       name="image" 
                                       id="image" 
                                       class="form-control @error('image') is-invalid @enderror" 
                                       accept="image/*">
                                <div class="form-text">
                                    Поддерживаемые форматы: JPEG, PNG, JPG, GIF. Максимальный размер: 2MB
                                    @if($event->image)
                                        <br><strong>Внимание:</strong> Загрузка нового изображения заменит текущее
                                    @endif
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
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-save me-1"></i>
                                        Сохранить изменения
                                    </button>
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            onclick="confirmDelete()">
                                        <i class="fas fa-trash me-1"></i>
                                        Удалить событие
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Форма удаления -->
                        <form id="delete-form" action="{{ route('admin.events.destroy', $event) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
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
                                   {{ old('is_published', $event->is_published) ? 'checked' : '' }}>
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
                                   {{ old('is_featured', $event->is_featured) ? 'checked' : '' }}>
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
                                   value="{{ old('max_participants', $event->max_participants) }}" 
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
                                       value="{{ old('price', $event->price) }}" 
                                       min="0" 
                                       step="0.01" 
                                       placeholder="0.00">
                                <select name="currency" 
                                        class="form-select @error('currency') is-invalid @enderror">
                                    <option value="RUB" {{ old('currency', $event->currency) == 'RUB' ? 'selected' : '' }}>₽</option>
                                    <option value="USD" {{ old('currency', $event->currency) == 'USD' ? 'selected' : '' }}>$</option>
                                    <option value="EUR" {{ old('currency', $event->currency) == 'EUR' ? 'selected' : '' }}>€</option>
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
                                   value="{{ old('registration_url', $event->registration_url) }}" 
                                   placeholder="https://example.com/register">
                            @error('registration_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Информация о событии -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Информация о событии</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Создано</h6>
                                    <small>{{ $event->created_at->format('d.m.Y') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Обновлено</h6>
                                <small>{{ $event->updated_at->format('d.m.Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Вы уверены, что хотите удалить это событие? Это действие нельзя отменить.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection
