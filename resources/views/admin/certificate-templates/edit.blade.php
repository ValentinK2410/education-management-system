@extends('layouts.admin')

@section('title', 'Редактировать шаблон сертификата')

@push('styles')
<style>
    .preview-container {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        background: #f8f9fa;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: auto;
    }
    .preview-canvas {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 100%;
        cursor: crosshair;
    }
    .text-element-item {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
        background: #f8f9fa;
        transition: all 0.2s;
    }
    .text-element-item:hover {
        border-color: #007bff;
        background: #e7f3ff;
    }
    .text-element-item.selected {
        border-color: #007bff;
        border-width: 2px;
        background: #cfe2ff;
    }
    .text-element-item .form-label {
        font-size: 0.875rem;
        font-weight: 500;
    }
    #previewCanvas {
        border: 1px solid #ccc;
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
                        <i class="fas fa-edit me-2"></i>Редактировать шаблон сертификата
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.certificate-templates.update', $certificateTemplate) }}" method="POST" enctype="multipart/form-data" id="templateForm">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="text_elements_json" id="text_elements_json">

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Основные настройки</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Название шаблона *</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name', $certificateTemplate->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Тип сертификата *</label>
                                            <select class="form-select @error('type') is-invalid @enderror"
                                                    id="type" name="type" required>
                                                <option value="course" {{ old('type', $certificateTemplate->type) === 'course' ? 'selected' : '' }}>Для курса</option>
                                                <option value="program" {{ old('type', $certificateTemplate->type) === 'program' ? 'selected' : '' }}>Для программы</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Описание</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description" name="description" rows="3">{{ old('description', $certificateTemplate->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h5 class="mb-3 mt-4">Размер и качество</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="width" class="form-label">Ширина (px) *</label>
                                            <input type="number" class="form-control @error('width') is-invalid @enderror"
                                                   id="width" name="width" value="{{ old('width', $certificateTemplate->width) }}" min="100" max="5000" required>
                                            @error('width')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="height" class="form-label">Высота (px) *</label>
                                            <input type="number" class="form-control @error('height') is-invalid @enderror"
                                                   id="height" name="height" value="{{ old('height', $certificateTemplate->height) }}" min="100" max="5000" required>
                                            @error('height')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="quality" class="form-label">Качество (%) *</label>
                                            <input type="number" class="form-control @error('quality') is-invalid @enderror"
                                                   id="quality" name="quality" value="{{ old('quality', $certificateTemplate->quality) }}" min="1" max="100" required>
                                            @error('quality')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3 mt-4">Настройки фона</h5>

                                <div class="mb-3">
                                    <label for="background_type" class="form-label">Тип фона *</label>
                                    <select class="form-select @error('background_type') is-invalid @enderror"
                                            id="background_type" name="background_type" required>
                                        <option value="color" {{ old('background_type', $certificateTemplate->background_type) === 'color' ? 'selected' : '' }}>Цвет</option>
                                        <option value="image" {{ old('background_type', $certificateTemplate->background_type) === 'image' ? 'selected' : '' }}>Изображение</option>
                                        <option value="gradient" {{ old('background_type', $certificateTemplate->background_type) === 'gradient' ? 'selected' : '' }}>Градиент</option>
                                    </select>
                                    @error('background_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="colorBackground" style="display: {{ $certificateTemplate->background_type === 'color' ? 'block' : 'none' }};">
                                    <label for="background_color" class="form-label">Цвет фона</label>
                                    <input type="color" class="form-control form-control-color @error('background_color') is-invalid @enderror"
                                           id="background_color" name="background_color" value="{{ old('background_color', $certificateTemplate->background_color) }}">
                                    @error('background_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="imageBackground" style="display: {{ $certificateTemplate->background_type === 'image' ? 'block' : 'none' }};">
                                    <label for="background_image" class="form-label">Изображение фона</label>
                                    @if($certificateTemplate->background_image)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($certificateTemplate->background_image) }}" alt="Фон" style="max-width: 200px; max-height: 150px;" class="img-thumbnail">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="remove_background_image" name="remove_background_image" value="1">
                                                <label class="form-check-label" for="remove_background_image">
                                                    Удалить текущее изображение
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('background_image') is-invalid @enderror"
                                           id="background_image" name="background_image" accept="image/*">
                                    @error('background_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="gradientBackground" style="display: {{ $certificateTemplate->background_type === 'gradient' ? 'block' : 'none' }};">
                                    <label class="form-label">Градиент</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="gradient_color1" class="form-label">Цвет 1</label>
                                            <input type="color" class="form-control form-control-color" id="gradient_color1"
                                                   value="{{ old('gradient_color1', $certificateTemplate->background_gradient['colors'][0] ?? '#ffffff') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gradient_color2" class="form-label">Цвет 2</label>
                                            <input type="color" class="form-control form-control-color" id="gradient_color2"
                                                   value="{{ old('gradient_color2', $certificateTemplate->background_gradient['colors'][1] ?? '#f0f0f0') }}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="background_gradient" id="background_gradient">
                                </div>

                                <h5 class="mb-3 mt-4">Текстовые элементы</h5>

                                <div id="textElements">
                                    @if($certificateTemplate->text_elements)
                                        @foreach($certificateTemplate->text_elements as $index => $element)
                                            <div class="text-element-item">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Текст</label>
                                                        <input type="text" class="form-control text-element-input"
                                                               value="{{ $element['text'] ?? '' }}"
                                                               placeholder="Используйте {user_name}, {course_name}, {date}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">X</label>
                                                        <input type="number" class="form-control text-element-input" value="{{ $element['x'] ?? 100 }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Y</label>
                                                        <input type="number" class="form-control text-element-input" value="{{ $element['y'] ?? 200 }}">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Размер</label>
                                                        <input type="number" class="form-control text-element-input" value="{{ $element['size'] ?? 24 }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Цвет</label>
                                                        <input type="color" class="form-control form-control-color text-element-input" value="{{ $element['color'] ?? '#000000' }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <button type="button" class="btn btn-sm btn-danger mt-4" onclick="this.closest('.text-element-item').remove(); updatePreview();">
                                                            <i class="fas fa-trash"></i> Удалить
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-primary" id="addTextElement">
                                    <i class="fas fa-plus"></i> Добавить текстовый элемент
                                </button>

                                <div class="mb-3 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', $certificateTemplate->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Активен
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                                               {{ old('is_default', $certificateTemplate->is_default) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">
                                            Шаблон по умолчанию
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-secondary">Отмена</a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">Сохранить изменения</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">Предпросмотр <small class="text-muted">(перетащите текстовые элементы для изменения позиции)</small></h5>
                                <div class="preview-container" style="position: relative;">
                                    <canvas id="previewCanvas" class="preview-canvas" style="cursor: crosshair;"></canvas>
                                    <div id="canvasOverlay" style="position: absolute; top: 0; left: 0; pointer-events: none;"></div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Кликните на текстовый элемент в предпросмотре для его выбора, затем перетащите для изменения позиции
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let textElementIndex = {{ $certificateTemplate->text_elements ? count($certificateTemplate->text_elements) : 1 }};

document.getElementById('background_type').addEventListener('change', function() {
    document.getElementById('colorBackground').style.display = this.value === 'color' ? 'block' : 'none';
    document.getElementById('imageBackground').style.display = this.value === 'image' ? 'block' : 'none';
    document.getElementById('gradientBackground').style.display = this.value === 'gradient' ? 'block' : 'none';
    updatePreview();
});

document.getElementById('addTextElement').addEventListener('click', function() {
    const container = document.getElementById('textElements');
    const newElement = document.createElement('div');
    newElement.className = 'text-element-item';
    newElement.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Текст</label>
                <input type="text" class="form-control text-element-input" placeholder="Используйте {user_name}, {course_name}, {date}">
            </div>
            <div class="col-md-3">
                <label class="form-label">X</label>
                <input type="number" class="form-control text-element-input" value="100">
            </div>
            <div class="col-md-3">
                <label class="form-label">Y</label>
                <input type="number" class="form-control text-element-input" value="200">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label class="form-label">Размер</label>
                <input type="number" class="form-control text-element-input" value="24">
            </div>
            <div class="col-md-3">
                <label class="form-label">Цвет</label>
                <input type="color" class="form-control form-control-color text-element-input" value="#000000">
            </div>
            <div class="col-md-6">
                <button type="button" class="btn btn-sm btn-danger mt-4" onclick="this.closest('.text-element-item').remove(); updatePreview();">
                    <i class="fas fa-trash"></i> Удалить
                </button>
            </div>
        </div>
    `;
    container.appendChild(newElement);
    textElementIndex++;

    // Добавляем слушатели для новых элементов
    newElement.querySelectorAll('.text-element-input').forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });

    updatePreview();
});

let selectedTextElement = null;
let isDragging = false;
let dragOffset = { x: 0, y: 0 };
let textElementRects = [];

function updatePreview() {
    const canvas = document.getElementById('previewCanvas');
    const ctx = canvas.getContext('2d');
    const width = parseInt(document.getElementById('width').value) || 1200;
    const height = parseInt(document.getElementById('height').value) || 800;

    // Увеличиваем размер canvas до половины экрана
    const container = canvas.parentElement;
    const maxWidth = Math.min(container.clientWidth - 40, width);
    const maxHeight = Math.min(window.innerHeight * 0.5, height);
    
    const scale = Math.min(maxWidth / width, maxHeight / height);
    
    canvas.width = width * scale;
    canvas.height = height * scale;
    canvas.style.width = canvas.width + 'px';
    canvas.style.height = canvas.height + 'px';

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const bgType = document.getElementById('background_type').value;
    if (bgType === 'color') {
        ctx.fillStyle = document.getElementById('background_color').value;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else if (bgType === 'gradient') {
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
        gradient.addColorStop(0, document.getElementById('gradient_color1').value);
        gradient.addColorStop(1, document.getElementById('gradient_color2').value);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // Сохраняем информацию о текстовых элементах для обработки кликов
    textElementRects = [];
    const textElementItems = document.querySelectorAll('.text-element-item');
    
    textElementItems.forEach((item, index) => {
        const inputs = item.querySelectorAll('input');
        if (inputs.length >= 5) {
            const text = inputs[0]?.value || '';
            const x = parseInt(inputs[1]?.value || 100) * scale;
            const y = parseInt(inputs[2]?.value || 200) * scale;
            const size = parseInt(inputs[3]?.value || 24) * scale;
            const color = inputs[4]?.value || '#000000';

            if (text && text.trim() !== '') {
                ctx.font = `bold ${size}px Arial`;
                ctx.textAlign = 'left';
                ctx.textBaseline = 'top';
                
                // Измеряем текст для отрисовки рамки
                const metrics = ctx.measureText(text);
                const textWidth = metrics.width;
                const textHeight = size * 1.2;
                
                // Сохраняем информацию о позиции для обработки кликов
                textElementRects.push({
                    item: item,
                    x: x,
                    y: y,
                    width: textWidth,
                    height: textHeight,
                    scale: scale
                });
                
                // Рисуем рамку вокруг текста (если элемент выбран)
                if (item === selectedTextElement) {
                    ctx.strokeStyle = '#007bff';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([5, 5]);
                    ctx.strokeRect(x - 5, y - 5, textWidth + 10, textHeight + 10);
                    ctx.setLineDash([]);
                }
                
                // Рисуем текст
                ctx.fillStyle = color;
                ctx.fillText(text, x, y);
            }
        }
    });
}

// Обработка кликов на canvas для выбора текстового элемента
document.getElementById('previewCanvas').addEventListener('click', function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    // Проверяем, попал ли клик в какой-либо текстовый элемент
    for (let i = textElementRects.length - 1; i >= 0; i--) {
        const textRect = textElementRects[i];
        if (x >= textRect.x - 5 && x <= textRect.x + textRect.width + 5 &&
            y >= textRect.y - 5 && y <= textRect.y + textRect.height + 5) {
            
            // Убираем выделение с предыдущего элемента
            if (selectedTextElement) {
                selectedTextElement.classList.remove('selected');
            }
            
            // Выделяем новый элемент
            selectedTextElement = textRect.item;
            selectedTextElement.classList.add('selected');
            
            // Прокручиваем к элементу в форме
            selectedTextElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            updatePreview();
            return;
        }
    }
    
    // Если клик не попал в элемент, снимаем выделение
    if (selectedTextElement) {
        selectedTextElement.classList.remove('selected');
        selectedTextElement = null;
        updatePreview();
    }
});

// Обработка перетаскивания текстовых элементов
document.getElementById('previewCanvas').addEventListener('mousedown', function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    // Проверяем, попал ли клик в какой-либо текстовый элемент
    for (let i = textElementRects.length - 1; i >= 0; i--) {
        const textRect = textElementRects[i];
        if (x >= textRect.x - 5 && x <= textRect.x + textRect.width + 5 &&
            y >= textRect.y - 5 && y <= textRect.y + textRect.height + 5) {
            
            selectedTextElement = textRect.item;
            selectedTextElement.classList.add('selected');
            isDragging = true;
            dragOffset.x = x - textRect.x;
            dragOffset.y = y - textRect.y;
            this.style.cursor = 'move';
            updatePreview();
            e.preventDefault();
            return;
        }
    }
});

document.getElementById('previewCanvas').addEventListener('mousemove', function(e) {
    if (isDragging && selectedTextElement) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left - dragOffset.x;
        const y = e.clientY - rect.top - dragOffset.y;
        
        // Находим scale для преобразования координат
        const width = parseInt(document.getElementById('width').value) || 1200;
        const height = parseInt(document.getElementById('height').value) || 800;
        const container = this.parentElement;
        const maxWidth = Math.min(container.clientWidth - 40, width);
        const maxHeight = Math.min(window.innerHeight * 0.5, height);
        const scale = Math.min(maxWidth / width, maxHeight / height);
        
        // Преобразуем координаты обратно в реальные
        const realX = Math.max(0, Math.min(width, x / scale));
        const realY = Math.max(0, Math.min(height, y / scale));
        
        // Обновляем значения в форме
        const inputs = selectedTextElement.querySelectorAll('input');
        if (inputs.length >= 3) {
            inputs[1].value = Math.round(realX);
            inputs[2].value = Math.round(realY);
            updatePreview();
        }
    } else {
        // Проверяем, находится ли курсор над текстовым элементом
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        let overElement = false;
        for (let i = textElementRects.length - 1; i >= 0; i--) {
            const textRect = textElementRects[i];
            if (x >= textRect.x - 5 && x <= textRect.x + textRect.width + 5 &&
                y >= textRect.y - 5 && y <= textRect.y + textRect.height + 5) {
                overElement = true;
                break;
            }
        }
        
        this.style.cursor = overElement ? 'move' : 'crosshair';
    }
});

document.getElementById('previewCanvas').addEventListener('mouseup', function() {
    if (isDragging) {
        isDragging = false;
        this.style.cursor = 'crosshair';
    }
});

document.getElementById('previewCanvas').addEventListener('mouseleave', function() {
    if (isDragging) {
        isDragging = false;
        this.style.cursor = 'crosshair';
    }
});

function updateGradient() {
    const gradient = {
        colors: [
            document.getElementById('gradient_color1').value,
            document.getElementById('gradient_color2').value
        ]
    };
    document.getElementById('background_gradient').value = JSON.stringify(gradient);
}

function prepareFormData() {
    const textElements = [];
    const items = document.querySelectorAll('.text-element-item');

    items.forEach((item) => {
        const inputs = item.querySelectorAll('input');
        if (inputs.length >= 5) {
            const text = inputs[0]?.value; // текст
            const x = inputs[1]?.value; // x
            const y = inputs[2]?.value; // y
            const size = inputs[3]?.value; // size
            const color = inputs[4]?.value; // color

            if (text && text.trim() !== '') {
                textElements.push({
                    text: text.trim(),
                    x: parseInt(x) || 0,
                    y: parseInt(y) || 0,
                    size: parseInt(size) || 24,
                    color: color || '#000000',
                    align: 'left'
                });
            }
        }
    });

    const jsonValue = JSON.stringify(textElements);
    document.getElementById('text_elements_json').value = jsonValue;

    // Проверка валидности JSON
    try {
        JSON.parse(jsonValue);
        console.log('JSON валиден:', jsonValue);
    } catch (e) {
        console.error('Ошибка JSON:', e);
        alert('Ошибка при подготовке данных. Пожалуйста, проверьте заполнение полей.');
        return false;
    }

    return true;
}

['width', 'height', 'background_color', 'background_type'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updatePreview);
    document.getElementById(id)?.addEventListener('change', updatePreview);
});

document.getElementById('gradient_color1')?.addEventListener('change', function() {
    updateGradient();
    updatePreview();
});
document.getElementById('gradient_color2')?.addEventListener('change', function() {
    updateGradient();
    updatePreview();
});

// Слушатели для текстовых элементов
document.addEventListener('input', function(e) {
    if (e.target.closest('.text-element-item')) {
        updatePreview();
    }
});

// Слушатель для изменения типа фона
document.getElementById('background_type')?.addEventListener('change', function() {
    updatePreview();
    updateGradient();
});

// Обработка отправки формы
document.getElementById('templateForm').addEventListener('submit', function(e) {
    if (!prepareFormData()) {
        e.preventDefault();
        return false;
    }
});

// Добавляем слушатели для существующих текстовых элементов
document.querySelectorAll('.text-element-input').forEach(input => {
    input.addEventListener('input', updatePreview);
    input.addEventListener('change', updatePreview);
});

updatePreview();
updateGradient();
</script>
@endpush
@endsection
