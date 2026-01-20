@extends('layouts.admin')

@section('title', 'Тестирование Moodle API')
@section('page-title', 'Тестирование Moodle API')

@push('styles')
<style>
    .test-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .result-container {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-top: 1.5rem;
        max-height: 600px;
        overflow-y: auto;
    }

    .json-viewer {
        background: #282c34;
        color: #abb2bf;
        padding: 1rem;
        border-radius: 0.5rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        line-height: 1.6;
        overflow-x: auto;
    }

    .json-key {
        color: #e06c75;
    }

    .json-string {
        color: #98c379;
    }

    .json-number {
        color: #d19a66;
    }

    .json-boolean {
        color: #56b6c2;
    }

    .json-null {
        color: #5c6370;
    }

    .field-info {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 0.75rem;
        margin: 0.5rem 0;
        border-radius: 0.25rem;
    }

    .field-info strong {
        color: #1976D2;
    }

    .loading {
        display: none;
        text-align: center;
        padding: 2rem;
    }

    .loading.active {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="test-container">
        <h4 class="mb-4">
            <i class="fas fa-flask me-2"></i>Тестирование функций Moodle API
        </h4>

        <form id="test-form">
            @csrf
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="course_id" class="form-label">
                        <i class="fas fa-book me-1"></i>ID курса в Moodle <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="course_id" 
                           name="course_id" 
                           required 
                           min="1"
                           placeholder="Например: 123">
                    <small class="form-text text-muted">ID курса из Moodle (moodle_course_id)</small>
                </div>

                <div class="col-md-4">
                    <label for="student_id" class="form-label">
                        <i class="fas fa-user me-1"></i>ID студента в системе
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="student_id" 
                           name="student_id" 
                           min="1"
                           placeholder="Например: 45">
                    <small class="form-text text-muted">ID студента в Laravel (user_id). Если не указан, будут показаны только общие данные курса.</small>
                </div>

                <div class="col-md-4">
                    <label for="test_type" class="form-label">
                        <i class="fas fa-list me-1"></i>Тип тестирования <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="test_type" name="test_type" required>
                        <option value="all">Все типы (задания, тесты, форумы)</option>
                        <option value="assignments">Задания (Assignments)</option>
                        <option value="quizzes">Тесты (Quizzes)</option>
                        <option value="forums">Форумы (Forums)</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play me-2"></i>Выполнить тест
                </button>
                <button type="button" class="btn btn-secondary" onclick="clearResults()">
                    <i class="fas fa-eraser me-2"></i>Очистить результаты
                </button>
            </div>
        </form>

        <div id="loading" class="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-2">Выполняется запрос к Moodle API...</p>
        </div>

        <div id="results" class="result-container" style="display: none;">
            <h5 class="mb-3">
                <i class="fas fa-check-circle text-success me-2"></i>Результаты тестирования
            </h5>
            <div id="results-content"></div>
        </div>

        <div id="error" class="alert alert-danger" style="display: none;">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Ошибка</h5>
            <div id="error-content"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('test-form');
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');
    const resultsContent = document.getElementById('results-content');
    const error = document.getElementById('error');
    const errorContent = document.getElementById('error-content');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Скрываем предыдущие результаты
        results.style.display = 'none';
        error.style.display = 'none';
        
        // Показываем загрузку
        loading.classList.add('active');
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route("admin.moodle-test.test") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData
            });
            
            const data = await response.json();
            
            loading.classList.remove('active');
            
            if (data.success) {
                displayResults(data.results);
            } else {
                showError(data.error || 'Произошла ошибка при выполнении теста');
            }
        } catch (err) {
            loading.classList.remove('active');
            showError('Ошибка подключения: ' + err.message);
        }
    });
    
    function displayResults(results) {
        let html = '';
        
        // Общая информация
        html += '<div class="mb-4">';
        html += '<h6><i class="fas fa-info-circle me-2"></i>Информация о тесте</h6>';
        html += '<ul class="list-unstyled">';
        html += `<li><strong>ID курса:</strong> ${results.course_id}</li>`;
        if (results.student_id) {
            html += `<li><strong>ID студента:</strong> ${results.student_id}</li>`;
            if (results.student_info) {
                html += `<li><strong>Имя студента:</strong> ${results.student_info.name}</li>`;
                html += `<li><strong>Email:</strong> ${results.student_info.email}</li>`;
                html += `<li><strong>Moodle User ID:</strong> ${results.student_info.moodle_user_id || 'не установлен'}</li>`;
            }
        } else {
            html += '<li><strong>ID студента:</strong> не указан (будут показаны только общие данные курса)</li>';
        }
        html += `<li><strong>Тип теста:</strong> ${results.test_type}</li>`;
        html += `<li><strong>Время выполнения:</strong> ${results.timestamp}</li>`;
        html += '</ul>';
        html += '</div>';
        
        // Данные по типам
        if (results.data.assignments) {
            html += displayAssignmentsData(results.data.assignments);
        }
        
        if (results.data.quizzes) {
            html += displayQuizzesData(results.data.quizzes);
        }
        
        if (results.data.forums) {
            html += displayForumsData(results.data.forums);
        }
        
        resultsContent.innerHTML = html;
        results.style.display = 'block';
    }
    
    function displayAssignmentsData(data) {
        let html = '<div class="mb-4">';
        html += '<h6><i class="fas fa-tasks me-2"></i>Задания (Assignments)</h6>';
        html += `<div class="field-info"><strong>API вызов:</strong> ${data.api_call}</div>`;
        html += `<div class="field-info"><strong>Количество заданий:</strong> ${data.assignments_count || 0}</div>`;
        
        if (data.assignments && data.assignments.length > 0) {
            html += '<h6 class="mt-3">Список заданий:</h6>';
            html += '<ul>';
            data.assignments.forEach(assignment => {
                html += '<li>';
                html += `<strong>${assignment.name || 'Без названия'}</strong> `;
                html += `(ID: ${assignment.id})`;
                if (assignment.duedate) {
                    const dueDate = new Date(assignment.duedate * 1000);
                    html += ` - Срок сдачи: ${dueDate.toLocaleString('ru-RU')}`;
                }
                html += '</li>';
            });
            html += '</ul>';
            
            html += '<details class="mt-3">';
            html += '<summary class="btn btn-sm btn-outline-secondary">Показать полные данные JSON</summary>';
            html += '<div class="json-viewer mt-2">' + formatJSON(data.assignments) + '</div>';
            html += '</details>';
        }
        
        if (data.student_data && Object.keys(data.student_data).length > 0) {
            html += '<h6 class="mt-3">Данные студента:</h6>';
            html += `<div class="field-info"><strong>Сдач:</strong> ${data.student_data.submissions_count || 0}</div>`;
            html += `<div class="field-info"><strong>Оценок:</strong> ${data.student_data.grades_count || 0}</div>`;
            
            if (data.student_data.submissions && Object.keys(data.student_data.submissions).length > 0) {
                html += '<details class="mt-3">';
                html += '<summary class="btn btn-sm btn-outline-info">Показать сдачи студента (JSON)</summary>';
                html += '<div class="json-viewer mt-2">' + formatJSON(data.student_data.submissions) + '</div>';
                html += '</details>';
            }
            
            if (data.student_data.grades && Object.keys(data.student_data.grades).length > 0) {
                html += '<details class="mt-3">';
                html += '<summary class="btn btn-sm btn-outline-success">Показать оценки студента (JSON)</summary>';
                html += '<div class="json-viewer mt-2">' + formatJSON(data.student_data.grades) + '</div>';
                html += '</details>';
            }
        }
        
        html += '</div>';
        return html;
    }
    
    function displayQuizzesData(data) {
        let html = '<div class="mb-4">';
        html += '<h6><i class="fas fa-question-circle me-2"></i>Тесты (Quizzes)</h6>';
        html += `<div class="field-info"><strong>API вызов:</strong> ${data.api_call}</div>`;
        html += `<div class="field-info"><strong>Количество тестов:</strong> ${data.quizzes_count || 0}</div>`;
        
        if (data.quizzes && data.quizzes.length > 0) {
            html += '<h6 class="mt-3">Список тестов:</h6>';
            html += '<ul>';
            data.quizzes.forEach(quiz => {
                html += '<li>';
                html += `<strong>${quiz.name || 'Без названия'}</strong> `;
                html += `(ID: ${quiz.id})`;
                if (quiz.grade) {
                    html += ` - Макс. оценка: ${quiz.grade}`;
                }
                if (quiz.attempts) {
                    html += ` - Попыток: ${quiz.attempts}`;
                }
                html += '</li>';
            });
            html += '</ul>';
            
            html += '<details class="mt-3">';
            html += '<summary class="btn btn-sm btn-outline-secondary">Показать полные данные JSON</summary>';
            html += '<div class="json-viewer mt-2">' + formatJSON(data.quizzes) + '</div>';
            html += '</details>';
        }
        
        if (data.student_data && Object.keys(data.student_data).length > 0) {
            html += '<h6 class="mt-3">Данные студента:</h6>';
            html += `<div class="field-info"><strong>Всего попыток:</strong> ${data.student_data.total_attempts || 0}</div>`;
            html += `<div class="field-info"><strong>Оценок:</strong> ${data.student_data.grades_count || 0}</div>`;
            
            if (data.student_data.attempts && Object.keys(data.student_data.attempts).length > 0) {
                html += '<details class="mt-3">';
                html += '<summary class="btn btn-sm btn-outline-info">Показать попытки студента (JSON)</summary>';
                html += '<div class="json-viewer mt-2">' + formatJSON(data.student_data.attempts) + '</div>';
                html += '</details>';
            }
            
            if (data.student_data.grades && Object.keys(data.student_data.grades).length > 0) {
                html += '<details class="mt-3">';
                html += '<summary class="btn btn-sm btn-outline-success">Показать оценки студента (JSON)</summary>';
                html += '<div class="json-viewer mt-2">' + formatJSON(data.student_data.grades) + '</div>';
                html += '</details>';
            }
        }
        
        html += '</div>';
        return html;
    }
    
    function displayForumsData(data) {
        let html = '<div class="mb-4">';
        html += '<h6><i class="fas fa-comments me-2"></i>Форумы (Forums)</h6>';
        html += `<div class="field-info"><strong>API вызов:</strong> ${data.api_call}</div>`;
        html += `<div class="field-info"><strong>Количество форумов:</strong> ${data.forums_count || 0}</div>`;
        
        if (data.forums && data.forums.length > 0) {
            html += '<h6 class="mt-3">Список форумов:</h6>';
            html += '<ul>';
            data.forums.forEach(forum => {
                html += '<li>';
                html += `<strong>${forum.name || 'Без названия'}</strong> `;
                html += `(ID: ${forum.id})`;
                html += '</li>';
            });
            html += '</ul>';
            
            html += '<details class="mt-3">';
            html += '<summary class="btn btn-sm btn-outline-secondary">Показать полные данные JSON</summary>';
            html += '<div class="json-viewer mt-2">' + formatJSON(data.forums) + '</div>';
            html += '</details>';
        }
        
        if (data.student_data && Object.keys(data.student_data).length > 0) {
            html += '<h6 class="mt-3">Данные студента:</h6>';
            html += `<div class="field-info"><strong>Всего постов:</strong> ${data.student_data.total_posts || 0}</div>`;
            html += `<div class="field-info"><strong>Неотвеченных постов:</strong> <span class="text-danger">${data.student_data.unanswered_posts || 0}</span></div>`;
            
            if (data.student_data.posts_by_forum && Object.keys(data.student_data.posts_by_forum).length > 0) {
                html += '<h6 class="mt-3">Посты по форумам:</h6>';
                Object.keys(data.student_data.posts_by_forum).forEach(forumId => {
                    const forumData = data.student_data.posts_by_forum[forumId];
                    html += `<div class="mb-3 p-3 border rounded">`;
                    html += `<strong>Форум ID: ${forumId}</strong> - Постов: ${forumData.posts_count}, Неотвеченных: <span class="text-danger">${forumData.unanswered_count}</span>`;
                    
                    if (forumData.posts && forumData.posts.length > 0) {
                        html += '<ul class="mt-2">';
                        forumData.posts.forEach(post => {
                            html += '<li>';
                            html += `<strong>${post.subject || 'Без темы'}</strong> `;
                            html += `(ID: ${post.id})`;
                            if (post.timecreated) {
                                const postDate = new Date(post.timecreated * 1000);
                                html += ` - ${postDate.toLocaleString('ru-RU')}`;
                            }
                            html += ` - <span class="${post.needs_response ? 'text-danger' : 'text-success'}">${post.needs_response ? 'Требует ответа' : 'Есть ответ'}</span>`;
                            if (post.message) {
                                html += `<br><small class="text-muted">${post.message}...</small>`;
                            }
                            html += '</li>';
                        });
                        html += '</ul>';
                    }
                    html += '</div>';
                });
            }
            
            if (data.student_data.posts && Object.keys(data.student_data.posts).length > 0) {
                html += '<details class="mt-3">';
                html += '<summary class="btn btn-sm btn-outline-info">Показать все посты студента (JSON)</summary>';
                html += '<div class="json-viewer mt-2">' + formatJSON(data.student_data.posts) + '</div>';
                html += '</details>';
            }
        }
        
        html += '</div>';
        return html;
    }
    
    function formatJSON(obj) {
        return JSON.stringify(obj, null, 2)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
    }
    
    function showError(message) {
        errorContent.textContent = message;
        error.style.display = 'block';
    }
    
    function clearResults() {
        results.style.display = 'none';
        error.style.display = 'none';
        resultsContent.innerHTML = '';
        errorContent.textContent = '';
    }
});
</script>
@endpush
