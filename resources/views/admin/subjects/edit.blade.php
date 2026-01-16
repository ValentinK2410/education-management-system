@extends('layouts.admin')

@section('title', 'Редактировать предмет')
@section('page-title', 'Редактировать предмет')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Редактировать предмет: {{ $subject->name }}
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.subjects.update', $subject) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название предмета *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $subject->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Код предмета</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code', $subject->code) }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="short_description" class="form-label">Краткое описание</label>
                            <textarea class="form-control @error('short_description') is-invalid @enderror"
                                      id="short_description" name="short_description" rows="2" maxlength="500">{{ old('short_description', $subject->short_description) }}</textarea>
                            @error('short_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Максимум 500 символов</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание предмета</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description', $subject->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Изображение предмета</label>
                                    @if($subject->image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $subject->image) }}" 
                                                 alt="{{ $subject->name }}" 
                                                 style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                                           id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Формат: JPEG, PNG, JPG, GIF. Максимальный размер: 2 МБ</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order" class="form-label">Порядок отображения</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror"
                                           id="order" name="order" value="{{ old('order', $subject->order) }}" min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Чем меньше число, тем выше в списке</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', $subject->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Предмет активен
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Назад к списку
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Секция прикрепления программ -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2"></i>Прикрепленные программы
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Список прикрепленных программ -->
                    @if($subject->programs->count() > 0)
                        <div class="mb-4">
                            <h6>Текущие программы:</h6>
                            <div class="list-group">
                                @foreach($subject->programs as $program)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $program->name }}</strong>
                                            @if($program->code)
                                                <span class="badge bg-info ms-2">{{ $program->code }}</span>
                                            @endif
                                            @if($program->institution)
                                                <small class="text-muted ms-2">— {{ $program->institution->name }}</small>
                                            @endif
                                            <span class="badge bg-secondary ms-2">Порядок: {{ $program->pivot->order ?? 0 }}</span>
                                        </div>
                                        <form action="{{ route('admin.subjects.programs.detach', [$subject, $program]) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Вы уверены, что хотите открепить эту программу?');">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-unlink"></i> Открепить
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            К этому предмету еще не прикреплены программы.
                        </div>
                    @endif

                    <!-- Форма для добавления новой программы -->
                    <div class="border-top pt-4">
                        <h6 class="mb-3">Добавить программу:</h6>
                        <form action="{{ route('admin.subjects.programs.attach', $subject) }}" method="POST" id="attachProgramForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="program_search" class="form-label">Поиск программы *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="program_search" 
                                               placeholder="Начните вводить название программы..."
                                               autocomplete="off">
                                        <input type="hidden" name="program_id" id="program_id" required>
                                        <div id="program_dropdown" class="dropdown-menu w-100" style="display: none; max-height: 300px; overflow-y: auto;">
                                            @foreach($availablePrograms as $program)
                                                @if(!$subject->programs->contains('id', $program->id))
                                                    <a href="#" 
                                                       class="dropdown-item program-option" 
                                                       data-id="{{ $program->id }}"
                                                       data-name="{{ $program->name }}"
                                                       data-code="{{ $program->code ?? '' }}"
                                                       data-institution="{{ $program->institution->name ?? '' }}">
                                                        <strong>{{ $program->name }}</strong>
                                                        @if($program->code)
                                                            <span class="badge bg-info ms-2">{{ $program->code }}</span>
                                                        @endif
                                                        @if($program->institution)
                                                            <small class="text-muted ms-2">— {{ $program->institution->name }}</small>
                                                        @endif
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                        <div id="selected_program" class="mt-2" style="display: none;">
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-check me-2"></i>
                                                <strong id="selected_program_name"></strong>
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" id="clear_selection">
                                                    <i class="fas fa-times"></i> Очистить
                                                </button>
                                            </div>
                                        </div>
                                        @error('program_id')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="program_order" class="form-label">Порядок в программе</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="program_order" 
                                               name="order" 
                                               value="{{ $subject->programs->count() }}" 
                                               min="0">
                                        <div class="form-text">Чем меньше число, тем выше в списке</div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success" id="attach_btn" disabled>
                                <i class="fas fa-link me-2"></i>Прикрепить к программе
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('program_search');
    const programIdInput = document.getElementById('program_id');
    const dropdown = document.getElementById('program_dropdown');
    const selectedProgramDiv = document.getElementById('selected_program');
    const selectedProgramName = document.getElementById('selected_program_name');
    const clearBtn = document.getElementById('clear_selection');
    const attachBtn = document.getElementById('attach_btn');
    const programOptions = document.querySelectorAll('.program-option');

    // Функция фильтрации программ
    function filterPrograms(searchText) {
        const searchLower = searchText.toLowerCase();
        let hasVisible = false;

        programOptions.forEach(option => {
            const name = option.getAttribute('data-name').toLowerCase();
            const code = (option.getAttribute('data-code') || '').toLowerCase();
            const institution = (option.getAttribute('data-institution') || '').toLowerCase();
            
            const matches = name.includes(searchLower) || 
                          code.includes(searchLower) || 
                          institution.includes(searchLower);

            if (matches) {
                option.style.display = 'block';
                hasVisible = true;
            } else {
                option.style.display = 'none';
            }
        });

        return hasVisible;
    }

    // Обработка ввода текста
    searchInput.addEventListener('input', function(e) {
        const searchText = e.target.value.trim();
        
        if (searchText.length === 0) {
            dropdown.style.display = 'none';
            selectedProgramDiv.style.display = 'none';
            programIdInput.value = '';
            attachBtn.disabled = true;
            return;
        }

        const hasVisible = filterPrograms(searchText);
        dropdown.style.display = hasVisible ? 'block' : 'none';
    });

    // Обработка клика по программе
    programOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            const programId = this.getAttribute('data-id');
            const programName = this.getAttribute('data-name');
            const programCode = this.getAttribute('data-code');
            const programInstitution = this.getAttribute('data-institution');
            
            programIdInput.value = programId;
            searchInput.value = programName;
            
            let displayText = programName;
            if (programCode) {
                displayText += ` <span class="badge bg-info">${programCode}</span>`;
            }
            if (programInstitution) {
                displayText += ` <small class="text-muted">— ${programInstitution}</small>`;
            }
            
            selectedProgramName.innerHTML = displayText;
            selectedProgramDiv.style.display = 'block';
            dropdown.style.display = 'none';
            attachBtn.disabled = false;
        });
    });

    // Очистка выбора
    clearBtn.addEventListener('click', function(e) {
        e.preventDefault();
        searchInput.value = '';
        programIdInput.value = '';
        selectedProgramDiv.style.display = 'none';
        attachBtn.disabled = true;
        dropdown.style.display = 'none';
        
        // Показать все опции снова
        programOptions.forEach(option => {
            option.style.display = 'block';
        });
    });

    // Закрытие dropdown при клике вне его
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Фокус на поле поиска
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            const hasVisible = filterPrograms(this.value.trim());
            dropdown.style.display = hasVisible ? 'block' : 'none';
        }
    });
});
</script>
@endpush
@endsection
