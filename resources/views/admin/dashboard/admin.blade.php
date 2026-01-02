{{-- Dashboard для администратора --}}
@if(session('is_switched'))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-user-secret me-2"></i>
            <strong>{{ __('messages.attention') }}!</strong> {{ __('messages.switched_user') }}: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
            <a href="{{ route('admin.user-switch.back') }}" class="btn btn-sm btn-outline-danger ms-3">
                <i class="fas fa-undo me-1"></i>{{ __('messages.switch_back') }}
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>
@endif

@if(session('role_switched'))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-user-tag me-2"></i>
            <strong>{{ __('messages.information_note') }}:</strong> {{ __('messages.switched_role') }}: <strong>{{ \App\Models\Role::find(session('switched_role_id'))->name ?? __('messages.unknown_role') }}</strong>
            <a href="{{ route('admin.role-switch.back') }}" class="btn btn-sm btn-outline-primary ms-3">
                <i class="fas fa-undo me-1"></i>{{ __('messages.switch_back_role') }}
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>
@endif

<!-- Статистические карточки -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            {{ __('messages.total_users') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['users'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            {{ __('messages.total_institutions') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['institutions'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-university fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            {{ __('messages.total_programs') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['programs'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            {{ __('messages.total_courses') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['courses'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Быстрые действия -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>{{ __('messages.quick_actions') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>{{ __('messages.add_user') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.institutions.create') }}" class="btn btn-success w-100">
                            <i class="fas fa-university me-2"></i>{{ __('messages.add_institution') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.programs.create') }}" class="btn btn-info w-100">
                            <i class="fas fa-book me-2"></i>{{ __('messages.add_program') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.courses.create') }}" class="btn btn-warning w-100">
                            <i class="fas fa-chalkboard-teacher me-2"></i>{{ __('messages.add_course') }}
                        </a>
                    </div>
                </div>
                @if(auth()->user()->hasPermission('sync_moodle'))
                <div class="row mt-3">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.moodle-sync.index') }}" class="btn btn-primary w-100">
                            <i class="fas fa-sync-alt me-2"></i>{{ __('messages.moodle_sync') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Последние действия -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>{{ __('messages.recent_users') }}
                </h5>
            </div>
            <div class="card-body">
                @if(isset($recentUsers) && $recentUsers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.user') }}</th>
                                    <th>{{ __('messages.email') }}</th>
                                    <th>{{ __('messages.role') }}</th>
                                    <th>{{ __('messages.registration_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentUsers as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-primary text-white rounded-circle">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-info me-1">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>{{ $user->created_at->format('d.m.Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Нет данных для отображения</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>{{ __('messages.role_statistics') }}
                </h5>
            </div>
            <div class="card-body">
                @if(isset($roleStats) && count($roleStats) > 0)
                    @foreach($roleStats as $role => $count)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>{{ $role }}</span>
                            <span class="badge bg-primary">{{ $count }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-pie fa-3x mb-3"></i>
                        <p>Нет данных для отображения</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
