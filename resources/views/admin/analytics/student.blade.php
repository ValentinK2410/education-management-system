@extends('layouts.admin')

@section('title', 'Аналитика студента: ' . $student->name)
@section('page-title', 'Аналитика студента')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>{{ $student->name }}
                        <small class="text-muted">({{ $student->email }})</small>
                    </h5>
                    <div>
                        <a href="{{ route('admin.users.show', $student) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user me-2"></i>Профиль студента
                        </a>
                        <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Назад к аналитике
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Курсы студента -->
    @if($courses->count() > 0)
        @foreach($courses as $course)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book me-2"></i>{{ $course->name }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @php
                                $courseActivities = $progressData->filter(function($item) use ($course) {
                                    return $item['course']->id === $course->id;
                                });
                            @endphp
                            
                            @if($courseActivities->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Элемент курса</th>
                                                <th>Тип</th>
                                                <th>Статус</th>
                                                <th>Оценка</th>
                                                <th>Дата сдачи</th>
                                                <th>Дата проверки</th>
                                                <th>История действий</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($courseActivities as $item)
                                                @php
                                                    $progress = $item['progress'];
                                                    $activity = $item['activity'];
                                                    $history = $item['history'];
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $activity->name }}</strong>
                                                        @if($activity->section_name)
                                                            <br><small class="text-muted">{{ $activity->section_name }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            @if($activity->activity_type == 'assign')
                                                                Задание
                                                            @elseif($activity->activity_type == 'quiz')
                                                                Тест
                                                            @elseif($activity->activity_type == 'forum')
                                                                Форум
                                                            @elseif($activity->activity_type == 'resource')
                                                                Материал
                                                            @else
                                                                {{ $activity->activity_type }}
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusClass = [
                                                                'not_started' => 'bg-secondary',
                                                                'in_progress' => 'bg-warning',
                                                                'submitted' => 'bg-info',
                                                                'graded' => 'bg-success',
                                                                'completed' => 'bg-primary',
                                                            ];
                                                            $class = $statusClass[$progress->status ?? 'not_started'] ?? 'bg-secondary';
                                                            $statusText = [
                                                                'not_started' => 'Не начато',
                                                                'in_progress' => 'В процессе',
                                                                'submitted' => 'Сдано',
                                                                'graded' => 'Проверено',
                                                                'completed' => 'Завершено',
                                                            ];
                                                        @endphp
                                                        <span class="badge {{ $class }}">
                                                            {{ $statusText[$progress->status ?? 'not_started'] ?? 'Не начато' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($progress && $progress->grade !== null)
                                                            <strong>{{ $progress->grade }}</strong>
                                                            @if($progress->max_grade)
                                                                / {{ $progress->max_grade }}
                                                            @endif
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $progress->submitted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                                    <td>{{ $progress->graded_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                                    <td>
                                                        @if($history->count() > 0)
                                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#historyModal{{ $activity->id }}">
                                                                <i class="fas fa-history me-1"></i>{{ $history->count() }} записей
                                                            </button>
                                                        @else
                                                            <span class="text-muted">Нет истории</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                
                                                <!-- Модальное окно истории -->
                                                @if($history->count() > 0)
                                                <div class="modal fade" id="historyModal{{ $activity->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">История действий: {{ $activity->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="timeline">
                                                                    @foreach($history as $historyItem)
                                                                        <div class="timeline-item mb-3">
                                                                            <div class="d-flex">
                                                                                <div class="timeline-marker me-3">
                                                                                    <i class="fas fa-circle text-primary"></i>
                                                                                </div>
                                                                                <div class="flex-grow-1">
                                                                                    <div class="d-flex justify-content-between">
                                                                                        <strong>
                                                                                            @if($historyItem->action_type == 'submitted')
                                                                                                Сдано
                                                                                            @elseif($historyItem->action_type == 'graded')
                                                                                                Проверено
                                                                                            @elseif($historyItem->action_type == 'started')
                                                                                                Начато
                                                                                            @elseif($historyItem->action_type == 'completed')
                                                                                                Завершено
                                                                                            @else
                                                                                                {{ $historyItem->action_type }}
                                                                                            @endif
                                                                                        </strong>
                                                                                        <small class="text-muted">{{ $historyItem->created_at->format('d.m.Y H:i') }}</small>
                                                                                    </div>
                                                                                    @if($historyItem->description)
                                                                                        <div class="mt-1">{{ $historyItem->description }}</div>
                                                                                    @endif
                                                                                    @if($historyItem->performedBy)
                                                                                        <small class="text-muted">Выполнено: {{ $historyItem->performedBy->name }}</small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Нет данных о прогрессе по этому курсу
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>Студент не записан ни на один курс
        </div>
    @endif
</div>

<style>
.timeline-item {
    border-left: 2px solid #e3e6f0;
    padding-left: 1rem;
}

.timeline-marker {
    margin-left: -1.5rem;
}
</style>
@endsection

