@extends('layouts.app')

@section('title', $program->name)

@section('content')
<div class="container py-5">
    <!-- Program Header -->
    <div class="row mb-5">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('institutions.show', $program->institution) }}" class="text-decoration-none">
                    <span class="badge bg-primary me-2">{{ $program->institution->name }}</span>
                </a>
                <span class="badge bg-success">{{ $program->degree_level }}</span>
            </div>

            <h1 class="fw-bold mb-3">{{ $program->name }}</h1>
            <p class="lead text-muted mb-4">{{ $program->description }}</p>

            <div class="row">
                @if($program->duration)
                    <div class="col-md-4 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-clock me-2"></i>Продолжительность
                        </h6>
                        <p class="mb-0">{{ $program->duration }}</p>
                    </div>
                @endif

                @if($program->tuition_fee)
                    <div class="col-md-4 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-ruble-sign me-2"></i>Стоимость
                        </h6>
                        <p class="mb-0">{{ number_format($program->tuition_fee, 0, ',', ' ') }} ₽</p>
                    </div>
                @endif

                @if($program->language)
                    <div class="col-md-4 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-language me-2"></i>Язык обучения
                        </h6>
                        <p class="mb-0">{{ $program->language === 'ru' ? 'Русский' : 'English' }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Информация о программе</h5>
                    <ul class="list-unstyled mb-3">
                        <li class="mb-2">
                            <i class="fas fa-university text-primary me-2"></i>
                            <a href="{{ route('institutions.show', $program->institution) }}" class="text-decoration-none">
                                {{ $program->institution->name }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                            {{ $program->courses->count() }} курсов
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            {{ $program->degree_level }}
                        </li>
                    </ul>
                    
                    @auth
                        @php
                            $enrollment = auth()->user()->programs()->where('program_id', $program->id)->first();
                        @endphp
                        
                        @if($enrollment)
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Вы записаны на эту программу</strong><br>
                                <small>
                                    Статус: 
                                    @if($enrollment->pivot->status === 'enrolled')
                                        <span class="badge bg-secondary">Записан</span>
                                    @elseif($enrollment->pivot->status === 'active')
                                        <span class="badge bg-primary">В процессе</span>
                                    @elseif($enrollment->pivot->status === 'completed')
                                        <span class="badge bg-success">Завершена</span>
                                    @elseif($enrollment->pivot->status === 'cancelled')
                                        <span class="badge bg-danger">Отменена</span>
                                    @endif
                                </small>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('profile.programs') }}" class="btn btn-primary">
                                    <i class="fas fa-book me-2"></i>Мои программы
                                </a>
                            </div>
                        @else
                            <form action="{{ route('programs.enroll', $program) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-plus me-2"></i>Записаться на программу
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Войти для записи
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Requirements Section -->
    @if($program->requirements && count($program->requirements) > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Требования для поступления</h3>
                <div class="row">
                    @foreach($program->requirements as $requirement)
                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle text-primary fa-2x mb-2"></i>
                                    <h6 class="card-title">{{ $requirement }}</h6>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Courses Section -->
    <div class="row">
        <div class="col-12">
            <h3 class="fw-bold mb-4">Курсы программы</h3>

            @if($program->courses->count() > 0)
                <div class="row">
                    @foreach($program->courses as $course)
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

                                    @if($course->instructor)
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                <a href="{{ route('instructors.show', $course->instructor) }}" class="text-decoration-none">
                                                    {{ $course->instructor->name }}
                                                </a>
                                            </small>
                                        </div>
                                    @endif

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

                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($course->location)
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $course->location }}
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
                        <p class="text-muted">В этой программе пока нет курсов</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
