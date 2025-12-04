@extends('layouts.app')

@section('title', 'Учебные заведения')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --purple-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .institutions-header {
        background: var(--info-gradient);
        color: white;
        padding: 4rem 0 3rem;
        margin-bottom: 3rem;
        border-radius: 0 0 2rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .institutions-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .institutions-header .container {
        position: relative;
        z-index: 2;
    }

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
        height: 200px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .institution-header.has-logo {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
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

    .institution-header img[onerror] {
        display: none;
    }

    .institution-icon {
        font-size: 3rem;
        color: white;
        position: relative;
        z-index: 2;
        opacity: 0.9;
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
        margin: 0 0 1rem 0;
        line-height: 1.3;
    }

    .institution-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .institution-info {
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .institution-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #475569;
    }

    .institution-info-item:last-child {
        margin-bottom: 0;
    }

    .institution-info-item i {
        width: 20px;
        color: #3b82f6;
        margin-right: 0.5rem;
    }

    .institution-info-item a {
        color: #475569;
        text-decoration: none;
        transition: color 0.2s;
    }

    .institution-info-item a:hover {
        color: #3b82f6;
    }

    .institution-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .institution-programs-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .institution-programs-count i {
        color: #3b82f6;
    }

    .institution-btn {
        background: var(--info-gradient);
        border: none;
        color: white;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .institution-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
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
        .institutions-header {
            padding: 2rem 0 1.5rem;
            border-radius: 0 0 1rem 1rem;
        }

        .institution-header {
            height: 150px;
        }

        .institution-icon {
            font-size: 2.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="institutions-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3">Учебные заведения</h1>
                <p class="lead mb-0">Откройте для себя лучшие образовательные учреждения</p>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
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
                        <p class="institution-description">{{ Str::limit($institution->description, 120) }}</p>

                        <div class="institution-info">
                            @if($institution->address)
                                <div class="institution-info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $institution->address }}</span>
                                </div>
                            @endif

                            @if($institution->phone)
                                <div class="institution-info-item">
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:{{ $institution->phone }}">{{ $institution->phone }}</a>
                                </div>
                            @endif

                            @if($institution->email)
                                <div class="institution-info-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:{{ $institution->email }}">{{ $institution->email }}</a>
                                </div>
                            @endif

                            @if($institution->website)
                                <div class="institution-info-item">
                                    <i class="fas fa-globe"></i>
                                    <a href="{{ $institution->website }}" target="_blank" rel="noopener noreferrer">
                                        {{ parse_url($institution->website, PHP_URL_HOST) }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="institution-footer">
                            <div class="institution-programs-count">
                                <i class="fas fa-graduation-cap"></i>
                                <span>{{ $institution->programs->count() }} {{ $institution->programs->count() == 1 ? 'программа' : ($institution->programs->count() < 5 ? 'программы' : 'программ') }}</span>
                            </div>
                            <a href="{{ route('institutions.show', $institution) }}" class="institution-btn">
                                Подробнее <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-university empty-state-icon"></i>
                    <h3 class="text-muted mb-3">Пока нет учебных заведений</h3>
                    <p class="text-muted">Учебные заведения будут добавлены в ближайшее время</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($institutions->hasPages())
        <div class="row mt-5">
            <div class="col-12 d-flex justify-content-center">
                {{ $institutions->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
