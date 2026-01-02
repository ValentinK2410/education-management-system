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

    <!-- Список всех курсов программы -->
    @if($program->courses->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Все курсы программы ({{ $program->courses->count() }})
                    </h5>
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
                                @foreach($program->courses as $course)
                                <tr>
                                    <td>{{ $course->id }}</td>
                                    <td>
                                        <strong>{{ $course->name }}</strong>
                                        @if($course->short_description)
                                            <br><small class="text-muted">{{ Str::limit($course->short_description, 50) }}</small>
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
</div>
@endsection
