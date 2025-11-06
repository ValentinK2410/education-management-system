@extends('layouts.app')

@section('title', 'Мой профиль')

@section('content')
<div class="container-fluid py-5">
    <!-- Profile Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-lg">
                            @if($user->photo)
                                <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="rounded-circle w-100">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="font-size: 3rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col">
                        <h2 class="mb-2">{{ $user->name }}</h2>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2"></i>{{ $user->email }}
                            @if($user->phone)
                                <span class="ms-3"><i class="fas fa-phone me-2"></i>{{ $user->phone }}</span>
                            @endif
                        </p>
                        @if($user->city)
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>{{ $user->city }}
                            </p>
                        @endif
                    </div>
                    <div class="col-auto">
                        @auth
                            @if(Auth::id() === $user->id)
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Редактировать профиль
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-book-open fa-3x text-primary mb-3"></i>
                    <h3 class="mb-1">{{ $stats['total_programs'] }}</h3>
                    <p class="text-muted mb-0">Программ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h3 class="mb-1">{{ $stats['completed_programs'] }}</h3>
                    <p class="text-muted mb-0">Завершено</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-play-circle fa-3x text-info mb-3"></i>
                    <h3 class="mb-1">{{ $stats['active_programs'] }}</h3>
                    <p class="text-muted mb-0">В процессе</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm border-0 stats-card">
                <div class="card-body text-center">
                    <i class="fas fa-chalkboard-teacher fa-3x text-warning mb-3"></i>
                    <h3 class="mb-1">{{ $stats['total_courses'] }}</h3>
                    <p class="text-muted mb-0">Курсов</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- About Section -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2 text-primary"></i>О себе</h5>
                </div>
                <div class="card-body">
                    @if($user->about_me)
                        <p class="text-muted">{{ $user->about_me }}</p>
                    @else
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-info-circle"></i><br>
                            Информация о себе не добавлена
                        </p>
                    @endif
                    
                    <hr>
                    
                    <div class="info-list">
                        @if($user->religion)
                            <div class="info-item mb-3">
                                <i class="fas fa-praying-hands text-primary me-2"></i>
                                <span class="fw-bold">Вероисповедание:</span> {{ $user->religion }}
                            </div>
                        @endif
                        
                        @if($user->church)
                            <div class="info-item mb-3">
                                <i class="fas fa-church text-primary me-2"></i>
                                <span class="fw-bold">Церковь:</span> {{ $user->church }}
                            </div>
                        @endif
                        
                        @if($user->marital_status)
                            <div class="info-item mb-3">
                                <i class="fas fa-heart text-primary me-2"></i>
                                <span class="fw-bold">Семейное положение:</span> {{ $user->marital_status }}
                            </div>
                        @endif
                        
                        @if($user->education)
                            <div class="info-item mb-3">
                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                <span class="fw-bold">Образование:</span> {{ $user->education }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs Section -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-book-open me-2 text-primary"></i>Мои программы</h5>
                    @if($enrolledPrograms->isEmpty())
                        <span class="text-muted">Нет записанных программ</span>
                    @else
                        <a href="{{ route('profile.programs') }}" class="btn btn-sm btn-outline-primary">
                            Посмотреть все
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($enrolledPrograms->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Вы еще не записались ни на одну программу</p>
                            <a href="{{ route('programs.index') }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Найти программу
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($enrolledPrograms->take(3) as $program)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 program-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge {{ $program->pivot->status === 'completed' ? 'bg-success' : ($program->pivot->status === 'active' ? 'bg-primary' : ($program->pivot->status === 'cancelled' ? 'bg-danger' : 'bg-secondary')) }}">
                                                    @switch($program->pivot->status)
                                                        @case('enrolled') Записан @break
                                                        @case('active') В процессе @break
                                                        @case('completed') Завершена @break
                                                        @case('cancelled') Отменена @break
                                                    @endswitch
                                                </span>
                                                @if($program->institution)
                                                    <small class="text-muted">
                                                        <i class="fas fa-university me-1"></i>{{ $program->institution->name }}
                                                    </small>
                                                @endif
                                            </div>
                                            <h6 class="card-title mb-2">{{ Str::limit($program->name, 50) }}</h6>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \Carbon\Carbon::parse($program->pivot->enrolled_at)->format('d.m.Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Courses Section -->
    @if(!$enrolledCourses->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Мои курсы</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Курс</th>
                                        <th>Программа</th>
                                        <th>Статус</th>
                                        <th>Прогресс</th>
                                        <th>Дата записи</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrolledCourses->take(5) as $course)
                                        <tr>
                                            <td>
                                                <strong>{{ $course->name }}</strong>
                                            </td>
                                            <td>
                                                @if($course->program)
                                                    {{ $course->program->name }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $course->pivot->status === 'completed' ? 'bg-success' : ($course->pivot->status === 'active' ? 'bg-primary' : 'bg-secondary') }}">
                                                    @switch($course->pivot->status)
                                                        @case('enrolled') Записан @break
                                                        @case('active') Активен @break
                                                        @case('completed') Завершен @break
                                                        @case('cancelled') Отменен @break
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $course->pivot->progress ?? 0 }}%;">
                                                        {{ $course->pivot->progress ?? 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($course->pivot->enrolled_at)->format('d.m.Y') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
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
    @endif
</div>

<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.avatar-lg {
    width: 120px;
    height: 120px;
}

.avatar-lg img {
    width: 120px;
    height: 120px;
    object-fit: cover;
}

.stats-card {
    transition: transform 0.3s, box-shadow 0.3s;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.program-card {
    transition: all 0.3s;
}

.program-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.info-item {
    display: flex;
    align-items: start;
}
</style>
@endsection

