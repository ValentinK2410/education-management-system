@extends('layouts.admin')

@section('title', 'Создать шаблон сертификата')

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
    .text-settings-panel {
        min-height: 400px;
    }
    #textSettingsForm .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .text-element-item {
        display: none;
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
                        <i class="fas fa-plus me-2"></i>Создать шаблон сертификата
                    </h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Ошибки валидации:</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.certificate-templates.store') }}" method="POST" enctype="multipart/form-data" id="templateForm">
                        @csrf

                        <input type="hidden" name="text_elements_json" id="text_elements_json">

                        <!-- Верхняя часть: основные настройки на всю ширину -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3">Основные настройки</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Название шаблона *</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name') }}" required>
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
                                                <option value="course" {{ old('type') === 'course' ? 'selected' : '' }}>Для курса</option>
                                                <option value="program" {{ old('type') === 'program' ? 'selected' : '' }}>Для программы</option>
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
                                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3">Размер и качество</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="width" class="form-label">Ширина (px) *</label>
                                            <input type="number" class="form-control @error('width') is-invalid @enderror"
                                                   id="width" name="width" value="{{ old('width', 1200) }}" min="100" max="5000" required>
                                            @error('width')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="height" class="form-label">Высота (px) *</label>
                                            <input type="number" class="form-control @error('height') is-invalid @enderror"
                                                   id="height" name="height" value="{{ old('height', 800) }}" min="100" max="5000" required>
                                            @error('height')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="quality" class="form-label">Качество (%) *</label>
                                            <input type="number" class="form-control @error('quality') is-invalid @enderror"
                                                   id="quality" name="quality" value="{{ old('quality', 90) }}" min="1" max="100" required>
                                            @error('quality')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3">Настройки фона</h5>

                                <div class="mb-3">
                                    <label for="background_type" class="form-label">Тип фона *</label>
                                    <select class="form-select @error('background_type') is-invalid @enderror"
                                            id="background_type" name="background_type" required>
                                        <option value="color" {{ old('background_type') === 'color' ? 'selected' : '' }}>Цвет</option>
                                        <option value="image" {{ old('background_type') === 'image' ? 'selected' : '' }}>Изображение</option>
                                        <option value="gradient" {{ old('background_type') === 'gradient' ? 'selected' : '' }}>Градиент</option>
                                    </select>
                                    @error('background_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="colorBackground">
                                    <label for="background_color" class="form-label">Цвет фона</label>
                                    <input type="color" class="form-control form-control-color @error('background_color') is-invalid @enderror"
                                           id="background_color" name="background_color" value="{{ old('background_color', '#ffffff') }}">
                                    @error('background_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="imageBackground" style="display: none;">
                                    <label for="background_image" class="form-label">Изображение фона</label>
                                    <input type="file" class="form-control @error('background_image') is-invalid @enderror"
                                           id="background_image" name="background_image" accept="image/*">
                                    @error('background_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="gradientBackground" style="display: none;">
                                    <label class="form-label">Градиент</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="gradient_color1" class="form-label">Цвет 1</label>
                                            <input type="color" class="form-control form-control-color" id="gradient_color1" value="#ffffff">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="gradient_color2" class="form-label">Цвет 2</label>
                                            <input type="color" class="form-control form-control-color" id="gradient_color2" value="#f0f0f0">
                                        </div>
                                    </div>
                                    <input type="hidden" name="background_gradient" id="background_gradient">
                                </div>

                                <div class="mb-3 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="is_active">
                                            Активен
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                                        <label class="form-check-label" for="is_default">
                                            Шаблон по умолчанию
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Нижняя часть: 40% настройки текста, 60% предпросмотр -->
                        <div class="row">
                            <div class="col-md-5">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-cog me-2"></i>Настройки текста
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="textSettingsPanel" class="text-settings-panel">
                                            <div class="alert alert-info mb-3" id="noTextSelectedAlert">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Кликните на текст в предпросмотре для редактирования или добавьте новый элемент
                                            </div>

                                            <div id="textSettingsForm" style="display: none;">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Текст</label>
                                                    <input type="text" class="form-control" id="textInput"
                                                           placeholder="Используйте {user_name}, {course_name}, {date}">
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Позиция X</label>
                                                        <input type="number" class="form-control" id="xInput" value="100">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Позиция Y</label>
                                                        <input type="number" class="form-control" id="yInput" value="200">
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Размер шрифта</label>
                                                        <input type="number" class="form-control" id="sizeInput" value="48" min="8" max="200">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Цвет</label>
                                                        <input type="color" class="form-control form-control-color" id="colorInput" value="#000000">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Шрифт</label>
                                                    <select class="form-select" id="fontInput">
                                                        <option value="Arial">Arial</option>
                                                        <option value="Times New Roman">Times New Roman</option>
                                                        <option value="Courier New">Courier New</option>
                                                        <option value="Georgia">Georgia</option>
                                                        <option value="Verdana">Verdana</option>
                                                        <option value="Comic Sans MS">Comic Sans MS</option>
                                                        <option value="Impact">Impact</option>
                                                        <option value="Trebuchet MS">Trebuchet MS</option>
                                                    </select>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Растяжение (letter-spacing)</label>
                                                        <input type="number" class="form-control" id="letterSpacingInput" value="0" min="0" max="50" step="0.5">
                                                        <small class="text-muted">Интервал между буквами</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Поворот (градусы)</label>
                                                        <input type="number" class="form-control" id="rotationInput" value="0" min="-360" max="360" step="1">
                                                        <small class="text-muted">Угол поворота текста</small>
                                                    </div>
                                                </div>

                                                <div class="d-grid gap-2">
                                                    <button type="button" class="btn btn-danger" id="deleteTextBtn">
                                                        <i class="fas fa-trash me-2"></i>Удалить текст
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mt-3 pt-3 border-top">
                                                <button type="button" class="btn btn-outline-primary w-100" id="addTextElement">
                                                    <i class="fas fa-plus me-2"></i>Добавить новый текст
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Предпросмотр</h5>
                                    <button type="button" class="btn btn-success btn-sm" id="downloadPreviewBtn">
                                        <i class="fas fa-download me-2"></i>Скачать предпросмотр
                                    </button>
                                </div>
                                <div class="preview-container" style="position: sticky; top: 20px; max-height: calc(100vh - 100px); overflow-y: auto;">
                                    <canvas id="previewCanvas" class="preview-canvas" style="cursor: crosshair;"></canvas>
                                    <div id="canvasOverlay" style="position: absolute; top: 0; left: 0; pointer-events: none;"></div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Кликните на текстовый элемент в предпросмотре для его выбора и редактирования
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-secondary">Отмена</a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">Создать шаблон</button>
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
// Массив текстовых элементов
let textElements = [
    {
        text: 'Сертификат',
        x: 100,
        y: 200,
        size: 48,
        color: '#000000',
        font: 'Arial',
        letterSpacing: 0,
        rotation: 0
    }
];

let selectedTextIndex = null;
let isDragging = false;
let dragOffset = { x: 0, y: 0 };
let textElementRects = [];
let resizeHandles = [];
let isResizing = false;
let resizeHandleIndex = -1;
let initialSize = 0;
let initialMouseX = 0;
let backgroundImage = null;

// Инициализация
document.getElementById('background_type').addEventListener('change', function() {
    document.getElementById('colorBackground').style.display = this.value === 'color' ? 'block' : 'none';
    document.getElementById('imageBackground').style.display = this.value === 'image' ? 'block' : 'none';
    document.getElementById('gradientBackground').style.display = this.value === 'gradient' ? 'block' : 'none';
    updatePreview();
});

// Добавление нового текстового элемента
document.getElementById('addTextElement').addEventListener('click', function() {
    const newText = {
        text: 'Новый текст',
        x: 100,
        y: 200,
        size: 24,
        color: '#000000',
        font: 'Arial',
        letterSpacing: 0,
        rotation: 0
    };
    textElements.push(newText);
    selectedTextIndex = textElements.length - 1;
    loadTextSettings(selectedTextIndex);
    updatePreview();
});

// Загрузка настроек текста в панель
function loadTextSettings(index) {
    if (index === null || index < 0 || index >= textElements.length) {
        document.getElementById('noTextSelectedAlert').style.display = 'block';
        document.getElementById('textSettingsForm').style.display = 'none';
        return;
    }

    const text = textElements[index];
    document.getElementById('noTextSelectedAlert').style.display = 'none';
    document.getElementById('textSettingsForm').style.display = 'block';

    document.getElementById('textInput').value = text.text;
    document.getElementById('xInput').value = text.x;
    document.getElementById('yInput').value = text.y;
    document.getElementById('sizeInput').value = text.size;
    document.getElementById('colorInput').value = text.color;
    document.getElementById('fontInput').value = text.font || 'Arial';
    document.getElementById('letterSpacingInput').value = text.letterSpacing || 0;
    document.getElementById('rotationInput').value = text.rotation || 0;
}

// Сохранение настроек текста из панели
function saveTextSettings() {
    if (selectedTextIndex === null || selectedTextIndex < 0 || selectedTextIndex >= textElements.length) {
        return;
    }

    textElements[selectedTextIndex] = {
        text: document.getElementById('textInput').value,
        x: parseInt(document.getElementById('xInput').value) || 0,
        y: parseInt(document.getElementById('yInput').value) || 0,
        size: parseInt(document.getElementById('sizeInput').value) || 24,
        color: document.getElementById('colorInput').value,
        font: document.getElementById('fontInput').value || 'Arial',
        letterSpacing: parseFloat(document.getElementById('letterSpacingInput').value) || 0,
        rotation: parseFloat(document.getElementById('rotationInput').value) || 0
    };
    updatePreview();
}

// Удаление текста
document.getElementById('deleteTextBtn').addEventListener('click', function() {
    if (selectedTextIndex !== null && selectedTextIndex >= 0 && selectedTextIndex < textElements.length) {
        textElements.splice(selectedTextIndex, 1);
        selectedTextIndex = null;
        loadTextSettings(null);
        updatePreview();
    }
});

// Слушатели для полей настроек
['textInput', 'xInput', 'yInput', 'sizeInput', 'colorInput', 'fontInput', 'letterSpacingInput', 'rotationInput'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', saveTextSettings);
        element.addEventListener('change', saveTextSettings);
    }
});

// Скачивание предпросмотра
document.getElementById('downloadPreviewBtn').addEventListener('click', function() {
    const canvas = document.getElementById('previewCanvas');
    const link = document.createElement('a');
    link.download = 'certificate-preview.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
});

// Загрузка фонового изображения
document.getElementById('background_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                backgroundImage = img;
                updatePreview();
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        backgroundImage = null;
        updatePreview();
    }
});

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

    // Рисуем фон
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
    } else if (bgType === 'image') {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        if (backgroundImage) {
            ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
        }
    } else {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // Сохраняем информацию о текстовых элементах для обработки кликов
    textElementRects = [];
    resizeHandles = [];

    // Рисуем текстовые элементы
    textElements.forEach((textData, index) => {
        if (!textData.text || textData.text.trim() === '') return;

        const x = textData.x * scale;
        const y = textData.y * scale;
        const size = textData.size * scale;
        const color = textData.color || '#000000';
        const font = textData.font || 'Arial';
        const letterSpacing = (textData.letterSpacing || 0) * scale;
        const rotation = (textData.rotation || 0) * Math.PI / 180; // Преобразуем в радианы

        ctx.save();

        // Применяем поворот
        if (rotation !== 0) {
            ctx.translate(x, y);
            ctx.rotate(rotation);
            ctx.translate(-x, -y);
        }

        ctx.font = `bold ${size}px ${font}`;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'top';
        ctx.fillStyle = color;

        let currentX = x;
        let totalWidth = 0;

        // Рисуем текст с учетом растяжения (letter-spacing)
        if (letterSpacing > 0) {
            for (let i = 0; i < textData.text.length; i++) {
                const char = textData.text[i];
                ctx.fillText(char, currentX, y);
                const charWidth = ctx.measureText(char).width;
                currentX += charWidth + letterSpacing;
                totalWidth += charWidth + (i < textData.text.length - 1 ? letterSpacing : 0);
            }
        } else {
            ctx.fillText(textData.text, x, y);
            const metrics = ctx.measureText(textData.text);
            totalWidth = metrics.width;
        }

        ctx.restore();

        const textHeight = size * 1.2;

        // Вычисляем границы с учетом поворота
        let bounds = {
            minX: x,
            maxX: x + totalWidth,
            minY: y,
            maxY: y + textHeight
        };

        if (rotation !== 0) {
            const cos = Math.cos(rotation);
            const sin = Math.sin(rotation);
            const corners = [
                { x: x, y: y },
                { x: x + totalWidth, y: y },
                { x: x + totalWidth, y: y + textHeight },
                { x: x, y: y + textHeight }
            ];

            const rotatedCorners = corners.map(corner => {
                const dx = corner.x - x;
                const dy = corner.y - y;
                return {
                    x: x + dx * cos - dy * sin,
                    y: y + dx * sin + dy * cos
                };
            });

            bounds.minX = Math.min(...rotatedCorners.map(c => c.x));
            bounds.maxX = Math.max(...rotatedCorners.map(c => c.x));
            bounds.minY = Math.min(...rotatedCorners.map(c => c.y));
            bounds.maxY = Math.max(...rotatedCorners.map(c => c.y));
        }

        // Сохраняем информацию о позиции для обработки кликов
        textElementRects.push({
            index: index,
            x: bounds.minX,
            y: bounds.minY,
            width: bounds.maxX - bounds.minX,
            height: bounds.maxY - bounds.minY,
            scale: scale,
            centerX: x,
            centerY: y
        });

        // Рисуем рамку и точки для изменения размера (если элемент выбран)
        if (index === selectedTextIndex) {
            ctx.save();
            if (rotation !== 0) {
                ctx.translate(x, y);
                ctx.rotate(rotation);
                ctx.translate(-x, -y);
            }

            ctx.strokeStyle = '#007bff';
            ctx.lineWidth = 2;
            ctx.setLineDash([5, 5]);
            ctx.strokeRect(x - 5, y - 5, totalWidth + 10, textHeight + 10);
            ctx.setLineDash([]);

            const handleSize = 8;
            const handles = [
                { x: x - 5, y: y + textHeight / 2, type: 'left' },
                { x: x + totalWidth + 5, y: y + textHeight / 2, type: 'right' }
            ];

            resizeHandles = handles.map(h => ({
                ...h,
                index: index,
                realX: (h.x / scale),
                realY: (h.y / scale),
                realWidth: (totalWidth / scale),
                realHeight: (textHeight / scale)
            }));

            handles.forEach(handle => {
                ctx.fillStyle = '#007bff';
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.arc(handle.x, handle.y, handleSize / 2, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();
            });

            ctx.restore();
        }
    });
}

// Проверка клика на точку изменения размера
function checkResizeHandleClick(x, y) {
    const handleSize = 8;
    for (let i = 0; i < resizeHandles.length; i++) {
        const handle = resizeHandles[i];
        const distance = Math.sqrt(Math.pow(x - handle.x, 2) + Math.pow(y - handle.y, 2));
        if (distance <= handleSize) {
            return i;
        }
    }
    return -1;
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadTextSettings(0);
    updatePreview();
});

