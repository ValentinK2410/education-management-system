{{-- Dashboard для преподавателя --}}
<!-- Статистические карточки -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            {{ __('messages.my_courses') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['my_courses'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            {{ __('messages.total_students') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            {{ __('messages.active_courses') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_courses'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Мои курсы -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chalkboard-teacher me-2"></i>{{ __('messages.my_courses') }}
                </h5>
            </div>
            <div class="card-body">
                @if(isset($myCourses) && $myCourses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.course_name') }}</th>
                                    <th>{{ __('messages.program') }}</th>
                                    <th>{{ __('messages.institution') }}</th>
                                    <th>{{ __('messages.students') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myCourses as $course)
                                    <tr>
                                        <td>
                                            <strong>{{ $course->name }}</strong>
                                            @if($course->code)
                                                <br><small class="text-muted">{{ $course->code }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $course->program->name ?? __('messages.no_program') }}</td>
                                        <td>{{ $course->program->institution->name ?? __('messages.no_institution') }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $course->users()
                                                    ->whereHas('roles', function ($query) {
                                                        $query->where('slug', 'student');
                                                    })
                                                    ->whereDoesntHave('roles', function ($query) {
                                                        $query->whereIn('slug', ['instructor', 'admin']);
                                                    })
                                                    ->count() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($course->is_active)
                                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                        <p>{{ __('messages.no_courses') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Мои студенты -->
@if(isset($myStudents) && $myStudents->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>{{ __('messages.my_students') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('messages.students') }}</th>
                                <th>{{ __('messages.email') }}</th>
                                <th>{{ __('messages.role') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myStudents as $student)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title bg-primary text-white rounded-circle">
                                                    {{ substr($student->name, 0, 1) }}
                                                </div>
                                            </div>
                                            {{ $student->name }}
                                        </div>
                                    </td>
                                    <td>{{ $student->email }}</td>
                                    <td>
                                        @foreach($student->roles as $role)
                                            <span class="badge bg-info me-1">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
