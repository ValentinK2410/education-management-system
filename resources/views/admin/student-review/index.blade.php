@extends('layouts.admin')

@section('title', 'Проверка студентов')
@section('page-title', 'Проверка студентов')

@push('styles')
<style>
    /* Tabs */
    .tabs-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        overflow-x: auto;
    }

    .tab-button {
        flex: 1;
        padding: 1rem 1.5rem;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        white-space: nowrap;
        min-width: 150px;
    }

    .tab-button:hover {
        background: #f1f5f9;
        color: #667eea;
    }

    .tab-button.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background: white;
    }

    .tab-button i {
        font-size: 1.125rem;
    }

    .tab-content {
        display: none;
        padding: 2rem;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .message-preview {
        max-width: 400px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .tabs-nav {
            flex-direction: column;
        }

        .tab-button {
            border-bottom: 1px solid #e2e8f0;
            border-right: none;
        }

        .tab-button.active {
            border-bottom-color: #e2e8f0;
            border-left: 3px solid #667eea;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Проверка студентов
                    </h4>
                    <p class="text-muted mb-0 mt-2">
                        Просмотр и проверка заданий, тестов и форумов студентов со всех ваших курсов
                    </p>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <div class="tabs-nav">
                    <button class="tab-button {{ request('tab', 'assignments') === 'assignments' ? 'active' : '' }}" onclick="switchTab(event, 'assignments')" data-tab="assignments">
                        <i class="fas fa-file-alt"></i>
                        <span>Задания <span class="badge bg-warning ms-2">{{ $assignments->count() }}</span></span>
                    </button>
                    <button class="tab-button {{ request('tab') === 'quizzes' ? 'active' : '' }}" onclick="switchTab(event, 'quizzes')" data-tab="quizzes">
                        <i class="fas fa-question-circle"></i>
                        <span>Тесты <span class="badge bg-info ms-2">{{ $quizzes->count() }}</span></span>
                    </button>
                    <button class="tab-button {{ request('tab') === 'forums' ? 'active' : '' }}" onclick="switchTab(event, 'forums')" data-tab="forums">
                        <i class="fas fa-comments"></i>
                        <span>Форумы <span class="badge bg-danger ms-2">{{ $forums->count() }}</span></span>
                    </button>
                </div>

                <!-- Tab: Задания -->
                <div id="tab-assignments" class="tab-content {{ request('tab', 'assignments') === 'assignments' ? 'active' : '' }}">
                    @if($assignments->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Нет заданий, ожидающих проверки</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Студент</th>
                                        <th>Курс</th>
                                        <th>Название задания</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                        @php
                                            $moodleUrl = null;
                                            try {
                                                if ($assignment->activity) {
                                                    $moodleUrl = $assignment->activity->moodle_url;
                                                }
                                            } catch (\Exception $e) {
                                                $moodleUrl = null;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $assignment->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $assignment->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $assignment->course->name }}</strong>
                                                @if($assignment->course->code)
                                                    <br><small class="text-muted">({{ $assignment->course->code }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $assignment->activity->name ?? 'Неизвестно' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $assignment->status_class }} status-badge">
                                                    {{ $assignment->status_text }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($assignment->display_date)
                                                    {{ \Carbon\Carbon::parse($assignment->display_date)->format('d.m.Y H:i') }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($moodleUrl)
                                                    <a href="{{ $moodleUrl }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Перейти в Moodle
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Ссылка недоступна
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab: Тесты -->
                <div id="tab-quizzes" class="tab-content {{ request('tab') === 'quizzes' ? 'active' : '' }}">
                    @if($quizzes->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Нет тестов для отображения</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Студент</th>
                                        <th>Курс</th>
                                        <th>Название теста</th>
                                        <th>Статус</th>
                                        <th>Попытки</th>
                                        <th>Оценка</th>
                                        <th>Дата последней попытки</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizzes as $quiz)
                                        @php
                                            $moodleUrl = null;
                                            try {
                                                if ($quiz->activity) {
                                                    $moodleUrl = $quiz->activity->moodle_url;
                                                }
                                            } catch (\Exception $e) {
                                                $moodleUrl = null;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $quiz->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $quiz->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $quiz->course->name }}</strong>
                                                @if($quiz->course->code)
                                                    <br><small class="text-muted">({{ $quiz->course->code }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $quiz->activity->name ?? 'Неизвестно' }}</strong>
                                            </td>
                                            <td>
                                                @if($quiz->status === 'answered')
                                                    <span class="badge bg-success status-badge">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning status-badge">
                                                        <i class="fas fa-exclamation-circle me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $quiz->attempts_count ?? 0 }}
                                            </td>
                                            <td>
                                                @if($quiz->grade !== null && $quiz->max_grade)
                                                    <strong>{{ number_format($quiz->grade, 1) }}</strong> / {{ number_format($quiz->max_grade, 1) }}
                                                    @if($quiz->max_grade > 0)
                                                        <br><small class="text-muted">({{ number_format(($quiz->grade / $quiz->max_grade) * 100, 1) }}%)</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($quiz->last_attempt_at)
                                                    {{ \Carbon\Carbon::parse($quiz->last_attempt_at)->format('d.m.Y H:i') }}
                                                @elseif($quiz->submitted_at)
                                                    {{ \Carbon\Carbon::parse($quiz->submitted_at)->format('d.m.Y H:i') }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($moodleUrl)
                                                    <a href="{{ $moodleUrl }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Перейти в Moodle
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Ссылка недоступна
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab: Форумы -->
                <div id="tab-forums" class="tab-content {{ request('tab') === 'forums' ? 'active' : '' }}">
                    @if($forums->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Нет форумов, ожидающих ответа</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Студент</th>
                                        <th>Курс</th>
                                        <th>Название форума</th>
                                        <th>Последнее сообщение</th>
                                        <th>Дата сообщения</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($forums as $forum)
                                        @php
                                            $moodleUrl = null;
                                            try {
                                                if ($forum->activity) {
                                                    $moodleUrl = $forum->activity->moodle_url;
                                                }
                                            } catch (\Exception $e) {
                                                $moodleUrl = null;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $forum->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $forum->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $forum->course->name }}</strong>
                                                @if($forum->course->code)
                                                    <br><small class="text-muted">({{ $forum->course->code }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $forum->activity->name ?? 'Неизвестно' }}</strong>
                                            </td>
                                            <td>
                                                <div class="message-preview" title="{{ $forum->message_text ?? 'Текст сообщения недоступен' }}">
                                                    {{ \Illuminate\Support\Str::limit($forum->message_text ?? 'Текст сообщения недоступен', 200) }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($forum->submitted_at)
                                                    {{ \Carbon\Carbon::parse($forum->submitted_at)->format('d.m.Y H:i') }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-danger status-badge">
                                                    <i class="fas fa-comments me-1"></i>
                                                    Ожидает ответа
                                                </span>
                                            </td>
                                            <td>
                                                @if($moodleUrl)
                                                    <a href="{{ $moodleUrl }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Перейти в Moodle
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Ссылка недоступна
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(evt, tabName) {
    if (evt) {
        evt.preventDefault();
    }

    // Hide all tab contents
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }

    // Remove active class from all buttons
    const tabButtons = document.getElementsByClassName('tab-button');
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove('active');
    }

    // Show selected tab content
    document.getElementById('tab-' + tabName).classList.add('active');

    // Add active class to clicked button
    if (evt && evt.currentTarget) {
        evt.currentTarget.classList.add('active');
    } else {
        // Если вызывается программно, находим кнопку по data-tab
        const targetButton = document.querySelector(`button[data-tab="${tabName}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
        }
    }

    // Обновляем URL без перезагрузки страницы
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
}

// При загрузке страницы проверяем параметр tab в URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    if (tabParam) {
        // Проверяем, что такая вкладка существует
        const tabElement = document.getElementById('tab-' + tabParam);
        if (tabElement) {
            switchTab(null, tabParam);
        }
    }
});
</script>
@endsection
