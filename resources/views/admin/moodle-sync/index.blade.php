@extends('layouts.admin')

@section('title', 'Синхронизация с Moodle')
@section('page-title', 'Синхронизация с Moodle')

@push('styles')
<style>
    /* Основная карточка */
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

    /* Заголовки */
    [data-theme="dark"] .container-fluid h3,
    [data-theme="dark"] .container-fluid h5,
    [data-theme="dark"] .container-fluid .card-title {
        color: var(--text-color) !important;
    }

    /* Статистические карточки */
    [data-theme="dark"] .container-fluid .card.bg-info {
        background-color: rgba(59, 130, 246, 0.2) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-success {
        background-color: rgba(16, 185, 129, 0.2) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-light {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    /* Таблица */
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

    [data-theme="dark"] .container-fluid .table-striped tbody tr:nth-of-type(odd) {
        background-color: var(--card-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-striped tbody tr:nth-of-type(even) {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-hover tbody tr:hover {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-hover tbody tr:hover td {
        background-color: var(--dark-bg) !important;
        color: var(--text-color) !important;
    }

    /* Бейджи */
    [data-theme="dark"] .container-fluid .badge.bg-secondary {
        background-color: var(--secondary-color) !important;
        color: var(--text-color) !important;
    }

    /* Pre элемент для логов */
    [data-theme="dark"] .container-fluid pre {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    /* Кнопки */
    [data-theme="dark"] .container-fluid .btn-primary {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-info {
        background-color: rgba(59, 130, 246, 0.8) !important;
        border-color: rgba(59, 130, 246, 0.8) !important;
    }

    [data-theme="dark"] .container-fluid .btn-info:hover {
        background-color: rgba(59, 130, 246, 1) !important;
        border-color: rgba(59, 130, 246, 1) !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning:hover {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #1e293b !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-sync me-2"></i>Синхронизация данных из Moodle
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Статистика -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Всего курсов</h5>
                                    <h2 class="mb-0">{{ $totalCourses }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Синхронизировано с Moodle</h5>
                                    <h2 class="mb-0">{{ $coursesCount }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки синхронизации -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Действия синхронизации:</h5>
                            
                            <div class="btn-group-vertical w-100" role="group">
                                <form action="{{ route('admin.moodle-sync.sync-all') }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-lg w-100" onclick="return confirm('Запустить полную синхронизацию? Это может занять некоторое время.')">
                                        <i class="fas fa-sync-alt me-2"></i>Полная синхронизация (курсы + записи студентов)
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.moodle-sync.sync-courses') }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-lg w-100" onclick="return confirm('Синхронизировать только курсы?')">
                                        <i class="fas fa-book me-2"></i>Синхронизировать только курсы
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Список курсов для синхронизации записей -->
                    @php
                        $moodleCourses = \App\Models\Course::whereNotNull('moodle_course_id')->get();
                    @endphp
                    
                    @if($moodleCourses->count() > 0)
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">Синхронизировать записи студентов для конкретного курса:</h5>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Название курса</th>
                                                <th>Moodle ID</th>
                                                <th>Действие</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($moodleCourses as $course)
                                                <tr>
                                                    <td>{{ $course->id }}</td>
                                                    <td>{{ $course->name }}</td>
                                                    <td><span class="badge bg-secondary">{{ $course->moodle_course_id }}</span></td>
                                                    <td>
                                                        <form action="{{ route('admin.moodle-sync.sync-enrollments', $course->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Синхронизировать записи студентов для курса «{{ $course->name }}»?')">
                                                                <i class="fas fa-users me-1"></i>Синхронизировать записи
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Последние логи -->
                    @if(!empty($recentLogs))
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Последние логи синхронизации:</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <pre class="mb-0" style="max-height: 300px; overflow-y: auto; font-size: 0.875rem;">@foreach($recentLogs as $log){{ $log }}
@endforeach</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        setTimeout(function() {
            alert('{{ session('success') }}');
        }, 100);
    </script>
@endif

@if(session('error'))
    <script>
        setTimeout(function() {
            alert('Ошибка: {{ session('error') }}');
        }, 100);
    </script>
@endif
@endsection

