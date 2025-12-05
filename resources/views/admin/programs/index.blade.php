@extends('layouts.admin')

@section('title', 'Управление программами')
@section('page-title', 'Образовательные программы')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-book me-2"></i>Образовательные программы
                    </h3>
                    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Добавить программу
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Учебное заведение</th>
                                    <th>Тип оплаты</th>
                                    <th>Цена</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programs as $program)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $program->id }}</span></td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $program->name }}</h6>
                                                <small class="text-muted">{{ $program->code ?? 'Без кода' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($program->institution)
                                                <span class="badge bg-info">{{ $program->institution->name }}</span>
                                            @else
                                                <span class="text-muted">Не указано</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->is_paid)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-dollar-sign me-1"></i>Платная
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-gift me-1"></i>Бесплатная
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->is_paid && $program->price)
                                                <strong>{{ number_format($program->price, 0) }} {{ $program->currency ?? 'USD' }}</strong>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Активна
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Неактивна
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.programs.show', $program) }}"
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.programs.edit', $program) }}"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.programs.destroy', $program) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить эту программу?')">
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
                                                <i class="fas fa-book fa-3x mb-3"></i>
                                                <p>Программы не найдены</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($programs->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $programs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
