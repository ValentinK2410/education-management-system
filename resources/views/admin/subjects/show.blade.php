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
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Активных курсов:</span>
                                        <span class="badge bg-success">{{ $courses->where('is_active', true)->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
</div>
@endsection
