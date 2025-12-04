@extends('layouts.app')

@section('title', $course->name)

@section('content')
<div class="container py-5">
    <!-- Course Header -->
    <div class="row mb-5">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('institutions.show', $course->program->institution) }}" class="text-decoration-none">
                    <span class="badge bg-primary me-2">{{ $course->program->institution->name }}</span>
                </a>
                <a href="{{ route('programs.show', $course->program) }}" class="text-decoration-none">
                    <span class="badge bg-success me-2">{{ $course->program->name }}</span>
                </a>
                @if($course->credits)
                    <span class="badge bg-warning">{{ $course->credits }} кредитов</span>
                @endif
            </div>

            <h1 class="fw-bold mb-3">{{ $course->name }}</h1>
            <p class="lead text-muted mb-4">{{ $course->description }}</p>

            <div class="row">
                @if($course->code)
                    <div class="col-md-3 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-code me-2"></i>Код курса
                        </h6>
                        <p class="mb-0">{{ $course->code }}</p>
                    </div>
                @endif

                @if($course->duration)
                    <div class="col-md-3 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-clock me-2"></i>Продолжительность
                        </h6>
                        <p class="mb-0">{{ $course->duration }}</p>
                    </div>
                @endif

                @if($course->schedule)
                    <div class="col-md-3 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-calendar me-2"></i>Расписание
                        </h6>
                        <p class="mb-0">{{ $course->schedule }}</p>
                    </div>
                @endif

                @if($course->location)
                    <div class="col-md-3 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-map-marker-alt me-2"></i>Место проведения
                        </h6>
                        <p class="mb-0">{{ $course->location }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Информация о курсе</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-university text-primary me-2"></i>
                            <a href="{{ route('institutions.show', $course->program->institution) }}" class="text-decoration-none">
                                {{ $course->program->institution->name }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-book text-primary me-2"></i>
                            <a href="{{ route('programs.show', $course->program) }}" class="text-decoration-none">
                                {{ $course->program->name }}
                            </a>
                        </li>
                        @if($course->instructor)
                            <li class="mb-2">
                                <i class="fas fa-user text-primary me-2"></i>
                                @if(Route::has('instructors.show'))
                                    <a href="{{ route('instructors.show', $course->instructor) }}" class="text-decoration-none">
                                        {{ $course->instructor->name }}
                                    </a>
                                @else
                                    <span>{{ $course->instructor->name }}</span>
                                @endif
                            </li>
                        @endif
                        @if($course->credits)
                            <li class="mb-2">
                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                {{ $course->credits }} кредитов
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Prerequisites Section -->
    @if($course->prerequisites && count($course->prerequisites) > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Предварительные требования</h3>
                <div class="row">
                    @foreach($course->prerequisites as $prerequisite)
                        <div class="col-md-4 mb-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                                    <h6 class="card-title">{{ $prerequisite }}</h6>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Learning Outcomes Section -->
    @if($course->learning_outcomes && count($course->learning_outcomes) > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Результаты обучения</h3>
                <div class="row">
                    @foreach($course->learning_outcomes as $outcome)
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                        <div>
                                            <h6 class="card-title mb-0">{{ $outcome }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Instructor Section -->
    @if($course->instructor)
        <div class="row">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Преподаватель</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                @php
                                    $hasAvatar = isset($course->instructor->avatar) && !empty($course->instructor->avatar);
                                @endphp
                                @if($hasAvatar)
                                    <img src="{{ Storage::url($course->instructor->avatar) }}"
                                         class="rounded-circle" width="100" height="100" alt="Avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                         style="width: 100px; height: 100px; display: none;">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                         style="width: 100px; height: 100px;">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <h5 class="card-title">{{ $course->instructor->name }}</h5>
                                @if($course->instructor->bio)
                                    <p class="card-text text-muted">{{ $course->instructor->bio }}</p>
                                @endif
                                <div class="d-flex gap-2">
                                    @if($course->instructor->email)
                                        <a href="mailto:{{ $course->instructor->email }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-envelope me-1"></i>Email
                                        </a>
                                    @endif
                                    @if(Route::has('instructors.show'))
                                        <a href="{{ route('instructors.show', $course->instructor) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-user me-1"></i>Профиль
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">
                    <i class="fas fa-star me-2 text-warning"></i>
                    Отзывы о курсе
                </h3>
                <div class="d-flex align-items-center">
                    @if($course->reviews_count > 0)
                        <div class="me-3">
                            <div class="d-flex align-items-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= round($course->average_rating) ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="ms-2 fw-bold">{{ number_format($course->average_rating, 1) }}</span>
                                <span class="text-muted ms-1">({{ $course->reviews_count }} отзывов)</span>
                            </div>
                        </div>
                    @endif
                    @auth
                        @php
                            $userReview = $course->reviews()->where('user_id', auth()->id())->first();
                        @endphp
                        @if(!$userReview)
                            <a href="{{ route('reviews.create', $course) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Оставить отзыв
                            </a>
                        @else
                            <a href="{{ route('reviews.edit', $userReview) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>
                                Редактировать отзыв
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Войти для отзыва
                        </a>
                    @endauth
                </div>
            </div>

            @if($course->approvedReviews->count() > 0)
                <div class="row">
                    @foreach($course->approvedReviews->take(6) as $review)
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                {{ substr($review->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $review->user->name }}</h6>
                                                <small class="text-muted">{{ $review->created_at->format('d.m.Y') }}</small>
                                            </div>
                                        </div>
                                        <div class="rating-display">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="card-text">{{ $review->comment }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($course->approvedReviews->count() > 6)
                    <div class="text-center mt-4">
                        <button class="btn btn-outline-primary" onclick="showAllReviews()">
                            <i class="fas fa-eye me-1"></i>
                            Показать все отзывы ({{ $course->approvedReviews->count() }})
                        </button>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Пока нет отзывов</h5>
                    <p class="text-muted">Станьте первым, кто оставит отзыв об этом курсе</p>
                    @auth
                        <a href="{{ route('reviews.create', $course) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Оставить отзыв
                        </a>
                    @endauth
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
}

.rating-display {
    display: flex;
    align-items: center;
}
</style>

<script>
function showAllReviews() {
    // Здесь можно добавить логику для показа всех отзывов
    // Например, через AJAX или модальное окно
    alert('Функция показа всех отзывов будет реализована в следующих версиях');
}
</script>
@endsection
