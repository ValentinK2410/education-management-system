@extends('layouts.app')

@section('title', 'Просмотр пользователя')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Информация о пользователе: {{ $user->name }}</h3>
                    <div>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Основная информация</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Имя:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Телефон:</strong></td>
                                    <td>{{ $user->phone ?? 'Не указан' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Статус:</strong></td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Дата регистрации:</strong></td>
                                    <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Роли пользователя</h5>
                            @if($user->roles->count() > 0)
                                <div class="mb-3">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary me-2">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Роли не назначены</p>
                            @endif

                            @if($user->bio)
                                <h5>Биография</h5>
                                <p>{{ $user->bio }}</p>
                            @endif
                        </div>
                    </div>

                    @if($user->taughtCourses->count() > 0)
                        <hr>
                        <h5>Преподаваемые курсы</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Название курса</th>
                                        <th>Программа</th>
                                        <th>Учебное заведение</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->taughtCourses as $course)
                                        <tr>
                                            <td>{{ $course->name }}</td>
                                            <td>{{ $course->program->name }}</td>
                                            <td>{{ $course->program->institution->name }}</td>
                                            <td>
                                                @if($course->is_active)
                                                    <span class="badge bg-success">Активен</span>
                                                @else
                                                    <span class="badge bg-secondary">Неактивен</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
