@extends('layouts.admin')

@section('title', 'Просмотр курса')
@section('page-title', 'Просмотр курса')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>{{ $course->name }}
                    </h3>
                    <div>
                        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Редактировать
                        </a>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Назад к списку
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Информация о курсе</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="200"><strong>ID:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $course->id }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Название:</strong></td>
                                    <td>{{ $course->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Код курса:</strong></td>
                                    <td>{{ $course->code ?? 'Не указан' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Программа:</strong></td>
                                    <td>
                                        @if($course->program)
                                            <span class="badge bg-info">{{ $course->program->name }}</span>
                                        @else
                                            <span class="text-muted">Не указана</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Преподаватель:</strong></td>
                                    <td>
                                        @if($course->instructor)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-success text-white rounded-circle">
                                                        {{ substr($course->instructor->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div>{{ $course->instructor->name }}</div>
                                                    <small class="text-muted">{{ $course->instructor->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Не назначен</span>
                                        @endif

                                        @if($course->instructors && $course->instructors->count() > 0)
                                            <div class="mt-2">
                                                <small class="text-muted d-block mb-1">Преподаватели курса (из Moodle):</small>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($course->instructors as $inst)
                                                        @php
                                                            $roleShort = $inst->pivot->moodle_role_shortname ?? null;
                                                            $isPrimary = (bool)($inst->pivot->is_primary ?? false);
                                                            $badgeClass = $isPrimary ? 'bg-success' : 'bg-secondary';
                                                            $roleLabel = $roleShort ? " ({$roleShort})" : '';
                                                            $source = $inst->pivot->source ?? 'moodle';
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }}"
                                                              title="{{ $inst->email }} | source={{ $source }}{{ $roleLabel }}">
                                                            {{ $inst->name }}@if($isPrimary) ★@endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Кредиты:</strong></td>
                                    <td>{{ $course->credits ?? 'Не указано' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Часы:</strong></td>
                                    <td>{{ $course->hours ?? 'Не указано' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Семестр:</strong></td>
                                    <td>{{ $course->semester ?? 'Не указан' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Статус:</strong></td>
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
                                </tr>
                                <tr>
                                    <td><strong>Создан:</strong></td>
                                    <td>{{ $course->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Обновлен:</strong></td>
                                    <td>{{ $course->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>

                            @if($course->description)
                                <h5>Описание</h5>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $course->description }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <h5>Дополнительная информация</h5>
                            <div class="card">
                                <div class="card-body">
                                    @if($course->program)
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span>Учебное заведение:</span>
                                            <span class="badge bg-primary">
                                                {{ $course->program->institution->name ?? 'Не указано' }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if($course->program)
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span>Курсов в программе:</span>
                                            <span class="badge bg-info">
                                                {{ $course->program->courses->count() ?? 0 }}
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Активных курсов:</span>
                                            <span class="badge bg-success">
                                                {{ $course->program->courses->where('is_active', true)->count() ?? 0 }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span>Курсов в программе:</span>
                                            <span class="badge bg-secondary">Не указано</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Активных курсов:</span>
                                            <span class="badge bg-secondary">Не указано</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($course->program && $course->program->courses->count() > 1)
                                <h5 class="mt-4">Другие курсы программы</h5>
                                <div class="list-group">
                                    @foreach($course->program->courses->where('id', '!=', $course->id)->take(5) as $otherCourse)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $otherCourse->name }}</h6>
                                                <small>
                                                    @if($otherCourse->is_active)
                                                        <span class="badge bg-success">Активен</span>
                                                    @else
                                                        <span class="badge bg-secondary">Неактивен</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <p class="mb-1">{{ $otherCourse->description ?? 'Без описания' }}</p>
                                            <small>
                                                @if($otherCourse->instructor)
                                                    Преподаватель: {{ $otherCourse->instructor->name }}
                                                @else
                                                    Преподаватель не назначен
                                                @endif
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
</style>
@endsection
