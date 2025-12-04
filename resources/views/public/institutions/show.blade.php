@extends('layouts.app')

@section('title', $institution->name)

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .institution-hero {
        background: var(--primary-gradient);
        color: white;
        padding: 4rem 0 3rem;
        margin-bottom: 3rem;
        border-radius: 0 0 2rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .institution-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .institution-hero-content {
        position: relative;
        z-index: 2;
    }

    .institution-logo-wrapper {
        text-align: center;
        margin-bottom: 2rem;
    }

    .institution-logo {
        width: 200px;
        height: 200px;
        object-fit: contain;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .institution-logo-placeholder {
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .institution-logo-placeholder i {
        font-size: 5rem;
        color: white;
    }

    .institution-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .institution-description {
        font-size: 1.25rem;
        opacity: 0.95;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .contact-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 2rem;
    }

    .contact-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 1.5rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.2s, background 0.2s;
    }

    .contact-card:hover {
        transform: translateY(-4px);
        background: rgba(255, 255, 255, 0.2);
    }

    .contact-card-icon {
        font-size: 1.75rem;
        margin-bottom: 0.75rem;
        opacity: 0.9;
    }

    .contact-card-label {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .contact-card-value {
        font-size: 1rem;
        font-weight: 600;
    }

    .contact-card-value a {
        color: white;
        text-decoration: none;
        transition: opacity 0.2s;
    }

    .contact-card-value a:hover {
        opacity: 0.8;
        text-decoration: underline;
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

    .program-card {
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

    .program-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .program-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .program-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        flex: 1;
    }

    .program-badge {
        background: var(--primary-gradient);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .program-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .program-info {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 0.5rem;
    }

    .program-info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #475569;
    }

    .program-info-item i {
        color: #667eea;
        width: 16px;
    }

    .program-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .program-courses-count {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .program-btn {
        background: var(--primary-gradient);
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

    .program-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        color: white;
    }

    @media (max-width: 768px) {
        .institution-title {
            font-size: 2rem;
        }

        .institution-description {
            font-size: 1rem;
        }

        .contact-info-grid {
            grid-template-columns: 1fr;
        }

        .institution-hero {
            padding: 2rem 0 1.5rem;
        }

        .institution-logo,
        .institution-logo-placeholder {
            width: 150px;
            height: 150px;
        }
    }
</style>
@endpush

@section('content')
<!-- Institution Hero Section -->
<div class="institution-hero">
    <div class="container institution-hero-content">
        <div class="institution-logo-wrapper">
            @if($institution->logo)
                <img src="{{ Storage::url($institution->logo) }}"
                     class="institution-logo"
                     alt="{{ $institution->name }}"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="institution-logo-placeholder" style="display: none;">
                    <i class="fas fa-university"></i>
                </div>
            @else
                <div class="institution-logo-placeholder">
                    <i class="fas fa-university"></i>
                </div>
            @endif
        </div>

        <h1 class="institution-title text-center">{{ $institution->name }}</h1>
        <p class="institution-description text-center">{{ $institution->description }}</p>

        <div class="contact-info-grid">
            @if($institution->address)
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-card-label">Адрес</div>
                    <div class="contact-card-value">{{ $institution->address }}</div>
                </div>
            @endif

            @if($institution->phone)
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-card-label">Телефон</div>
                    <div class="contact-card-value">
                        <a href="tel:{{ $institution->phone }}">{{ $institution->phone }}</a>
                    </div>
                </div>
            @endif

            @if($institution->email)
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-card-label">Email</div>
                    <div class="contact-card-value">
                        <a href="mailto:{{ $institution->email }}">{{ $institution->email }}</a>
                    </div>
                </div>
            @endif

            @if($institution->website)
                <div class="contact-card">
                    <div class="contact-card-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="contact-card-label">Веб-сайт</div>
                    <div class="contact-card-value">
                        <a href="{{ $institution->website }}" target="_blank" rel="noopener noreferrer">
                            {{ parse_url($institution->website, PHP_URL_HOST) ?: $institution->website }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Programs Section -->
    <div class="content-section">
        <h2 class="section-title">
            <i class="fas fa-graduation-cap"></i>
            Образовательные программы
        </h2>

        @if($institution->programs->count() > 0)
            <div class="row g-4">
                @foreach($institution->programs as $program)
                    <div class="col-lg-6">
                        <div class="program-card">
                            <div class="program-header">
                                <h5 class="program-title">{{ $program->name }}</h5>
                                @if($program->degree_level_label)
                                    <span class="program-badge">{{ $program->degree_level_label }}</span>
                                @endif
                            </div>

                            <p class="program-description">{{ Str::limit($program->description, 150) }}</p>

                            <div class="program-info">
                                @if($program->duration)
                                    <div class="program-info-item">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $program->duration }}</span>
                                    </div>
                                @endif

                                @if($program->tuition_fee)
                                    <div class="program-info-item">
                                        <i class="fas fa-ruble-sign"></i>
                                        <span>{{ number_format($program->tuition_fee, 0, ',', ' ') }} ₽</span>
                                    </div>
                                @endif

                                @if($program->language)
                                    <div class="program-info-item">
                                        <i class="fas fa-language"></i>
                                        <span>{{ $program->language === 'ru' ? 'Русский' : 'English' }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="program-footer">
                                <span class="program-courses-count">
                                    <i class="fas fa-book me-1"></i>
                                    {{ $program->courses->count() }} {{ $program->courses->count() == 1 ? 'курс' : ($program->courses->count() < 5 ? 'курса' : 'курсов') }}
                                </span>
                                <a href="{{ route('programs.show', $program) }}" class="program-btn">
                                    Подробнее <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h5 class="text-muted mb-2">Программы не найдены</h5>
                <p class="text-muted">В этом учебном заведении пока нет образовательных программ</p>
            </div>
        @endif
    </div>
</div>
@endsection
