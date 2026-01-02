@extends('layouts.admin')

@section('title', 'Статистика преподавателей')
@section('page-title', 'Статистика преподавателей')

@push('styles')
<style>
    .instructor-card {
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .instructor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-color: #6366f1;
    }

    .instructor-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #6366f1;
    }

    .instructor-avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: bold;
        border: 3px solid #6366f1;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .stat-badge-primary {
        background-color: #eef2ff;
        color: #4f46e5;
    }

    .stat-badge-success {
        background-color: #d1fae5;
        color: #059669;
    }

    .stat-badge-warning {
        background-color: #fef3c7;
        color: #d97706;
    }

    .stat-badge-danger {
        background-color: #fee2e2;
        color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h4 class="mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        Статистика преподавателей
                    </h4>
                </div>
                <div class="card-body">
                    @if($instructors->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Преподаватели не найдены</p>
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach($instructors as $instructor)
                            <div class="col-md-6 col-lg-4">
                                <div class="instructor-card card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            @if($instructor['photo'])
                                                <img src="{{ asset('storage/' . $instructor['photo']) }}" 
                                                     alt="{{ $instructor['name'] }}" 
                                                     class="instructor-avatar me-3">
                                            @else
                                                <div class="instructor-avatar-placeholder me-3">
                                                    {{ strtoupper(mb_substr($instructor['name'], 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">
                                                    <a href="{{ route('admin.instructor-stats.show', $instructor['id']) }}" 
                                                       class="text-decoration-none text-dark">
                                                        {{ $instructor['name'] }}
                                                    </a>
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    {{ $instructor['email'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="stat-badge stat-badge-primary">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                    <span>{{ $instructor['total_courses'] }}</span>
                                                </div>
                                                <small class="text-muted d-block mt-1">Курсов</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-badge stat-badge-success">
                                                    <i class="fas fa-users"></i>
                                                    <span>{{ $instructor['total_students'] }}</span>
                                                </div>
                                                <small class="text-muted d-block mt-1">Студентов</small>
                                            </div>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="stat-badge stat-badge-success">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>{{ $instructor['graded_activities'] }}</span>
                                                </div>
                                                <small class="text-muted d-block mt-1">Проверено</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-badge stat-badge-warning">
                                                    <i class="fas fa-clock"></i>
                                                    <span>{{ $instructor['pending_activities'] }}</span>
                                                </div>
                                                <small class="text-muted d-block mt-1">Ожидает</small>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <a href="{{ route('admin.instructor-stats.show', $instructor['id']) }}" 
                                               class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-eye me-1"></i>
                                                Подробнее
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

