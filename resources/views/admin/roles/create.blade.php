@extends('layouts.admin')

@section('title', 'Создание роли')
@section('page-title', 'Создать роль')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Создание роли
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название роли *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Слаг</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                           id="slug" name="slug" value="{{ old('slug') }}"
                                           placeholder="Автоматически генерируется из названия">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Оставьте пустым для автоматической генерации</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Разрешения</label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @forelse($permissions as $permission)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                               value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                                               {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            <strong>{{ $permission->name }}</strong>
                                            @if($permission->description)
                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                            @endif
                                            <br><code class="text-muted small">{{ $permission->slug }}</code>
                                        </label>
                                    </div>
                                @empty
                                    <p class="text-muted">Разрешения не найдены</p>
                                @endforelse
                            </div>
                            @error('permissions')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Назад
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Создать роль
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
