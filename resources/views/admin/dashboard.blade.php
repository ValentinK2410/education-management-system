@extends('layouts.admin')

@section('title', 'Панель управления')
@section('page-title', 'Панель управления')

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.avatar-sm {
    width: 30px;
    height: 30px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
}

/* Темная тема для дашборда */
[data-theme="dark"] .container-fluid .card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .card-header {
    background: var(--card-bg) !important;
    border-bottom-color: var(--border-color) !important;
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .card-body {
    background: var(--card-bg) !important;
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid h5,
[data-theme="dark"] .container-fluid .card-title {
    color: var(--text-color) !important;
}

/* Статистические карточки */
[data-theme="dark"] .container-fluid .card.border-left-primary,
[data-theme="dark"] .container-fluid .card.border-left-success,
[data-theme="dark"] .container-fluid .card.border-left-info,
[data-theme="dark"] .container-fluid .card.border-left-warning {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

[data-theme="dark"] .container-fluid .text-gray-800 {
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .text-primary {
    color: #a5b4fc !important;
}

[data-theme="dark"] .container-fluid .text-success {
    color: #6ee7b7 !important;
}

[data-theme="dark"] .container-fluid .text-info {
    color: #93c5fd !important;
}

[data-theme="dark"] .container-fluid .text-warning {
    color: #fbbf24 !important;
}

[data-theme="dark"] .container-fluid .text-gray-300 {
    color: #64748b !important;
    opacity: 0.6;
}

/* Таблица */
[data-theme="dark"] .container-fluid .table {
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .table thead th {
    background-color: var(--dark-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .table tbody td {
    border-color: var(--border-color) !important;
    background-color: transparent !important;
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .table-sm tbody tr {
    background-color: transparent !important;
}

/* Текст */
[data-theme="dark"] .container-fluid .text-muted {
    color: #94a3b8 !important;
    opacity: 0.8;
}

/* Бейджи */
[data-theme="dark"] .container-fluid .badge.bg-info {
    background-color: rgba(59, 130, 246, 0.8) !important;
    color: white !important;
}

[data-theme="dark"] .container-fluid .badge.bg-primary {
    background-color: var(--primary-color) !important;
    color: white !important;
}

/* Аватар */
[data-theme="dark"] .container-fluid .avatar-title.bg-primary {
    background-color: var(--primary-color) !important;
    color: white !important;
}

/* Алерты */
[data-theme="dark"] .container-fluid .alert-warning {
    background-color: rgba(245, 158, 11, 0.1) !important;
    border-color: rgba(245, 158, 11, 0.3) !important;
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .alert-info {
    background-color: rgba(59, 130, 246, 0.1) !important;
    border-color: rgba(59, 130, 246, 0.3) !important;
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .alert-warning strong,
[data-theme="dark"] .container-fluid .alert-info strong {
    color: var(--text-color) !important;
}

[data-theme="dark"] .container-fluid .btn-outline-danger {
    border-color: var(--danger-color) !important;
    color: var(--danger-color) !important;
}

[data-theme="dark"] .container-fluid .btn-outline-danger:hover {
    background-color: var(--danger-color) !important;
    border-color: var(--danger-color) !important;
    color: white !important;
}

[data-theme="dark"] .container-fluid .btn-outline-primary {
    border-color: var(--primary-color) !important;
    color: var(--primary-color) !important;
}

[data-theme="dark"] .container-fluid .btn-outline-primary:hover {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: white !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    @php
        $userRole = $userRole ?? 'student';
    @endphp

    @if($userRole === 'admin')
        {{-- DASHBOARD ДЛЯ АДМИНИСТРАТОРА --}}
        @include('admin.dashboard.admin')
    @elseif($userRole === 'instructor')
        {{-- DASHBOARD ДЛЯ ПРЕПОДАВАТЕЛЯ --}}
        @include('admin.dashboard.instructor')
    @else
        {{-- DASHBOARD ДЛЯ СТУДЕНТА --}}
        @include('admin.dashboard.student')
    @endif
</div>
@endsection
