{{-- Dashboard для студента --}}
<!-- Статистические карточки -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Активные курсы
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_courses'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Завершенные курсы
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_courses'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Активные программы
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_programs'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Завершенные программы
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_programs'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-certificate fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Активные курсы -->
@if(isset($myCourses) && $myCourses->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-book-open me-2"></i>Мои активные курсы
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($myCourses as $course)
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $course->name }}</h6>
                                    <p class="card-text text-muted small">
                                        {{ $course->program->name ?? 'Без программы' }}
                                        <br>
                                        <small>{{ $course->program->institution->name ?? '' }}</small>
                                    </p>
                                    @if($course->pivot->progress)
                                        <div class="mb-2">
                                            <small class="text-muted">Прогресс: {{ $course->pivot->progress }}%</small>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar" style="width: {{ $course->pivot->progress }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>Открыть курс
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Активные программы -->
@if(isset($myPrograms) && $myPrograms->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>Мои активные программы
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($myPrograms as $program)
                        <div class="col-md-6 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $program->name }}</h6>
                                    <p class="card-text text-muted small">
                                        {{ $program->institution->name ?? 'Без заведения' }}
                                        @if($program->duration)
                                            <br><small>Длительность: {{ $program->duration }}</small>
                                        @endif
                                    </p>
                                    <a href="{{ route('programs.show', $program) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye me-1"></i>Открыть программу
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Завершенные курсы -->
@if(isset($completedCourses) && $completedCourses->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle me-2"></i>Завершенные курсы
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($completedCourses as $course)
                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $course->name }}</h6>
                                    <p class="card-text text-muted small">{{ $course->program->name ?? 'Без программы' }}</p>
                                    @if($course->pivot->completed_at)
                                        <small class="text-muted">
                                            Завершен: {{ \Carbon\Carbon::parse($course->pivot->completed_at)->format('d.m.Y') }}
                                        </small>
                                    @endif
                                    <div class="mt-2">
                                        <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-eye me-1"></i>Просмотр
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Завершенные программы -->
@if(isset($completedPrograms) && $completedPrograms->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-certificate me-2"></i>Завершенные программы
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($completedPrograms as $program)
                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $program->name }}</h6>
                                    <p class="card-text text-muted small">{{ $program->institution->name ?? 'Без заведения' }}</p>
                                    @if($program->pivot->completed_at)
                                        <small class="text-muted">
                                            Завершена: {{ \Carbon\Carbon::parse($program->pivot->completed_at)->format('d.m.Y') }}
                                        </small>
                                    @endif
                                    <div class="mt-2">
                                        <a href="{{ route('programs.show', $program) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-eye me-1"></i>Просмотр
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
