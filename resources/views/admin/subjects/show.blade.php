@extends('layouts.admin')

@section('title', 'Просмотр предмета')
@section('page-title', 'Просмотр предмета')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-book-open me-2"></i>{{ $subject->name }}
                    </h3>
                    <div>
                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Редактировать
                        </a>
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Назад к списку
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Информация о предмете</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="200"><strong>ID:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $subject->id }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Название:</strong></td>
                                    <td>{{ $subject->name }}</td>
                                </tr>
                                @if($subject->code)
                                <tr>
                                    <td><strong>Код предмета:</strong></td>
                                    <td><span class="badge bg-info">{{ $subject->code }}</span></td>
                                </tr>
                                @endif
                                @if($subject->short_description)
                                <tr>
                                    <td><strong>Краткое описание:</strong></td>
                                    <td>{{ $subject->short_description }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Статус:</strong></td>
                                    <td>
                                        @if($subject->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Активен
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Неактивен
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Порядок:</strong></td>
                                    <td>{{ $subject->order }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Создан:</strong></td>
                                    <td>{{ $subject->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Обновлен:</strong></td>
                                    <td>{{ $subject->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>

                            @if($subject->description)
                                <h5>Описание</h5>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $subject->description }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            @if($subject->image)
                                <h5>Изображение</h5>
                                <div class="card mb-3">
                                    <img src="{{ asset('storage/' . $subject->image) }}" 
                                         alt="{{ $subject->name }}" 
                                         class="card-img-top" 
                                         style="max-height: 300px; object-fit: cover;">
                                </div>
                            @endif

                            <h5>Статистика</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Курсов в предмете:</span>
                                        <span class="badge bg-primary">{{ $courses->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Активных курсов:</span>
                                        <span class="badge bg-success">{{ $courses->where('is_active', true)->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Программ:</span>
                                        <span class="badge bg-info">{{ $programs->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Список программ предмета -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>
                        Программы, в которые входит предмет ({{ $programs->count() }})
                    </h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                        <i class="fas fa-plus me-1"></i>Добавить программу
                    </button>
                </div>
                <div class="card-body">
                    @if($programs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Название программы</th>
                                        <th>Код</th>
                                        <th>Учебное заведение</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programs as $program)
                                    <tr>
                                        <td>{{ $program->id }}</td>
                                        <td>
                                            <strong>{{ $program->name }}</strong>
                                            @if($program->code)
                                                <br><small class="text-muted">{{ $program->code }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->code)
                                                <span class="badge bg-info">{{ $program->code }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->institution)
                                                <span class="badge bg-secondary">{{ $program->institution->name }}</span>
                                            @else
                                                <span class="text-muted">Не указано</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.programs.show', $program) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.subjects.programs.detach', [$subject->id, $program->id]) }}" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Удалить программу «{{ $program->name }}» из предмета?');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Удалить из предмета">
                                                        <i class="fas fa-times"></i>
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
                            Предмет пока не добавлен ни в одну программу. 
                            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                                <i class="fas fa-plus me-1"></i>Добавить программу
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Список курсов предмета -->
    @if($courses->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Курсы предмета ({{ $courses->count() }})
                    </h5>
                    <a href="{{ route('admin.courses.create') }}?subject_id={{ $subject->id }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Добавить курс
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
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
                                @foreach($courses as $course)
                                <tr>
                                    <td>{{ $course->id }}</td>
                                        <td>
                                            <strong>{{ $course->name }}</strong>
                                            @if($course->short_description)
                                                <br><small class="text-muted">{{ \Str::limit($course->short_description, 50) }}</small>
                                            @endif
                                        </td>
                                    <td>
                                        @if($course->code)
                                            <span class="badge bg-info">{{ $course->code }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->instructor)
                                            {{ $course->instructor->name }}
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $course->users_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @if($course->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-secondary">Неактивен</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.courses.show', $course) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.courses.edit', $course) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
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
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                В этом предмете пока нет курсов. 
                <a href="{{ route('admin.courses.create') }}?subject_id={{ $subject->id }}" class="alert-link">Добавить первый курс</a>
            </div>
        </div>
    </div>
    @endif

    <!-- Модальное окно для добавления программы -->
    <div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProgramModalLabel">Добавить программу к предмету</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.subjects.programs.attach', $subject->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="program_id" class="form-label">Выберите программу *</label>
                            <select class="form-select @error('program_id') is-invalid @enderror" 
                                    id="program_id" name="program_id" required>
                                <option value="">Выберите программу</option>
                                @foreach($availablePrograms as $availableProgram)
                                    @if(!$programs->contains('id', $availableProgram->id))
                                        <option value="{{ $availableProgram->id }}">
                                            {{ $availableProgram->name }}
                                            @if($availableProgram->code)
                                                ({{ $availableProgram->code }})
                                            @endif
                                            @if($availableProgram->institution)
                                                — {{ $availableProgram->institution->name }}
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="order" class="form-label">Порядок в предмете</label>
                            <input type="number" class="form-control" 
                                   id="order" name="order" 
                                   value="{{ $programs->count() }}" min="0">
                            <div class="form-text">Чем меньше число, тем выше в списке</div>
                        </div>
                        @if($availablePrograms->whereNotIn('id', $programs->pluck('id'))->isEmpty())
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Все доступные программы уже добавлены к предмету. 
                                <a href="{{ route('admin.programs.create') }}" target="_blank" class="alert-link">
                                    Создать новую программу
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary" 
                                {{ $availablePrograms->whereNotIn('id', $programs->pluck('id'))->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-plus me-1"></i>Добавить программу
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
