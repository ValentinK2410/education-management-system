@extends('layouts.admin')

@section('title', 'Управление учебными заведениями')
@section('page-title', 'Учебные заведения')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-university me-2"></i>Учебные заведения
                    </h3>
                    <a href="{{ route('admin.institutions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Добавить учебное заведение
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Тип</th>
                                    <th>Адрес</th>
                                    <th>Контакты</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($institutions as $institution)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $institution->id }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($institution->logo)
                                                    <img src="{{ asset('storage/' . $institution->logo) }}" 
                                                         alt="Логотип" class="me-3" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                                                @else
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-primary text-white rounded-circle">
                                                            <i class="fas fa-university"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $institution->name }}</h6>
                                                    <small class="text-muted">{{ $institution->short_name ?? 'Без сокращения' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $institution->type ?? 'Не указан' }}</span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $institution->address }}">
                                                {{ $institution->address ?? 'Не указан' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                @if($institution->phone)
                                                    <small class="d-block"><i class="fas fa-phone me-1"></i>{{ $institution->phone }}</small>
                                                @endif
                                                @if($institution->email)
                                                    <small class="d-block"><i class="fas fa-envelope me-1"></i>{{ $institution->email }}</small>
                                                @endif
                                            </div>
                                        </td>
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
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.institutions.show', $institution) }}" 
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.institutions.edit', $institution) }}" 
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.institutions.destroy', $institution) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить это учебное заведение?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-university fa-3x mb-3"></i>
                                                <p>Учебные заведения не найдены</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($institutions->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $institutions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
</style>
@endsection
