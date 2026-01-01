@extends('layouts.admin')

@section('title', 'Управление пользователями')
@section('page-title', 'Пользователи')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Пользователи
                    </h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                            <i class="fas fa-trash me-2"></i>Удалить выбранные
                        </button>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Добавить пользователя
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Форма поиска и фильтров --}}
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Поиск</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text"
                                           name="search"
                                           class="form-control"
                                           placeholder="Имя, email или телефон..."
                                           value="{{ $search ?? '' }}"
                                           autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Роль</label>
                                <select name="role" class="form-select">
                                    <option value="">Все роли</option>
                                    @foreach($roles ?? [] as $role)
                                        <option value="{{ $role->slug }}" {{ ($roleFilter ?? '') === $role->slug ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="">Все</option>
                                    <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Активные</option>
                                    <option value="inactive" {{ ($statusFilter ?? '') === 'inactive' ? 'selected' : '' }}>Неактивные</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="fas fa-filter me-1"></i>Применить
                                </button>
                            </div>
                        </div>
                        @if($search || ($roleFilter ?? '') || ($statusFilter ?? ''))
                        <div class="row mt-2">
                            <div class="col-12">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Сбросить фильтры
                                </a>
                            </div>
                        </div>
                        @endif
                    </form>

                    @if($search ?? '')
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Результаты поиска по запросу: <strong>"{{ $search }}"</strong>
                        <span class="badge bg-primary ms-2">Найдено: {{ $users->total() }}</span>
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAll" title="Выбрать все">
                                    </th>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Роли</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="user-checkbox" value="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $user->id }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <div class="avatar-title bg-primary text-white rounded-circle">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? 'Не указан' }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-info me-1">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Активен
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Неактивен
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.users.show', $user) }}"
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.users.destroy', $user) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
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
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>Пользователи не найдены</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $users->links() }}
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    // Обработчик для "Выбрать все"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }
    
    // Обработчики для отдельных чекбоксов
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkDeleteButton();
        });
    });
    
    // Обновление состояния "Выбрать все"
    function updateSelectAllCheckbox() {
        if (selectAllCheckbox) {
            const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(userCheckboxes).some(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }
    
    // Обновление видимости кнопки массового удаления
    function updateBulkDeleteButton() {
        if (bulkDeleteBtn) {
            const checkedCount = Array.from(userCheckboxes).filter(cb => cb.checked).length;
            if (checkedCount > 0) {
                bulkDeleteBtn.style.display = 'inline-block';
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash me-2"></i>Удалить выбранных (${checkedCount})`;
            } else {
                bulkDeleteBtn.style.display = 'none';
            }
        }
    }
    
    // Обработчик массового удаления
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const selectedIds = Array.from(userCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                alert('Выберите хотя бы одного пользователя для удаления');
                return;
            }
            
            const userNames = Array.from(userCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => `${cb.dataset.userName} (${cb.dataset.userEmail})`)
                .join('\n');
            
            if (!confirm(`Вы уверены, что хотите удалить следующих пользователей?\n\n${userNames}\n\nЭто действие нельзя отменить!`)) {
                return;
            }
            
            // Отправка запроса на удаление
            fetch('{{ route("admin.users.bulk-destroy") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: selectedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Показываем сообщение об успехе
                    if (data.message) {
                        alert(data.message);
                    }
                    
                    // Перезагружаем страницу
                    window.location.reload();
                } else {
                    alert(data.message || 'Произошла ошибка при удалении пользователей');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении пользователей');
            });
        });
    }
});
</script>
@endpush
@endsection
