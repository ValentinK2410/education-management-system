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
                                        <span>Курсы в программе:</span>
                                        <span class="badge bg-primary">{{ $program->courses->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Активные курсы:</span>
                                        <span class="badge bg-success">{{ $program->courses->where('is_active', true)->count() }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($program->courses->count() > 0)
                                <h5 class="mt-4">Курсы программы</h5>
                                <div class="list-group">
                                    @foreach($program->courses->take(5) as $course)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $course->name }}</h6>
                                                <small>
                                                    @if($course->is_active)
                                                        <span class="badge bg-success">Активен</span>
                                                    @else
                                                        <span class="badge bg-secondary">Неактивен</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <p class="mb-1">{{ $course->description ?? 'Без описания' }}</p>
                                            <small>
                                                @if($course->instructor)
                                                    Преподаватель: {{ $course->instructor->name }}
                                                @else
                                                    Преподаватель не назначен
                                                @endif
                                            </small>
                                        </div>
                                    @endforeach
                                    
                                    @if($program->courses->count() > 5)
                                        <div class="list-group-item text-center">
                                            <small class="text-muted">
                                                И еще {{ $program->courses->count() - 5 }} курсов...
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
</div>
@endsection
