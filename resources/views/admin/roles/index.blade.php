@extends('layouts.admin')

@section('title', 'Управление ролями')
@section('page-title', 'Роли')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Роли
                    </h3>
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Добавить роль
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Слаг</th>
                                    <th>Описание</th>
                                    <th>Разрешения</th>
                                    <th>Пользователи</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $role->id }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <div class="avatar-title bg-primary text-white rounded-circle">
                                                        {{ substr($role->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $role->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><code>{{ $role->slug }}</code></td>
                                        <td>{{ $role->description ?? '—' }}</td>
                                        <td>
                                            @if($role->permissions->count() > 0)
                                                <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                                            @else
                                                <span class="text-muted">Нет</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($role->users->count() > 0)
                                                <span class="badge bg-success">{{ $role->users->count() }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.roles.show', $role) }}"
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.roles.edit', $role) }}"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.roles.destroy', $role) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить эту роль?')">
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
                                                <i class="fas fa-shield-alt fa-3x mb-3"></i>
                                                <p>Роли не найдены</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($roles->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $roles->links() }}
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
