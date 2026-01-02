<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Студент</th>
                <th>Элемент курса</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Оценка</th>
                <th>Дата сдачи</th>
                <th>Дата проверки</th>
                <th>Проверил</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                @php
                    $progress = $activity->studentProgress->first() ?? null;
                @endphp
                <tr>
                    <td>
                        @if($activity->studentProgress->count() > 0)
                            @foreach($activity->studentProgress->take(5) as $p)
                                <div class="mb-1">
                                    <strong>{{ $p->user->name ?? '' }}</strong>
                                    <br><small class="text-muted">{{ $p->user->email ?? '' }}</small>
                                </div>
                            @endforeach
                            @if($activity->studentProgress->count() > 5)
                                <small class="text-muted">и еще {{ $activity->studentProgress->count() - 5 }} студентов</small>
                            @endif
                        @else
                            <span class="text-muted">Нет студентов</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $activity->name }}</strong>
                        @if($activity->section_name)
                            <br><small class="text-muted">{{ $activity->section_name }}</small>
                        @endif
                    </td>
                    <td>
                        @if($activity->activity_type == 'assign')
                            <span class="badge activity-type-badge activity-type-assign">
                                <i class="fas fa-file-alt me-1"></i>Задание
                            </span>
                        @elseif($activity->activity_type == 'quiz')
                            <span class="badge activity-type-badge activity-type-quiz">
                                <i class="fas fa-clipboard-check me-1"></i>Тест
                            </span>
                        @elseif($activity->activity_type == 'forum')
                            <span class="badge activity-type-badge activity-type-forum">
                                <i class="fas fa-comments me-1"></i>Форум
                            </span>
                        @elseif($activity->activity_type == 'resource')
                            <span class="badge activity-type-badge activity-type-resource">
                                <i class="fas fa-book me-1"></i>Материал
                            </span>
                        @elseif($activity->activity_type == 'exam')
                            <span class="badge activity-type-badge activity-type-exam">
                                <i class="fas fa-graduation-cap me-1"></i>Экзамен
                            </span>
                        @else
                            <span class="badge bg-secondary">{{ $activity->activity_type }}</span>
                        @endif
                    </td>
                    <td>
                        @if($progress)
                            @php
                                $statusClass = [
                                    'not_started' => 'bg-secondary',
                                    'in_progress' => 'bg-warning',
                                    'submitted' => 'bg-info',
                                    'graded' => 'bg-success',
                                    'completed' => 'bg-primary',
                                ];
                                $class = $statusClass[$progress->status] ?? 'bg-secondary';
                                $statusText = [
                                    'not_started' => 'Не начато',
                                    'in_progress' => 'В процессе',
                                    'submitted' => 'Сдано',
                                    'graded' => 'Проверено',
                                    'completed' => 'Завершено',
                                ];
                            @endphp
                            <span class="badge {{ $class }}">
                                {{ $statusText[$progress->status] ?? $progress->status }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Не начато</span>
                        @endif
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
                    <td>
                        @if($progress && $progress->submitted_at && $progress->status == 'submitted')
                            @php
                                $daysAgo = now()->diffInDays($progress->submitted_at);
                                $dateClass = 'submitted-date-cell ';
                                if ($daysAgo < 1) {
                                    $dateClass .= 'submitted-date-recent';
                                } elseif ($daysAgo < 3) {
                                    $dateClass .= 'submitted-date-1-3days';
                                } elseif ($daysAgo < 7) {
                                    $dateClass .= 'submitted-date-3-7days';
                                } elseif ($daysAgo < 14) {
                                    $dateClass .= 'submitted-date-7-14days';
                                } else {
                                    $dateClass .= 'submitted-date-old';
                                }
                            @endphp
                            <span class="{{ $dateClass }}">{{ $progress->submitted_at->format('d.m.Y H:i') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $progress->graded_at?->format('d.m.Y H:i') ?? '—' }}</td>
                    <td>{{ $progress->gradedBy->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.analytics.student', $progress->user_id ?? '#') }}" class="btn btn-sm btn-info" title="Просмотр студента">
                            <i class="fas fa-user"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>Элементы курса не найдены</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
/* Стили для бейджей типов элементов курса - нейтральные цвета */
.activity-type-badge {
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.4rem 0.65rem;
    border: 1px solid #dee2e6;
    background-color: #6c757d !important;
    color: #ffffff !important;
}

.activity-type-badge i {
    color: #ffffff;
    opacity: 0.9;
}

/* Подсветка даты сдачи в зависимости от давности */
.submitted-date-cell {
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
    font-weight: 500;
}

.submitted-date-recent {
    background-color: #fff9c4; /* Светло-желтый - менее 1 дня */
}

.submitted-date-1-3days {
    background-color: #ffe082; /* Желтый - 1-3 дня */
}

.submitted-date-3-7days {
    background-color: #ffb74d; /* Оранжевый - 3-7 дней */
}

.submitted-date-7-14days {
    background-color: #ff8a65; /* Красно-оранжевый - 7-14 дней */
}

.submitted-date-old {
    background-color: #d32f2f; /* Темно-красный - более 14 дней */
    color: #ffffff;
}
</style>
