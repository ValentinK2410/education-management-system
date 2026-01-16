@extends('layouts.admin')

@section('title', 'Статистика преподавателя: ' . $instructor->name)
@section('page-title', 'Статистика преподавателя')

@push('styles')
<style>
    .instructor-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .instructor-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .instructor-avatar-placeholder-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        font-weight: bold;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .stat-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .stat-card-primary {
        border-left-color: #6366f1;
    }

    .stat-card-success {
        border-left-color: #10b981;
    }

    .stat-card-warning {
        border-left-color: #f59e0b;
    }

    .stat-card-danger {
        border-left-color: #ef4444;
    }

    .activity-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .activity-badge-assign {
        background-color: #eef2ff;
        color: #4f46e5;
    }

    .activity-badge-quiz {
        background-color: #f0fdf4;
        color: #059669;
    }

    .activity-badge-forum {
        background-color: #fef3c7;
        color: #d97706;
    }

    .activity-badge-resource {
        background-color: #f3e8ff;
        color: #7c3aed;
    }

    .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок преподавателя -->
    <div class="instructor-header">
        <div class="d-flex align-items-center gap-4">
            @if($instructor->photo)
                <img src="{{ asset('storage/' . $instructor->photo) }}" 
                     alt="{{ $instructor->name }}" 
                     class="instructor-avatar-large">
            @else
                <div class="instructor-avatar-placeholder-large">
                    {{ strtoupper(mb_substr($instructor->name, 0, 1)) }}
                </div>
            @endif
            <div class="flex-grow-1">
                <h2 class="mb-2">{{ $instructor->name }}</h2>
                <p class="mb-1">
                    <i class="fas fa-envelope me-2"></i>
                    {{ $instructor->email }}
                </p>
                @if($instructor->phone)
                <p class="mb-0">
                    <i class="fas fa-phone me-2"></i>
                    {{ $instructor->phone }}
                </p>
                @endif
            </div>
            <div>
                <a href="{{ route('admin.users.show', $instructor->id) }}" class="btn btn-light">
                    <i class="fas fa-user me-1"></i>
                    Профиль
                </a>
            </div>
        </div>
    </div>

    <!-- Фильтры по дате -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Фильтр по дате проверки
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.instructor-stats.show', $instructor->id) }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">Дата начала</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ $dateFrom ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">Дата окончания</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ $dateTo ?? '' }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Применить фильтр
                            </button>
                            @if($dateFrom || $dateTo)
                            <a href="{{ route('admin.instructor-stats.show', $instructor->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Сбросить
                            </a>
                            @endif
                        </div>
                    </form>
                    @if($dateFrom || $dateTo)
                    <div class="mt-3">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Период:</strong> 
                            {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') : 'с начала' }} 
                            - 
                            {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d.m.Y') : 'до конца' }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stat-card stat-card-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1">Всего курсов</div>
                            <div class="h4 mb-0">{{ $stats['total_courses'] }}</div>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-card-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1">Уникальных студентов</div>
                            <div class="h4 mb-0">{{ $stats['unique_students'] }}</div>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card stat-card-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1">Всего студентов со всех курсов</div>
                            <div class="h4 mb-0">{{ $stats['total_students_all'] }}</div>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card stat-card-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1">Проверено работ</div>
                            <div class="h4 mb-0">{{ $stats['total_graded'] }}</div>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card stat-card-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1">Ожидает проверки</div>
                            <div class="h4 mb-0">{{ $stats['total_pending'] }}</div>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика по типам активностей -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Статистика по типам активностей
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <div class="h3 mb-1 text-primary">{{ $stats['assignments_graded'] }}</div>
                                <div class="text-muted small">Заданий проверено</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <div class="h3 mb-1 text-success">{{ $stats['quizzes_graded'] }}</div>
                                <div class="text-muted small">Тестов проверено</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <div class="h3 mb-1 text-warning">{{ $stats['forums_graded'] }}</div>
                                <div class="text-muted small">Форумов проверено</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Курсы преподавателя с активностью студентов -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Курсы преподавателя и активность студентов ({{ $courses->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($courses->isEmpty())
                        <p class="text-muted text-center py-4">У преподавателя нет курсов</p>
                    @else
                        @foreach($courses as $course)
                            <div class="card mb-4 border">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <strong>{{ $course->name }}</strong>
                                                @if($course->code)
                                                    <small class="text-muted">({{ $course->code }})</small>
                                                @endif
                                            </h6>
                                            @if($course->program)
                                                <small class="text-muted">{{ $course->program->name }}</small>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge bg-info">Студентов: {{ $course->users_count }}</span>
                                            @if(isset($coursesWithStudents[$course->id]) && !empty($coursesWithStudents[$course->id]))
                                                <span class="badge bg-success">Активных: {{ count($coursesWithStudents[$course->id]) }}</span>
                                            @endif
                                            <a href="{{ route('admin.courses.show', $course->id) }}" 
                                               class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if(isset($coursesWithStudents[$course->id]) && !empty($coursesWithStudents[$course->id]))
                                        @foreach($coursesWithStudents[$course->id] as $studentData)
                                            @php
                                                $student = $studentData['student'];
                                                $activities = $studentData['activities'];
                                            @endphp
                                            <div class="border rounded p-3 mb-3 bg-white">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            <a href="{{ route('admin.users.show', $student->id) }}" 
                                                               class="text-decoration-none">
                                                                <i class="fas fa-user me-2"></i>{{ $student->name }}
                                                            </a>
                                                        </h6>
                                                        <small class="text-muted">{{ $student->email }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="small text-muted mb-1">Активность</div>
                                                        <div>
                                                            <span class="badge bg-success">{{ $studentData['graded_count'] }} проверено</span>
                                                            <span class="badge bg-info">{{ $studentData['submitted_count'] }} сдано</span>
                                                            <span class="badge bg-warning">{{ $studentData['pending_count'] }} ожидает</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row g-2">
                                                    @foreach($activities as $item)
                                                        @php
                                                            $activity = $item['activity'];
                                                            $activityTypeIcons = [
                                                                'assign' => 'fa-file-alt',
                                                                'quiz' => 'fa-question-circle',
                                                                'forum' => 'fa-comments',
                                                                'resource' => 'fa-file',
                                                                'page' => 'fa-file-alt',
                                                                'url' => 'fa-link',
                                                            ];
                                                            $activityTypeLabels = [
                                                                'assign' => 'Задание',
                                                                'quiz' => 'Тест',
                                                                'forum' => 'Форум',
                                                                'resource' => 'Ресурс',
                                                                'page' => 'Страница',
                                                                'url' => 'Ссылка',
                                                            ];
                                                            $icon = $activityTypeIcons[$activity->activity_type] ?? 'fa-circle';
                                                            $typeLabel = $activityTypeLabels[$activity->activity_type] ?? ucfirst($activity->activity_type);
                                                        @endphp
                                                        <div class="col-md-6 col-lg-4">
                                                            <div class="border rounded p-2 bg-light">
                                                                <div class="d-flex align-items-start mb-1">
                                                                    <i class="fas {{ $icon }} me-2 text-primary mt-1"></i>
                                                                    <div class="flex-grow-1">
                                                                        <div class="small fw-bold mb-1">
                                                                            {{ \Illuminate\Support\Str::limit($activity->name, 40) }}
                                                                        </div>
                                                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                                                            <span class="badge bg-secondary small">{{ $typeLabel }}</span>
                                                                            <span class="badge bg-{{ $item['status_class'] }} small">
                                                                                <i class="fas {{ $item['status_icon'] }} me-1"></i>{{ $item['status_text'] }}
                                                                            </span>
                                                                        </div>
                                                                        @if($item['submitted_at'])
                                                                            <div class="small text-info">
                                                                                <i class="fas fa-paper-plane me-1"></i>
                                                                                Сдано: {{ \Carbon\Carbon::parse($item['submitted_at'])->format('d.m.Y H:i') }}
                                                                            </div>
                                                                        @endif
                                                                        @if($item['graded_at'])
                                                                            <div class="small text-success">
                                                                                <i class="fas fa-check-double me-1"></i>
                                                                                Проверено: {{ \Carbon\Carbon::parse($item['graded_at'])->format('d.m.Y H:i') }}
                                                                            </div>
                                                                        @endif
                                                                        @if($item['grade'] !== null && $item['max_grade'])
                                                                            <div class="small text-primary fw-bold">
                                                                                <i class="fas fa-star me-1"></i>
                                                                                Оценка: {{ number_format($item['grade'], 1) }} / {{ number_format($item['max_grade'], 1) }}
                                                                                @if($item['max_grade'] > 0)
                                                                                    ({{ number_format(($item['grade'] / $item['max_grade']) * 100, 1) }}%)
                                                                                @endif
                                                                            </div>
                                                                        @elseif($item['status'] === 'needs_grading' || $item['status'] === 'submitted')
                                                                            <div class="small text-warning">
                                                                                <i class="fas fa-clock me-1"></i>Ожидает оценки
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted text-center py-3 mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Нет студентов с учебной активностью в Moodle
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Проверенные работы -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Проверенные работы ({{ $gradedActivities->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($gradedActivities->isEmpty())
                        <p class="text-muted text-center py-4">Нет проверенных работ</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Студент</th>
                                        <th>Курс</th>
                                        <th>Элемент</th>
                                        <th>Тип</th>
                                        <th>Оценка</th>
                                        <th>Дата проверки</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gradedActivities as $activity)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.users.show', $activity->user_id) }}" 
                                               class="text-decoration-none">
                                                {{ $activity->user->name ?? 'Неизвестно' }}
                                            </a>
                                        </td>
                                        <td>{{ $activity->course->name ?? 'Неизвестно' }}</td>
                                        <td>{{ $activity->activity->name ?? 'Неизвестно' }}</td>
                                        <td>
                                            @php
                                                $type = $activity->activity->activity_type ?? '';
                                                $badgeClass = 'activity-badge-assign';
                                                if ($type === 'quiz') $badgeClass = 'activity-badge-quiz';
                                                elseif ($type === 'forum') $badgeClass = 'activity-badge-forum';
                                                elseif ($type === 'resource') $badgeClass = 'activity-badge-resource';
                                                
                                                $typeNames = [
                                                    'assign' => 'Задание',
                                                    'quiz' => 'Тест',
                                                    'forum' => 'Форум',
                                                    'resource' => 'Материал',
                                                ];
                                            @endphp
                                            <span class="activity-badge {{ $badgeClass }}">
                                                {{ $typeNames[$type] ?? ucfirst($type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                {{ $activity->grade ?? 0 }} / {{ $activity->max_grade ?? 0 }}
                                            </strong>
                                        </td>
                                        <td>
                                            {{ $activity->graded_at ? $activity->graded_at->format('d.m.Y H:i') : 'Не указано' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Работы, ожидающие проверки -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Работы, ожидающие проверки ({{ $pendingActivities->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($pendingActivities->isEmpty())
                        <p class="text-muted text-center py-4">Нет работ, ожидающих проверки</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Студент</th>
                                        <th>Курс</th>
                                        <th>Элемент</th>
                                        <th>Тип</th>
                                        <th>Дата сдачи</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingActivities as $activity)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.users.show', $activity->user_id) }}" 
                                               class="text-decoration-none">
                                                {{ $activity->user->name ?? 'Неизвестно' }}
                                            </a>
                                        </td>
                                        <td>{{ $activity->course->name ?? 'Неизвестно' }}</td>
                                        <td>{{ $activity->activity->name ?? 'Неизвестно' }}</td>
                                        <td>
                                            @php
                                                $type = $activity->activity->activity_type ?? '';
                                                $badgeClass = 'activity-badge-assign';
                                                if ($type === 'quiz') $badgeClass = 'activity-badge-quiz';
                                                elseif ($type === 'forum') $badgeClass = 'activity-badge-forum';
                                                elseif ($type === 'resource') $badgeClass = 'activity-badge-resource';
                                                
                                                $typeNames = [
                                                    'assign' => 'Задание',
                                                    'quiz' => 'Тест',
                                                    'forum' => 'Форум',
                                                    'resource' => 'Материал',
                                                ];
                                            @endphp
                                            <span class="activity-badge {{ $badgeClass }}">
                                                {{ $typeNames[$type] ?? ucfirst($type) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $activity->submitted_at ? $activity->submitted_at->format('d.m.Y H:i') : 'Не указано' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.analytics.index', ['user_id' => $activity->user_id, 'course_id' => $activity->course_id]) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-eye"></i>
                                                Проверить
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

