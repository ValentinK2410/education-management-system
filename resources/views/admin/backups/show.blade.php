@extends('layouts.admin')

@section('title', 'Информация о резервной копии')
@section('page-title', 'Информация о резервной копии')

@push('styles')
<style>
    [data-theme="dark"] .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .list-group-item {
        background-color: var(--card-bg) !important;
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
                        <i class="fas fa-info-circle me-2"></i>Информация о резервной копии
                    </h3>
                    <a href="{{ route('admin.backups.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Назад к списку
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Имя файла:</strong></span>
                                    <code>{{ $backup['filename'] }}</code>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Тип:</strong></span>
                                    @if($backup['type'] === 'full')
                                        <span class="badge bg-primary">Полная резервная копия</span>
                                    @else
                                        <span class="badge bg-info">Резервная копия таблицы</span>
                                    @endif
                                </li>
                                @if($backup['table'])
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span><strong>Таблица:</strong></span>
                                        <span class="badge bg-secondary">{{ $backup['table'] }}</span>
                                    </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Размер:</strong></span>
                                    <span>{{ $backup['size_formatted'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Дата создания:</strong></span>
                                    <span>{{ $backup['created_at_formatted'] }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Путь:</strong></span>
                                    <small class="text-muted">{{ $backup['path'] }}</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Действия</h5>
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.backups.download', $backup['filename']) }}" 
                               class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Скачать
                            </a>
                            @if($backup['type'] === 'full' || $backup['table'])
                                <button type="button" 
                                        class="btn btn-warning" 
                                        onclick="showRestoreModal('{{ $backup['filename'] }}', '{{ $backup['type'] }}', '{{ $backup['table'] ?? '' }}')">
                                    <i class="fas fa-undo me-2"></i>Восстановить
                                </button>
                            @endif
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="showDeleteModal('{{ $backup['filename'] }}')">
                                <i class="fas fa-trash me-2"></i>Удалить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна (те же, что в index.blade.php) -->
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
</script>
@endpush
@endsection

