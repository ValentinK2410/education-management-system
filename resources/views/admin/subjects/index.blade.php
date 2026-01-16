@extends('layouts.admin')

@section('title', 'Предметы')
@section('page-title', 'Предметы (глобальные курсы)')

@push('styles')
<style>
    [data-theme="dark"] .container-fluid .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card-header {
        background: var(--card-bg) !important;
        border-bottom-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card-body {
        background: var(--card-bg) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table thead th {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table tbody td {
        border-color: var(--border-color) !important;
        background-color: transparent !important;
        color: var(--text-color) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-book-open me-2"></i>Предметы
                </h2>
                <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Добавить предмет
                </a>
            </div>
            <p class="text-muted mt-2">Предметы объединяют несколько курсов одной тематики</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Список предметов</h5>
                </div>
                <div class="card-body">
                    @if($subjects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Порядок</th>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Код</th>
                                        <th>Курсов</th>
                                        <th>Программ</th>
                                        <th>Статус</th>
                                        <th style="width: 200px;">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $subject)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ $subject->order }}</span>
                                        </td>
                                        <td>{{ $subject->id }}</td>
                                        <td>
                                            <strong>{{ $subject->name }}</strong>
                                            @if($subject->short_description)
                                                <br><small class="text-muted">{{ \Str::limit($subject->short_description, 50) }}</small>
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
                                            <span class="badge bg-primary">{{ $subject->courses_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $subject->programs_count }}</span>
                                        </td>
                                        <td>
                                            @if($subject->is_active)
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
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.subjects.show', $subject) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.subjects.edit', $subject) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-success" 
                                                        title="Прикрепить к программе"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#attachProgramModal{{ $subject->id }}">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                                <form action="{{ route('admin.subjects.destroy', $subject) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот предмет?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $subjects->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Предметы еще не созданы. 
                            <a href="{{ route('admin.subjects.create') }}" class="alert-link">Создать первый предмет</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна для прикрепления к программам -->
@foreach($subjects as $subject)
<div class="modal fade" id="attachProgramModal{{ $subject->id }}" tabindex="-1" aria-labelledby="attachProgramModalLabel{{ $subject->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachProgramModalLabel{{ $subject->id }}">
                    Прикрепить предмет «{{ $subject->name }}» к программе
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.subjects.programs.attach', $subject->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="program_id_{{ $subject->id }}" class="form-label">Выберите программу *</label>
                        <select class="form-select @error('program_id') is-invalid @enderror" 
                                id="program_id_{{ $subject->id }}" name="program_id" required>
                            <option value="">Выберите программу</option>
                            @foreach($availablePrograms as $program)
                                @if(!$subject->programs->contains('id', $program->id))
                                    <option value="{{ $program->id }}">
                                        {{ $program->name }}
                                        @if($program->code)
                                            ({{ $program->code }})
                                        @endif
                                        @if($program->institution)
                                            — {{ $program->institution->name }}
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
                        <label for="order_{{ $subject->id }}" class="form-label">Порядок в программе</label>
                        <input type="number" class="form-control" 
                               id="order_{{ $subject->id }}" name="order" 
                               value="{{ $subject->programs->count() }}" min="0">
                        <div class="form-text">Чем меньше число, тем выше в списке</div>
                    </div>
                    @if($availablePrograms->whereNotIn('id', $subject->programs->pluck('id'))->isEmpty())
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Все доступные программы уже прикреплены к этому предмету. 
                            <a href="{{ route('admin.programs.create') }}" target="_blank" class="alert-link">
                                Создать новую программу
                            </a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary" 
                            {{ $availablePrograms->whereNotIn('id', $subject->programs->pluck('id'))->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-link me-1"></i>Прикрепить к программе
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
