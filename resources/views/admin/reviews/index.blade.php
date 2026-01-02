@extends('layouts.admin')

@section('title', 'Отзывы')

@push('styles')
<style>
    /* Заголовки */
    [data-theme="dark"] .main-content h1,
    [data-theme="dark"] .main-content h3,
    [data-theme="dark"] .main-content h4,
    [data-theme="dark"] .main-content h5,
    [data-theme="dark"] .main-content h6 {
        color: var(--text-color) !important;
    }

    /* Текст */
    [data-theme="dark"] .main-content .text-muted {
        color: #94a3b8 !important;
        opacity: 0.8;
    }

    [data-theme="dark"] .main-content p {
        color: var(--text-color) !important;
    }

    /* Карточки */
    [data-theme="dark"] .main-content .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card-body {
        background: var(--card-bg) !important;
        color: var(--text-color) !important;
    }

    /* Статистические карточки */
    [data-theme="dark"] .main-content .card.bg-primary {
        background-color: rgba(99, 102, 241, 0.2) !important;
        border-color: rgba(99, 102, 241, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-primary.text-white,
    [data-theme="dark"] .main-content .card.bg-primary .text-white {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-primary h4,
    [data-theme="dark"] .main-content .card.bg-primary small {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-warning {
        background-color: rgba(245, 158, 11, 0.2) !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-warning.text-white,
    [data-theme="dark"] .main-content .card.bg-warning .text-white {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-warning h4,
    [data-theme="dark"] .main-content .card.bg-warning small {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-success {
        background-color: rgba(16, 185, 129, 0.2) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-success.text-white,
    [data-theme="dark"] .main-content .card.bg-success .text-white {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-success h4,
    [data-theme="dark"] .main-content .card.bg-success small {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-info {
        background-color: rgba(59, 130, 246, 0.2) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-info.text-white,
    [data-theme="dark"] .main-content .card.bg-info .text-white {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .card.bg-info h4,
    [data-theme="dark"] .main-content .card.bg-info small {
        color: var(--text-color) !important;
    }

    /* Формы */
    [data-theme="dark"] .main-content .form-select,
    [data-theme="dark"] .main-content .form-control {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .form-select:focus,
    [data-theme="dark"] .main-content .form-control:focus {
        background-color: var(--card-bg) !important;
        border-color: #6366f1 !important;
        color: var(--text-color) !important;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25) !important;
    }

    [data-theme="dark"] .main-content .form-control::placeholder {
        color: #94a3b8 !important;
        opacity: 0.6;
    }

    /* Кнопки */
    [data-theme="dark"] .main-content .btn-warning {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .main-content .btn-success {
        background-color: rgba(16, 185, 129, 0.8) !important;
        border-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .main-content .btn-outline-secondary {
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .btn-outline-secondary:hover {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .btn-outline-primary {
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }

    [data-theme="dark"] .main-content .btn-outline-primary:hover {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: white !important;
    }

    [data-theme="dark"] .main-content .btn-outline-success {
        border-color: rgba(16, 185, 129, 0.8) !important;
        color: rgba(16, 185, 129, 0.8) !important;
    }

    [data-theme="dark"] .main-content .btn-outline-success:hover {
        background-color: rgba(16, 185, 129, 0.8) !important;
        border-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .main-content .btn-outline-warning {
        border-color: var(--warning-color) !important;
        color: var(--warning-color) !important;
    }

    [data-theme="dark"] .main-content .btn-outline-warning:hover {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .main-content .btn-outline-danger {
        border-color: var(--danger-color) !important;
        color: var(--danger-color) !important;
    }

    [data-theme="dark"] .main-content .btn-outline-danger:hover {
        background-color: var(--danger-color) !important;
        border-color: var(--danger-color) !important;
        color: white !important;
    }

    /* Таблица */
    [data-theme="dark"] .main-content .table {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .table thead th {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .table tbody td {
        border-color: var(--border-color) !important;
        background-color: transparent !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .main-content .table-hover tbody tr:hover {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .main-content .table-hover tbody tr:hover td {
        background-color: var(--dark-bg) !important;
        color: var(--text-color) !important;
    }

    /* Бейджи */
    [data-theme="dark"] .main-content .badge.bg-success {
        background-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .main-content .badge.bg-warning {
        background-color: rgba(245, 158, 11, 0.8) !important;
        color: #1e293b !important;
    }

    /* Рейтинг звезды */
    [data-theme="dark"] .main-content .rating-display .text-warning {
        color: var(--warning-color) !important;
    }

    [data-theme="dark"] .main-content .rating-display .text-muted {
        color: #475569 !important;
    }

    /* Аватар */
    [data-theme="dark"] .main-content .avatar-sm {
        background-color: var(--primary-color) !important;
        color: white !important;
    }

    /* Пустое состояние */
    [data-theme="dark"] .main-content .text-center .text-muted {
        color: #94a3b8 !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-star me-2"></i>
                    Отзывы
                </h1>
                <p class="text-muted mb-0">Модерация отзывов пользователей</p>
            </div>
            <div>
                <a href="{{ route('admin.reviews.pending') }}" class="btn btn-warning me-2">
                    <i class="fas fa-clock me-1"></i>
                    На модерации
                </a>
                <a href="{{ route('admin.reviews.approved') }}" class="btn btn-success">
                    <i class="fas fa-check me-1"></i>
                    Одобренные
                </a>
            </div>
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
                            <option value="approved">Одобренные</option>
                            <option value="pending">На модерации</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="ratingFilter">
                            <option value="">Все рейтинги</option>
                            <option value="5">5 звезд</option>
                            <option value="4">4 звезды</option>
                            <option value="3">3 звезды</option>
                            <option value="2">2 звезды</option>
                            <option value="1">1 звезда</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchInput" placeholder="Поиск по курсу или пользователю">
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
                                <h4 class="mb-0">{{ $reviews->where('is_approved', true)->count() }}</h4>
                                <small>Одобрено</small>
                            </div>
                            <i class="fas fa-check fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $reviews->where('is_approved', false)->count() }}</h4>
                                <small>На модерации</small>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $reviews->where('rating', 5)->count() }}</h4>
                                <small>5 звезд</small>
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
                                <h4 class="mb-0">{{ round($reviews->where('is_approved', true)->avg('rating'), 1) }}</h4>
                                <small>Средний рейтинг</small>
                            </div>
                            <i class="fas fa-star-half-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Таблица отзывов -->
        <div class="card">
            <div class="card-body">
                @if($reviews->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Отзыв</th>
                                    <th>Курс</th>
                                    <th>Пользователь</th>
                                    <th>Рейтинг</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviews as $review)
                                    <tr>
                                        <td>
                                            <div class="review-preview">
                                                <p class="mb-1 fw-bold">{{ Str::limit($review->comment, 80) }}</p>
                                                @if(strlen($review->comment) > 80)
                                                    <small class="text-muted">Нажмите "Просмотр" для полного текста</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">{{ $review->course->name }}</h6>
                                                <small class="text-muted">
                                                    {{ $review->course->program->institution->name ?? 'Не указано' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ substr($review->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $review->user->name }}</h6>
                                                    <small class="text-muted">{{ $review->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="rating-display">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                                <span class="ms-1 fw-bold">{{ $review->rating }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($review->is_approved)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>
                                                    Одобрен
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>
                                                    На модерации
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $review->created_at->format('d.m.Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $review->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.reviews.show', $review) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(!$review->is_approved)
                                                    <form action="{{ route('admin.reviews.approve', $review) }}" 
                                                          method="POST" 
                                                          style="display: inline;">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                title="Одобрить"
                                                                onclick="return confirm('Одобрить этот отзыв?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.reviews.reject', $review) }}" 
                                                          method="POST" 
                                                          style="display: inline;">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-warning" 
                                                                title="Отклонить"
                                                                onclick="return confirm('Отклонить этот отзыв?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.reviews.destroy', $review) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот отзыв?')">
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
                        {{ $reviews->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Отзывы не найдены</h5>
                        <p class="text-muted">Пользователи еще не оставили отзывы</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.rating-display {
    display: flex;
    align-items: center;
}

.review-preview {
    max-width: 300px;
}
</style>

<script>
function resetFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('ratingFilter').value = '';
    document.getElementById('searchInput').value = '';
    // Здесь можно добавить логику для применения фильтров
}
</script>
@endsection
