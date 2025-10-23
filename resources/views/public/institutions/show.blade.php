@extends('layouts.app')

@section('title', $institution->name)

@section('content')
<div class="container py-5">
    <!-- Institution Header -->
    <div class="row mb-5">
        <div class="col-lg-4">
            @if($institution->logo)
                <img src="{{ Storage::url($institution->logo) }}" class="img-fluid rounded shadow" alt="{{ $institution->name }}">
            @else
                <div class="bg-primary rounded shadow d-flex align-items-center justify-content-center" style="height: 300px;">
                    <i class="fas fa-university fa-5x text-white"></i>
                </div>
            @endif
        </div>
        <div class="col-lg-8">
            <h1 class="fw-bold mb-3">{{ $institution->name }}</h1>
            <p class="lead text-muted mb-4">{{ $institution->description }}</p>

            <div class="row">
                @if($institution->address)
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-map-marker-alt me-2"></i>Адрес
                        </h6>
                        <p class="mb-0">{{ $institution->address }}</p>
                    </div>
                @endif

                @if($institution->phone)
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-phone me-2"></i>Телефон
                        </h6>
                        <p class="mb-0">{{ $institution->phone }}</p>
                    </div>
                @endif

                @if($institution->email)
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-envelope me-2"></i>Email
                        </h6>
                        <p class="mb-0">
                            <a href="mailto:{{ $institution->email }}" class="text-decoration-none">
                                {{ $institution->email }}
                            </a>
                        </p>
                    </div>
                @endif

                @if($institution->website)
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="fas fa-globe me-2"></i>Веб-сайт
                        </h6>
                        <p class="mb-0">
                            <a href="{{ $institution->website }}" target="_blank" class="text-decoration-none">
                                {{ $institution->website }}
                            </a>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Programs Section -->
    <div class="row">
        <div class="col-12">
            <h2 class="fw-bold mb-4">Образовательные программы</h2>

            @if($institution->programs->count() > 0)
                <div class="row">
                    @foreach($institution->programs as $program)
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title">{{ $program->name }}</h5>
                                        <span class="badge bg-primary">{{ $program->degree_level }}</span>
                                    </div>

                                    <p class="card-text text-muted">{{ Str::limit($program->description, 150) }}</p>

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
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Программы не найдены</h5>
                        <p class="text-muted">В этом учебном заведении пока нет образовательных программ</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
