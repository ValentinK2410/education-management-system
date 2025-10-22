@extends('layouts.app')

@section('title', 'Учебные заведения')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="fw-bold mb-3">Учебные заведения</h1>
            <p class="text-muted">Откройте для себя лучшие образовательные учреждения</p>
        </div>
    </div>

    <div class="row">
        @forelse($institutions as $institution)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    @if($institution->logo)
                        <img src="{{ Storage::url($institution->logo) }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $institution->name }}">
                    @else
                        <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-university fa-3x text-white"></i>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $institution->name }}</h5>
                        <p class="card-text text-muted flex-grow-1">{{ Str::limit($institution->description, 120) }}</p>
                        
                        @if($institution->address)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $institution->address }}
                                </small>
                            </div>
                        @endif
                        
                        @if($institution->phone)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>{{ $institution->phone }}
                                </small>
                            </div>
                        @endif
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $institution->programs->count() }} программ
                                </small>
                                <a href="{{ route('institutions.show', $institution) }}" class="btn btn-primary btn-sm">
                                    Подробнее <i class="fas fa-arrow-right ms-1"></i>
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
                        <i class="fas fa-university fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Пока нет учебных заведений</h5>
                        <p class="text-muted">Учебные заведения будут добавлены в ближайшее время</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($institutions->hasPages())
        <div class="row">
            <div class="col-12">
                {{ $institutions->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
