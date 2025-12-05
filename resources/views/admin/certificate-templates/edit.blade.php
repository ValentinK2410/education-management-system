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

    /* Стили для ползунка угла градиента */
    .gradient-angle-slider-container {
        position: relative;
        padding: 20px 0;
    }

    .gradient-angle-slider {
        width: 100%;
        height: 8px;
        border-radius: 5px;
        background: linear-gradient(to right,
            #ff0000 0%,
            #ffff00 15%,
            #00ff00 30%,
            #00ffff 45%,
            #0000ff 60%,
            #ff00ff 75%,
            #ff0000 100%);
        outline: none;
        -webkit-appearance: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .gradient-angle-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #007bff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .gradient-angle-slider::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 8px rgba(0, 123, 255, 0.4);
    }

    .gradient-angle-slider::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #007bff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .gradient-angle-slider::-moz-range-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 8px rgba(0, 123, 255, 0.4);
    }

    .gradient-angle-visual {
        margin-top: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gradient-preview-box {
        width: 200px;
        height: 60px;
        border-radius: 8px;
        border: 2px solid #ddd;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    #gradient_angle_value {
        font-weight: 600;
        color: #007bff;
        font-size: 1.1em;
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

                        <input type="hidden" name="text_elements_json" id="text_elements_json" value="[]" tabindex="-1" aria-hidden="true">

                        <!-- Верхняя часть: основные настройки на всю ширину -->
                        <div class="row mb-4">
                            <div class="col-12">
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
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3">Настройки фона</h5>

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
                                    <div class="mt-3">
                                        <label for="gradient_angle" class="form-label">
                                            Угол градиента: <span id="gradient_angle_value">{{ old('gradient_angle', $certificateTemplate->background_gradient['angle'] ?? 0) }}</span>°
                                        </label>
                                        <div class="gradient-angle-slider-container">
                                            <input type="range" class="form-range gradient-angle-slider" id="gradient_angle"
                                                   min="0" max="360" value="{{ old('gradient_angle', $certificateTemplate->background_gradient['angle'] ?? 0) }}" step="1">
                                            <div class="gradient-angle-visual">
                                                <div class="gradient-preview-box" id="gradientPreviewBox"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="background_gradient" id="background_gradient">
                                </div>

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
                            </div>
                        </div>

                        <!-- Нижняя часть: 40% настройки текста, 60% предпросмотр -->
                        <div class="row">
                            <div class="col-md-5">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-cog me-2"></i>Настройки элементов
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="elementSettingsPanel" class="text-settings-panel">
                                            <div class="alert alert-info mb-3" id="noElementSelectedAlert">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Кликните на элемент в предпросмотре для редактирования или добавьте новый элемент
                                            </div>

                                            <div id="elementSettingsForm" style="display: none;">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Тип элемента</label>
                                                    <select class="form-select" id="elementTypeInput">
                                                        <option value="text">Текст</option>
                                                        <option value="line">Линия</option>
                                                        <option value="circle">Круг</option>
                                                        <option value="square">Квадрат</option>
                                                        <option value="rectangle">Прямоугольник</option>
                                                        <option value="trapezoid">Трапеция</option>
                                                        <option value="image">Изображение/Иконка</option>
                                                    </select>
                                                </div>

                                                <!-- Настройки для текста -->
                                                <div id="textSettings" class="element-type-settings">
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

                                                </div>

                                                <!-- Настройки для линии -->
                                                <div id="lineSettings" class="element-type-settings" style="display: none;">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">X1 (начало)</label>
                                                            <input type="number" class="form-control" id="lineX1Input" value="100">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Y1 (начало)</label>
                                                            <input type="number" class="form-control" id="lineY1Input" value="100">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">X2 (конец)</label>
                                                            <input type="number" class="form-control" id="lineX2Input" value="200">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Y2 (конец)</label>
                                                            <input type="number" class="form-control" id="lineY2Input" value="200">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Толщина линии</label>
                                                        <input type="number" class="form-control" id="lineWidthInput" value="2" min="1" max="50">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет</label>
                                                        <input type="color" class="form-control form-control-color" id="lineColorInput" value="#000000">
                                                    </div>
                                                </div>

                                                <!-- Настройки для круга -->
                                                <div id="circleSettings" class="element-type-settings" style="display: none;">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Центр X</label>
                                                            <input type="number" class="form-control" id="circleXInput" value="200">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Центр Y</label>
                                                            <input type="number" class="form-control" id="circleYInput" value="200">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Радиус</label>
                                                        <input type="number" class="form-control" id="circleRadiusInput" value="50" min="1">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет заливки</label>
                                                        <input type="color" class="form-control form-control-color" id="circleFillColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет обводки</label>
                                                        <input type="color" class="form-control form-control-color" id="circleStrokeColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Толщина обводки</label>
                                                        <input type="number" class="form-control" id="circleStrokeWidthInput" value="2" min="0" max="50">
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="circleFilledInput" checked>
                                                        <label class="form-check-label" for="circleFilledInput">
                                                            Заливка
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Настройки для квадрата -->
                                                <div id="squareSettings" class="element-type-settings" style="display: none;">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">X (левый верхний угол)</label>
                                                            <input type="number" class="form-control" id="squareXInput" value="100">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Y (левый верхний угол)</label>
                                                            <input type="number" class="form-control" id="squareYInput" value="100">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Размер стороны</label>
                                                        <input type="number" class="form-control" id="squareSizeInput" value="100" min="1">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет заливки</label>
                                                        <input type="color" class="form-control form-control-color" id="squareFillColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет обводки</label>
                                                        <input type="color" class="form-control form-control-color" id="squareStrokeColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Толщина обводки</label>
                                                        <input type="number" class="form-control" id="squareStrokeWidthInput" value="2" min="0" max="50">
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="squareFilledInput" checked>
                                                        <label class="form-check-label" for="squareFilledInput">
                                                            Заливка
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Настройки для прямоугольника -->
                                                <div id="rectangleSettings" class="element-type-settings" style="display: none;">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">X (левый верхний угол)</label>
                                                            <input type="number" class="form-control" id="rectXInput" value="100">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Y (левый верхний угол)</label>
                                                            <input type="number" class="form-control" id="rectYInput" value="100">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Ширина</label>
                                                            <input type="number" class="form-control" id="rectWidthInput" value="200" min="1">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Высота</label>
                                                            <input type="number" class="form-control" id="rectHeightInput" value="100" min="1">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет заливки</label>
                                                        <input type="color" class="form-control form-control-color" id="rectFillColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет обводки</label>
                                                        <input type="color" class="form-control form-control-color" id="rectStrokeColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Толщина обводки</label>
                                                        <input type="number" class="form-control" id="rectStrokeWidthInput" value="2" min="0" max="50">
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="rectFilledInput" checked>
                                                        <label class="form-check-label" for="rectFilledInput">
                                                            Заливка
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Настройки для трапеции -->
                                                <div id="trapezoidSettings" class="element-type-settings" style="display: none;">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">X (левый верхний угол)</label>
                                                            <input type="number" class="form-control" id="trapXInput" value="100">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Y (левый верхний угол)</label>
                                                            <input type="number" class="form-control" id="trapYInput" value="100">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Верхняя ширина</label>
                                                            <input type="number" class="form-control" id="trapTopWidthInput" value="200" min="1">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Нижняя ширина</label>
                                                            <input type="number" class="form-control" id="trapBottomWidthInput" value="300" min="1">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Высота</label>
                                                        <input type="number" class="form-control" id="trapHeightInput" value="100" min="1">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет заливки</label>
                                                        <input type="color" class="form-control form-control-color" id="trapFillColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Цвет обводки</label>
                                                        <input type="color" class="form-control form-control-color" id="trapStrokeColorInput" value="#000000">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Толщина обводки</label>
                                                        <input type="number" class="form-control" id="trapStrokeWidthInput" value="2" min="0" max="50">
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="trapFilledInput" checked>
                                                        <label class="form-check-label" for="trapFilledInput">
                                                            Заливка
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Настройки для изображения -->
                                                <div id="imageSettings" class="element-type-settings" style="display: none;">
                                                    <div class="mb-3">
                                                        <label class="form-label">Изображение</label>
                                                        <input type="file" class="form-control" id="imageFileInput" accept="image/*">
                                                        <small class="text-muted">Загрузите изображение или иконку</small>
                                                    </div>
                                                    <div id="imagePreview" class="mb-3" style="display: none;">
                                                        <img id="imagePreviewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">X</label>
                                                            <input type="number" class="form-control" id="imageXInput" value="100">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Y</label>
                                                            <input type="number" class="form-control" id="imageYInput" value="100">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Ширина</label>
                                                            <input type="number" class="form-control" id="imageWidthInput" value="100" min="1">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Высота</label>
                                                            <input type="number" class="form-control" id="imageHeightInput" value="100" min="1">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Поворот (градусы)</label>
                                                        <input type="number" class="form-control" id="imageRotationInput" value="0" min="-360" max="360" step="1">
                                                    </div>
                                                </div>

                                                <!-- Общие настройки для всех элементов -->
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

                                                <div class="d-grid gap-2">
                                                    <div class="btn-group mb-2" role="group">
                                                        <button type="button" class="btn btn-outline-info btn-sm" id="moveToFrontBtn" title="На передний план">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info btn-sm" id="moveUpBtn" title="На уровень выше">
                                                            <i class="fas fa-chevron-up"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info btn-sm" id="moveDownBtn" title="На уровень ниже">
                                                            <i class="fas fa-chevron-down"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info btn-sm" id="moveToBackBtn" title="На задний план">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </button>
                                                    </div>
                                                    <button type="button" class="btn btn-success mb-2" id="duplicateElementBtn">
                                                        <i class="fas fa-copy me-2"></i>Дублировать элемент
                                                    </button>
                                                    <button type="button" class="btn btn-danger" id="deleteElementBtn">
                                                        <i class="fas fa-trash me-2"></i>Удалить элемент
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mt-3 pt-3 border-top">
                                                <div class="btn-group-vertical w-100" role="group">
                                                    <button type="button" class="btn btn-outline-primary mb-2" id="addTextElement">
                                                        <i class="fas fa-font me-2"></i>Добавить текст
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary mb-2" id="addLineElement">
                                                        <i class="fas fa-minus me-2"></i>Добавить линию
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary mb-2" id="addCircleElement">
                                                        <i class="fas fa-circle me-2"></i>Добавить круг
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary mb-2" id="addSquareElement">
                                                        <i class="fas fa-square me-2"></i>Добавить квадрат
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary mb-2" id="addRectangleElement">
                                                        <i class="fas fa-square-full me-2"></i>Добавить прямоугольник
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary mb-2" id="addTrapezoidElement">
                                                        <i class="fas fa-shapes me-2"></i>Добавить трапецию
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" id="addImageElement">
                                                        <i class="fas fa-image me-2"></i>Добавить изображение
                                                    </button>
                                                </div>
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
                                        <i class="fas fa-info-circle"></i> Кликните на элемент в предпросмотре для его выбора и редактирования
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-secondary">Отмена</a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">Сохранить изменения</button>
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
// Массив всех элементов - загружаем из шаблона
@php
    $defaultElements = [
        [
            'type' => 'text',
            'text' => 'Сертификат',
            'x' => 100,
            'y' => 200,
            'size' => 48,
            'color' => '#000000',
            'font' => 'Arial',
            'letterSpacing' => 0,
            'rotation' => 0
        ]
    ];
@endphp
let elements = @json($certificateTemplate->text_elements ?? $defaultElements);

// Добавляем type и rotation если их нет (для совместимости со старыми шаблонами)
elements = elements.map(el => ({
    type: el.type || 'text',
    ...el,
    rotation: el.rotation || 0
}));

let selectedElementIndex = null;
let isDragging = false;
let dragOffset = { x: 0, y: 0 };
let elementRects = [];
let resizeHandles = [];
let isResizing = false;
let resizeHandleIndex = -1;
let initialSize = 0;
let initialMouseX = 0;
let backgroundImage = null;
let elementImages = {}; // Хранилище загруженных изображений для элементов

// Инициализация
document.getElementById('background_type').addEventListener('change', function() {
    document.getElementById('colorBackground').style.display = this.value === 'color' ? 'block' : 'none';
    document.getElementById('imageBackground').style.display = this.value === 'image' ? 'block' : 'none';
    document.getElementById('gradientBackground').style.display = this.value === 'gradient' ? 'block' : 'none';
    updatePreview();
});

// Добавление новых элементов
document.getElementById('addTextElement').addEventListener('click', function() {
    const newElement = {
        type: 'text',
        text: 'Новый текст',
        x: 100,
        y: 200,
        size: 24,
        color: '#000000',
        font: 'Arial',
        letterSpacing: 0,
        rotation: 0
    };
    elements.push(newElement);
    selectedElementIndex = elements.length - 1;
    loadElementSettings(selectedElementIndex);
    updatePreview();
});

document.getElementById('addLineElement').addEventListener('click', function() {
    const newElement = {
        type: 'line',
        x1: 100,
        y1: 100,
        x2: 200,
        y2: 200,
        lineWidth: 2,
        color: '#000000'
    };
    elements.push(newElement);
    selectedElementIndex = elements.length - 1;
    loadElementSettings(selectedElementIndex);
    updatePreview();
});

document.getElementById('addCircleElement').addEventListener('click', function() {
    const newElement = {
        type: 'circle',
        x: 200,
        y: 200,
        radius: 50,
        fillColor: '#000000',
        strokeColor: '#000000',
        strokeWidth: 2,
        filled: true
    };
    elements.push(newElement);
    selectedElementIndex = elements.length - 1;
    loadElementSettings(selectedElementIndex);
    updatePreview();
});

document.getElementById('addSquareElement').addEventListener('click', function() {
    const newElement = {
        type: 'square',
        x: 100,
        y: 100,
        size: 100,
        fillColor: '#000000',
        strokeColor: '#000000',
        strokeWidth: 2,
        filled: true
    };
    elements.push(newElement);
    selectedElementIndex = elements.length - 1;
    loadElementSettings(selectedElementIndex);
    updatePreview();
});

document.getElementById('addRectangleElement').addEventListener('click', function() {
    const newElement = {
        type: 'rectangle',
        x: 100,
        y: 100,
        width: 200,
        height: 100,
        fillColor: '#000000',
        strokeColor: '#000000',
        strokeWidth: 2,
        filled: true
    };
    elements.push(newElement);
    selectedElementIndex = elements.length - 1;
    loadElementSettings(selectedElementIndex);
    updatePreview();
});

document.getElementById('addTrapezoidElement').addEventListener('click', function() {
    const newElement = {
        type: 'trapezoid',
        x: 100,
        y: 100,
        topWidth: 200,
        bottomWidth: 300,
        height: 100,
        fillColor: '#000000',
        strokeColor: '#000000',
        strokeWidth: 2,
        filled: true
    };
    elements.push(newElement);
    selectedElementIndex = elements.length - 1;
    loadElementSettings(selectedElementIndex);
    updatePreview();
});

document.getElementById('addImageElement').addEventListener('click', function() {
    const newElement = {
        type: 'image',
        x: 100,
        y: 100,
        width: 100,
        height: 100,
        rotation: 0,
        imageData: null
    };
    elements.push(newElement);
    selectedElementIndex = elements.length - 1;
    loadElementSettings(selectedElementIndex);
    document.getElementById('imageFileInput').click();
});

// Обновление состояния кнопок управления порядком элементов
function updateZIndexButtons() {
    const moveToFrontBtn = document.getElementById('moveToFrontBtn');
    const moveUpBtn = document.getElementById('moveUpBtn');
    const moveDownBtn = document.getElementById('moveDownBtn');
    const moveToBackBtn = document.getElementById('moveToBackBtn');

    if (selectedElementIndex === null || selectedElementIndex < 0 || selectedElementIndex >= elements.length) {
        // Элемент не выбран - отключаем все кнопки
        moveToFrontBtn.disabled = true;
        moveUpBtn.disabled = true;
        moveDownBtn.disabled = true;
        moveToBackBtn.disabled = true;
        return;
    }

    // Элемент выбран - обновляем состояние кнопок
    const isFirst = selectedElementIndex === 0;
    const isLast = selectedElementIndex === elements.length - 1;

    moveToFrontBtn.disabled = isLast; // Нельзя переместить на передний план, если уже там
    moveUpBtn.disabled = isFirst; // Нельзя переместить вверх, если уже первый
    moveDownBtn.disabled = isLast; // Нельзя переместить вниз, если уже последний
    moveToBackBtn.disabled = isFirst; // Нельзя переместить на задний план, если уже там
}

// Загрузка настроек элемента в панель
function loadElementSettings(index) {
    if (index === null || index < 0 || index >= elements.length) {
        document.getElementById('noElementSelectedAlert').style.display = 'block';
        document.getElementById('elementSettingsForm').style.display = 'none';
        updateZIndexButtons(); // Обновляем состояние кнопок
        return;
    }

    const element = elements[index];
    document.getElementById('noElementSelectedAlert').style.display = 'none';
    document.getElementById('elementSettingsForm').style.display = 'block';

    document.querySelectorAll('.element-type-settings').forEach(el => el.style.display = 'none');
    document.getElementById('elementTypeInput').value = element.type;

    if (element.type === 'text') {
        document.getElementById('textSettings').style.display = 'block';
        document.getElementById('textInput').value = element.text || '';
        document.getElementById('sizeInput').value = element.size || 24;
        document.getElementById('colorInput').value = element.color || '#000000';
        document.getElementById('fontInput').value = element.font || 'Arial';
        document.getElementById('letterSpacingInput').value = element.letterSpacing || 0;
        document.getElementById('rotationInput').value = element.rotation || 0;
    } else if (element.type === 'line') {
        document.getElementById('lineSettings').style.display = 'block';
        document.getElementById('lineX1Input').value = element.x1 || 100;
        document.getElementById('lineY1Input').value = element.y1 || 100;
        document.getElementById('lineX2Input').value = element.x2 || 200;
        document.getElementById('lineY2Input').value = element.y2 || 200;
        document.getElementById('lineWidthInput').value = element.lineWidth || 2;
        document.getElementById('lineColorInput').value = element.color || '#000000';
    } else if (element.type === 'circle') {
        document.getElementById('circleSettings').style.display = 'block';
        document.getElementById('circleXInput').value = element.x || 200;
        document.getElementById('circleYInput').value = element.y || 200;
        document.getElementById('circleRadiusInput').value = element.radius || 50;
        document.getElementById('circleFillColorInput').value = element.fillColor || '#000000';
        document.getElementById('circleStrokeColorInput').value = element.strokeColor || '#000000';
        document.getElementById('circleStrokeWidthInput').value = element.strokeWidth || 2;
        document.getElementById('circleFilledInput').checked = element.filled !== false;
    } else if (element.type === 'square') {
        document.getElementById('squareSettings').style.display = 'block';
        document.getElementById('squareXInput').value = element.x || 100;
        document.getElementById('squareYInput').value = element.y || 100;
        document.getElementById('squareSizeInput').value = element.size || 100;
        document.getElementById('squareFillColorInput').value = element.fillColor || '#000000';
        document.getElementById('squareStrokeColorInput').value = element.strokeColor || '#000000';
        document.getElementById('squareStrokeWidthInput').value = element.strokeWidth || 2;
        document.getElementById('squareFilledInput').checked = element.filled !== false;
    } else if (element.type === 'rectangle') {
        document.getElementById('rectangleSettings').style.display = 'block';
        document.getElementById('rectXInput').value = element.x || 100;
        document.getElementById('rectYInput').value = element.y || 100;
        document.getElementById('rectWidthInput').value = element.width || 200;
        document.getElementById('rectHeightInput').value = element.height || 100;
        document.getElementById('rectFillColorInput').value = element.fillColor || '#000000';
        document.getElementById('rectStrokeColorInput').value = element.strokeColor || '#000000';
        document.getElementById('rectStrokeWidthInput').value = element.strokeWidth || 2;
        document.getElementById('rectFilledInput').checked = element.filled !== false;
    } else if (element.type === 'trapezoid') {
        document.getElementById('trapezoidSettings').style.display = 'block';
        document.getElementById('trapXInput').value = element.x || 100;
        document.getElementById('trapYInput').value = element.y || 100;
        document.getElementById('trapTopWidthInput').value = element.topWidth || 200;
        document.getElementById('trapBottomWidthInput').value = element.bottomWidth || 300;
        document.getElementById('trapHeightInput').value = element.height || 100;
        document.getElementById('trapFillColorInput').value = element.fillColor || '#000000';
        document.getElementById('trapStrokeColorInput').value = element.strokeColor || '#000000';
        document.getElementById('trapStrokeWidthInput').value = element.strokeWidth || 2;
        document.getElementById('trapFilledInput').checked = element.filled !== false;
    } else if (element.type === 'image') {
        document.getElementById('imageSettings').style.display = 'block';
        document.getElementById('imageXInput').value = element.x || 100;
        document.getElementById('imageYInput').value = element.y || 100;
        document.getElementById('imageWidthInput').value = element.width || 100;
        document.getElementById('imageHeightInput').value = element.height || 100;
        document.getElementById('imageRotationInput').value = element.rotation || 0;
        if (element.imageData && elementImages[element.imageData]) {
            document.getElementById('imagePreviewImg').src = elementImages[element.imageData].src;
            document.getElementById('imagePreview').style.display = 'block';
        }
    }

    document.getElementById('xInput').value = element.x || 100;
    document.getElementById('yInput').value = element.y || 100;

    // Обновляем состояние кнопок управления порядком
    updateZIndexButtons();
}

// Сохранение настроек элемента из панели
function saveElementSettings() {
    if (selectedElementIndex === null || selectedElementIndex < 0 || selectedElementIndex >= elements.length) {
        return;
    }

    const type = document.getElementById('elementTypeInput').value;
    let elementData = {
        type: type,
        x: parseInt(document.getElementById('xInput').value) || 0,
        y: parseInt(document.getElementById('yInput').value) || 0
    };

    if (type === 'text') {
        elementData = {
            ...elementData,
            text: document.getElementById('textInput').value,
            size: parseInt(document.getElementById('sizeInput').value) || 24,
            color: document.getElementById('colorInput').value,
            font: document.getElementById('fontInput').value || 'Arial',
            letterSpacing: parseFloat(document.getElementById('letterSpacingInput').value) || 0,
            rotation: parseFloat(document.getElementById('rotationInput').value) || 0
        };
    } else if (type === 'line') {
        elementData = {
            ...elementData,
            x1: parseInt(document.getElementById('lineX1Input').value) || 100,
            y1: parseInt(document.getElementById('lineY1Input').value) || 100,
            x2: parseInt(document.getElementById('lineX2Input').value) || 200,
            y2: parseInt(document.getElementById('lineY2Input').value) || 200,
            lineWidth: parseInt(document.getElementById('lineWidthInput').value) || 2,
            color: document.getElementById('lineColorInput').value
        };
    } else if (type === 'circle') {
        elementData = {
            ...elementData,
            radius: parseInt(document.getElementById('circleRadiusInput').value) || 50,
            fillColor: document.getElementById('circleFillColorInput').value,
            strokeColor: document.getElementById('circleStrokeColorInput').value,
            strokeWidth: parseInt(document.getElementById('circleStrokeWidthInput').value) || 2,
            filled: document.getElementById('circleFilledInput').checked
        };
    } else if (type === 'square') {
        elementData = {
            ...elementData,
            size: parseInt(document.getElementById('squareSizeInput').value) || 100,
            fillColor: document.getElementById('squareFillColorInput').value,
            strokeColor: document.getElementById('squareStrokeColorInput').value,
            strokeWidth: parseInt(document.getElementById('squareStrokeWidthInput').value) || 2,
            filled: document.getElementById('squareFilledInput').checked
        };
    } else if (type === 'rectangle') {
        elementData = {
            ...elementData,
            width: parseInt(document.getElementById('rectWidthInput').value) || 200,
            height: parseInt(document.getElementById('rectHeightInput').value) || 100,
            fillColor: document.getElementById('rectFillColorInput').value,
            strokeColor: document.getElementById('rectStrokeColorInput').value,
            strokeWidth: parseInt(document.getElementById('rectStrokeWidthInput').value) || 2,
            filled: document.getElementById('rectFilledInput').checked
        };
    } else if (type === 'trapezoid') {
        elementData = {
            ...elementData,
            topWidth: parseInt(document.getElementById('trapTopWidthInput').value) || 200,
            bottomWidth: parseInt(document.getElementById('trapBottomWidthInput').value) || 300,
            height: parseInt(document.getElementById('trapHeightInput').value) || 100,
            fillColor: document.getElementById('trapFillColorInput').value,
            strokeColor: document.getElementById('trapStrokeColorInput').value,
            strokeWidth: parseInt(document.getElementById('trapStrokeWidthInput').value) || 2,
            filled: document.getElementById('trapFilledInput').checked
        };
    } else if (type === 'image') {
        elementData = {
            ...elementData,
            width: parseInt(document.getElementById('imageWidthInput').value) || 100,
            height: parseInt(document.getElementById('imageHeightInput').value) || 100,
            rotation: parseFloat(document.getElementById('imageRotationInput').value) || 0,
            imageData: elements[selectedElementIndex].imageData || null
        };
    }

    elements[selectedElementIndex] = elementData;
    updatePreview();
}

// Дублирование элемента
document.getElementById('duplicateElementBtn').addEventListener('click', function() {
    if (selectedElementIndex !== null && selectedElementIndex >= 0 && selectedElementIndex < elements.length) {
        const originalElement = elements[selectedElementIndex];

        // Создаем глубокую копию элемента
        const duplicatedElement = JSON.parse(JSON.stringify(originalElement));

        // Смещаем новый элемент на 30 пикселей вправо и вниз
        const offsetX = 30;
        const offsetY = 30;

        // Обновляем позицию в зависимости от типа элемента
        if (duplicatedElement.type === 'text') {
            duplicatedElement.x = (duplicatedElement.x || 100) + offsetX;
            duplicatedElement.y = (duplicatedElement.y || 100) + offsetY;
        } else if (duplicatedElement.type === 'line') {
            duplicatedElement.x1 = (duplicatedElement.x1 || 100) + offsetX;
            duplicatedElement.y1 = (duplicatedElement.y1 || 100) + offsetY;
            duplicatedElement.x2 = (duplicatedElement.x2 || 200) + offsetX;
            duplicatedElement.y2 = (duplicatedElement.y2 || 200) + offsetY;
        } else if (duplicatedElement.type === 'circle') {
            duplicatedElement.x = (duplicatedElement.x || 100) + offsetX;
            duplicatedElement.y = (duplicatedElement.y || 100) + offsetY;
        } else if (duplicatedElement.type === 'square') {
            duplicatedElement.x = (duplicatedElement.x || 100) + offsetX;
            duplicatedElement.y = (duplicatedElement.y || 100) + offsetY;
        } else if (duplicatedElement.type === 'rectangle') {
            duplicatedElement.x = (duplicatedElement.x || 100) + offsetX;
            duplicatedElement.y = (duplicatedElement.y || 100) + offsetY;
        } else if (duplicatedElement.type === 'trapezoid') {
            duplicatedElement.x = (duplicatedElement.x || 100) + offsetX;
            duplicatedElement.y = (duplicatedElement.y || 100) + offsetY;
        } else if (duplicatedElement.type === 'image') {
            duplicatedElement.x = (duplicatedElement.x || 100) + offsetX;
            duplicatedElement.y = (duplicatedElement.y || 100) + offsetY;
            // Для изображений копируем imageData, если оно есть
            if (duplicatedElement.imageData && elementImages[duplicatedElement.imageData]) {
                // Изображение уже есть в хранилище, можно использовать тот же imageData
                // или создать новый идентификатор, но проще использовать тот же
            }
        }

        // Добавляем новый элемент в массив
        elements.push(duplicatedElement);

        // Выбираем новый элемент
        selectedElementIndex = elements.length - 1;
        loadElementSettings(selectedElementIndex);
        updatePreview();
    }
});

// Управление порядком элементов (z-index)
document.getElementById('moveToFrontBtn').addEventListener('click', function() {
    if (selectedElementIndex !== null && selectedElementIndex >= 0 && selectedElementIndex < elements.length) {
        // Перемещаем элемент в конец массива (рисуется последним, виден сверху)
        const element = elements.splice(selectedElementIndex, 1)[0];
        elements.push(element);
        selectedElementIndex = elements.length - 1;
        loadElementSettings(selectedElementIndex);
        updatePreview();
    }
});

document.getElementById('moveUpBtn').addEventListener('click', function() {
    if (selectedElementIndex !== null && selectedElementIndex > 0 && selectedElementIndex < elements.length) {
        // Перемещаем элемент на одну позицию вверх
        const temp = elements[selectedElementIndex];
        elements[selectedElementIndex] = elements[selectedElementIndex - 1];
        elements[selectedElementIndex - 1] = temp;
        selectedElementIndex = selectedElementIndex - 1;
        loadElementSettings(selectedElementIndex);
        updatePreview();
    }
});

document.getElementById('moveDownBtn').addEventListener('click', function() {
    if (selectedElementIndex !== null && selectedElementIndex >= 0 && selectedElementIndex < elements.length - 1) {
        // Перемещаем элемент на одну позицию вниз
        const temp = elements[selectedElementIndex];
        elements[selectedElementIndex] = elements[selectedElementIndex + 1];
        elements[selectedElementIndex + 1] = temp;
        selectedElementIndex = selectedElementIndex + 1;
        loadElementSettings(selectedElementIndex);
        updatePreview();
    }
});

document.getElementById('moveToBackBtn').addEventListener('click', function() {
    if (selectedElementIndex !== null && selectedElementIndex >= 0 && selectedElementIndex < elements.length) {
        // Перемещаем элемент в начало массива (рисуется первым, виден снизу)
        const element = elements.splice(selectedElementIndex, 1)[0];
        elements.unshift(element);
        selectedElementIndex = 0;
        loadElementSettings(selectedElementIndex);
        updatePreview();
    }
});

// Удаление элемента
document.getElementById('deleteElementBtn').addEventListener('click', function() {
    if (selectedElementIndex !== null && selectedElementIndex >= 0 && selectedElementIndex < elements.length) {
        const element = elements[selectedElementIndex];
        if (element.type === 'image' && element.imageData && elementImages[element.imageData]) {
            delete elementImages[element.imageData];
        }
        elements.splice(selectedElementIndex, 1);
        selectedElementIndex = null;
        loadElementSettings(null);
        updatePreview();
    }
});

// Обработка изменения типа элемента
document.getElementById('elementTypeInput').addEventListener('change', function() {
    if (selectedElementIndex !== null) {
        const oldElement = elements[selectedElementIndex];
        const newType = this.value;

        let newElement = {
            type: newType,
            x: oldElement.x || 100,
            y: oldElement.y || 100
        };

        if (newType === 'text') {
            newElement = { ...newElement, text: 'Новый текст', size: 24, color: '#000000', font: 'Arial', letterSpacing: 0, rotation: 0 };
        } else if (newType === 'line') {
            newElement = { ...newElement, x1: 100, y1: 100, x2: 200, y2: 200, lineWidth: 2, color: '#000000' };
        } else if (newType === 'circle') {
            newElement = { ...newElement, radius: 50, fillColor: '#000000', strokeColor: '#000000', strokeWidth: 2, filled: true };
        } else if (newType === 'square') {
            newElement = { ...newElement, size: 100, fillColor: '#000000', strokeColor: '#000000', strokeWidth: 2, filled: true };
        } else if (newType === 'rectangle') {
            newElement = { ...newElement, width: 200, height: 100, fillColor: '#000000', strokeColor: '#000000', strokeWidth: 2, filled: true };
        } else if (newType === 'trapezoid') {
            newElement = { ...newElement, topWidth: 200, bottomWidth: 300, height: 100, fillColor: '#000000', strokeColor: '#000000', strokeWidth: 2, filled: true };
        } else if (newType === 'image') {
            newElement = { ...newElement, width: 100, height: 100, rotation: 0, imageData: null };
        }

        elements[selectedElementIndex] = newElement;
        loadElementSettings(selectedElementIndex);
        updatePreview();
    }
});

// Обработка загрузки изображения для элемента
document.getElementById('imageFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && selectedElementIndex !== null) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const imageId = 'img_' + Date.now() + '_' + selectedElementIndex;
                elementImages[imageId] = img;
                elements[selectedElementIndex].imageData = imageId;
                document.getElementById('imagePreviewImg').src = event.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                updatePreview();
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Слушатели для всех полей настроек
const allInputIds = [
    'textInput', 'xInput', 'yInput', 'sizeInput', 'colorInput', 'fontInput', 'letterSpacingInput', 'rotationInput',
    'lineX1Input', 'lineY1Input', 'lineX2Input', 'lineY2Input', 'lineWidthInput', 'lineColorInput',
    'circleXInput', 'circleYInput', 'circleRadiusInput', 'circleFillColorInput', 'circleStrokeColorInput', 'circleStrokeWidthInput', 'circleFilledInput',
    'squareXInput', 'squareYInput', 'squareSizeInput', 'squareFillColorInput', 'squareStrokeColorInput', 'squareStrokeWidthInput', 'squareFilledInput',
    'rectXInput', 'rectYInput', 'rectWidthInput', 'rectHeightInput', 'rectFillColorInput', 'rectStrokeColorInput', 'rectStrokeWidthInput', 'rectFilledInput',
    'trapXInput', 'trapYInput', 'trapTopWidthInput', 'trapBottomWidthInput', 'trapHeightInput', 'trapFillColorInput', 'trapStrokeColorInput', 'trapStrokeWidthInput', 'trapFilledInput',
    'imageXInput', 'imageYInput', 'imageWidthInput', 'imageHeightInput', 'imageRotationInput', 'elementTypeInput'
];

allInputIds.forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', saveElementSettings);
        element.addEventListener('change', saveElementSettings);
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

// Загрузка существующего изображения при редактировании
@if($certificateTemplate->background_image)
const existingImageUrl = '{{ Storage::url($certificateTemplate->background_image) }}';
const existingImg = new Image();
existingImg.crossOrigin = 'anonymous';
existingImg.onload = function() {
    backgroundImage = existingImg;
    updatePreview();
};
existingImg.onerror = function() {
    console.error('Не удалось загрузить существующее изображение');
};
existingImg.src = existingImageUrl;
@endif

// Загрузка нового фонового изображения
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
        // Если файл не выбран, используем существующее изображение (если есть)
        @if($certificateTemplate->background_image)
        if (!backgroundImage && existingImageUrl) {
            existingImg.src = existingImageUrl;
        }
        @else
        backgroundImage = null;
        @endif
        updatePreview();
    }
});

