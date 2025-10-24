@extends('layouts.admin')

@section('title', 'Отзыв от ' . $review->user->name)

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-star me-2"></i>
                    Отзыв от {{ $review->user->name }}
                </h1>
                <p class="text-muted mb-0">{{ $review->course->name }}</p>
            </div>
            <div>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Назад к списку
                </a>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-8">
                <!-- Основная информация об отзыве -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Содержание отзыва</h5>
                    </div>
                    <div class="card-body">
                        <!-- Рейтинг -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Оценка</h6>
                            <div class="rating-display-large">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star fa-2x {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="ms-3 fs-4 fw-bold">{{ $review->rating }} из 5</span>
                            </div>
                        </div>

                        <!-- Комментарий -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Комментарий</h6>
                            <div class="review-content p-3 bg-light rounded">
                                {{ $review->comment }}
                            </div>
                        </div>

                        <!-- Действия модерации -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Действия модерации</h5>
                            </div>
                            <div class="card-body">
                                @if($review->is_approved)
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Этот отзыв одобрен и опубликован
                                    </div>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-warning"
                                                    onclick="return confirm('Отклонить этот отзыв? Он будет скрыт с сайта.')">
                                                <i class="fas fa-times me-1"></i>
                                                Отклонить отзыв
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                                              onsubmit="return confirm('Вы уверены, что хотите удалить этот отзыв?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i>
                                                Удалить отзыв
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock me-2"></i>
                                        Этот отзыв ожидает модерации
                                    </div>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-success"
                                                    onclick="return confirm('Одобрить этот отзыв? Он будет опубликован на сайте.')">
                                                <i class="fas fa-check me-1"></i>
                                                Одобрить отзыв
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                                              onsubmit="return confirm('Вы уверены, что хотите удалить этот отзыв?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i>
                                                Удалить отзыв
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Информация о пользователе -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Информация о пользователе</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                {{ substr($review->user->name, 0, 1) }}
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $review->user->name }}</h6>
                                <small class="text-muted">{{ $review->user->email }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Дата регистрации</h6>
                            <p class="mb-0">{{ $review->user->created_at->format('d.m.Y') }}</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Роль</h6>
                            <p class="mb-0">
                                @if($review->user->roles->count() > 0)
                                    @foreach($review->user->roles as $role)
                                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Роль не назначена</span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Количество отзывов</h6>
                            <p class="mb-0">{{ $review->user->reviews->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Информация о курсе -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Информация о курсе</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Название курса</h6>
                            <p class="mb-0">{{ $review->course->name }}</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Учебное заведение</h6>
                            <p class="mb-0">{{ $review->course->program->institution->name ?? 'Не указано' }}</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Программа</h6>
                            <p class="mb-0">{{ $review->course->program->name ?? 'Не указано' }}</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Общий рейтинг курса</h6>
                            <div class="rating-display">
                                @php
                                    $avgRating = $review->course->average_rating;
                                    $reviewsCount = $review->course->reviews_count;
                                @endphp
                                @if($avgRating > 0)
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($avgRating) ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                    <span class="ms-2">{{ number_format($avgRating, 1) }} ({{ $reviewsCount }} отзывов)</span>
                                @else
                                    <span class="text-muted">Нет отзывов</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('courses.show', $review->course) }}" 
                           class="btn btn-outline-primary btn-sm" 
                           target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>
                            Посмотреть курс
                        </a>
                    </div>
                </div>

                <!-- Информация об отзыве -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Информация об отзыве</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Дата создания</h6>
                            <p class="mb-0">{{ $review->created_at->format('d.m.Y H:i') }}</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Последнее обновление</h6>
                            <p class="mb-0">{{ $review->updated_at->format('d.m.Y H:i') }}</p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Статус</h6>
                            <p class="mb-0">
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
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Длина комментария</h6>
                            <p class="mb-0">{{ strlen($review->comment) }} символов</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 60px;
    height: 60px;
    font-size: 24px;
}

.rating-display-large {
    display: flex;
    align-items: center;
}

.rating-display {
    display: flex;
    align-items: center;
}

.review-content {
    line-height: 1.6;
    white-space: pre-wrap;
}
</style>
@endsection
