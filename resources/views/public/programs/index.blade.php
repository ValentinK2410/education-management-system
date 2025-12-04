@extends('layouts.app')

@section('title', 'Образовательные программы')

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

    .programs-header {
        background: var(--success-gradient);
        color: white;
        padding: 4rem 0 3rem;
        margin-bottom: 3rem;
        border-radius: 0 0 2rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .programs-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .programs-header .container {
        position: relative;
        z-index: 2;
    }

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
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .program-header::before {
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

    .program-icon {
        font-size: 3rem;
        color: white;
        position: relative;
        z-index: 2;
        opacity: 0.9;
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
        margin-bottom: 1rem;
        gap: 0.5rem;
    }

    .program-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        line-height: 1.3;
        flex: 1;
    }

    .program-degree-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
    }

    .program-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        flex-grow: 1;
    }

    .program-info {
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .program-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #475569;
    }

    .program-info-item:last-child {
        margin-bottom: 0;
    }

    .program-info-item i {
        width: 20px;
        color: #10b981;
        margin-right: 0.5rem;
    }

    .program-info-item a {
        color: #475569;
        text-decoration: none;
        transition: color 0.2s;
    }

    .program-info-item a:hover {
        color: #10b981;
    }

    .program-requirements {
        margin-bottom: 1rem;
    }

    .program-requirements-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .program-requirements-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
    }

    .requirement-tag {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #93c5fd;
    }

    .program-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .program-courses-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .program-courses-count i {
        color: #10b981;
    }

    .program-btn {
        background: var(--success-gradient);
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

    .program-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
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
        .programs-header {
            padding: 2rem 0 1.5rem;
            border-radius: 0 0 1rem 1rem;
        }

        .program-header {
            height: 150px;
        }

        .program-icon {
            font-size: 2.5rem;
        }

        .program-title-row {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="programs-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3">Образовательные программы</h1>
                <p class="lead mb-0">Выберите подходящую образовательную программу</p>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
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
                                <span class="program-degree-badge">{{ $program->degree_level_label }}</span>
                            @endif
                        </div>

                        <p class="program-description">{{ Str::limit($program->description, 120) }}</p>

                        <div class="program-info">
                            @if($program->institution)
                                <div class="program-info-item">
                                    <i class="fas fa-university"></i>
                                    <a href="{{ route('institutions.show', $program->institution) }}">
                                        {{ $program->institution->name }}
                                    </a>
                                </div>
                            @endif

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

                        @if($program->requirements && count($program->requirements) > 0)
                            <div class="program-requirements">
                                <div class="program-requirements-label">Требования</div>
                                <div class="program-requirements-tags">
                                    @foreach($program->requirements as $requirement)
                                        <span class="requirement-tag">{{ $requirement }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="program-footer">
                            <div class="program-courses-count">
                                <i class="fas fa-book"></i>
                                <span>{{ $program->courses->count() }} {{ $program->courses->count() == 1 ? 'курс' : ($program->courses->count() < 5 ? 'курса' : 'курсов') }}</span>
                            </div>
                            <a href="{{ route('programs.show', $program) }}" class="program-btn">
                                Подробнее <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-graduation-cap empty-state-icon"></i>
                    <h3 class="text-muted mb-3">Программы не найдены</h3>
                    <p class="text-muted">Образовательные программы будут добавлены в ближайшее время</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($programs->hasPages())
        <div class="row mt-5">
            <div class="col-12 d-flex justify-content-center">
                {{ $programs->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
