@extends('layouts.admin')

@section('title', 'Одобренные отзывы')

@section('content')
<div class="main-content">
    <div class="admin-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-check me-2"></i>
                    Одобренные отзывы
                </h1>
                <p class="text-muted mb-0">Опубликованные отзывы пользователей</p>
            </div>
            <div>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-primary me-2">
                    <i class="fas fa-list me-1"></i>
                    Все отзывы
                </a>
                <a href="{{ route('admin.reviews.pending') }}" class="btn btn-outline-warning">
                    <i class="fas fa-clock me-1"></i>
                    На модерации
                </a>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $reviews->count() }}</h4>
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
                                <h4 class="mb-0">{{ $reviews->where('rating', '>=', 4)->count() }}</h4>
                                <small>Положительные</small>
                            </div>
                            <i class="fas fa-thumbs-up fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ round($reviews->avg('rating'), 1) }}</h4>
                                <small>Средний рейтинг</small>
                            </div>
                            <i class="fas fa-star-half-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
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
                        <select class="form-select" id="courseFilter">
                            <option value="">Все курсы</option>
                            @foreach($reviews->pluck('course.name')->unique() as $courseName)
                                <option value="{{ $courseName }}">{{ $courseName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchInput" placeholder="Поиск по пользователю">
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
                                    <th>Дата одобрения</th>
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
                                                <strong>{{ $review->updated_at->format('d.m.Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $review->updated_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.reviews.show', $review) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.reviews.reject', $review) }}" 
                                                      method="POST" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            title="Отклонить"
                                                            onclick="return confirm('Отклонить этот отзыв? Он будет скрыт с сайта.')">
                                                        <i class="fas fa-times"></i>
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
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Нет одобренных отзывов</h5>
                        <p class="text-muted">Одобренные отзывы будут отображаться здесь</p>
                        <a href="{{ route('admin.reviews.pending') }}" class="btn btn-primary">
                            <i class="fas fa-clock me-1"></i>
                            Посмотреть отзывы на модерации
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

<script>
function resetFilters() {
    document.getElementById('ratingFilter').value = '';
    document.getElementById('courseFilter').value = '';
    document.getElementById('searchInput').value = '';
    // Здесь можно добавить логику для применения фильтров
}
</script>
@endsection
