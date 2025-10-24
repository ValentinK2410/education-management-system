@extends('layouts.app')

@section('title', 'Главная - Система управления образованием')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Добро пожаловать в EduManage</h1>
                <p class="lead mb-4">Современная система управления образовательными процессами. Управляйте учебными заведениями, программами и курсами с легкостью.</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('institutions.index') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-university me-2"></i>Учебные заведения
                    </a>
                    <a href="{{ route('programs.index') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-book me-2"></i>Программы
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-graduation-cap" style="font-size: 200px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-university fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">{{ $institutions->count() }}</h3>
                        <p class="text-muted">Учебных заведений</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-book fa-3x text-success mb-3"></i>
                        <h3 class="fw-bold">{{ $programs->count() }}</h3>
                        <p class="text-muted">Образовательных программ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-chalkboard-teacher fa-3x text-warning mb-3"></i>
                        <h3 class="fw-bold">{{ $courses->count() }}</h3>
                        <p class="text-muted">Курсов</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h3 class="fw-bold">1000+</h3>
                        <p class="text-muted">Студентов</p>
                    </div>
                </div>
            </div>
        </div>
<!-- Events Section -->
@if($upcomingEvents->count() > 0 || $featuredEvents->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                    Предстоящие события
                </h2>
                <p class="text-muted">Присоединяйтесь к нашим образовательным мероприятиям и семинарам</p>
            </div>
        </div>

        <!-- Рекомендуемые события -->
        @if($featuredEvents->count() > 0)
            <div class="row mb-5">
                <div class="col-12">
                    <h4 class="mb-3">
                        <i class="fas fa-star me-2 text-warning"></i>
                        Рекомендуемые события
                    </h4>
                </div>
                @foreach($featuredEvents as $event)
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            @if($event->image)
                                <img src="{{ Storage::url($event->image) }}" 
                                     class="card-img-top" 
                                     alt="{{ $event->title }}" 
                                     style="height: 200px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-calendar-alt fa-3x text-white opacity-75"></i>
                                </div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <span class="badge bg-warning mb-2">
                                        <i class="fas fa-star me-1"></i>
                                        Рекомендуемое
                                    </span>
                                    <h5 class="card-title fw-bold">{{ $event->title }}</h5>
                                    @if($event->description)
                                        <p class="card-text text-muted">{{ Str::limit($event->description, 120) }}</p>
                                    @endif
                                </div>
                                
                                <div class="mt-auto">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Дата</small>
                                            <strong>{{ $event->start_date->format('d.m.Y') }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Время</small>
                                            <strong>{{ $event->start_date->format('H:i') }}</strong>
                                        </div>
                                    </div>
                                    
                                    @if($event->location)
                                        <div class="mb-3">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                Место
                                            </small>
                                            <span>{{ Str::limit($event->location, 50) }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($event->isFree())
                                                <span class="badge bg-success">Бесплатно</span>
                                            @else
                                                <span class="badge bg-info">{{ $event->formatted_price }}</span>
                                            @endif
                                        </div>
                                        @if($event->registration_url)
                                            <a href="{{ $event->registration_url }}" 
                                               target="_blank" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Регистрация
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Обычные события -->
        @if($upcomingEvents->count() > 0)
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-3">
                        <i class="fas fa-calendar me-2 text-primary"></i>
                        Все события
                    </h4>
                </div>
                @foreach($upcomingEvents as $event)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            @if($event->image)
                                <img src="{{ Storage::url($event->image) }}" 
                                     class="card-img-top" 
                                     alt="{{ $event->title }}" 
                                     style="height: 150px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 150px;">
                                    <i class="fas fa-calendar fa-2x text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h6 class="card-title fw-bold">{{ Str::limit($event->title, 50) }}</h6>
                                    @if($event->description)
                                        <p class="card-text text-muted small">{{ Str::limit($event->description, 80) }}</p>
                                    @endif
                                </div>
                                
                                <div class="mt-auto">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Дата</small>
                                            <strong class="small">{{ $event->start_date->format('d.m.Y') }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Время</small>
                                            <strong class="small">{{ $event->start_date->format('H:i') }}</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($event->isFree())
                                                <span class="badge bg-success small">Бесплатно</span>
                                            @else
                                                <span class="badge bg-info small">{{ $event->formatted_price }}</span>
                                            @endif
                                        </div>
                                        @if($event->registration_url)
                                            <a href="{{ $event->registration_url }}" 
                                               target="_blank" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Регистрация
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="fw-bold mb-3">Популярные учебные заведения</h2>
                <p class="text-muted">Откройте для себя лучшие образовательные учреждения</p>
            </div>
        </div>

        <div class="row">
            @forelse($institutions as $institution)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        @if($institution->logo)
                            <img src="{{ Storage::url($institution->logo) }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $institution->name }}">
                        @else
                            <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-university fa-3x text-white"></i>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $institution->name }}</h5>
                            <p class="card-text text-muted flex-grow-1">{{ Str::limit($institution->description, 100) }}</p>
                            <div class="mt-auto">
                                <a href="{{ route('institutions.show', $institution) }}" class="btn btn-primary">
                                    Подробнее <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Пока нет учебных заведений</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('institutions.index') }}" class="btn btn-outline-primary btn-lg">
                Все учебные заведения <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Featured Programs -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="fw-bold mb-3">Популярные программы</h2>
                <p class="text-muted">Выберите подходящую образовательную программу</p>
            </div>
        </div>

        <div class="row">
            @forelse($programs as $program)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title">{{ $program->name }}</h5>
                                <span class="badge bg-primary">{{ $program->degree_level }}</span>
                            </div>
                            <p class="card-text text-muted">{{ Str::limit($program->description, 100) }}</p>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-university me-1"></i>{{ $program->institution->name }}
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $program->duration }}
                                </small>
                                <a href="{{ route('programs.show', $program) }}" class="btn btn-sm btn-primary">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Пока нет программ</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('programs.index') }}" class="btn btn-outline-primary btn-lg">
                Все программы <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Featured Courses -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="fw-bold mb-3">Популярные курсы</h2>
                <p class="text-muted">Изучайте новые навыки с лучшими преподавателями</p>
            </div>
        </div>

        <div class="row">
            @forelse($courses as $course)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title">{{ $course->name }}</h5>
                                @if($course->credits)
                                    <span class="badge bg-success">{{ $course->credits }} кредитов</span>
                                @endif
                            </div>
                            <p class="card-text text-muted">{{ Str::limit($course->description, 100) }}</p>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-university me-1"></i>{{ $course->program->institution->name }}
                                </small>
                            </div>
                            @if($course->instructor)
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>{{ $course->instructor->name }}
                                    </small>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $course->duration }}
                                </small>
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-primary">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">Пока нет курсов</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('courses.index') }}" class="btn btn-outline-primary btn-lg">
                Все курсы <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
@endsection
