@extends('layouts.admin')

@section('title', 'Создать шаблон сертификата')

@push('styles')
<style>
    .preview-container {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        background: #f8f9fa;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .preview-canvas {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 100%;
    }
    .text-element-item {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
        background: #f8f9fa;
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

                        <div class="row">
                            <div class="col-md-8">
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

                                <h5 class="mb-3 mt-4">Размер и качество</h5>

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

                                <h5 class="mb-3 mt-4">Настройки фона</h5>

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

                                <h5 class="mb-3 mt-4">Текстовые элементы</h5>

                                <div id="textElements">
                                    <div class="text-element-item">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Текст</label>
                                                <input type="text" class="form-control" name="text_elements[0][text]"
                                                       value="Сертификат" placeholder="Используйте {user_name}, {course_name}, {date}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">X</label>
                                                <input type="number" class="form-control" name="text_elements[0][x]" value="100">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Y</label>
                                                <input type="number" class="form-control" name="text_elements[0][y]" value="200">
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-3">
                                                <label class="form-label">Размер</label>
                                                <input type="number" class="form-control" name="text_elements[0][size]" value="48">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Цвет</label>
                                                <input type="color" class="form-control form-control-color" name="text_elements[0][color]" value="#000000">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-primary" id="addTextElement">
                                    <i class="fas fa-plus"></i> Добавить текстовый элемент
                                </button>

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

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-secondary">Отмена</a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">Создать шаблон</button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h5 class="mb-3">Предпросмотр</h5>
                                <div class="preview-container">
                                    <canvas id="previewCanvas" class="preview-canvas"></canvas>
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
let textElementIndex = 1;

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
                <input type="text" class="form-control" name="text_elements[${textElementIndex}][text]"
                       placeholder="Используйте {user_name}, {course_name}, {date}">
            </div>
            <div class="col-md-3">
                <label class="form-label">X</label>
                <input type="number" class="form-control" name="text_elements[${textElementIndex}][x]" value="100">
            </div>
            <div class="col-md-3">
                <label class="form-label">Y</label>
                <input type="number" class="form-control" name="text_elements[${textElementIndex}][y]" value="200">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label class="form-label">Размер</label>
                <input type="number" class="form-control" name="text_elements[${textElementIndex}][size]" value="24">
            </div>
            <div class="col-md-3">
                <label class="form-label">Цвет</label>
                <input type="color" class="form-control form-control-color" name="text_elements[${textElementIndex}][color]" value="#000000">
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
});

// Обновление предпросмотра
function updatePreview() {
    const canvas = document.getElementById('previewCanvas');
    const ctx = canvas.getContext('2d');
    const width = parseInt(document.getElementById('width').value) || 1200;
    const height = parseInt(document.getElementById('height').value) || 800;

    canvas.width = Math.min(width, 400);
    canvas.height = Math.min(height, 300);

    const scale = Math.min(400 / width, 300 / height);

    // Очистка
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Фон
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

    // Текстовые элементы
    const textElements = document.querySelectorAll('[name^="text_elements"]');
    textElements.forEach((input, index) => {
        if (input.name.includes('[text]') && input.value) {
            const x = parseInt(document.querySelector(`[name="text_elements[${Math.floor(index/6)}][x]"]`)?.value || 100) * scale;
            const y = parseInt(document.querySelector(`[name="text_elements[${Math.floor(index/6)}][y]"]`)?.value || 200) * scale;
            const size = parseInt(document.querySelector(`[name="text_elements[${Math.floor(index/6)}][size]"]`)?.value || 24) * scale;
            const color = document.querySelector(`[name="text_elements[${Math.floor(index/6)}][color]"]`)?.value || '#000000';

            ctx.fillStyle = color;
            ctx.font = `${size}px Arial`;
            ctx.fillText(input.value, x, y);
        }
    });
}

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
['width', 'height', 'background_color', 'background_type'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updatePreview);
    document.getElementById(id)?.addEventListener('change', updatePreview);
});

// Подготовка данных формы перед отправкой
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
