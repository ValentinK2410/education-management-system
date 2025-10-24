@extends('layouts.admin')

@section('title', 'Отзывы на модерации')

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Отзывы на модерации
                </h1>
                <p class="text-muted mb-0">Отзывы, ожидающие одобрения</p>
            </div>
            <div>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-primary me-2">
                    <i class="fas fa-list me-1"></i>
                    Все отзывы
                </a>
                <a href="{{ route('admin.reviews.approved') }}" class="btn btn-outline-success">
                    <i class="fas fa-check me-1"></i>
                    Одобренные
                </a>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $reviews->count() }}</h4>
                                <small>На модерации</small>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
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
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $reviews->where('rating', '>=', 4)->count() }}</h4>
                                <small>Положительные (4-5)</small>
                            </div>
                            <i class="fas fa-thumbs-up fa-2x opacity-75"></i>
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
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-muted">Нет отзывов на модерации</h5>
                        <p class="text-muted">Все отзывы обработаны</p>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>
                            Посмотреть все отзывы
                        </a>
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
@endsection
