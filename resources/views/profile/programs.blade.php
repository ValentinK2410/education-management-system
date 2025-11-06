@extends('layouts.app')

@section('title', 'Мои программы')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-3">
                <i class="fas fa-book-open me-2 text-primary"></i>Мои программы
            </h2>
            <p class="text-muted">Просмотр всех программ, на которые вы записаны, и их текущий статус</p>
        </div>
    </div>

    @if($enrolledPrograms->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-info-circle fa-3x mb-3 text-primary"></i>
                    <h4>У вас нет записанных программ</h4>
                    <p class="mb-0">Начните обучение, записавшись на одну из доступных программ</p>
                    <a href="{{ route('programs.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-search me-2"></i>Посмотреть все программы
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($enrolledPrograms as $program)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm program-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge {{ $program->pivot->status === 'completed' ? 'bg-success' : ($program->pivot->status === 'active' ? 'bg-primary' : ($program->pivot->status === 'cancelled' ? 'bg-danger' : 'bg-secondary')) }}">
                                    @switch($program->pivot->status)
                                        @case('enrolled')
                                            Записан
                                            @break
                                        @case('active')
                                            В процессе
                                            @break
                                        @case('completed')
                                            Завершена
                                            @break
                                        @case('cancelled')
                                            Отменена
                                            @break
                                    @endswitch
                                </span>
                                @if($program->is_paid)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-dollar-sign me-1"></i>Платная
                                    </span>
                                @endif
                            </div>
                            
                            <h5 class="card-title mb-2">{{ $program->name }}</h5>
                            
                            @if($program->code)
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-code me-1"></i>Код: {{ $program->code }}
                                </p>
                            @endif
                            
                            @if($program->institution)
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-university me-1"></i>{{ $program->institution->name }}
                                </p>
                            @endif
                            
                            @if($program->description)
                                <p class="card-text text-muted small mb-3">
                                    {{ Str::limit($program->description, 100) }}
                                </p>
                            @endif
                            
                            <div class="program-info mb-3">
                                @if($program->duration)
                                    <div class="d-flex align-items-center text-muted small mb-1">
                                        <i class="fas fa-clock me-2"></i>
                                        {{ $program->duration }} {{ $program->duration == 1 ? 'месяц' : 'месяцев' }}
                                    </div>
                                @endif
                                
                                @if($program->credits)
                                    <div class="d-flex align-items-center text-muted small mb-1">
                                        <i class="fas fa-graduation-cap me-2"></i>
                                        {{ $program->credits }} {{ $program->credits == 1 ? 'кредит' : 'кредитов' }}
                                    </div>
                                @endif
                                
                                @if($program->pivot->enrolled_at)
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fas fa-calendar me-2"></i>
                                        Записан: {{ \Carbon\Carbon::parse($program->pivot->enrolled_at)->format('d.m.Y') }}
                                    </div>
                                @endif
                                
                                @if($program->pivot->status === 'completed' && $program->pivot->completed_at)
                                    <div class="d-flex align-items-center text-muted small mt-1">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        Завершена: {{ \Carbon\Carbon::parse($program->pivot->completed_at)->format('d.m.Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex gap-2">
                                <a href="{{ route('programs.show', $program) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>Подробнее
                                </a>
                                
                                @if($program->pivot->status === 'enrolled')
                                    <form action="{{ route('programs.activate', $program) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="fas fa-play me-1"></i>Начать
                                        </button>
                                    </form>
                                @endif
                                
                                @if($program->pivot->status === 'active')
                                    <form action="{{ route('programs.complete', $program) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="fas fa-check me-1"></i>Завершить
                                        </button>
                                    </form>
                                @endif
                                
                                @if($program->pivot->status !== 'completed' && $program->pivot->status !== 'cancelled')
                                    <form action="{{ route('programs.unenroll', $program) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger w-100" 
                                                onclick="return confirm('Вы уверены, что хотите отменить запись на эту программу?')">
                                            <i class="fas fa-times me-1"></i>Отменить
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
.program-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.program-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card-title {
    color: #333;
    font-weight: 600;
}
</style>
@endsection

