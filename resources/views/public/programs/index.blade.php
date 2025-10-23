@extends('layouts.app')

@section('title', 'Образовательные программы')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="fw-bold mb-3">Образовательные программы</h1>
            <p class="text-muted">Выберите подходящую образовательную программу</p>
        </div>
    </div>

    <div class="row">
        @forelse($programs as $program)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title">{{ $program->name }}</h5>
                            <span class="badge bg-primary">{{ $program->degree_level }}</span>
                        </div>

                        <p class="card-text text-muted flex-grow-1">{{ Str::limit($program->description, 120) }}</p>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-university me-1"></i>{{ $program->institution->name }}
                            </small>
                        </div>

                        <div class="row mb-3">
                            @if($program->duration)
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $program->duration }}
                                    </small>
                                </div>
                            @endif
                            @if($program->tuition_fee)
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-ruble-sign me-1"></i>{{ number_format($program->tuition_fee, 0, ',', ' ') }} ₽
                                    </small>
                                </div>
                            @endif
                        </div>

                        @if($program->requirements && count($program->requirements) > 0)
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Требования:</strong>
                                    @foreach($program->requirements as $requirement)
                                        <span class="badge bg-light text-dark me-1">{{ $requirement }}</span>
                                    @endforeach
                                </small>
                            </div>
                        @endif

                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $program->courses->count() }} курсов
                                </small>
                                <a href="{{ route('programs.show', $program) }}" class="btn btn-primary btn-sm">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Программы не найдены</h5>
                        <p class="text-muted">Образовательные программы будут добавлены в ближайшее время</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($programs->hasPages())
        <div class="row">
            <div class="col-12">
                {{ $programs->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
