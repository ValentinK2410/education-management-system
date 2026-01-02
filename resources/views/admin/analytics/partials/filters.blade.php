<form method="GET" action="{{ route('admin.analytics.course', $course ?? null) }}" id="analytics-filter-form">
    <div class="row g-3">
        @if(!isset($course))
        <div class="col-md-3">
            <label for="course_id" class="form-label">Курс</label>
            <select class="form-select" id="course_id" name="course_id">
                <option value="">Все курсы</option>
                @foreach($courses ?? [] as $c)
                    <option value="{{ $c->id }}" {{ (request('course_id') == $c->id) ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        
        <div class="col-md-3">
            <label for="user_id" class="form-label">Студент</label>
            <select class="form-select" id="user_id" name="user_id">
                <option value="">Все студенты</option>
                @foreach($students ?? [] as $student)
                    <option value="{{ $student->id }}" {{ (request('user_id') == $student->id) ? 'selected' : '' }}>
                        {{ $student->name }} ({{ $student->email }})
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-2">
            <label for="activity_type" class="form-label">Тип элемента</label>
            <select class="form-select" id="activity_type" name="activity_type">
                <option value="">Все типы</option>
                <option value="assign" {{ (request('activity_type') == 'assign') ? 'selected' : '' }}>📄 Задания</option>
                <option value="quiz" {{ (request('activity_type') == 'quiz') ? 'selected' : '' }}>✅ Тесты</option>
                <option value="forum" {{ (request('activity_type') == 'forum') ? 'selected' : '' }}>💬 Форумы</option>
                <option value="resource" {{ (request('activity_type') == 'resource') ? 'selected' : '' }}>📚 Материалы</option>
                <option value="exam" {{ (request('activity_type') == 'exam') ? 'selected' : '' }}>🎓 Экзамены</option>
            </select>
        </div>
        
        <div class="col-md-2">
            <label for="status" class="form-label">Статус</label>
            <select class="form-select" id="status" name="status">
                <option value="">Все статусы</option>
                <option value="not_started" {{ (request('status') == 'not_started') ? 'selected' : '' }}>Не начато</option>
                <option value="submitted" {{ (request('status') == 'submitted') ? 'selected' : '' }}>Сдано</option>
                <option value="graded" {{ (request('status') == 'graded') ? 'selected' : '' }}>Проверено</option>
                <option value="completed" {{ (request('status') == 'completed') ? 'selected' : '' }}>Завершено</option>
            </select>
        </div>
        
        <div class="col-md-2">
            <label for="date_from" class="form-label">Дата от</label>
            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
        </div>
        
        <div class="col-md-2">
            <label for="date_to" class="form-label">Дата до</label>
            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
        </div>
        
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter me-2"></i>Применить фильтры
            </button>
            <a href="{{ route('admin.analytics.course', $course ?? null) }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Сбросить
            </a>
        </div>
    </div>
</form>

