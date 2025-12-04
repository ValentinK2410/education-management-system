@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Курсы')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .courses-header {
        background: var(--primary-gradient);
        color: white;
        padding: 4rem 0 3rem;
        margin-bottom: 3rem;
        border-radius: 0 0 2rem 2rem;
    }

    .course-card {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .course-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .course-header {
        height: 200px;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .course-header.has-image {
        background-image: var(--course-image);
    }

    .course-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        background-size: cover;
        opacity: 0.3;
    }

    .course-header.has-image::before {
        background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%);
        opacity: 1;
    }

    .course-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
    }

    .course-icon {
        font-size: 3rem;
        color: white;
        position: relative;
        z-index: 2;
        opacity: 0.9;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .course-header.has-image .course-icon {
        display: none;
    }

    .course-card:nth-child(3n+1) .course-header:not(.has-image) {
        background: var(--primary-gradient);
    }

    .course-card:nth-child(3n+2) .course-header:not(.has-image) {
        background: var(--success-gradient);
    }

    .course-card:nth-child(3n+3) .course-header:not(.has-image) {
        background: var(--info-gradient);
    }

    .course-body {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .course-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 0.5rem;
    }

    .course-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        line-height: 1.3;
        flex: 1;
    }

    .course-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
    }

    .course-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .course-info {
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .course-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #475569;
    }

    .course-info-item:last-child {
        margin-bottom: 0;
    }

    .course-info-item i {
        width: 20px;
        color: #667eea;
        margin-right: 0.5rem;
    }

    .course-info-item a {
        color: #475569;
        text-decoration: none;
        transition: color 0.2s;
    }

    .course-info-item a:hover {
        color: #667eea;
    }

    .course-footer {
        margin-top: auto;
        padding-top: 1rem;
    }

    .course-btn {
        background: var(--primary-gradient);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .course-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .courses-header {
            padding: 2rem 0 1.5rem;
            border-radius: 0 0 1rem 1rem;
        }

        .course-header {
            height: 150px;
        }

        .course-icon {
            font-size: 2.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="courses-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3">Учебные курсы</h1>
                <p class="lead mb-0">Изучайте новые навыки с лучшими преподавателями</p>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        @forelse($courses as $course)
            <div class="col-lg-4 col-md-6">
                <div class="course-card">
                    @php
                        $hasImage = isset($course->image) && !empty($course->image);
                        $imageUrl = $hasImage ? Storage::url($course->image) : null;
                    @endphp
                    <div class="course-header {{ $hasImage ? 'has-image' : '' }}"
                         @if($hasImage && $imageUrl) style="background-image: url('{{ $imageUrl }}');" @endif>
                        @if($hasImage && $imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $course->name }}" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.remove('has-image'); this.parentElement.querySelector('.course-icon').style.display='block';">
                            <i class="fas fa-book course-icon" style="display: none;"></i>
                        @else
                            <i class="fas fa-book course-icon"></i>
                        @endif
                    </div>
                    <div class="course-body">
                        <div class="course-title-row">
                            <h5 class="course-title">{{ $course->name }}</h5>
                            @if($course->credits)
                                <span class="course-badge">{{ $course->credits }} кредитов</span>
                            @endif
                        </div>

                        <p class="course-description">{{ Str::limit($course->description, 120) }}</p>

                        <div class="course-info">
                            @if($course->program && $course->program->institution)
                                <div class="course-info-item">
                                    <i class="fas fa-university"></i>
                                    <span>{{ $course->program->institution->name }}</span>
                                </div>
                            @endif

                            @if($course->program)
                                <div class="course-info-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>{{ $course->program->name }}</span>
                                </div>
                            @endif

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
                                Подробнее <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-chalkboard-teacher empty-state-icon"></i>
                    <h3 class="text-muted mb-3">Курсы не найдены</h3>
                    <p class="text-muted">Учебные курсы будут добавлены в ближайшее время</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($courses->hasPages())
        <div class="row mt-5">
            <div class="col-12 d-flex justify-content-center">
                {{ $courses->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
