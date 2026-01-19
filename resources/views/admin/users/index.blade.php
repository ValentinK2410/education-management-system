@extends('layouts.admin')

@section('title', __('messages.users_list'))
@section('page-title', __('messages.users'))

@push('styles')
<style>
    /* Темная тема для страницы пользователей */
    [data-theme="dark"] .container-fluid .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card-header {
        background: var(--card-bg) !important;
        border-bottom-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card-body {
        background: var(--card-bg) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid h3,
    [data-theme="dark"] .container-fluid h6,
    [data-theme="dark"] .container-fluid .card-title {
        color: var(--text-color) !important;
    }

    /* Формы поиска и фильтров */
    [data-theme="dark"] .container-fluid .form-label {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .form-select,
    [data-theme="dark"] .container-fluid .form-control {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .form-select:focus,
    [data-theme="dark"] .container-fluid .form-control:focus {
        background-color: var(--card-bg) !important;
        border-color: #6366f1 !important;
        color: var(--text-color) !important;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25) !important;
    }

    [data-theme="dark"] .container-fluid .form-control::placeholder {
        color: #94a3b8 !important;
        opacity: 0.6;
    }

    [data-theme="dark"] .container-fluid .input-group-text {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-outline-secondary {
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-outline-secondary:hover {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    /* Таблица */
    [data-theme="dark"] .container-fluid .table {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table thead th {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table tbody td {
        border-color: var(--border-color) !important;
        background-color: transparent !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table-striped tbody tr:nth-of-type(odd) {
        background-color: var(--card-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-striped tbody tr:nth-of-type(even) {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-hover tbody tr:hover {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-hover tbody tr:hover td {
        background-color: var(--dark-bg) !important;
        color: var(--text-color) !important;
    }

    /* Текст */
    [data-theme="dark"] .container-fluid .text-muted {
        color: #94a3b8 !important;
        opacity: 0.8;
    }

    [data-theme="dark"] .container-fluid small {
        color: #94a3b8 !important;
    }

    /* Бейджи */
    [data-theme="dark"] .container-fluid .badge.bg-secondary {
        background-color: var(--secondary-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-info {
        background-color: rgba(59, 130, 246, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-success {
        background-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-danger {
        background-color: rgba(239, 68, 68, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-primary {
        background-color: var(--primary-color) !important;
        color: white !important;
    }

    /* Кнопки */
    [data-theme="dark"] .container-fluid .btn-info {
        background-color: rgba(59, 130, 246, 0.8) !important;
        border-color: rgba(59, 130, 246, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .btn-info:hover {
        background-color: rgba(59, 130, 246, 1) !important;
        border-color: rgba(59, 130, 246, 1) !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning:hover {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
    }

    [data-theme="dark"] .container-fluid .btn-danger {
        background-color: var(--danger-color) !important;
        border-color: var(--danger-color) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .btn-danger:hover {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
    }

    /* Аватар */
    [data-theme="dark"] .container-fluid .avatar-title.bg-primary {
        background-color: var(--primary-color) !important;
        color: white !important;
    }

    /* Алерт */
    [data-theme="dark"] .container-fluid .alert-info {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .alert-info strong {
        color: var(--text-color) !important;
    }

    /* Чекбоксы */
    [data-theme="dark"] .container-fluid input[type="checkbox"] {
        filter: brightness(0.8);
    }
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>{{ __('messages.users') }}
                    </h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                            <i class="fas fa-trash me-2"></i>{{ __('messages.delete_selected') }}
                        </button>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>{{ __('messages.add_user') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Форма поиска и фильтров --}}
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">{{ __('messages.search') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text"
                                           name="search"
                                           class="form-control"
                                           placeholder="{{ __('messages.name') }}, {{ __('messages.email') }} {{ __('messages.or') }} {{ __('messages.phone') }}..."
                                           value="{{ $search ?? '' }}"
                                           autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">{{ __('messages.role') }}</label>
                                <select name="role" class="form-select">
                                    <option value="">{{ __('messages.all_roles') }}</option>
                                    @foreach($roles ?? [] as $role)
                                        <option value="{{ $role->slug }}" {{ ($roleFilter ?? '') === $role->slug ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">{{ __('messages.status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('messages.all') }}</option>
                                    <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                    <option value="inactive" {{ ($statusFilter ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="fas fa-filter me-1"></i>{{ __('messages.apply') }}
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">
                                    <i class="fas fa-list-ol me-1"></i>Элементов на странице
                                </label>
                                <div class="input-group">
                                    <select name="per_page" id="per_page_select" class="form-select" onchange="updatePerPage(this.value)">
                                        <option value="10" {{ (request('per_page', auth()->user()->users_per_page ?? 50) == 10) ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ (request('per_page', auth()->user()->users_per_page ?? 50) == 25) ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ (request('per_page', auth()->user()->users_per_page ?? 50) == 50) ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ (request('per_page', auth()->user()->users_per_page ?? 50) == 100) ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ (request('per_page', auth()->user()->users_per_page ?? 50) == 200) ? 'selected' : '' }}>200</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @if($search || ($roleFilter ?? '') || ($statusFilter ?? ''))
                        <div class="row mt-2">
                            <div class="col-12">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>{{ __('messages.reset_filters') }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </form>

                    @if($search ?? '')
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.search_results_for') }}: <strong>"{{ $search }}"</strong>
                        <span class="badge bg-primary ms-2">{{ __('messages.found') }}: {{ $users->total() }}</span>
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAll" title="{{ __('messages.select_all') }}">
                                    </th>
                                    <th>ID</th>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.email') }}</th>
                                    <th>{{ __('messages.phone') }}</th>
                                    <th>{{ __('messages.roles') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
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
                                                    <td>{{ $user->phone ?? __('messages.not_specified') }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-info me-1">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>{{ __('messages.active') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>{{ __('messages.inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.users.show', $user) }}"
                                                   class="btn btn-sm btn-info" title="{{ __('messages.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   class="btn btn-sm btn-warning" title="{{ __('messages.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.users.destroy', $user) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="{{ __('messages.delete') }}"
                                                            onclick="return confirm('{{ __('messages.confirm_delete_user') }}')">
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
                                                <p>{{ __('messages.users_not_found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages() || $users->total() > 0)
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Показано {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} из {{ $users->total() }} пользователей
                            </div>
                            <div>
                                {{ $users->links() }}
                            </div>
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
    // Функция для обновления количества элементов на странице
    function updatePerPage(value) {
        // Сохраняем настройку в базе данных
        fetch('{{ route("admin.users.update-per-page") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                per_page: parseInt(value)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем URL с новым параметром per_page и перезагружаем страницу
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', value);
                // Убираем параметр page, чтобы начать с первой страницы
                url.searchParams.delete('page');
                window.location.href = url.toString();
            } else {
                console.error('Ошибка сохранения настройки:', data.message);
                // Все равно обновляем страницу с новым параметром
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', value);
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            // Все равно обновляем страницу с новым параметром
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // Translations for JavaScript
    const userTranslations = {
        delete_selected: '{{ __('messages.delete_selected') }}',
        select_at_least_one: '{{ __('messages.select_at_least_one_user') }}',
        confirm_delete_users: '{{ __('messages.confirm_delete_users') }}',
        action_cannot_be_undone: '{{ __('messages.action_cannot_be_undone') }}',
        users_deleted_successfully: '{{ __('messages.users_deleted_successfully') }}',
        error_deleting_users: '{{ __('messages.error_deleting_users') }}',
        cannot_delete_yourself: '{{ __('messages.cannot_delete_yourself') }}'
    };
    
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
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash me-2"></i>${userTranslations.delete_selected} (${checkedCount})`;
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
                alert(userTranslations.select_at_least_one);
                return;
            }
            
            const userNames = Array.from(userCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => `${cb.dataset.userName} (${cb.dataset.userEmail})`)
                .join('\n');
            
            if (!confirm(`${userTranslations.confirm_delete_users}\n\n${userNames}\n\n${userTranslations.action_cannot_be_undone}`)) {
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
                    alert(data.message || userTranslations.error_deleting_users);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(userTranslations.error_deleting_users);
            });
        });
    }
});
</script>
@endpush
@endsection
