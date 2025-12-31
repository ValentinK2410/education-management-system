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
                        @if(!$isAdmin)
                            <small class="text-muted ms-2">(Мои курсы)</small>
                        @endif
                    </h3>
                    @if($isAdmin)
                        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Добавить курс
                        </a>
                    @endif
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
                                                @if($isAdmin)
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
                                                @endif
                                                @if(!$isAdmin && isset($coursesWithAssignments[$course->id]) && !empty($coursesWithAssignments[$course->id]))
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary assignment-toggle"
                                                            data-course-id="{{ $course->id }}"
                                                            title="Показать задания">
                                                        <i class="fas fa-tasks"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if(!$isAdmin && isset($coursesWithAssignments[$course->id]) && !empty($coursesWithAssignments[$course->id]))
                                        <tr class="assignment-row" id="assignments-{{ $course->id }}" style="display: none;">
                                            <td colspan="8">
                                                <div class="p-3 bg-light">
                                                    <h6 class="mb-3">
                                                        <i class="fas fa-tasks me-2"></i>ПОСЛЕ СЕССИИ
                                                    </h6>
                                                    <div class="row">
                                                        @foreach($coursesWithAssignments[$course->id] as $assignment)
                                                            <div class="col-md-12 mb-2">
                                                                <div class="card assignment-card assignment-status-{{ $assignment['status'] }}">
                                                                    <div class="card-body py-2">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <h6 class="card-title mb-1">{{ $assignment['name'] }}</h6>
                                                                                @if($assignment['submitted_at'])
                                                                                    <small class="text-muted">
                                                                                        Сдано: {{ \Carbon\Carbon::createFromTimestamp($assignment['submitted_at'])->format('d.m.Y H:i') }}
                                                                                    </small>
                                                                                @endif
                                                                            </div>
                                                                            <div class="assignment-status-badge assignment-status-{{ $assignment['status'] }}">
                                                                                {{ $assignment['status_text'] }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
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

/* Стили для уменьшения размера пагинации (Bootstrap) */
.pagination-wrapper .pagination {
    margin-bottom: 0 !important;
    font-size: 0.875rem !important;
}

.pagination-wrapper .pagination .page-link {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1.4 !important;
    min-width: 32px !important;
    height: 32px !important;
    text-align: center !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.pagination-wrapper .pagination .page-item {
    margin: 0 1px !important;
}

.pagination-wrapper .pagination .page-item:first-child .page-link {
    border-top-left-radius: 0.375rem !important;
    border-bottom-left-radius: 0.375rem !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-item:last-child .page-link {
    border-top-right-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-item.disabled .page-link {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-item.active .page-link {
    z-index: 3 !important;
    color: #fff !important;
    background-color: #6366f1 !important;
    border-color: #6366f1 !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-link i {
    font-size: 0.75rem !important;
}

.pagination-wrapper .pagination .page-link span {
    font-size: 0.875rem !important;
}

/* Стили для Laravel Tailwind пагинации */
.pagination-wrapper nav[role="navigation"] a,
.pagination-wrapper nav[role="navigation"] span[aria-disabled="true"] span,
.pagination-wrapper nav[role="navigation"] span[aria-current="page"] span {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1.4 !important;
    min-width: 32px !important;
    height: 32px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.pagination-wrapper nav[role="navigation"] svg {
    width: 1rem !important;
    height: 1rem !important;
}

.pagination-wrapper nav[role="navigation"] .text-sm {
    font-size: 0.875rem !important;
}

/* Переопределение Tailwind padding классов в пагинации */
.pagination-wrapper nav[role="navigation"] .px-4 {
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
}

.pagination-wrapper nav[role="navigation"] .py-2 {
    padding-top: 0.25rem !important;
    padding-bottom: 0.25rem !important;
}

.pagination-wrapper nav[role="navigation"] .px-2 {
    padding-left: 0.25rem !important;
    padding-right: 0.25rem !important;
}

/* Уменьшаем размер иконок в пагинации */
.pagination-wrapper nav[role="navigation"] .w-5 {
    width: 1rem !important;
}

.pagination-wrapper nav[role="navigation"] .h-5 {
    height: 1rem !important;
}

/* Стили для заданий */
.assignment-card {
    border-left: 4px solid;
    transition: transform 0.2s, box-shadow 0.2s;
}

.assignment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Красный - не сдано */
.assignment-status-not-submitted {
    border-left-color: #dc3545;
    background-color: #fff5f5;
}

.assignment-status-not-submitted .assignment-status-badge {
    background-color: #dc3545;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

/* Желтый - не проверено */
.assignment-status-pending {
    border-left-color: #ffc107;
    background-color: #fffbf0;
}

.assignment-status-pending .assignment-status-badge {
    background-color: #ffc107;
    color: #000;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

/* Зеленый - оценка */
.assignment-status-graded {
    border-left-color: #28a745;
    background-color: #f0fff4;
}

.assignment-status-graded .assignment-status-badge {
    background-color: #28a745;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.assignment-status-badge {
    white-space: nowrap;
}

.assignment-row {
    background-color: #f8f9fa;
}
</style>

@if(!$isAdmin)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.assignment-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            const assignmentRow = document.getElementById('assignments-' + courseId);
            const icon = this.querySelector('i');
            
            if (assignmentRow.style.display === 'none') {
                assignmentRow.style.display = '';
                icon.classList.remove('fa-tasks');
                icon.classList.add('fa-chevron-up');
                this.setAttribute('title', 'Скрыть задания');
            } else {
                assignmentRow.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-tasks');
                this.setAttribute('title', 'Показать задания');
            }
        });
    });
});
</script>
@endif
@endsection
