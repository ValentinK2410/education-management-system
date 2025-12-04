@extends('layouts.app')

@section('title', $course->name)

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .course-hero {
        background: var(--primary-gradient);
        color: white;
        padding: 4rem 0 3rem;
        margin-bottom: 3rem;
        border-radius: 0 0 2rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .course-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .course-hero-content {
        position: relative;
        z-index: 2;
    }

    .course-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .course-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .course-badge a {
        color: white;
        text-decoration: none;
    }

    .course-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .course-description {
        font-size: 1.25rem;
        opacity: 0.95;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .course-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 2rem;
    }

    .info-item {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 1rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .info-item-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .info-item-label {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item-value {
        font-size: 1rem;
        font-weight: 600;
    }

    .content-section {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title i {
        color: #667eea;
    }

    .prerequisite-card {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid #f59e0b;
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.2s;
    }

    .prerequisite-card:hover {
        transform: translateY(-4px);
    }

    .prerequisite-card i {
        font-size: 2rem;
        color: #f59e0b;
        margin-bottom: 0.75rem;
    }

    .outcome-card {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: 2px solid #10b981;
        border-radius: 0.75rem;
        padding: 1.5rem;
        transition: transform 0.2s;
    }

    .outcome-card:hover {
        transform: translateY(-4px);
    }

    .outcome-card i {
        font-size: 1.5rem;
        color: #10b981;
        margin-right: 1rem;
    }

    .instructor-card {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .instructor-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .instructor-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .instructor-avatar-placeholder i {
        font-size: 3rem;
        color: white;
    }

    .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .rating-display {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .review-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .avatar-sm {
        width: 48px;
        height: 48px;
        font-size: 18px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .course-title {
            font-size: 2rem;
        }

        .course-description {
            font-size: 1rem;
        }

        .course-info-grid {
            grid-template-columns: 1fr;
        }

        .course-hero {
            padding: 2rem 0 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Course Hero Section -->
<div class="course-hero">
    <div class="container course-hero-content">
        <div class="course-badges">
            @if($course->program && $course->program->institution)
                <a href="{{ route('institutions.show', $course->program->institution) }}" class="course-badge">
                    <i class="fas fa-university me-1"></i>{{ $course->program->institution->name }}
                </a>
            @endif
            @if($course->program)
                <a href="{{ route('programs.show', $course->program) }}" class="course-badge">
                    <i class="fas fa-graduation-cap me-1"></i>{{ $course->program->name }}
                </a>
            @endif
            @if($course->credits)
                <span class="course-badge">
                    <i class="fas fa-star me-1"></i>{{ $course->credits }} кредитов
                </span>
            @endif
        </div>

        <h1 class="course-title">{{ $course->name }}</h1>
        <p class="course-description">{{ $course->description }}</p>

        <div class="course-info-grid">
            @if($course->code)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-code"></i></div>
                    <div class="info-item-label">Код курса</div>
                    <div class="info-item-value">{{ $course->code }}</div>
                </div>
            @endif

            @if($course->duration)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-item-label">Продолжительность</div>
                    <div class="info-item-value">{{ $course->duration }}</div>
                </div>
            @endif

            @if($course->schedule)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="info-item-label">Расписание</div>
                    <div class="info-item-value">{{ $course->schedule }}</div>
                </div>
            @endif

            @if($course->location)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-item-label">Место проведения</div>
                    <div class="info-item-value">{{ $course->location }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Prerequisites Section -->
    @if($course->prerequisites && count($course->prerequisites) > 0)
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-exclamation-triangle"></i>
                Предварительные требования
            </h2>
            <div class="row g-3">
                @foreach($course->prerequisites as $prerequisite)
                    <div class="col-md-4 col-sm-6">
                        <div class="prerequisite-card">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h6 class="mb-0 fw-bold">{{ $prerequisite }}</h6>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Learning Outcomes Section -->
    @if($course->learning_outcomes && count($course->learning_outcomes) > 0)
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-check-circle"></i>
                Результаты обучения
            </h2>
            <div class="row g-3">
                @foreach($course->learning_outcomes as $outcome)
                    <div class="col-md-6">
                        <div class="outcome-card d-flex align-items-center">
                            <i class="fas fa-check-circle"></i>
                            <h6 class="mb-0">{{ $outcome }}</h6>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Instructor Section -->
    @if($course->instructor)
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-user-tie"></i>
                Преподаватель
            </h2>
            <div class="instructor-card">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        @php
                            $hasAvatar = isset($course->instructor->avatar) && !empty($course->instructor->avatar);
                        @endphp
                        @if($hasAvatar)
                            <img src="{{ Storage::url($course->instructor->avatar) }}"
                                 class="instructor-avatar" alt="Avatar"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="instructor-avatar-placeholder mx-auto" style="display: none;">
                                <i class="fas fa-user"></i>
                            </div>
                        @else
                            <div class="instructor-avatar-placeholder mx-auto">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <h4 class="fw-bold mb-2">{{ $course->instructor->name }}</h4>
                        @if($course->instructor->bio)
                            <p class="text-muted mb-3">{{ $course->instructor->bio }}</p>
                        @endif
                        <div class="d-flex gap-2 flex-wrap">
                            @if($course->instructor->email)
                                <a href="mailto:{{ $course->instructor->email }}" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </a>
                            @endif
                            @if(Route::has('instructors.show'))
                                <a href="{{ route('instructors.show', $course->instructor) }}" class="btn btn-primary">
                                    <i class="fas fa-user me-1"></i>Профиль преподавателя
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reviews Section -->
    <div class="content-section">
        <div class="reviews-header">
            <h2 class="section-title mb-0">
                <i class="fas fa-star"></i>
                Отзывы о курсе
            </h2>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                @if($course->reviews_count > 0)
                    <div class="rating-display">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= round($course->average_rating) ? 'text-warning' : 'text-muted' }}"></i>
                        @endfor
                        <span class="fw-bold ms-2">{{ number_format($course->average_rating, 1) }}</span>
                        <span class="text-muted ms-1">({{ $course->reviews_count }} отзывов)</span>
                    </div>
                @endif
                @auth
                    @php
                        $userReview = $course->reviews()->where('user_id', auth()->id())->first();
                    @endphp
                    @if(!$userReview)
                        <a href="{{ route('reviews.create', $course) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Оставить отзыв
                        </a>
                    @else
                        <a href="{{ route('reviews.edit', $userReview) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Редактировать отзыв
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-1"></i>Войти для отзыва
                    </a>
                @endauth
            </div>
        </div>

        @if($course->approvedReviews->count() > 0)
            <div class="row g-4">
                @foreach($course->approvedReviews->take(6) as $review)
                    <div class="col-lg-6">
                        <div class="review-card">
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
                            <p class="mb-0 text-muted">{{ $review->comment }}</p>
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
                <h5 class="text-muted mb-2">Пока нет отзывов</h5>
                <p class="text-muted mb-4">Станьте первым, кто оставит отзыв об этом курсе</p>
                @auth
                    <a href="{{ route('reviews.create', $course) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Оставить отзыв
                    </a>
                @endauth
            </div>
        @endif
    </div>
</div>

<script>
function showAllReviews() {
    alert('Функция показа всех отзывов будет реализована в следующих версиях');
}
</script>
@endsection
