@extends('layouts.admin')

@section('title', $event->title)

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    {{ $event->title }}
                </h1>
                <p class="text-muted mb-0">
                    @if($event->is_published)
                        <span class="badge bg-success me-2">
                            <i class="fas fa-check me-1"></i>
                            Опубликовано
                        </span>
                    @else
                        <span class="badge bg-secondary me-2">
                            <i class="fas fa-edit me-1"></i>
                            Черновик
                        </span>
                    @endif
                    @if($event->is_featured)
                        <span class="badge bg-warning">
                            <i class="fas fa-star me-1"></i>
                            Рекомендуемое
                        </span>
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-1"></i>
                    Редактировать
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
                <!-- Основная информация -->
                <div class="card mb-4">
                    <div class="card-body">
                        @if($event->image)
                            <div class="text-center mb-4">
                                <img src="{{ Storage::url($event->image) }}" 
                                     alt="{{ $event->title }}" 
                                     class="img-fluid rounded" 
                                     style="max-height: 400px;">
                            </div>
                        @endif

                        @if($event->description)
                            <div class="mb-4">
                                <h5>Краткое описание</h5>
                                <p class="text-muted">{{ $event->description }}</p>
                            </div>
                        @endif

                        @if($event->content)
                            <div class="mb-4">
                                <h5>Подробное содержание</h5>
                                <div class="content-text">
                                    {!! nl2br(e($event->content)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Действия -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Действия</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <form action="{{ route('admin.events.toggle-published', $event) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="btn {{ $event->is_published ? 'btn-outline-secondary' : 'btn-success' }} w-100">
                                        <i class="fas {{ $event->is_published ? 'fa-eye-slash' : 'fa-eye' }} me-1"></i>
                                        {{ $event->is_published ? 'Снять с публикации' : 'Опубликовать' }}
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6 mb-3">
                                <form action="{{ route('admin.events.toggle-featured', $event) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="btn {{ $event->is_featured ? 'btn-outline-warning' : 'btn-warning' }} w-100">
                                        <i class="fas fa-star me-1"></i>
                                        {{ $event->is_featured ? 'Убрать из рекомендуемых' : 'Добавить в рекомендуемые' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-edit me-1"></i>
                                    Редактировать
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="button" 
                                        class="btn btn-danger w-100" 
                                        onclick="confirmDelete()">
                                    <i class="fas fa-trash me-1"></i>
                                    Удалить событие
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Форма удаления -->
                <form id="delete-form" action="{{ route('admin.events.destroy', $event) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <div class="col-lg-4">
                <!-- Детали события -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Детали события</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Дата и время начала</h6>
                            <p class="mb-0">
                                <i class="fas fa-calendar me-2"></i>
                                {{ $event->start_date->format('d.m.Y') }}
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $event->start_date->format('H:i') }}
                            </small>
                        </div>

                        @if($event->end_date)
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Дата и время окончания</h6>
                                <p class="mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    {{ $event->end_date->format('d.m.Y') }}
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $event->end_date->format('H:i') }}
                                </small>
                            </div>
                        @endif

                        @if($event->location)
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Место проведения</h6>
                                <p class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    {{ $event->location }}
                                </p>
                            </div>
                        @endif

                        @if($event->max_participants)
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Максимальное количество участников</h6>
                                <p class="mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    {{ $event->max_participants }} человек
                                </p>
                            </div>
                        @endif

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Цена участия</h6>
                            <p class="mb-0">
                                <i class="fas fa-ruble-sign me-2"></i>
                                @if($event->isFree())
                                    <span class="text-success">Бесплатно</span>
                                @else
                                    {{ $event->formatted_price }}
                                @endif
                            </p>
                        </div>

                        @if($event->registration_url)
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Регистрация</h6>
                                <a href="{{ $event->registration_url }}" 
                                   target="_blank" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    Перейти к регистрации
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Статус события -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Статус события</h5>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $status = $event->status;
                            $statusClass = match($status) {
                                'upcoming' => 'success',
                                'ongoing' => 'warning',
                                'past' => 'secondary',
                                default => 'secondary'
                            };
                            $statusText = match($status) {
                                'upcoming' => 'Предстоящее',
                                'ongoing' => 'Проходит сейчас',
                                'past' => 'Завершено',
                                default => 'Неизвестно'
                            };
                        @endphp
                        
                        <div class="mb-3">
                            <span class="badge bg-{{ $statusClass }} fs-6">
                                {{ $statusText }}
                            </span>
                        </div>

                        @if($status === 'upcoming')
                            <p class="text-muted mb-0">
                                До начала события: 
                                <strong>{{ $event->days_until_event }} дн.</strong>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Информация о создании -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Информация</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Создано</h6>
                                    <small>{{ $event->created_at->format('d.m.Y') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $event->created_at->format('H:i') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Обновлено</h6>
                                <small>{{ $event->updated_at->format('d.m.Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $event->updated_at->format('H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.content-text {
    line-height: 1.6;
    white-space: pre-wrap;
}
</style>

<script>
function confirmDelete() {
    if (confirm('Вы уверены, что хотите удалить это событие? Это действие нельзя отменить.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection
