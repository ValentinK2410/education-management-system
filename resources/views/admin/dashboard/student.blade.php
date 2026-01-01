{{-- Dashboard для студента --}}

<!-- Индикатор синхронизации -->
<div id="sync-indicator" class="alert alert-info d-none mb-4" role="alert">
    <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm me-3" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
        <div class="flex-grow-1">
            <strong>Синхронизация данных с Moodle...</strong>
            <div class="progress mt-2" style="height: 6px;">
                <div id="sync-progress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            <small id="sync-message" class="d-block mt-1">Подготовка к синхронизации...</small>
        </div>
    </div>
</div>

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
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 30%;">Название курса</th>
                                <th style="width: 20%;">Программа</th>
                                <th style="width: 15%;">Преподаватель</th>
                                <th style="width: 30%;">Статус заданий (ПОСЛЕ СЕССИИ)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myCourses as $course)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $course->id }}</span></td>
                                    <td>
                                        <strong>{{ $course->name }}</strong>
                                        @if($course->code)
                                            <br><small class="text-muted">{{ $course->code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->program)
                                            <span class="badge bg-info">{{ $course->program->name }}</span>
                                            @if($course->program->institution)
                                                <br><small class="text-muted">{{ $course->program->institution->name }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Без программы</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->instructor)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-success text-white rounded-circle">
                                                        {{ substr($course->instructor->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <small class="d-block">{{ $course->instructor->name }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($coursesWithAssignments[$course->id]) && !empty($coursesWithAssignments[$course->id]))
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($coursesWithAssignments[$course->id] as $assignment)
                                                    @php
                                                        $moodleApiService = new \App\Services\MoodleApiService();
                                                        $assignmentUrl = $moodleApiService->getAssignmentUrl(
                                                            $assignment['cmid'] ?? null,
                                                            $assignment['id'] ?? null,
                                                            $course->moodle_course_id ?? null
                                                        );
                                                    @endphp
                                                    @if($assignmentUrl && ($assignment['status'] === 'not_submitted' || $assignment['status'] === 'pending'))
                                                        <a href="{{ $assignmentUrl }}" target="_blank" class="text-decoration-none">
                                                            <span class="badge assignment-mini-badge assignment-status-{{ $assignment['status'] }}" 
                                                                  title="{{ $assignment['name'] }}: {{ $assignment['status_text'] }} - Нажмите для сдачи">
                                                                @if($assignment['status'] === 'not_submitted')
                                                                    <i class="fas fa-times-circle me-1"></i>Не сдано
                                                                @elseif($assignment['status'] === 'pending')
                                                                    <i class="fas fa-clock me-1"></i>Не проверено
                                                                @else
                                                                    <i class="fas fa-check-circle me-1"></i>{{ $assignment['status_text'] }}
                                                                @endif
                                                            </span>
                                                        </a>
                                                    @else
                                                        <span class="badge assignment-mini-badge assignment-status-{{ $assignment['status'] }}" 
                                                              title="{{ $assignment['name'] }}: {{ $assignment['status_text'] }}">
                                                            @if($assignment['status'] === 'not_submitted')
                                                                <i class="fas fa-times-circle me-1"></i>Не сдано
                                                            @elseif($assignment['status'] === 'pending')
                                                                <i class="fas fa-clock me-1"></i>Не проверено
                                                            @else
                                                                <i class="fas fa-check-circle me-1"></i>{{ $assignment['status_text'] }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif($course->moodle_course_id && auth()->user()->moodle_user_id)
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>Задания не найдены
                                            </small>
                                        @elseif(!auth()->user()->moodle_user_id)
                                            <small class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Не настроена синхронизация
                                            </small>
                                        @else
                                            <small class="text-muted">—</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
.avatar-sm {
    width: 30px;
    height: 30px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
}

/* Мини-бейджи для статусов заданий */
.assignment-mini-badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.7rem;
    font-weight: 700;
    white-space: nowrap;
    border-radius: 0.375rem;
    border: 2px solid transparent;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.1);
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

/* Красный - не сдано */
.assignment-mini-badge.assignment-status-not-submitted,
.assignment-mini-badge.assignment-status-not_submitted {
    background-color: #b91c1c;
    color: #ffffff;
    border-color: #991b1b;
    box-shadow: 0 2px 6px rgba(185, 28, 28, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
}

/* Желтый - не проверено */
.assignment-mini-badge.assignment-status-pending {
    background-color: #d97706;
    color: #ffffff;
    border-color: #b45309;
    box-shadow: 0 2px 6px rgba(217, 119, 6, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
}

/* Зеленый - оценка */
.assignment-mini-badge.assignment-status-graded {
    background-color: #059669;
    color: #ffffff;
    border-color: #047857;
    box-shadow: 0 2px 6px rgba(5, 150, 105, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
}
</style>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const syncIndicator = document.getElementById('sync-indicator');
    const syncProgress = document.getElementById('sync-progress');
    const syncMessage = document.getElementById('sync-message');
    
    // Проверяем, есть ли у пользователя moodle_user_id
    @if(auth()->user()->moodle_user_id)
        // Показываем индикатор синхронизации
        syncIndicator.classList.remove('d-none');
        
        // Обновляем сообщение
        syncMessage.textContent = 'Запуск синхронизации...';
        syncProgress.style.width = '10%';
        
        // Симуляция прогресса во время синхронизации
        let progress = 10;
        let progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += 5;
                syncProgress.style.width = progress + '%';
            }
        }, 1000);
        
        // Функция для остановки синхронизации и скрытия индикатора
        function stopSync(message, isError = false) {
            clearInterval(progressInterval);
            syncProgress.style.width = '100%';
            syncMessage.textContent = message;
            
            if (isError) {
                syncIndicator.classList.remove('alert-info');
                syncIndicator.classList.add('alert-danger');
            } else {
                syncIndicator.classList.remove('alert-info');
                syncIndicator.classList.add('alert-success');
            }
            
            // Скрываем индикатор через несколько секунд
            setTimeout(() => {
                syncIndicator.classList.add('d-none');
                // Не перезагружаем страницу автоматически
                // Пользователь может обновить страницу вручную при необходимости
            }, isError ? 5000 : 3000);
        }
        
        // Создаем AbortController для возможности отмены запроса
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 90000); // 90 секунд таймаут
        
        // Запускаем синхронизацию через AJAX
        fetch('{{ route("admin.dashboard.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            clearInterval(progressInterval);
            
            if (data.success) {
                stopSync('Синхронизация завершена успешно!', false);
            } else {
                stopSync('Ошибка: ' + (data.message || 'Неизвестная ошибка'), true);
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            clearInterval(progressInterval);
            
            if (error.name === 'AbortError') {
                stopSync('Синхронизация превысила время ожидания. Попробуйте обновить страницу.', true);
            } else {
                console.error('Ошибка синхронизации:', error);
                stopSync('Ошибка соединения с сервером: ' + error.message, true);
            }
        });
    @else
        // Если нет moodle_user_id, скрываем индикатор
        syncIndicator.classList.add('d-none');
    @endif
});
</script>
