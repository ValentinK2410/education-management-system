@extends('layouts.app')

@section('title', 'Мой профиль')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <!-- Карточка профиля -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    @if($user->photo)
                        <img src="{{ asset('storage/' . $user->photo) }}" 
                             alt="Фото пользователя" 
                             class="rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-primary text-white rounded-circle">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        </div>
                    @endif
                    
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    @if($user->phone)
                        <p><i class="fas fa-phone me-2"></i>{{ $user->phone }}</p>
                    @endif
                    
                    @if($user->city)
                        <p><i class="fas fa-map-marker-alt me-2"></i>{{ $user->city }}</p>
                    @endif
                    
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Редактировать профиль
                    </a>
                </div>
            </div>

            <!-- Статистика -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Статистика обучения
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Активные программы:</span>
                        <span class="badge bg-primary">{{ $user->activePrograms()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Активные курсы:</span>
                        <span class="badge bg-success">{{ $user->activeCourses()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Завершенные программы:</span>
                        <span class="badge bg-info">{{ $user->completedPrograms()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Завершенные курсы:</span>
                        <span class="badge bg-warning">{{ $user->completedCourses()->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Информация о профиле -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Информация о профиле
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Имя:</strong></td>
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
                                    <td><strong>Город:</strong></td>
                                    <td>{{ $user->city ?? 'Не указан' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Вероисповедание:</strong></td>
                                    <td>{{ $user->religion ?? 'Не указано' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Церковь:</strong></td>
                                    <td>{{ $user->church ?? 'Не указана' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Семейное положение:</strong></td>
                                    <td>
                                        @switch($user->marital_status)
                                            @case('single')
                                                Холост/Не замужем
                                                @break
                                            @case('married')
                                                Женат/Замужем
                                                @break
                                            @case('divorced')
                                                Разведен/Разведена
                                                @break
                                            @case('widowed')
                                                Вдовец/Вдова
                                                @break
                                            @default
                                                Не указано
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Образование:</strong></td>
                                    <td>{{ $user->education ?? 'Не указано' }}</td>
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
                            </table>
                        </div>
                    </div>

                    @if($user->about_me)
                        <h6 class="mt-4">О себе</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                {{ $user->about_me }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Активные программы -->
            @if($user->activePrograms()->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book me-2"></i>Активные программы
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($user->activePrograms as $program)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $program->name }}</h6>
                                            <p class="card-text text-muted small">{{ Str::limit($program->description ?? 'Без описания', 100) }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    {{ $program->institution->name ?? 'Без заведения' }}
                                                </small>
                                                <span class="badge bg-primary">
                                                    @if($program->is_paid)
                                                        Платная
                                                    @else
                                                        Бесплатная
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Активные курсы -->
            @if($user->activeCourses()->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Активные курсы
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($user->activeCourses as $course)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $course->name }}</h6>
                                            <p class="card-text text-muted small">{{ Str::limit($course->description ?? 'Без описания', 100) }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    {{ $course->program->name ?? 'Без программы' }}
                                                </small>
                                                <span class="badge bg-success">
                                                    @if($course->is_paid)
                                                        Платный
                                                    @else
                                                        Бесплатный
                                                    @endif
                                                </span>
                                            </div>
                                            @if($course->pivot->progress)
                                                <div class="mt-2">
                                                    <small class="text-muted">Прогресс: {{ $course->pivot->progress }}%</small>
                                                    <div class="progress" style="height: 5px;">
                                                        <div class="progress-bar" style="width: {{ $course->pivot->progress }}%"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 150px;
    height: 150px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 3rem;
}
</style>
@endsection
