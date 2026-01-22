@extends('layouts.admin')

@section('title', 'Просмотр группы')
@section('page-title', 'Группа: ' . $group->name)

@section('content')
<div class="container-fluid fade-in-up">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>{{ $group->name }}
                    </h3>
                    <div class="btn-group">
                        <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Редактировать
                        </a>
                        <form action="{{ route('admin.groups.destroy', $group) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Вы уверены, что хотите удалить эту группу?')">
                                <i class="fas fa-trash me-1"></i>Удалить
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Основная информация</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $group->id }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Название:</strong></td>
                                    <td>{{ $group->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Курс:</strong></td>
                                    <td>
                                        @if($group->course)
                                            <a href="{{ route('admin.courses.show', $group->course) }}" class="text-decoration-none">
                                                {{ $group->course->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Не привязан</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Программа:</strong></td>
                                    <td>
                                        @if($group->program)
                                            <a href="{{ route('admin.programs.show', $group->program) }}" class="text-decoration-none">
                                                {{ $group->program->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Не привязана</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Статус:</strong></td>
                                    <td>
                                        @if($group->is_active)
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
                            </table>
                        </div>
                    </div>

                    @if($group->description)
                        <h5 class="mt-4">Описание</h5>
                        <p>{{ $group->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Список студентов -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Студенты в группе ({{ $group->students->count() }})
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="fas fa-plus me-1"></i>Добавить студента
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Email</th>
                                    <th>Дата зачисления</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($group->students as $student)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $student->id }}</span></td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $student) }}" class="text-decoration-none">
                                                {{ $student->name }}
                                            </a>
                                        </td>
                                        <td>{{ $student->email }}</td>
                                        <td>
                                            {{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d.m.Y') : '—' }}
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.groups.remove-student', [$group, $student]) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Удалить студента из группы?')">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-user-graduate fa-3x mb-3"></i>
                                                <p>В группе пока нет студентов</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Статистика -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">Статистика</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12">
                            <h4 class="text-primary">{{ $group->students->count() }}</h4>
                            <small class="text-muted">Студентов в группе</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal для добавления студента -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.groups.add-student', $group) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Добавить студента в группу</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Студент *</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                id="user_id" name="user_id" required>
                            <option value="">Выберите студента</option>
                            @foreach(\App\Models\User::whereHas('roles', function($q) { $q->where('slug', 'student'); })->orderBy('name')->get() as $user)
                                @if(!$group->students->contains($user->id))
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endif
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Заметки</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
