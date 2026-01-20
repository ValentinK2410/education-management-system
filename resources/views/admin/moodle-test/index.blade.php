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

        <div class="alert alert-info mb-4">
            <h6><i class="fas fa-info-circle me-2"></i>Как использовать эту страницу:</h6>
            <ul class="mb-0">
                <li><strong>ID курса:</strong> Выберите курс из списка <strong>ИЛИ</strong> введите ID курса в Moodle вручную в поле ниже</li>
                <li><strong>ID студента:</strong> Выберите студента из списка <strong>ИЛИ</strong> введите ID студента в системе вручную (не Moodle ID!)</li>
                <li>Если студент не выбран, будут показаны только общие данные курса (задания, тесты, форумы без данных конкретного студента)</li>
            </ul>
        </div>

        <form id="test-form">
            @csrf
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="course_id" class="form-label">
                        <i class="fas fa-book me-1"></i>ID курса в Moodle <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="course_id" name="course_id">
                        <option value="">-- Выберите курс из списка ИЛИ введите ID ниже --</option>
                        @if(isset($courses) && $courses->count() > 0)
                            @foreach($courses as $course)
                                <option value="{{ $course->moodle_course_id }}" data-course-name="{{ $course->name }}">
                                    {{ $course->name }} (Moodle ID: {{ $course->moodle_course_id }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="mt-2">
                        <label for="course_id_manual" class="form-label small text-muted">
                            <i class="fas fa-keyboard me-1"></i>Или введите ID курса в Moodle вручную:
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="course_id_manual" 
                               name="course_id_manual"
                               min="1"
                               step="1"
                               placeholder="Например: 123"
                               oninput="if(this.value) { document.getElementById('course_id').value = ''; }">
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="student_id" class="form-label">
                        <i class="fas fa-user me-1"></i>Студент
                    </label>
                    <select class="form-select" id="student_id" name="student_id">
                        <option value="">-- Не выбран (только общие данные курса) --</option>
                        @if(isset($students) && $students->count() > 0)
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" data-moodle-id="{{ $student->moodle_user_id }}">
                                    {{ $student->name }} (ID: {{ $student->id }}, Moodle: {{ $student->moodle_user_id }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="mt-2">
                        <label for="student_id_manual" class="form-label small text-muted">
                            <i class="fas fa-keyboard me-1"></i>Или введите ID студента вручную (ID в системе, не Moodle ID):
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="student_id_manual" 
                               name="student_id_manual"
                               min="1"
                               step="1"
                               placeholder="Например: 45"
                               oninput="if(this.value) { document.getElementById('student_id').value = ''; }">
                    </div>
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
    // Проверяем, что все элементы существуют
    const form = document.getElementById('test-form');
    if (!form) {
        console.error('Форма не найдена');
        return;
    }
    
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');
    const resultsContent = document.getElementById('results-content');
    const error = document.getElementById('error');
    const errorContent = document.getElementById('error-content');
    
    // Проверяем доступность полей для ручного ввода
    const courseIdManual = document.getElementById('course_id_manual');
    const studentIdManual = document.getElementById('student_id_manual');
    
    if (courseIdManual) {
        // Убеждаемся, что поле не заблокировано
        courseIdManual.removeAttribute('disabled');
        courseIdManual.removeAttribute('readonly');
        console.log('Поле course_id_manual доступно для ввода');
    } else {
        console.error('Поле course_id_manual не найдено');
    }
    
    if (studentIdManual) {
        // Убеждаемся, что поле не заблокировано
        studentIdManual.removeAttribute('disabled');
        studentIdManual.removeAttribute('readonly');
        console.log('Поле student_id_manual доступно для ввода');
    } else {
        console.error('Поле student_id_manual не найдено');
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Скрываем предыдущие результаты
        if (results) results.style.display = 'none';
        if (error) error.style.display = 'none';
        
        // Показываем загрузку
        loading.classList.add('active');
        
        // Получаем значения из select или manual input
        const courseIdSelect = document.getElementById('course_id');
        const courseIdManual = document.getElementById('course_id_manual');
        const studentIdSelect = document.getElementById('student_id');
        const studentIdManual = document.getElementById('student_id_manual');
        
        // Определяем course_id
        let courseId = null;
        if (courseIdManual.value && courseIdManual.value.trim() !== '') {
            courseId = courseIdManual.value.trim();
        } else if (courseIdSelect.value && courseIdSelect.value.trim() !== '') {
            courseId = courseIdSelect.value.trim();
        }
        
        if (!courseId || courseId === '') {
            showError('Пожалуйста, выберите курс из списка ИЛИ введите ID курса в Moodle вручную в поле ниже');
            loading.classList.remove('active');
            return;
        }
        
        // Определяем student_id (опционально)
        let studentId = null;
        if (studentIdManual.value && studentIdManual.value.trim() !== '') {
            studentId = studentIdManual.value.trim();
        } else if (studentIdSelect.value && studentIdSelect.value.trim() !== '') {
            studentId = studentIdSelect.value.trim();
        }
        
        // Создаем FormData с правильными значениями
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
        formData.append('course_id', courseId);
        if (studentId) {
            formData.append('student_id', studentId);
        }
        formData.append('test_type', document.getElementById('test_type').value);
        
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
    
    function displayResults(resultsData) {
        const resultsEl = document.getElementById('results');
        const resultsContentEl = document.getElementById('results-content');
        
        if (!resultsEl || !resultsContentEl) {
            console.error('Results elements not found');
            showError('Ошибка: элементы результатов не найдены');
            return;
        }
        
        let html = '';
        
        // Общая информация
        html += '<div class="mb-4">';
        html += '<h6><i class="fas fa-info-circle me-2"></i>Информация о тесте</h6>';
        html += '<ul class="list-unstyled">';
        html += `<li><strong>ID курса:</strong> ${resultsData.course_id}</li>`;
        if (resultsData.student_id) {
            html += `<li><strong>ID студента:</strong> ${resultsData.student_id}</li>`;
            if (resultsData.student_info) {
                html += `<li><strong>Имя студента:</strong> ${resultsData.student_info.name}</li>`;
                html += `<li><strong>Email:</strong> ${resultsData.student_info.email}</li>`;
                html += `<li><strong>Moodle User ID:</strong> ${resultsData.student_info.moodle_user_id || 'не установлен'}</li>`;
            }
        } else {
            html += '<li><strong>ID студента:</strong> не указан (будут показаны только общие данные курса)</li>';
        }
        html += `<li><strong>Тип теста:</strong> ${resultsData.test_type}</li>`;
        html += `<li><strong>Время выполнения:</strong> ${resultsData.timestamp}</li>`;
        html += '</ul>';
        html += '</div>';
        
        // Данные по типам
        if (resultsData.data.assignments) {
            html += displayAssignmentsData(resultsData.data.assignments);
        }
        
        if (resultsData.data.quizzes) {
            html += displayQuizzesData(resultsData.data.quizzes);
        }
        
        if (resultsData.data.forums) {
            html += displayForumsData(resultsData.data.forums);
        }
        
        resultsContentEl.innerHTML = html;
        resultsEl.style.display = 'block';
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
        if (error && errorContent) {
            errorContent.textContent = message;
            error.style.display = 'block';
        } else {
            console.error('Error elements not found:', message);
        }
    }
    
    // Делаем функцию глобальной для вызова из onclick
    window.clearResults = function() {
        const resultsEl = document.getElementById('results');
        const errorEl = document.getElementById('error');
        const resultsContentEl = document.getElementById('results-content');
        const errorContentEl = document.getElementById('error-content');
        
        if (resultsEl) resultsEl.style.display = 'none';
        if (errorEl) errorEl.style.display = 'none';
        if (resultsContentEl) resultsContentEl.innerHTML = '';
        if (errorContentEl) errorContentEl.textContent = '';
    };
});
</script>
@endpush
