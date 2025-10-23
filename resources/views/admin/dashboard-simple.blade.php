@extends('layouts.admin')

@section('title', 'Панель управления')
@section('page-title', 'Панель управления')

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Статистические карточки -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего пользователей
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['users'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Учебные заведения
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['institutions'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Образовательные программы
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['programs'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Курсы
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['courses'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Быстрые действия -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Быстрые действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="/admin/users" class="btn btn-primary w-100">
                                <i class="fas fa-users me-2"></i>Управление пользователями
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/admin/institutions" class="btn btn-success w-100">
                                <i class="fas fa-university me-2"></i>Учебные заведения
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/admin/programs" class="btn btn-info w-100">
                                <i class="fas fa-book me-2"></i>Образовательные программы
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/admin/courses" class="btn btn-warning w-100">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Курсы
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Информация о системе -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Информация о системе
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Статус системы</h6>
                            <p class="text-success">
                                <i class="fas fa-check-circle me-2"></i>Система работает нормально
                            </p>
                            <p class="text-info">
                                <i class="fas fa-user me-2"></i>Текущий пользователь: {{ auth()->user()->name ?? 'Не авторизован' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Версия системы</h6>
                            <p>Laravel {{ app()->version() }}</p>
                            <p>PHP {{ PHP_VERSION }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endsection