// Обработка удаления изображения
document.getElementById('remove_background_image')?.addEventListener('change', function(e) {
    if (e.target.checked) {
        backgroundImage = null;
        updatePreview();
    } else {
        // Восстанавливаем существующее изображение
        @if($certificateTemplate->background_image)
        if (!backgroundImage && existingImageUrl) {
            existingImg.src = existingImageUrl;
        }
        @endif
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

    const bgType = document.getElementById('background_type').value;
    if (bgType === 'color') {
        ctx.fillStyle = document.getElementById('background_color').value;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else if (bgType === 'gradient') {
        const angle = parseInt(document.getElementById('gradient_angle').value) || 0;
        const color1 = document.getElementById('gradient_color1').value;
        const color2 = document.getElementById('gradient_color2').value;

        // Преобразуем угол в радианы и вычисляем координаты для градиента
        const angleRad = (angle * Math.PI) / 180;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const length = Math.sqrt(canvas.width * canvas.width + canvas.height * canvas.height);

        const x1 = centerX - Math.cos(angleRad) * length / 2;
        const y1 = centerY - Math.sin(angleRad) * length / 2;
        const x2 = centerX + Math.cos(angleRad) * length / 2;
        const y2 = centerY + Math.sin(angleRad) * length / 2;

        const gradient = ctx.createLinearGradient(x1, y1, x2, y2);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    } else if (bgType === 'image') {
        // Рисуем белый фон по умолчанию
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Если изображение загружено, рисуем его
        if (backgroundImage) {
            // Масштабируем изображение под размер canvas
            ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
        }
    } else {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    elementRects = [];
    resizeHandles = [];

    // Рисуем все элементы
    elements.forEach((elementData, index) => {
        ctx.save();
        let bounds = { minX: 0, maxX: 0, minY: 0, maxY: 0 };

        if (elementData.type === 'text') {
        if (!textData.text || textData.text.trim() === '') return;

        const x = textData.x * scale;
        const y = textData.y * scale;
        const size = textData.size * scale;
        const color = textData.color || '#000000';
        const font = textData.font || 'Arial';
        const letterSpacing = (textData.letterSpacing || 0) * scale;
        const rotation = (textData.rotation || 0) * Math.PI / 180;

        ctx.save();

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
    if (elements.length > 0) {
        loadElementSettings(0);
        selectedElementIndex = 0;
    }
    updatePreview();
});

// Обработка кликов на canvas для выбора элемента
document.getElementById('previewCanvas').addEventListener('click', function(e) {
    if (isResizing) {
        return;
    }

    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const handleIndex = checkResizeHandleClick(x, y);
    if (handleIndex !== -1) {
        return;
    }

    for (let i = elementRects.length - 1; i >= 0; i--) {
        const elementRect = elementRects[i];
        if (x >= elementRect.x - 5 && x <= elementRect.x + elementRect.width + 5 &&
            y >= elementRect.y - 5 && y <= elementRect.y + elementRect.height + 5) {

            selectedElementIndex = elementRect.index;
            loadElementSettings(selectedElementIndex);
            updatePreview();
            return;
        }
    }

    selectedElementIndex = null;
    loadElementSettings(null);
    updatePreview();
});

// Обработка перетаскивания элементов
document.getElementById('previewCanvas').addEventListener('mousedown', function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const handleIndex = checkResizeHandleClick(x, y);
    if (handleIndex !== -1 && selectedElementIndex !== null) {
        isResizing = true;
        isDragging = false;
        resizeHandleIndex = handleIndex;
        initialMouseX = x;
        const element = elements[selectedElementIndex];
        if (element.type === 'text') {
            initialSize = element.size || 24;
        } else if (element.type === 'image' || element.type === 'rectangle') {
            initialSize = element.width || 100;
        } else if (element.type === 'square') {
            initialSize = element.size || 100;
        }
        this.style.cursor = 'ew-resize';
        e.preventDefault();
        e.stopPropagation();
        return;
    }

    for (let i = elementRects.length - 1; i >= 0; i--) {
        const elementRect = elementRects[i];
        if (x >= elementRect.x - 5 && x <= elementRect.x + elementRect.width + 5 &&
            y >= elementRect.y - 5 && y <= elementRect.y + elementRect.height + 5) {

            selectedElementIndex = elementRect.index;
            loadElementSettings(selectedElementIndex);
            isDragging = true;
            isResizing = false;
            dragOffset.x = x - elementRect.centerX;
            dragOffset.y = y - elementRect.centerY;
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

    if (isResizing && selectedElementIndex !== null && resizeHandleIndex !== -1) {
        const handle = resizeHandles[resizeHandleIndex];
        const deltaX = x - initialMouseX;
        const element = elements[selectedElementIndex];

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

        if (element.type === 'text') {
            const newSize = Math.max(8, Math.min(200, initialSize + sizeDelta * 0.5));
            element.size = Math.round(newSize);
            document.getElementById('sizeInput').value = Math.round(newSize);
        } else if (element.type === 'image' || element.type === 'rectangle') {
            const newWidth = Math.max(10, initialSize + sizeDelta);
            element.width = Math.round(newWidth);
            document.getElementById('imageWidthInput').value = Math.round(newWidth);
            if (element.type === 'rectangle') {
                document.getElementById('rectWidthInput').value = Math.round(newWidth);
            }
        } else if (element.type === 'square') {
            const newSize = Math.max(10, initialSize + sizeDelta);
            element.size = Math.round(newSize);
            document.getElementById('squareSizeInput').value = Math.round(newSize);
        }

        updatePreview();
        this.style.cursor = 'ew-resize';
        return;
    }

    if (!isDragging && !isResizing) {
        const handleIndex = checkResizeHandleClick(x, y);
        if (handleIndex !== -1) {
            this.style.cursor = 'ew-resize';
            return;
        }
    }

    if (isDragging && selectedElementIndex !== null) {
        const width = parseInt(document.getElementById('width').value) || 1200;
        const height = parseInt(document.getElementById('height').value) || 800;
        const container = this.parentElement;
        const maxWidth = Math.min(container.clientWidth - 40, width);
        const maxHeight = Math.min(window.innerHeight * 0.5, height);
        const scale = Math.min(maxWidth / width, maxHeight / height);

        const realX = Math.max(0, Math.min(width, (x - dragOffset.x) / scale));
        const realY = Math.max(0, Math.min(height, (y - dragOffset.y) / scale));

        const element = elements[selectedElementIndex];
        element.x = Math.round(realX);
        element.y = Math.round(realY);

        if (element.type === 'line') {
            const deltaX = realX - element.x1;
            const deltaY = realY - element.y1;
            element.x1 = realX;
            element.y1 = realY;
            element.x2 += deltaX;
            element.y2 += deltaY;
        } else if (element.type === 'circle') {
            element.x = Math.round(realX);
            element.y = Math.round(realY);
        }

        document.getElementById('xInput').value = Math.round(realX);
        document.getElementById('yInput').value = Math.round(realY);
        updatePreview();
        this.style.cursor = 'move';
    } else {
        let overElement = false;
        for (let i = elementRects.length - 1; i >= 0; i--) {
            const elementRect = elementRects[i];
            if (x >= elementRect.x - 5 && x <= elementRect.x + elementRect.width + 5 &&
                y >= elementRect.y - 5 && y <= elementRect.y + elementRect.height + 5) {
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
    if (isResizing) {
        isResizing = false;
        this.style.cursor = 'crosshair';
    }
});

document.getElementById('previewCanvas').addEventListener('mouseleave', function() {
    if (isDragging) {
        isDragging = false;
        this.style.cursor = 'crosshair';
    }
    if (isResizing) {
        isResizing = false;
        this.style.cursor = 'crosshair';
    }
});

document.addEventListener('mouseup', function() {
    if (isResizing) {
        isResizing = false;
        resizeHandleIndex = -1;
        initialMouseX = 0;
        initialSize = 0;
    }
    if (isDragging) {
        isDragging = false;
    }
});

function updateGradient() {
    const gradient = {
        colors: [
            document.getElementById('gradient_color1').value,
            document.getElementById('gradient_color2').value
        ],
        angle: parseInt(document.getElementById('gradient_angle').value) || 0
    };
    document.getElementById('background_gradient').value = JSON.stringify(gradient);
    updateGradientPreview();
}

function updateGradientPreview() {
    const angle = parseInt(document.getElementById('gradient_angle').value) || 0;
    const color1 = document.getElementById('gradient_color1').value;
    const color2 = document.getElementById('gradient_color2').value;

    const angleRad = (angle * Math.PI) / 180;
    const previewBox = document.getElementById('gradientPreviewBox');

    // Вычисляем координаты для градиента в превью
    const length = Math.sqrt(200 * 200 + 60 * 60);
    const centerX = 100;
    const centerY = 30;

    const x1 = centerX - Math.cos(angleRad) * length / 2;
    const y1 = centerY - Math.sin(angleRad) * length / 2;
    const x2 = centerX + Math.cos(angleRad) * length / 2;
    const y2 = centerY + Math.sin(angleRad) * length / 2;

    // Создаем градиент через CSS
    const angleDeg = angle;
    previewBox.style.background = `linear-gradient(${angleDeg}deg, ${color1}, ${color2})`;
}

function prepareFormData() {
    if (selectedElementIndex !== null) {
        saveElementSettings();
    }

    const filteredElements = elements.filter(el => {
        if (el.type === 'text') {
            return el.text && el.text.trim() !== '';
        } else if (el.type === 'image') {
            return el.imageData && elementImages[el.imageData];
        }
        return true;
    });

    const jsonValue = JSON.stringify(filteredElements);

    // Проверяем существование поля перед установкой значения
    const textElementsField = document.getElementById('text_elements_json');
    if (!textElementsField) {
        console.error('Поле text_elements_json не найдено');
        alert('Ошибка: поле формы не найдено. Пожалуйста, обновите страницу.');
        return false;
    }

    // Убеждаемся, что поле доступно для записи
    if (textElementsField.disabled || textElementsField.readOnly) {
        textElementsField.disabled = false;
        textElementsField.readOnly = false;
    }

    // Устанавливаем значение без установки фокуса
    textElementsField.setAttribute('value', jsonValue);
    textElementsField.value = jsonValue;

    try {
        JSON.parse(jsonValue);
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

// Обновление угла градиента
document.getElementById('gradient_angle')?.addEventListener('input', function() {
    document.getElementById('gradient_angle_value').textContent = this.value;
    updateGradient();
    updatePreview();
});

document.getElementById('gradient_angle')?.addEventListener('change', function() {
    document.getElementById('gradient_angle_value').textContent = this.value;
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
function initFormSubmitHandler() {
    const templateForm = document.getElementById('templateForm');
    if (!templateForm) {
        console.error('Форма templateForm не найдена!');
        return;
    }

    // Проверяем, не был ли уже добавлен обработчик
    if (templateForm.hasAttribute('data-submit-handler-attached')) {
        console.log('Обработчик формы уже добавлен');
        return;
    }

    templateForm.setAttribute('data-submit-handler-attached', 'true');

    // Предотвращаем автоматическую валидацию браузера для скрытых полей
    const hiddenFields = templateForm.querySelectorAll('input[type="hidden"]');
    hiddenFields.forEach(function(field) {
        field.setAttribute('tabindex', '-1');
        field.setAttribute('aria-hidden', 'true');
    });

    let isSubmitting = false; // Флаг для предотвращения повторной отправки

    templateForm.addEventListener('submit', function(e) {
        // Если форма уже отправляется программно, пропускаем обработку
        if (isSubmitting) {
            return true;
        }

        console.log('Форма отправляется...');

        try {
            if (!prepareFormData()) {
                console.log('prepareFormData вернула false, отменяем отправку');
                e.preventDefault();
                return false;
            }
            console.log('Данные подготовлены успешно, форма отправляется');

            // Устанавливаем флаг и позволяем форме отправиться естественным образом
            isSubmitting = true;
            // Форма отправится автоматически после успешной подготовки данных
        } catch (error) {
            console.error('Ошибка при отправке формы:', error);
            alert('Произошла ошибка при подготовке данных: ' + error.message);
            e.preventDefault();
            isSubmitting = false;
            return false;
        }
    });
    console.log('Обработчик формы инициализирован');
}

// Инициализация после загрузки DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM загружен, инициализация...');
        initFormSubmitHandler();
    });
} else {
    // DOM уже загружен
    console.log('DOM уже загружен, инициализация...');
    initFormSubmitHandler();
}

// Добавляем слушатели для существующих текстовых элементов
document.querySelectorAll('.text-element-input, select.text-element-input').forEach(input => {
    input.addEventListener('input', updatePreview);
    input.addEventListener('change', updatePreview);
});

// Инициализация
updatePreview();
updateGradient();
updateGradientPreview();
</script>
@endpush
@endsection
