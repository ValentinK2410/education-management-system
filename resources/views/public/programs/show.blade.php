@extends('layouts.app')

@section('title', $program->name)

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .program-hero {
        background: var(--success-gradient);
        color: white;
        padding: 4rem 0 3rem;
        margin-bottom: 3rem;
        border-radius: 0 0 2rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .program-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .program-hero-content {
        position: relative;
        z-index: 2;
    }

    .program-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .program-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .program-badge a {
        color: white;
        text-decoration: none;
    }

    .program-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .program-description {
        font-size: 1.25rem;
        opacity: 0.95;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .program-info-grid {
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
        color: #10b981;
    }

    .requirement-card {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 2px solid #3b82f6;
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.2s;
    }

    .requirement-card:hover {
        transform: translateY(-4px);
    }

    .requirement-card i {
        font-size: 2rem;
        color: #3b82f6;
        margin-bottom: 0.75rem;
    }

    .course-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .course-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        flex: 1;
    }

    .course-badge {
        background: var(--success-gradient);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .course-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .course-info {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 0.5rem;
    }

    .course-info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #475569;
    }

    .course-info-item i {
        color: #10b981;
        width: 16px;
    }

    .course-info-item a {
        color: #475569;
        text-decoration: none;
        transition: color 0.2s;
    }

    .course-info-item a:hover {
        color: #10b981;
    }

    .course-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .course-btn {
        background: var(--success-gradient);
        border: none;
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .course-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        color: white;
    }

    @media (max-width: 768px) {
        .program-title {
            font-size: 2rem;
        }

        .program-description {
            font-size: 1rem;
        }

        .program-info-grid {
            grid-template-columns: 1fr;
        }

        .program-hero {
            padding: 2rem 0 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Program Hero Section -->
<div class="program-hero">
    <div class="container program-hero-content">
        <div class="program-badges">
            <a href="{{ route('institutions.show', $program->institution) }}" class="program-badge">
                <i class="fas fa-university me-1"></i>{{ $program->institution->name }}
            </a>
            @if($program->degree_level_label)
                <span class="program-badge">
                    <i class="fas fa-graduation-cap me-1"></i>{{ $program->degree_level_label }}
                </span>
            @endif
            </div>

        <h1 class="program-title">{{ $program->name }}</h1>
        <p class="program-description">{{ $program->description }}</p>

        <div class="program-info-grid">
                @if($program->duration)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-item-label">Продолжительность</div>
                    <div class="info-item-value">{{ $program->duration }}</div>
                    </div>
                @endif

                @if($program->tuition_fee)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-ruble-sign"></i></div>
                    <div class="info-item-label">Стоимость</div>
                    <div class="info-item-value">{{ number_format($program->tuition_fee, 0, ',', ' ') }} ₽</div>
                    </div>
                @endif

                @if($program->language)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-language"></i></div>
                    <div class="info-item-label">Язык обучения</div>
                    <div class="info-item-value">{{ $program->language === 'ru' ? 'Русский' : 'English' }}</div>
                    </div>
                @endif

                        @if($program->location)
                <div class="info-item">
                    <div class="info-item-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-item-label">Место проведения</div>
                    <div class="info-item-value">{{ $program->location }}</div>
                </div>
            @endif
            </div>
        </div>
    </div>

<div class="container pb-5">
    <!-- Requirements Section -->
    @if($program->requirements && count($program->requirements) > 0)
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-clipboard-check"></i>
                Требования для поступления
            </h2>
            <div class="row g-3">
                    @foreach($program->requirements as $requirement)
                    <div class="col-md-4 col-sm-6">
                        <div class="requirement-card">
                            <i class="fas fa-check-circle"></i>
                            <h6 class="mb-0 fw-bold">{{ $requirement }}</h6>
                            </div>
                        </div>
                    @endforeach
            </div>
        </div>
    @endif

    <!-- Courses Section -->
    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-book"></i>
            Курсы программы
        </h2>

            @if($program->courses->count() > 0)
            <div class="row g-4">
                    @foreach($program->courses as $course)
                    <div class="col-lg-6">
                        <div class="course-card">
                            <div class="course-header">
                                <h5 class="course-title">{{ $course->name }}</h5>
                                        @if($course->credits)
                                    <span class="course-badge">{{ $course->credits }} кредитов</span>
                                @endif
                            </div>

                            <p class="course-description">{{ Str::limit($course->description, 120) }}</p>

                            <div class="course-info">
                                @if($course->instructor)
                                    <div class="course-info-item">
                                        <i class="fas fa-user-tie"></i>
                                        @if(Route::has('instructors.show'))
                                            <a href="{{ route('instructors.show', $course->instructor) }}">
                                                {{ $course->instructor->name }}
                                            </a>
                                        @else
                                            <span>{{ $course->instructor->name }}</span>
                                        @endif
                                        </div>
                                    @endif

                                        @if($course->duration)
                                    <div class="course-info-item">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $course->duration }}</span>
                                            </div>
                                        @endif

                                        @if($course->schedule)
                                    <div class="course-info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>{{ $course->schedule }}</span>
                                    </div>
                                @endif

                                        @if($course->location)
                                    <div class="course-info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>{{ $course->location }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="course-footer">
                                <a href="{{ route('courses.show', $course) }}" class="course-btn">
                                    Подробнее <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
            <div class="text-center py-5">
                        <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                <h5 class="text-muted mb-2">Курсы не найдены</h5>
                        <p class="text-muted">В этой программе пока нет курсов</p>
                </div>
            @endif
    </div>
</div>
@endsection
