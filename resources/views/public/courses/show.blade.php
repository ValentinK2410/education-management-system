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
                                <a href="{{ route('instructors.show', $course->instructor) }}" class="text-decoration-none">
                                    {{ $course->instructor->name }}
                                </a>
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
                                @if($course->instructor->avatar)
                                    <img src="{{ Storage::url($course->instructor->avatar) }}" 
                                         class="rounded-circle" width="100" height="100" alt="Avatar">
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
                                    <a href="{{ route('instructors.show', $course->instructor) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-user me-1"></i>Профиль
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
