@extends('layouts.admin')

@section('title', 'Группы студентов')
@section('page-title', 'Группы студентов')

@push('styles')
<style>
    [data-theme="dark"] .container-fluid .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }
    [data-theme="dark"] .container-fluid .table {
        color: var(--text-color) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Группы студентов
                    </h3>
                    <a href="{{ route('admin.groups.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Создать группу
                    </a>
                </div>
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" action="{{ route('admin.groups.index') }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                                       placeholder="Поиск по названию">
                            </div>
                            <div class="col-md-2">
                                <select name="course_id" class="form-select">
                                    <option value="">Все курсы</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="program_id" class="form-select">
                                    <option value="">Все программы</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="is_active" class="form-select">
                                    <option value="">Все статусы</option>
                                    <option value="1" @selected(request('is_active') === '1')>Активные</option>
                                    <option value="0" @selected(request('is_active') === '0')>Неактивные</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search me-1"></i>Найти
                                </button>
                                <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo me-1"></i>Сбросить
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Курс</th>
                                    <th>Программа</th>
                                    <th>Студентов</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $group)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $group->id }}</span></td>
                                        <td>
                                            <strong>{{ $group->name }}</strong>
                                            @if($group->description)
                                                <br><small class="text-muted">{{ Str::limit($group->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($group->course)
                                                <a href="{{ route('admin.courses.show', $group->course) }}" class="text-decoration-none">
                                                    {{ $group->course->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($group->program)
                                                <a href="{{ route('admin.programs.show', $group->program) }}" class="text-decoration-none">
                                                    {{ $group->program->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $group->students->count() }}</span>
                                        </td>
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
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.groups.show', $group) }}"
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.groups.edit', $group) }}"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.groups.destroy', $group) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить эту группу?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>Группы не найдены</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($groups->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $groups->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
