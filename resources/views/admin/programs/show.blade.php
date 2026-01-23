@extends('layouts.admin')

@section('title', 'Просмотр программы')
@section('page-title', 'Просмотр образовательной программы')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-book me-2"></i>{{ $program->name }}
                    </h3>
                    <div>
                        <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Редактировать
                        </a>
                        <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Назад к списку
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Информация о программе</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="200"><strong>ID:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $program->id }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Название:</strong></td>
                                    <td>{{ $program->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Код программы:</strong></td>
                                    <td>{{ $program->code ?? 'Не указан' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Учебное заведение:</strong></td>
                                    <td>
                                        @if($program->institution)
                                            <span class="badge bg-info">{{ $program->institution->name }}</span>
                                        @else
                                            <span class="text-muted">Не указано</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Локация:</strong></td>
                                    <td>{{ $program->location ?? 'Не указана' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Продолжительность:</strong></td>
                                    <td>{{ $program->duration ? $program->duration . ' месяцев' : 'Не указана' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Кредиты:</strong></td>
                                    <td>{{ $program->credits ?? 'Не указано' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Статус:</strong></td>
                                    <td>
                                        @if($program->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Активна
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Неактивна
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Создана:</strong></td>
                                    <td>{{ $program->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Обновлена:</strong></td>
                                    <td>{{ $program->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>

                            @if($program->description)
                                <h5>Описание</h5>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $program->description }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <h5>Статистика</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Предметов в программе:</span>
                                        <span class="badge bg-primary">{{ $subjects->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Активных предметов:</span>
                                        <span class="badge bg-success">{{ $subjects->where('is_active', true)->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Всего курсов:</span>
                                        <span class="badge bg-info">{{ $subjects->sum(fn($s) => $s->courses->count()) + $courses->count() }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($subjects->count() > 0)
                                <h5 class="mt-4">Предметы программы</h5>
                                <div class="list-group">
                                    @foreach($subjects->take(5) as $subject)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $subject->name }}</h6>
                                                <small>
                                                    @if($subject->is_active)
                                                        <span class="badge bg-success">Активен</span>
                                                    @else
                                                        <span class="badge bg-secondary">Неактивен</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <p class="mb-1">{{ $subject->short_description ?? $subject->description ?? 'Без описания' }}</p>
                                            <small>
                                                Курсов: {{ $subject->courses->count() }}
                                            </small>
                                        </div>
                                    @endforeach
                                    
                                    @if($subjects->count() > 5)
                                        <div class="list-group-item text-center">
                                            <small class="text-muted">
                                                И еще {{ $subjects->count() - 5 }} предметов...
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Список всех предметов программы -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book-open me-2"></i>
                        Предметы программы ({{ $subjects->count() }})
                    </h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="fas fa-plus me-1"></i>Добавить предмет
                    </button>
                </div>
                <div class="card-body">
                    @if($subjects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Порядок</th>
                                        <th>ID</th>
                                        <th>Название предмета</th>
                                        <th>Код</th>
                                        <th>Курсов</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $index => $subject)
                                    <tr>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <form method="POST" action="{{ route('admin.programs.subjects.move-up', [$program->id, $subject->id]) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-outline-secondary btn-sm" 
                                                            title="Переместить вверх"
                                                            {{ $index === 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-arrow-up"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.programs.subjects.move-down', [$program->id, $subject->id]) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-outline-secondary btn-sm" 
                                                            title="Переместить вниз"
                                                            {{ $index === $subjects->count() - 1 ? 'disabled' : '' }}>
                                                        <i class="fas fa-arrow-down"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td>{{ $subject->id }}</td>
                                        <td>
                                            <strong>{{ $subject->name }}</strong>
                                            @if($subject->short_description)
                                                <br><small class="text-muted">{{ Str::limit($subject->short_description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($subject->code)
                                                <span class="badge bg-info">{{ $subject->code }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $subject->courses->count() }}</span>
                                        </td>
                                        <td>
                                            @if($subject->is_active)
                                                <span class="badge bg-success">Активен</span>
                                            @else
                                                <span class="badge bg-secondary">Неактивен</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.subjects.show', $subject) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.programs.subjects.detach', [$program->id, $subject->id]) }}" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Удалить предмет «{{ $subject->name }}» из программы?');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Удалить из программы">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Показываем курсы предмета -->
                                    @if($subject->courses->count() > 0)
                                        <tr class="bg-light">
                                            <td colspan="7" class="p-0">
                                                <div class="p-3">
                                                    <strong class="text-muted small">Курсы предмета «{{ $subject->name }}»:</strong>
                                                    <div class="mt-2">
                                                        @foreach($subject->courses as $course)
                                                            <span class="badge bg-secondary me-2 mb-1">
                                                                <a href="{{ route('admin.courses.show', $course) }}" 
                                                                   class="text-white text-decoration-none">
                                                                    {{ $course->name }}
                                                                </a>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            В программе пока нет предметов. 
                            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                <i class="fas fa-plus me-1"></i>Добавить предмет
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Старые курсы (для обратной совместимости) -->
    @if($courses->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Курсы программы (без предмета) ({{ $courses->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Порядок</th>
                                    <th>ID</th>
                                    <th>Название курса</th>
                                    <th>Код</th>
                                    <th>Преподаватель</th>
                                    <th>Студентов</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $index => $course)
                                <tr>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <form method="POST" action="{{ route('admin.programs.courses.move-up', [$program->id, $course->id]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-outline-secondary btn-sm" 
                                                        title="Переместить вверх"
                                                        {{ $index === 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.programs.courses.move-down', [$program->id, $course->id]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-outline-secondary btn-sm" 
                                                        title="Переместить вниз"
                                                        {{ $index === $program->courses->count() - 1 ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td>{{ $course->id }}</td>
                                    <td>
                                        <strong>{{ $course->name }}</strong>
                                        @if($course->short_description)
                                            <br><small class="text-muted">{{ \Str::limit($course->short_description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->code)
                                            <span class="badge bg-secondary">{{ $course->code }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->instructor)
                                            <div class="d-flex align-items-center">
                                                @if($course->instructor->photo)
                                                    <img src="{{ asset('storage/' . $course->instructor->photo) }}" 
                                                         alt="{{ $course->instructor->name }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 30px; height: 30px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 30px; height: 30px; font-size: 0.75rem;">
                                                        {{ strtoupper(mb_substr($course->instructor->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div>{{ $course->instructor->name }}</div>
                                                    <small class="text-muted">{{ $course->instructor->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $course->users_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @if($course->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Активен
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-times me-1"></i>Неактивен
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.courses.show', $course->id) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Просмотр курса">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.courses.edit', $course->id) }}" 
                                           class="btn btn-sm btn-warning" 
                                           title="Редактировать курс">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
    @else
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                    <p class="text-muted">В программе пока нет курсов</p>
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Добавить курс
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Прикрепленные группы -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Прикрепленные глобальные группы ({{ $attachedGroups->count() }})
                    </h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#attachGroupModal">
                        <i class="fas fa-plus me-1"></i>Прикрепить группу
                    </button>
                </div>
                <div class="card-body">
                    @if($attachedGroups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Название группы</th>
                                        <th>Описание</th>
                                        <th>Студентов в группе</th>
                                        <th>Прикреплена</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attachedGroups as $group)
                                    <tr>
                                        <td>{{ $group->id }}</td>
                                        <td>
                                            <strong>{{ $group->name }}</strong>
                                            @if($group->moodle_cohort_id)
                                                <br><small class="text-muted">
                                                    <i class="fas fa-link me-1"></i>Moodle Cohort ID: {{ $group->moodle_cohort_id }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($group->description)
                                                {{ Str::limit($group->description, 100) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $group->students_count }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $group->pivot->attached_at ? \Carbon\Carbon::parse($group->pivot->attached_at)->format('d.m.Y H:i') : '—' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.groups.show', $group) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Просмотр группы">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.programs.groups.detach', [$program->id, $group->id]) }}" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Открепить группу «{{ $group->name }}» от программы? Студенты останутся записанными на программу.');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Открепить от программы">
                                                        <i class="fas fa-unlink"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            К программе пока не прикреплено ни одной глобальной группы. 
                            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#attachGroupModal">
                                <i class="fas fa-plus me-1"></i>Прикрепить группу
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для прикрепления группы -->
    <div class="modal fade" id="attachGroupModal" tabindex="-1" aria-labelledby="attachGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachGroupModalLabel">
                        <i class="fas fa-users me-2"></i>Прикрепить глобальную группу к программе
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.programs.groups.attach', $program->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            При прикреплении группы все студенты из этой группы будут автоматически записаны на программу.
                        </div>
                        <div class="mb-3">
                            <label for="group_id" class="form-label">Выберите группу <span class="text-danger">*</span></label>
                            <select class="form-select" id="group_id" name="group_id" required>
                                <option value="">— Выберите группу —</option>
                                @foreach($availableGroups as $group)
                                    <option value="{{ $group->id }}">
                                        {{ $group->name }}
                                        @if($group->students_count > 0)
                                            ({{ $group->students_count }} {{ trans_choice('студент|студента|студентов', $group->students_count) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($availableGroups->isEmpty())
                                <small class="text-muted">
                                    Нет доступных глобальных групп для прикрепления. 
                                    <a href="{{ route('admin.moodle-sync.index') }}">Синхронизируйте группы из Moodle</a>.
                                </small>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Заметки (необязательно)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Дополнительная информация о прикреплении группы..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary" 
                                {{ $availableGroups->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-link me-1"></i>Прикрепить группу
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для добавления предмета -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubjectModalLabel">Добавить предмет в программу</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.programs.subjects.attach', $program->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Выберите предмет *</label>
                            <select class="form-select @error('subject_id') is-invalid @enderror" 
                                    id="subject_id" name="subject_id" required>
                                <option value="">Выберите предмет</option>
                                @foreach($availableSubjects as $availableSubject)
                                    @if(!$subjects->contains('id', $availableSubject->id))
                                        <option value="{{ $availableSubject->id }}">
                                            {{ $availableSubject->name }}
                                            @if($availableSubject->code)
                                                ({{ $availableSubject->code }})
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="order" class="form-label">Порядок в программе</label>
                            <input type="number" class="form-control" 
                                   id="order" name="order" 
                                   value="{{ $subjects->count() }}" min="0">
                            <div class="form-text">Чем меньше число, тем выше в списке</div>
                        </div>
                        @if($availableSubjects->whereNotIn('id', $subjects->pluck('id'))->isEmpty())
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Все доступные предметы уже добавлены в программу. 
                                <a href="{{ route('admin.subjects.create') }}" target="_blank" class="alert-link">
                                    Создать новый предмет
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary" 
                                {{ $availableSubjects->whereNotIn('id', $subjects->pluck('id'))->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-plus me-1"></i>Добавить предмет
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
