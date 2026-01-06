@extends('layouts.admin')

@section('title', 'Создать резервную копию')
@section('page-title', 'Создать резервную копию')

@push('styles')
<style>
    [data-theme="dark"] .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .form-label {
        color: var(--text-color) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-database me-2"></i>Создание резервной копии
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.backups.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Тип резервной копии</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="typeFull" value="full" checked onchange="toggleTableSelection()">
                                <label class="form-check-label" for="typeFull">
                                    <strong>Полная резервная копия</strong>
                                    <br>
                                    <small class="text-muted">Создаст резервную копию всей базы данных</small>
                                </label>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="radio" name="type" id="typeTable" value="table" onchange="toggleTableSelection()">
                                <label class="form-check-label" for="typeTable">
                                    <strong>Резервная копия таблиц</strong>
                                    <br>
                                    <small class="text-muted">Выберите одну или несколько таблиц для резервного копирования</small>
                                </label>
                            </div>
                        </div>

                        <div id="tableSelection" style="display: none;">
                            <label class="form-label">Выберите таблицы</label>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllTables()">
                                        Выбрать все
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllTables()">
                                        Снять выбор
                                    </button>
                                </div>
                                <div class="row">
                                    @foreach($tables as $table)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input table-checkbox" 
                                                       type="checkbox" 
                                                       name="tables[]" 
                                                       value="{{ $table }}" 
                                                       id="table_{{ $table }}">
                                                <label class="form-check-label" for="table_{{ $table }}">
                                                    {{ $table }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('tables')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Создать резервную копию
                            </button>
                            <a href="{{ route('admin.backups.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleTableSelection() {
        const tableSelection = document.getElementById('tableSelection');
        const typeTable = document.getElementById('typeTable');
        
        if (typeTable.checked) {
            tableSelection.style.display = 'block';
        } else {
            tableSelection.style.display = 'none';
            // Снимаем выбор со всех таблиц
            document.querySelectorAll('.table-checkbox').forEach(cb => cb.checked = false);
        }
    }

    function selectAllTables() {
        document.querySelectorAll('.table-checkbox').forEach(cb => cb.checked = true);
    }

    function deselectAllTables() {
        document.querySelectorAll('.table-checkbox').forEach(cb => cb.checked = false);
    }
</script>
@endpush
@endsection

