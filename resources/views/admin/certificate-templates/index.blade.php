@extends('layouts.admin')

@section('title', 'Шаблоны сертификатов')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-certificate me-2"></i>Шаблоны сертификатов
                    </h3>
                    <a href="{{ route('admin.certificate-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Создать шаблон
                    </a>
                </div>
                <div class="card-body">
                    <!-- Форма поиска -->
                    <form method="GET" action="{{ route('admin.certificate-templates.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Поиск по названию или описанию..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search me-2"></i>Найти
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    @if(request('search'))
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Найдено результатов по запросу: <strong>"{{ request('search') }}"</strong>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Тип</th>
                                    <th>Размер</th>
                                    <th>Качество</th>
                                    <th>Фон</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $template->id }}</span></td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $template->name }}</h6>
                                                @if($template->is_default)
                                                    <small class="text-success"><i class="fas fa-star"></i> По умолчанию</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($template->type === 'course')
                                                <span class="badge bg-info">Курс</span>
                                            @else
                                                <span class="badge bg-primary">Программа</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->width }}x{{ $template->height }}px</td>
                                        <td>{{ $template->quality }}%</td>
                                        <td>
                                            @if($template->background_type === 'color')
                                                <span class="badge bg-light text-dark">Цвет</span>
                                            @elseif($template->background_type === 'image')
                                                <span class="badge bg-light text-dark">Изображение</span>
                                            @else
                                                <span class="badge bg-light text-dark">Градиент</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">Активен</span>
                                            @else
                                                <span class="badge bg-danger">Неактивен</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.certificate-templates.edit', $template) }}"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.certificate-templates.destroy', $template) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить этот шаблон?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-certificate fa-3x mb-3"></i>
                                                <p>Шаблоны сертификатов не найдены</p>
                                                <a href="{{ route('admin.certificate-templates.create') }}" class="btn btn-primary">
                                                    Создать первый шаблон
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($templates->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
