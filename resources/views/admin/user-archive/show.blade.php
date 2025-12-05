@extends('layouts.admin')

@section('title', 'История обучения - ' . $user->name)
@section('page-title', 'История обучения: ' . $user->name)

@section('content')
<div class="container-fluid fade-in-up">
    <!-- Информация о пользователе -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="user-avatar mx-auto" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h4 class="mb-2">{{ $user->name }}</h4>
                            <p class="text-muted mb-1"><i class="fas fa-envelope me-2"></i>{{ $user->email }}</p>
                            @if($user->phone)
                                <p class="text-muted mb-1"><i class="fas fa-phone me-2"></i>{{ $user->phone }}</p>
                            @endif
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-2"></i>Зарегистрирован: {{ $user->created_at->format('d.m.Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $stats['total_courses'] }}</h3>
                    <p class="text-muted mb-0">Всего курсов</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $stats['completed_courses'] }}</h3>
                    <p class="text-muted mb-0">Завершено курсов</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $stats['total_programs'] }}</h3>
                    <p class="text-muted mb-0">Всего программ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ $stats['total_certificates'] }}</h3>
                    <p class="text-muted mb-0">Сертификатов</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Вкладки -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="historyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">
                                <i class="fas fa-book me-2"></i>Курсы
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="programs-tab" data-bs-toggle="tab" data-bs-target="#programs" type="button" role="tab">
                                <i class="fas fa-graduation-cap me-2"></i>Программы
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                                <i class="fas fa-history me-2"></i>История изменений
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                                <i class="fas fa-money-bill-wave me-2"></i>Платежи
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="certificates-tab" data-bs-toggle="tab" data-bs-target="#certificates" type="button" role="tab">
                                <i class="fas fa-certificate me-2"></i>Сертификаты
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="historyTabsContent">
                        <!-- Вкладка Курсы -->
                        <div class="tab-pane fade show active" id="courses" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Курс</th>
                                            <th>Программа</th>
                                            <th>Статус</th>
                                            <th>Прогресс</th>
                                            <th>Статус оплаты</th>
                                            <th>Дата зачисления</th>
                                            <th>Дата завершения</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($courseEnrollments as $course)
                                            @php
                                                $pivot = $course->pivot;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $course->name }}</strong>
                                                    @if($course->code)
                                                        <br><small class="text-muted">{{ $course->code }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($course->program)
                                                        <span class="badge bg-info">{{ $course->program->name }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($pivot->status === 'completed')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Завершен
                                                        </span>
                                                    @elseif($pivot->status === 'active')
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-play me-1"></i>Активен
                                                        </span>
                                                    @elseif($pivot->status === 'cancelled')
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>Отчислен
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-clock me-1"></i>Зачислен
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $pivot->progress }}%">
                                                            {{ $pivot->progress }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($course->is_paid)
                                                        @if($pivot->payment_status === 'paid')
                                                            <span class="badge bg-success">Оплачено</span>
                                                        @elseif($pivot->payment_status === 'pending')
                                                            <span class="badge bg-warning">Ожидает оплаты</span>
                                                        @elseif($pivot->payment_status === 'failed')
                                                            <span class="badge bg-danger">Ошибка оплаты</span>
                                                        @else
                                                            <span class="badge bg-secondary">Не требуется</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-info">Бесплатно</span>
                                                    @endif
                                                </td>
                                                <td>{{ $pivot->enrolled_at ? \Carbon\Carbon::parse($pivot->enrolled_at)->format('d.m.Y') : '—' }}</td>
                                                <td>{{ $pivot->completed_at ? \Carbon\Carbon::parse($pivot->completed_at)->format('d.m.Y') : '—' }}</td>
                                                <td>
                                                    @if($pivot->status === 'completed')
                                                        @php
                                                            $certificate = $certificates->where('course_id', $course->id)->first();
                                                        @endphp
                                                        @if($certificate)
                                                            <a href="{{ route('admin.user-archive.download-certificate', [$user, $certificate]) }}" 
                                                               class="btn btn-sm btn-success" title="Скачать сертификат">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @else
                                                            <form action="{{ route('certificates.generate.course', $course) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-primary" title="Сгенерировать сертификат">
                                                                    <i class="fas fa-certificate"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-book fa-3x mb-3"></i>
                                                        <p>Пользователь не записан ни на один курс</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Вкладка Программы -->
                        <div class="tab-pane fade" id="programs" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Программа</th>
                                            <th>Учебное заведение</th>
                                            <th>Статус</th>
                                            <th>Статус оплаты</th>
                                            <th>Дата зачисления</th>
                                            <th>Дата завершения</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($programEnrollments as $program)
                                            @php
                                                $pivot = $program->pivot;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $program->name }}</strong>
                                                    @if($program->code)
                                                        <br><small class="text-muted">{{ $program->code }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($program->institution)
                                                        <span class="badge bg-info">{{ $program->institution->name }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($pivot->status === 'completed')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Завершена
                                                        </span>
                                                    @elseif($pivot->status === 'active')
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-play me-1"></i>Активна
                                                        </span>
                                                    @elseif($pivot->status === 'cancelled')
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>Отчислен
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-clock me-1"></i>Зачислен
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($program->is_paid)
                                                        @if($pivot->payment_status === 'paid')
                                                            <span class="badge bg-success">Оплачено</span>
                                                        @elseif($pivot->payment_status === 'pending')
                                                            <span class="badge bg-warning">Ожидает оплаты</span>
                                                        @elseif($pivot->payment_status === 'failed')
                                                            <span class="badge bg-danger">Ошибка оплаты</span>
                                                        @else
                                                            <span class="badge bg-secondary">Не требуется</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-info">Бесплатно</span>
                                                    @endif
                                                </td>
                                                <td>{{ $pivot->enrolled_at ? \Carbon\Carbon::parse($pivot->enrolled_at)->format('d.m.Y') : '—' }}</td>
                                                <td>{{ $pivot->completed_at ? \Carbon\Carbon::parse($pivot->completed_at)->format('d.m.Y') : '—' }}</td>
                                                <td>
                                                    @if($pivot->status === 'completed')
                                                        @php
                                                            $certificate = $certificates->where('program_id', $program->id)->first();
                                                        @endphp
                                                        @if($certificate)
                                                            <a href="{{ route('admin.user-archive.download-certificate', [$user, $certificate]) }}" 
                                                               class="btn btn-sm btn-success" title="Скачать сертификат">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @else
                                                            <form action="{{ route('certificates.generate.program', $program) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-primary" title="Сгенерировать сертификат">
                                                                    <i class="fas fa-certificate"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                                                        <p>Пользователь не записан ни на одну программу</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Вкладка История изменений -->
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Тип</th>
                                            <th>Название</th>
                                            <th>Действие</th>
                                            <th>Старый статус</th>
                                            <th>Новый статус</th>
                                            <th>Кто изменил</th>
                                            <th>Примечания</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($enrollmentHistory as $history)
                                            <tr>
                                                <td>{{ $history->changed_at->format('d.m.Y H:i') }}</td>
                                                <td>
                                                    @if($history->entity_type === 'course')
                                                        <span class="badge bg-info">Курс</span>
                                                    @else
                                                        <span class="badge bg-primary">Программа</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($history->course)
                                                        {{ $history->course->name }}
                                                    @elseif($history->program)
                                                        {{ $history->program->name }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $history->action_label }}</span>
                                                </td>
                                                <td>
                                                    @if($history->old_status)
                                                        <span class="badge bg-light text-dark">{{ $history->old_status }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $history->status_label }}</span>
                                                </td>
                                                <td>
                                                    @if($history->changedBy)
                                                        {{ $history->changedBy->name }}
                                                    @else
                                                        <span class="text-muted">Система</span>
                                                    @endif
                                                </td>
                                                <td>{{ $history->notes ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-history fa-3x mb-3"></i>
                                                        <p>История изменений пуста</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Вкладка Платежи -->
                        <div class="tab-pane fade" id="payments" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Тип</th>
                                            <th>Название</th>
                                            <th>Сумма</th>
                                            <th>Статус</th>
                                            <th>Способ оплаты</th>
                                            <th>Транзакция</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                                <td>
                                                    @if($payment->entity_type === 'course')
                                                        <span class="badge bg-info">Курс</span>
                                                    @else
                                                        <span class="badge bg-primary">Программа</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($payment->course)
                                                        {{ $payment->course->name }}
                                                    @elseif($payment->program)
                                                        {{ $payment->program->name }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</strong>
                                                </td>
                                                <td>
                                                    @if($payment->status === 'paid')
                                                        <span class="badge bg-success">{{ $payment->status_label }}</span>
                                                    @elseif($payment->status === 'pending')
                                                        <span class="badge bg-warning">{{ $payment->status_label }}</span>
                                                    @elseif($payment->status === 'failed')
                                                        <span class="badge bg-danger">{{ $payment->status_label }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $payment->status_label }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $payment->payment_method ?? '—' }}</td>
                                                <td>
                                                    @if($payment->transaction_id)
                                                        <code>{{ $payment->transaction_id }}</code>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                                                        <p>История платежей пуста</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($payments->count() > 0)
                                <div class="mt-3">
                                    <strong>Всего оплачено: {{ number_format($stats['paid_amount'], 2) }} RUB</strong>
                                </div>
                            @endif
                        </div>

                        <!-- Вкладка Сертификаты -->
                        <div class="tab-pane fade" id="certificates" role="tabpanel">
                            <div class="row">
                                @forelse($certificates as $certificate)
                                    <div class="col-md-4 mb-4">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-certificate fa-3x text-warning mb-3"></i>
                                                <h5 class="card-title">
                                                    @if($certificate->course)
                                                        {{ $certificate->course->name }}
                                                    @elseif($certificate->program)
                                                        {{ $certificate->program->name }}
                                                    @endif
                                                </h5>
                                                <p class="text-muted mb-2">
                                                    <small>Номер: {{ $certificate->certificate_number }}</small>
                                                </p>
                                                <p class="text-muted mb-3">
                                                    <small>Выдан: {{ $certificate->issued_at->format('d.m.Y') }}</small>
                                                </p>
                                                <a href="{{ route('admin.user-archive.download-certificate', [$user, $certificate]) }}" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-download me-1"></i>Скачать
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-certificate fa-3x mb-3"></i>
                                                <p>Сертификаты отсутствуют</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Кнопка назад -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('admin.user-archive.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Назад к списку пользователей
            </a>
        </div>
    </div>
</div>
@endsection
