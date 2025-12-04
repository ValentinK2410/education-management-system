@extends('layouts.app')

@section('title', 'Главная - Система управления образованием')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --purple-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    /* Hero Section */
    .hero-section {
        background: var(--primary-gradient);
        color: white;
        padding: 6rem 0 4rem;
        position: relative;
        overflow: hidden;
        min-height: 600px;
        display: flex;
        align-items: center;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
        animation: float 20s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        line-height: 1.2;
        animation: fadeInUp 0.8s ease-out;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.95;
        margin-bottom: 2rem;
        line-height: 1.6;
        animation: fadeInUp 1s ease-out;
    }

    .hero-buttons {
        animation: fadeInUp 1.2s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-icon {
        font-size: 15rem;
        opacity: 0.15;
        position: absolute;
        right: 5%;
        top: 50%;
        transform: translateY(-50%);
        animation: float 15s ease-in-out infinite;
    }

    .hero-btn {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .hero-btn-primary {
        background: white;
        color: #667eea;
    }

    .hero-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
        color: #667eea;
    }

    .hero-btn-outline {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .hero-btn-outline:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        transform: translateY(-3px);
    }

    /* Statistics Section */
    .stats-section {
        padding: 4rem 0;
        background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
        margin-top: -3rem;
        position: relative;
        z-index: 3;
    }

    .stat-card {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .stat-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-card:nth-child(1) .stat-icon {
        background: var(--info-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-card:nth-child(2) .stat-icon {
        background: var(--success-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-card:nth-child(3) .stat-icon {
        background: var(--warning-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-card:nth-child(4) .stat-icon {
        background: var(--purple-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #64748b;
        font-size: 1rem;
        font-weight: 500;
    }

    /* Section Headers */
    .section-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .section-subtitle {
        font-size: 1.125rem;
        color: #64748b;
    }

    /* Institution Card */
    .institution-card {
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

    .institution-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .institution-header {
        height: 180px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .institution-header.has-logo {
        background-size: cover;
        background-position: center;
    }

    .institution-header.has-logo::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%);
        z-index: 1;
    }

    .institution-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 0;
    }

    .institution-icon {
        font-size: 3rem;
        color: white;
        position: relative;
        z-index: 2;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .institution-card:nth-child(3n+1) .institution-header:not(.has-logo) {
        background: var(--info-gradient);
    }

    .institution-card:nth-child(3n+2) .institution-header:not(.has-logo) {
        background: var(--primary-gradient);
    }

    .institution-card:nth-child(3n+3) .institution-header:not(.has-logo) {
        background: var(--success-gradient);
    }

    .institution-body {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .institution-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }

    .institution-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        flex-grow: 1;
        margin-bottom: 1rem;
    }

    .institution-btn {
        background: var(--info-gradient);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .institution-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        color: white;
    }

    /* Program Card */
    .program-card {
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

    .program-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .program-header {
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .program-icon {
        font-size: 2.5rem;
        color: white;
        position: relative;
        z-index: 2;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .program-card:nth-child(3n+1) .program-header {
        background: var(--success-gradient);
    }

    .program-card:nth-child(3n+2) .program-header {
        background: var(--info-gradient);
    }

    .program-card:nth-child(3n+3) .program-header {
        background: var(--purple-gradient);
    }

    .program-body {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .program-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
        gap: 0.5rem;
    }

    .program-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        flex: 1;
    }

    .program-badge {
        background: var(--success-gradient);
        color: white;
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .program-description {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .program-info {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1rem;
    }

    .program-btn {
        background: var(--success-gradient);
        color: white;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .program-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* Course Card */
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
        height: 150px;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .course-header.has-image {
        background-size: cover;
        background-position: center;
    }

    .course-header.has-image::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%);
        z-index: 1;
    }

    .course-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 0;
    }

    .course-icon {
        font-size: 2.5rem;
        color: white;
        position: relative;
        z-index: 2;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
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
        margin-bottom: 0.75rem;
        gap: 0.5rem;
    }

    .course-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        flex: 1;
    }

    .course-credits {
        background: var(--success-gradient);
        color: white;
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .course-description {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .course-info {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .course-btn {
        background: var(--primary-gradient);
        color: white;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
        margin-top: auto;
    }

    .course-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
        color: white;
    }

    /* Section Link Button */
    .section-link-btn {
        background: var(--primary-gradient);
        color: white;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        margin-top: 2rem;
    }

    .section-link-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        color: white;
    }

    /* Events Section */
    .event-card {
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

    .event-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .event-header {
        height: 180px;
        position: relative;
        overflow: hidden;
    }

    .event-header.has-image {
        background-size: cover;
        background-position: center;
    }

    .event-header.has-image::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%);
        z-index: 1;
    }

    .event-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 0;
    }

    .event-icon {
        font-size: 3rem;
        color: white;
        position: relative;
        z-index: 2;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .event-header:not(.has-image) {
        background: var(--warning-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .event-body {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .event-featured-badge {
        background: var(--warning-gradient);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 0.75rem;
    }

    .event-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }

    .event-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .event-info {
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .event-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #475569;
    }

    .event-info-item:last-child {
        margin-bottom: 0;
    }

    .event-info-item i {
        width: 20px;
        color: #f59e0b;
        margin-right: 0.5rem;
    }

    .event-btn {
        background: var(--warning-gradient);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .event-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.4);
        color: white;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .hero-subtitle {
            font-size: 1rem;
        }

        .hero-icon {
            display: none;
        }

        .section-title {
            font-size: 2rem;
        }

        .stats-section {
            margin-top: -2rem;
            padding: 3rem 0;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <h1 class="hero-title">Добро пожаловать в EduManage</h1>
                <p class="hero-subtitle">Современная система управления образовательными процессами. Управляйте учебными заведениями, программами и курсами с легкостью.</p>
                <div class="hero-buttons d-flex gap-3 flex-wrap">
                    <a href="{{ route('institutions.index') }}" class="hero-btn hero-btn-primary">
                        <i class="fas fa-university"></i>
                        Учебные заведения
                    </a>
                    <a href="{{ route('programs.index') }}" class="hero-btn hero-btn-outline">
                        <i class="fas fa-book"></i>
                        Программы
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="fas fa-graduation-cap hero-icon"></i>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="stat-number">{{ $institutions->count() }}</div>
                    <div class="stat-label">Учебных заведений</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number">{{ $programs->count() }}</div>
                    <div class="stat-label">Образовательных программ</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-number">{{ $courses->count() }}</div>
                    <div class="stat-label">Курсов</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Студентов</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Events Section -->
@if($upcomingEvents->count() > 0 || $featuredEvents->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt me-2" style="color: #f59e0b;"></i>
                Предстоящие события
            </h2>
            <p class="section-subtitle">Присоединяйтесь к нашим образовательным мероприятиям и семинарам</p>
        </div>

        @if($featuredEvents->count() > 0)
            <div class="row mb-5">
                <div class="col-12 mb-4">
                    <h4 class="fw-bold">
                        <i class="fas fa-star me-2" style="color: #f59e0b;"></i>
                        Рекомендуемые события
                    </h4>
                </div>
                @foreach($featuredEvents as $event)
                    <div class="col-lg-6 mb-4">
                        <div class="event-card">
                            @php
                                $hasImage = isset($event->image) && !empty($event->image);
                            @endphp
                            <div class="event-header {{ $hasImage ? 'has-image' : '' }}"
                                 @if($hasImage) style="background-image: url('{{ Storage::url($event->image) }}');" @endif>
                                @if($hasImage)
                                    <img src="{{ Storage::url($event->image) }}"
                                         alt="{{ $event->title }}"
                                         loading="lazy"
                                         onerror="this.style.display='none'; this.parentElement.classList.remove('has-image');">
                                    <i class="fas fa-calendar-alt event-icon" style="display: none;"></i>
                                @else
                                    <i class="fas fa-calendar-alt event-icon"></i>
                                @endif
                            </div>
                            <div class="event-body">
                                <span class="event-featured-badge">
                                    <i class="fas fa-star me-1"></i>
                                    Рекомендуемое
                                </span>
                                <h5 class="event-title">{{ $event->title }}</h5>
                                @if($event->description)
                                    <p class="event-description">{{ Str::limit($event->description, 120) }}</p>
                                @endif

                                <div class="event-info">
                                    <div class="event-info-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>{{ $event->start_date->format('d.m.Y') }} в {{ $event->start_date->format('H:i') }}</span>
                                    </div>
                                    @if($event->location)
                                        <div class="event-info-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ Str::limit($event->location, 50) }}</span>
                                        </div>
                                    @endif
                                    <div class="event-info-item">
                                        <i class="fas fa-tag"></i>
                                        @if($event->isFree())
                                            <span class="badge bg-success">Бесплатно</span>
                                        @else
                                            <span class="badge bg-info">{{ $event->formatted_price }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if($event->registration_url)
                                    <a href="{{ $event->registration_url }}"
                                       target="_blank"
                                       class="event-btn">
                                        <i class="fas fa-external-link-alt"></i>
                                        Регистрация
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($upcomingEvents->count() > 0)
            <div class="row">
                <div class="col-12 mb-4">
                    <h4 class="fw-bold">
                        <i class="fas fa-calendar me-2" style="color: #3b82f6;"></i>
                        Все события
                    </h4>
                </div>
                @foreach($upcomingEvents as $event)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="event-card">
                            @php
                                $hasImage = isset($event->image) && !empty($event->image);
                            @endphp
                            <div class="event-header {{ $hasImage ? 'has-image' : '' }}"
                                 @if($hasImage) style="background-image: url('{{ Storage::url($event->image) }}');" @endif>
                                @if($hasImage)
                                    <img src="{{ Storage::url($event->image) }}"
                                         alt="{{ $event->title }}"
                                         loading="lazy"
                                         onerror="this.style.display='none'; this.parentElement.classList.remove('has-image');">
                                    <i class="fas fa-calendar-alt event-icon" style="display: none;"></i>
                                @else
                                    <i class="fas fa-calendar-alt event-icon"></i>
                                @endif
                            </div>
                            <div class="event-body">
                                <h6 class="event-title">{{ Str::limit($event->title, 50) }}</h6>
                                @if($event->description)
                                    <p class="event-description">{{ Str::limit($event->description, 80) }}</p>
                                @endif

                                <div class="event-info">
                                    <div class="event-info-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>{{ $event->start_date->format('d.m.Y') }}</span>
                                    </div>
                                    @if($event->isFree())
                                        <div class="event-info-item">
                                            <i class="fas fa-tag"></i>
                                            <span class="badge bg-success">Бесплатно</span>
                                        </div>
                                    @endif
                                </div>

                                @if($event->registration_url)
                                    <a href="{{ $event->registration_url }}"
                                       target="_blank"
                                       class="event-btn">
                                        <i class="fas fa-external-link-alt"></i>
                                        Регистрация
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

<!-- Featured Institutions -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Популярные учебные заведения</h2>
            <p class="section-subtitle">Откройте для себя лучшие образовательные учреждения</p>
        </div>

        <div class="row g-4">
            @forelse($institutions as $institution)
                <div class="col-lg-4 col-md-6">
                    <div class="institution-card">
                        @php
                            $hasLogo = isset($institution->logo) && !empty($institution->logo);
                        @endphp
                        <div class="institution-header {{ $hasLogo ? 'has-logo' : '' }}"
                             @if($hasLogo) style="background-image: url('{{ Storage::url($institution->logo) }}');" @endif>
                            @if($hasLogo)
                                <img src="{{ Storage::url($institution->logo) }}"
                                     alt="{{ $institution->name }}"
                                     loading="lazy"
                                     onerror="this.style.display='none'; this.parentElement.classList.remove('has-logo'); this.parentElement.querySelector('.institution-icon').style.display='flex';">
                                <i class="fas fa-university institution-icon" style="display: none;"></i>
                            @else
                                <i class="fas fa-university institution-icon"></i>
                            @endif
                        </div>
                        <div class="institution-body">
                            <h5 class="institution-title">{{ $institution->name }}</h5>
                            <p class="institution-description">{{ Str::limit($institution->description, 100) }}</p>
                            <a href="{{ route('institutions.show', $institution) }}" class="institution-btn">
                                Подробнее <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Пока нет учебных заведений</p>
                </div>
            @endforelse
        </div>

        <div class="text-center">
            <a href="{{ route('institutions.index') }}" class="section-link-btn">
                Все учебные заведения <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Featured Programs -->
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Популярные программы</h2>
            <p class="section-subtitle">Выберите подходящую образовательную программу</p>
        </div>

        <div class="row g-4">
            @forelse($programs as $program)
                <div class="col-lg-4 col-md-6">
                    <div class="program-card">
                        <div class="program-header">
                            <i class="fas fa-graduation-cap program-icon"></i>
                        </div>
                        <div class="program-body">
                            <div class="program-title-row">
                                <h5 class="program-title">{{ $program->name }}</h5>
                                @if($program->degree_level_label)
                                    <span class="program-badge">{{ $program->degree_level_label }}</span>
                                @endif
                            </div>
                            <p class="program-description">{{ Str::limit($program->description, 100) }}</p>
                            <div class="program-info">
                                <i class="fas fa-university me-1"></i>{{ $program->institution->name }}
                            </div>
                            @if($program->duration)
                                <div class="program-info mb-3">
                                    <i class="fas fa-clock me-1"></i>{{ $program->duration }}
                                </div>
                            @endif
                            <a href="{{ route('programs.show', $program) }}" class="program-btn">
                                Подробнее <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Пока нет программ</p>
                </div>
            @endforelse
        </div>

        <div class="text-center">
            <a href="{{ route('programs.index') }}" class="section-link-btn">
                Все программы <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Featured Courses -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Популярные курсы</h2>
            <p class="section-subtitle">Изучайте новые навыки с лучшими преподавателями</p>
        </div>

        <div class="row g-4">
            @forelse($courses as $course)
                <div class="col-lg-4 col-md-6">
                    <div class="course-card">
                        @php
                            $hasImage = isset($course->image) && !empty($course->image);
                        @endphp
                        <div class="course-header {{ $hasImage ? 'has-image' : '' }}"
                             @if($hasImage) style="background-image: url('{{ asset('storage/' . $course->image) }}');" @endif>
                            @if($hasImage)
                                <img src="{{ asset('storage/' . $course->image) }}"
                                     alt="{{ $course->name }}"
                                     loading="lazy"
                                     onerror="this.style.display='none'; this.parentElement.classList.remove('has-image'); this.parentElement.querySelector('.course-icon').style.display='flex';">
                                <i class="fas fa-book course-icon" style="display: none;"></i>
                            @else
                                <i class="fas fa-book course-icon"></i>
                            @endif
                        </div>
                        <div class="course-body">
                            <div class="course-title-row">
                                <h5 class="course-title">{{ $course->name }}</h5>
                                @if($course->credits)
                                    <span class="course-credits">{{ $course->credits }} кредитов</span>
                                @endif
                            </div>
                            <p class="course-description">{{ Str::limit($course->description, 100) }}</p>
                            @if($course->program && $course->program->institution)
                                <div class="course-info">
                                    <i class="fas fa-university me-1"></i>{{ $course->program->institution->name }}
                                </div>
                            @endif
                            @if($course->instructor)
                                <div class="course-info">
                                    <i class="fas fa-user-tie me-1"></i>{{ $course->instructor->name }}
                                </div>
                            @endif
                            @if($course->duration)
                                <div class="course-info">
                                    <i class="fas fa-clock me-1"></i>{{ $course->duration }}
                                </div>
                            @endif
                            <a href="{{ route('courses.show', $course) }}" class="course-btn">
                                Подробнее <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Пока нет курсов</p>
                </div>
            @endforelse
        </div>

        <div class="text-center">
            <a href="{{ route('courses.index') }}" class="section-link-btn">
                Все курсы <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endsection
