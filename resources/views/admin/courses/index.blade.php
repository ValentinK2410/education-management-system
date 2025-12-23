@extends('layouts.admin')

@section('title', 'Управление курсами')
@section('page-title', 'Курсы')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Курсы
                    </h3>
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Добавить курс
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Программа</th>
                                    <th>Преподаватель</th>
                                    <th>Тип оплаты</th>
                                    <th>Цена</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $course->id }}</span></td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $course->name }}</h6>
                                                <small class="text-muted">{{ $course->code ?? 'Без кода' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($course->program)
                                                <span class="badge bg-info">{{ $course->program->name }}</span>
                                            @else
                                                <span class="text-muted">Не указана</span>
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
                                                        <small class="text-muted">{{ $course->instructor->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Не назначен</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($course->is_paid)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-dollar-sign me-1"></i>Платный
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-gift me-1"></i>Бесплатный
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($course->is_paid && $course->price)
                                                <strong>{{ number_format($course->price, 0) }} {{ $course->currency ?? 'USD' }}</strong>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($course->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Активен
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Неактивен
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.courses.show', $course) }}"
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.courses.edit', $course) }}"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.courses.duplicate', $course) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary"
                                                            title="Дублировать курс"
                                                            onclick="return confirm('Создать копию этого курса?')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.courses.destroy', $course) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить этот курс?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                                                <p>Курсы не найдены</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($courses->hasPages())
                        <div class="d-flex justify-content-center mt-4 pagination-wrapper">
                            {{ $courses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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

/* Стили для уменьшения размера пагинации */
.pagination-wrapper .pagination {
    margin-bottom: 0;
}

.pagination-wrapper .pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
    min-width: 38px;
    text-align: center;
}

.pagination-wrapper .pagination .page-item {
    margin: 0 2px;
}

.pagination-wrapper .pagination .page-item:first-child .page-link {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.pagination-wrapper .pagination .page-item:last-child .page-link {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.pagination-wrapper .pagination .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-wrapper .pagination .page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #6366f1;
    border-color: #6366f1;
}
</style>
@endsection
