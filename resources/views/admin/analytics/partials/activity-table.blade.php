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
                    <td>{{ $progress->submitted_at?->format('d.m.Y H:i') ?? '—' }}</td>
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

