@extends('layouts.admin')

@section('title', 'События')

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    События
                </h1>
                <p class="text-muted mb-0">Управление событиями и мероприятиями</p>
            </div>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Добавить событие
            </a>
        </div>
    </div>

    <div class="content-wrapper">
        <!-- Фильтры -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">Все статусы</option>
                            <option value="published">Опубликованные</option>
                            <option value="draft">Черновики</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="featuredFilter">
                            <option value="">Все события</option>
                            <option value="featured">Рекомендуемые</option>
                            <option value="regular">Обычные</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="timeFilter">
                            <option value="">Все периоды</option>
                            <option value="upcoming">Предстоящие</option>
                            <option value="past">Прошедшие</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                            <i class="fas fa-refresh me-1"></i>
                            Сбросить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $events->where('is_published', true)->count() }}</h4>
                                <small>Опубликовано</small>
                            </div>
                            <i class="fas fa-eye fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $events->where('is_published', false)->count() }}</h4>
                                <small>Черновики</small>
                            </div>
                            <i class="fas fa-edit fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $events->where('is_featured', true)->count() }}</h4>
                                <small>Рекомендуемые</small>
                            </div>
                            <i class="fas fa-star fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $events->where('start_date', '>', now())->count() }}</h4>
                                <small>Предстоящие</small>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Таблица событий -->
        <div class="card">
            <div class="card-body">
                @if($events->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Событие</th>
                                    <th>Дата проведения</th>
                                    <th>Место</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $event)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($event->image)
                                                    <img src="{{ Storage::url($event->image) }}" 
                                                         alt="{{ $event->title }}" 
                                                         class="rounded me-3" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-calendar text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-1">{{ $event->title }}</h6>
                                                    <small class="text-muted">
                                                        {{ Str::limit($event->description, 50) }}
                                                    </small>
                                                    @if($event->is_featured)
                                                        <span class="badge bg-warning ms-2">
                                                            <i class="fas fa-star me-1"></i>
                                                            Рекомендуемое
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $event->start_date->format('d.m.Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $event->start_date->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($event->location)
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ Str::limit($event->location, 30) }}
                                            @else
                                                <span class="text-muted">Не указано</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($event->is_published)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>
                                                    Опубликовано
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Черновик
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.events.show', $event) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.events.edit', $event) }}" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.events.toggle-published', $event) }}" 
                                                      method="POST" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm {{ $event->is_published ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                                            title="{{ $event->is_published ? 'Снять с публикации' : 'Опубликовать' }}">
                                                        <i class="fas {{ $event->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.events.destroy', $event) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить это событие?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $events->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">События не найдены</h5>
                        <p class="text-muted">Создайте первое событие, чтобы начать работу</p>
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Добавить событие
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function resetFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('featuredFilter').value = '';
    document.getElementById('timeFilter').value = '';
    // Здесь можно добавить логику для применения фильтров
}
</script>
@endsection
