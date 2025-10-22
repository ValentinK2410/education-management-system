@extends('layouts.app')

@section('title', $instructor->name)

@section('content')
<div class="container py-5">
    <!-- Instructor Header -->
    <div class="row mb-5">
        <div class="col-lg-4 text-center">
            @if($instructor->avatar)
                <img src="{{ Storage::url($instructor->avatar) }}" 
                     class="rounded-circle shadow" width="200" height="200" alt="Avatar">
            @else
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto shadow" 
                     style="width: 200px; height: 200px;">
                    <i class="fas fa-user fa-4x text-white"></i>
                </div>
            @endif
        </div>
        <div class="col-lg-8">
            <h1 class="fw-bold mb-3">{{ $instructor->name }}</h1>
            @if($instructor->bio)
                <p class="lead text-muted mb-4">{{ $instructor->bio }}</p>
            @endif
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold text-primary">
                        <i class="fas fa-envelope me-2"></i>Email
                    </h6>
                    <p class="mb-0">
                        <a href="mailto:{{ $instructor->email }}" class="text-decoration-none">
                            {{ $instructor->email }}
                        </a>
                    </p>
                </div>
                
                @if($instructor->phone)
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-phone me-2"></i>Телефон
                        </h6>
                        <p class="mb-0">{{ $instructor->phone }}</p>
                    </div>
                @endif
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold text-primary">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Курсы
                    </h6>
                    <p class="mb-0">{{ $instructor->taughtCourses->count() }} курсов</p>
                </div>
                
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold text-primary">
                        <i class="fas fa-user-tag me-2"></i>Роли
                    </h6>
                    <p class="mb-0">
                        @foreach($instructor->roles as $role)
                            <span class="badge bg-primary me-1">{{ $role->name }}</span>
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses Section -->
    <div class="row">
        <div class="col-12">
            <h3 class="fw-bold mb-4">Преподаваемые курсы</h3>
            
            @if($instructor->taughtCourses->count() > 0)
                <div class="row">
                    @foreach($instructor->taughtCourses as $course)
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title">{{ $course->name }}</h5>
                                        @if($course->credits)
                                            <span class="badge bg-success">{{ $course->credits }} кредитов</span>
                                        @endif
                                    </div>
                                    
                                    <p class="card-text text-muted">{{ Str::limit($course->description, 120) }}</p>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-university me-1"></i>{{ $course->program->institution->name }}
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-book me-1"></i>{{ $course->program->name }}
                                        </small>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        @if($course->duration)
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $course->duration }}
                                                </small>
                                            </div>
                                        @endif
                                        @if($course->schedule)
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $course->schedule }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($course->location)
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $course->location }}
                                            </small>
                                        </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($course->code)
                                            <small class="text-muted">
                                                <i class="fas fa-code me-1"></i>{{ $course->code }}
                                            </small>
                                        @endif
                                        <a href="{{ route('courses.show', $course) }}" class="btn btn-primary btn-sm">
                                            Подробнее
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Курсы не найдены</h5>
                        <p class="text-muted">У этого преподавателя пока нет курсов</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
