@extends('layouts.admin')

@section('title', 'Резервные копии')
@section('page-title', 'Резервные копии')

@push('styles')
<style>
    [data-theme="dark"] .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .table {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .table thead th {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .table tbody td {
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
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
                        <i class="fas fa-database me-2"></i>Резервные копии базы данных
                    </h3>
                    <div class="btn-group">
                        <button type="button" 
                                class="btn btn-danger" 
                                onclick="showClearTablesModal()"
                                title="Очистить все таблицы кроме ролей и прав">
                            <i class="fas fa-broom me-2"></i>Очистить таблицы
                        </button>
                        <a href="{{ route('admin.backups.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Создать резервную копию
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($backups) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Имя файла</th>
                                        <th>Тип</th>
                                        <th>Таблица</th>
                                        <th>Размер</th>
                                        <th>Дата создания</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td>
                                                <code>{{ $backup['filename'] }}</code>
                                            </td>
                                            <td>
                                                @if($backup['type'] === 'full')
                                                    <span class="badge bg-primary">Полная</span>
                                                @else
                                                    <span class="badge bg-info">Таблица</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($backup['table'])
                                                    <span class="badge bg-secondary">{{ $backup['table'] }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $backup['size_formatted'] }}</td>
                                            <td>{{ $backup['created_at_formatted'] }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.backups.show', $backup['filename']) }}" 
                                                       class="btn btn-sm btn-info" title="Просмотр">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.backups.download', $backup['filename']) }}" 
                                                       class="btn btn-sm btn-success" title="Скачать">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    @if($backup['type'] === 'full' || $backup['table'])
                                                        <button type="button" 
                                                                class="btn btn-sm btn-warning" 
                                                                title="Восстановить"
                                                                onclick="showRestoreModal('{{ $backup['filename'] }}', '{{ $backup['type'] }}', '{{ $backup['table'] ?? '' }}')">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Удалить"
                                                            onclick="showDeleteModal('{{ $backup['filename'] }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Резервные копии не найдены. Создайте первую резервную копию.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно восстановления -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Восстановление из резервной копии</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="restoreForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Внимание!</strong> Перед восстановлением будет создана резервная копия текущего состояния БД.
                        Восстановление может занять некоторое время.
                    </div>
                    <p>Вы уверены, что хотите восстановить из резервной копии <strong id="restoreFilename"></strong>?</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirm" id="confirmRestore" required>
                        <label class="form-check-label" for="confirmRestore">
                            Я подтверждаю восстановление
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">Восстановить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление резервной копии</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Вы уверены, что хотите удалить резервную копию <strong id="deleteFilename"></strong>?
                        Это действие нельзя отменить.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirm" id="confirmDelete" required>
                        <label class="form-check-label" for="confirmDelete">
                            Я подтверждаю удаление
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно очистки таблиц -->
<div class="modal fade" id="clearTablesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Очистка таблиц базы данных</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clearTablesForm" method="POST" action="{{ route('admin.backups.clear-tables') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Внимание!</strong> Это действие удалит все данные из всех таблиц базы данных, 
                        кроме таблиц ролей и прав доступа.
                    </div>
                    <p>Будут очищены все таблицы, кроме:</p>
                    <ul>
                        <li><code>roles</code> - Роли пользователей</li>
                        <li><code>permissions</code> - Права доступа</li>
                        <li><code>role_permissions</code> - Связь ролей и прав</li>
                        <li><code>user_roles</code> - Связь пользователей и ролей</li>
                    </ul>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Перед очисткой будет автоматически создана полная резервная копия базы данных.
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="confirm" id="confirmClearTables" required>
                        <label class="form-check-label" for="confirmClearTables">
                            Я понимаю последствия и подтверждаю очистку всех таблиц
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-broom me-2"></i>Очистить таблицы
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showRestoreModal(filename, type, table) {
        document.getElementById('restoreFilename').textContent = filename;
        let actionUrl = '{{ route("admin.backups.restore", ":filename") }}'.replace(':filename', filename);
        if (type === 'table' && table) {
            actionUrl = '{{ route("admin.backups.restore-table", [":filename", ":table"]) }}'
                .replace(':filename', filename)
                .replace(':table', table);
        }
        document.getElementById('restoreForm').action = actionUrl;
        document.getElementById('confirmRestore').checked = false;
        new bootstrap.Modal(document.getElementById('restoreModal')).show();
    }

    function showDeleteModal(filename) {
        document.getElementById('deleteFilename').textContent = filename;
        document.getElementById('deleteForm').action = '{{ route("admin.backups.destroy", ":filename") }}'.replace(':filename', filename);
        document.getElementById('confirmDelete').checked = false;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function showClearTablesModal() {
        document.getElementById('confirmClearTables').checked = false;
        new bootstrap.Modal(document.getElementById('clearTablesModal')).show();
    }
</script>
@endpush
@endsection

