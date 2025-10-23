@extends('layouts.admin')

@section('title', 'Просмотр учебного заведения')
@section('page-title', 'Учебное заведение: ' . $institution->name)

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>{{ $institution->name }}
                    </h3>
                    <div class="btn-group">
                        <a href="{{ route('admin.institutions.edit', $institution) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Редактировать
                        </a>
                        <form action="{{ route('admin.institutions.destroy', $institution) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Вы уверены, что хотите удалить это учебное заведение?')">
                                <i class="fas fa-trash me-1"></i>Удалить
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Основная информация</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $institution->id }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Название:</strong></td>
                                    <td>{{ $institution->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $institution->email ?? 'Не указан' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Телефон:</strong></td>
                                    <td>{{ $institution->phone ?? 'Не указан' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Веб-сайт:</strong></td>
                                    <td>
                                        @if($institution->website)
                                            <a href="{{ $institution->website }}" target="_blank" class="text-decoration-none">
                                                {{ $institution->website }} <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @else
                                            Не указан
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Статус:</strong></td>
                                    <td>
                                        @if($institution->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Активно
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Неактивно
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($institution->logo)
                                <div class="text-center mb-3">
                                    <img src="{{ Storage::url($institution->logo) }}" alt="Логотип" class="img-fluid" style="max-height: 200px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($institution->address)
                        <h5 class="mt-4">Адрес</h5>
                        <p class="text-muted">{{ $institution->address }}</p>
                    @endif

                    @if($institution->description)
                        <h5 class="mt-4">Описание</h5>
                        <p>{{ $institution->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Статистика -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Статистика</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $institution->programs->count() }}</h4>
                                <small class="text-muted">Программ</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $institution->programs->sum('courses_count') ?? 0 }}</h4>
                            <small class="text-muted">Курсов</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Образовательные программы -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Образовательные программы</h6>
                    <a href="{{ route('admin.programs.create') }}?institution_id={{ $institution->id }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
                <div class="card-body">
                    @forelse($institution->programs as $program)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0">{{ $program->name }}</h6>
                                <small class="text-muted">{{ $program->courses->count() }} курсов</small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">Программы не найдены</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