// Обработка кликов на canvas для выбора текстового элемента
document.getElementById('previewCanvas').addEventListener('click', function(e) {
    if (isResizing) {
        return;
    }

    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Проверяем клик на точки изменения размера
    const handleIndex = checkResizeHandleClick(x, y);
    if (handleIndex !== -1) {
        return;
    }

    // Проверяем, попал ли клик в какой-либо текстовый элемент
    for (let i = textElementRects.length - 1; i >= 0; i--) {
        const textRect = textElementRects[i];
        if (x >= textRect.x - 5 && x <= textRect.x + textRect.width + 5 &&
            y >= textRect.y - 5 && y <= textRect.y + textRect.height + 5) {

            selectedTextIndex = textRect.index;
            loadTextSettings(selectedTextIndex);
            updatePreview();
            return;
        }
    }

    // Если клик не попал в элемент, снимаем выделение
    selectedTextIndex = null;
    loadTextSettings(null);
    updatePreview();
});

// Обработка перетаскивания текстовых элементов
document.getElementById('previewCanvas').addEventListener('mousedown', function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Сначала проверяем клик на точки изменения размера
    const handleIndex = checkResizeHandleClick(x, y);
    if (handleIndex !== -1 && selectedTextIndex !== null) {
        isResizing = true;
        isDragging = false;
        resizeHandleIndex = handleIndex;
        initialMouseX = x;
        initialSize = textElements[selectedTextIndex].size;
        this.style.cursor = 'ew-resize';
        e.preventDefault();
        e.stopPropagation();
        return;
    }

    // Проверяем, попал ли клик в какой-либо текстовый элемент
    for (let i = textElementRects.length - 1; i >= 0; i--) {
        const textRect = textElementRects[i];
        if (x >= textRect.x - 5 && x <= textRect.x + textRect.width + 5 &&
            y >= textRect.y - 5 && y <= textRect.y + textRect.height + 5) {

            selectedTextIndex = textRect.index;
            loadTextSettings(selectedTextIndex);
            isDragging = true;
            isResizing = false;
            dragOffset.x = x - textRect.centerX;
            dragOffset.y = y - textRect.centerY;
            this.style.cursor = 'move';
            updatePreview();
            e.preventDefault();
            return;
        }
    }

    isDragging = false;
    isResizing = false;
});

