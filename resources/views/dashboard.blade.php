@extends('layouts.app')

@section('title', 'Панель управления')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Панель управления</h1>
                <div class="text-muted">
                    Добро пожаловать, {{ auth()->user()->name }}!
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <!-- Admin Dashboard -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-4">Административная панель</h3>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Пользователи</h4>
                                <p class="card-text">Управление пользователями</p>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                            <i class="fas fa-cog me-1"></i>Управлять
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Заведения</h4>
                                <p class="card-text">Учебные заведения</p>
                            </div>
                            <i class="fas fa-university fa-2x"></i>
                        </div>
                        <a href="{{ route('admin.institutions.index') }}" class="btn btn-light">
                            <i class="fas fa-cog me-1"></i>Управлять
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Программы</h4>
                                <p class="card-text">Образовательные программы</p>
                            </div>
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                        <a href="{{ route('admin.programs.index') }}" class="btn btn-light">
                            <i class="fas fa-cog me-1"></i>Управлять
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">Курсы</h4>
                                <p class="card-text">Учебные курсы</p>
                            </div>
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-light">
                            <i class="fas fa-cog me-1"></i>Управлять
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- User Profile -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Мой профиль</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                @if(auth()->user()->avatar)
                                    <img src="{{ Storage::url(auth()->user()->avatar) }}"
                                         class="rounded-circle" width="100" height="100" alt="Avatar">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                         style="width: 100px; height: 100px;">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <h5>{{ auth()->user()->name }}</h5>
                            <p class="text-muted">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Имя:</strong></td>
                                    <td>{{ auth()->user()->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ auth()->user()->email }}</td>
                                </tr>
                                @if(auth()->user()->phone)
                                    <tr>
                                        <td><strong>Телефон:</strong></td>
                                        <td>{{ auth()->user()->phone }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>Роли:</strong></td>
                                    <td>
                                        @foreach(auth()->user()->roles as $role)
                                            <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Статус:</strong></td>
                                    <td>
                                        @if(auth()->user()->is_active)
                                            <span class="badge bg-success">Активен</span>
                                        @else
                                            <span class="badge bg-danger">Неактивен</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Быстрые действия</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('institutions.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-university me-2"></i>Учебные заведения
                        </a>
                        <a href="{{ route('programs.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-book me-2"></i>Программы
                        </a>
                        <a href="{{ route('courses.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Курсы
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
