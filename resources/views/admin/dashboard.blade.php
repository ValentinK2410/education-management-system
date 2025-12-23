@extends('layouts.admin')

@section('title', 'Панель управления')
@section('page-title', 'Панель управления')

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
</style>
@endsection