document.getElementById('previewCanvas').addEventListener('mousemove', function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Обработка изменения размера текста
    if (isResizing && selectedTextIndex !== null && resizeHandleIndex !== -1) {
        const handle = resizeHandles[resizeHandleIndex];
        const deltaX = x - initialMouseX;

        const width = parseInt(document.getElementById('width').value) || 1200;
        const height = parseInt(document.getElementById('height').value) || 800;
        const container = this.parentElement;
        const maxWidth = Math.min(container.clientWidth - 40, width);
        const maxHeight = Math.min(window.innerHeight * 0.5, height);
        const scale = Math.min(maxWidth / width, maxHeight / height);

        let sizeDelta = 0;
        if (handle.type === 'left') {
            sizeDelta = -deltaX / scale;
        } else if (handle.type === 'right') {
            sizeDelta = deltaX / scale;
        }

        const newSize = Math.max(8, Math.min(200, initialSize + sizeDelta * 0.5));
        textElements[selectedTextIndex].size = Math.round(newSize);
        document.getElementById('sizeInput').value = Math.round(newSize);
        updatePreview();
        this.style.cursor = 'ew-resize';
        return;
    }

    // Проверка наведения на точки изменения размера
    if (!isDragging && !isResizing) {
        const handleIndex = checkResizeHandleClick(x, y);
        if (handleIndex !== -1) {
            this.style.cursor = 'ew-resize';
            return;
        }
    }

    // Обработка перетаскивания текстового элемента
    if (isDragging && selectedTextIndex !== null) {
        const width = parseInt(document.getElementById('width').value) || 1200;
        const height = parseInt(document.getElementById('height').value) || 800;
        const container = this.parentElement;
        const maxWidth = Math.min(container.clientWidth - 40, width);
        const maxHeight = Math.min(window.innerHeight * 0.5, height);
        const scale = Math.min(maxWidth / width, maxHeight / height);

        const realX = Math.max(0, Math.min(width, (x - dragOffset.x) / scale));
        const realY = Math.max(0, Math.min(height, (y - dragOffset.y) / scale));

        textElements[selectedTextIndex].x = Math.round(realX);
        textElements[selectedTextIndex].y = Math.round(realY);
        document.getElementById('xInput').value = Math.round(realX);
        document.getElementById('yInput').value = Math.round(realY);
        updatePreview();
        this.style.cursor = 'move';
    } else {
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

// Обновление градиента в скрытом поле
document.getElementById('gradient_color1').addEventListener('change', function() {
    updateGradient();
});
document.getElementById('gradient_color2').addEventListener('change', function() {
    updateGradient();
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

// Слушатели для обновления предпросмотра
['width', 'height', 'background_color', 'background_type', 'gradient_color1', 'gradient_color2'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', updatePreview);
        element.addEventListener('change', updatePreview);
    }
});

// Слушатели для текстовых элементов
document.addEventListener('input', function(e) {
    if (e.target.closest('.text-element-item')) {
        updatePreview();
    }
});

document.addEventListener('change', function(e) {
    if (e.target.closest('.text-element-item')) {
        updatePreview();
    }
});

// Слушатель для изменения типа фона
document.getElementById('background_type')?.addEventListener('change', function() {
    updatePreview();
    updateGradient();
});

// Подготовка данных формы перед отправкой
function prepareFormData() {
    // Сохраняем текущие настройки перед отправкой
    if (selectedTextIndex !== null) {
        saveTextSettings();
    }

    const jsonValue = JSON.stringify(textElements.filter(t => t.text && t.text.trim() !== ''));
    document.getElementById('text_elements_json').value = jsonValue;

    try {
        JSON.parse(jsonValue);
    } catch (e) {
        console.error('Ошибка JSON:', e);
        alert('Ошибка при подготовке данных. Пожалуйста, проверьте заполнение полей.');
        return false;
    }

    return true;
}

// Обработка отправки формы
document.getElementById('templateForm').addEventListener('submit', function(e) {
    if (!prepareFormData()) {
        e.preventDefault();
        return false;
    }
});

// Инициализация
updatePreview();
updateGradient();
</script>
@endpush
@endsection
