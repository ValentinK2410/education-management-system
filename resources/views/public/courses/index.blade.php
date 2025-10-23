@extends('layouts.app')

@section('title', 'Курсы')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="fw-bold mb-3">Учебные курсы</h1>
            <p class="text-muted">Изучайте новые навыки с лучшими преподавателями</p>
        </div>
    </div>

    <div class="row">
        @forelse($courses as $course)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title">{{ $course->name }}</h5>
                            @if($course->credits)
                                <span class="badge bg-success">{{ $course->credits }} кредитов</span>
                            @endif
                        </div>

                        <p class="card-text text-muted flex-grow-1">{{ Str::limit($course->description, 120) }}</p>

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

                        @if($course->location)
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $course->location }}
                                </small>
                            </div>
                        @endif

                        <div class="mt-auto">
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-primary btn-sm w-100">
                                Подробнее <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Курсы не найдены</h5>
                        <p class="text-muted">Учебные курсы будут добавлены в ближайшее время</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($courses->hasPages())
        <div class="row">
            <div class="col-12">
                {{ $courses->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
