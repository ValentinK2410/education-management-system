<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Административная панель') - EduManage</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --dark-bg: #0f172a;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        [data-theme="dark"] {
            --light-bg: #1e293b;
            --dark-bg: #0f172a;
            --text-color: #e2e8f0;
            --card-bg: #334155;
            --border-color: #475569;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color, #1e293b);
            transition: all 0.3s ease;
        }

        [data-theme="dark"] body {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-brand:hover {
            color: rgba(255,255,255,0.8);
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 1rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        /* Main Content */
        .main-content {
            width: 100%;
            min-height: 100vh;
            transition: all 0.3s ease;
            padding: 0;
        }

        .main-content.expanded {
            width: 100%;
        }

        /* Содержимое страницы - отступ от sidebar */
        .main-content main {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
        }

        .main-content.expanded main {
            margin-left: 80px;
        }

        /* Когда боковая панель скрыта на мобильных устройствах */
        @media (max-width: 768px) {
            .main-content {
                width: 100%;
            }

            .main-content.expanded {
                width: 100%;
            }

            .main-content main {
                margin-left: 0;
            }

            .main-content.expanded main {
                margin-left: 0;
            }
        }

        /* Header */
        .admin-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: between;
            padding: 0 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
        }

        .main-content.expanded .admin-header {
            margin-left: 80px;
        }

        @media (max-width: 768px) {
            .admin-header {
                margin-left: 0;
            }

            .main-content.expanded .admin-header {
                margin-left: 0;
            }
        }

        [data-theme="dark"] .admin-header {
            background: var(--card-bg);
            border-bottom-color: var(--border-color);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-left .d-flex.flex-column {
            gap: 0.25rem;
        }

        .header-left small {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }

        [data-theme="dark"] .sidebar-toggle:hover {
            background-color: var(--hover-bg);
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .theme-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--secondary-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }

        /* Language Switcher */
        .language-switcher .dropdown-toggle {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-color);
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .language-switcher .dropdown-toggle:hover {
            background-color: var(--hover-bg);
            border-color: var(--primary-color);
        }

        .language-switcher .dropdown-menu {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .language-switcher .dropdown-item {
            color: var(--text-color);
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .language-switcher .dropdown-item:hover {
            background-color: var(--hover-bg);
        }

        .language-switcher .dropdown-item.active {
            background-color: var(--primary-color);
            color: white;
        }

        [data-theme="dark"] .language-switcher .dropdown-toggle {
            border-color: var(--border-color);
        }

        [data-theme="dark"] .language-switcher .dropdown-menu {
            background: var(--card-bg);
            border-color: var(--border-color);
        }

        .user-menu {
            position: relative;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        /* User Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            left: auto;
            min-width: 200px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }

        [data-theme="dark"] .dropdown-menu {
            background: var(--card-bg);
            border-color: var(--border-color);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }

        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }

        [data-theme="dark"] .dropdown-item:hover {
            background-color: var(--hover-bg);
        }

        .dropdown-divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 0.5rem 0;
        }

        [data-theme="dark"] .dropdown-divider {
            background-color: var(--border-color);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            background: white;
        }

        [data-theme="dark"] .card {
            background: var(--card-bg);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        [data-theme="dark"] .card-header {
            border-bottom-color: var(--border-color);
        }

        /* Buttons */
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        /* Tables */
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table thead th {
            background-color: var(--light-bg);
            border: none;
            font-weight: 600;
            color: var(--secondary-color);
        }

        [data-theme="dark"] .table thead th {
            background-color: var(--card-bg);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            /* Адаптивное позиционирование dropdown меню */
            .dropdown-menu {
                right: 0;
                left: auto;
                min-width: 180px;
                transform: translateX(50%);
                margin-right: 20px;
            }

            /* Если меню все еще выходит за экран, позиционируем его справа */
            @media (max-width: 400px) {
                .dropdown-menu {
                    right: 0;
                    left: auto;
                    transform: none;
                    margin-left: 0;
                    margin-right: 10px;
                }
            }
        }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Компактная пагинация для админ-панели */
        .pagination-wrapper .pagination,
        .pagination {
            margin-bottom: 0 !important;
            font-size: 0.875rem !important;
        }

        .pagination-wrapper .pagination .page-link,
        .pagination .page-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.4 !important;
            min-width: 32px !important;
            height: 32px !important;
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .pagination-wrapper .pagination .page-item,
        .pagination .page-item {
            margin: 0 1px !important;
        }

        .pagination-wrapper .pagination .page-item:first-child .page-link,
        .pagination .page-item:first-child .page-link {
            border-top-left-radius: 0.375rem !important;
            border-bottom-left-radius: 0.375rem !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-item:last-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-top-right-radius: 0.375rem !important;
            border-bottom-right-radius: 0.375rem !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-item.disabled .page-link,
        .pagination .page-item.disabled .page-link {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-item.active .page-link,
        .pagination .page-item.active .page-link {
            z-index: 3 !important;
            color: #fff !important;
            background-color: #6366f1 !important;
            border-color: #6366f1 !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-link i,
        .pagination .page-link i {
            font-size: 0.75rem !important;
        }

        .pagination-wrapper .pagination .page-link span,
        .pagination .page-link span {
            font-size: 0.875rem !important;
        }
    </style>

    @stack('styles')
    
    <!-- Компактная пагинация - переопределение после Bootstrap -->
    <style>
        /* Компактная пагинация для админ-панели - переопределение Bootstrap */
        .pagination-wrapper .pagination,
        .pagination {
            margin-bottom: 0 !important;
            font-size: 0.875rem !important;
        }

        .pagination-wrapper .pagination .page-link,
        .pagination .page-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.4 !important;
            min-width: 32px !important;
            height: 32px !important;
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .pagination-wrapper .pagination .page-item,
        .pagination .page-item {
            margin: 0 1px !important;
        }

        .pagination-wrapper .pagination .page-item:first-child .page-link,
        .pagination .page-item:first-child .page-link {
            border-top-left-radius: 0.375rem !important;
            border-bottom-left-radius: 0.375rem !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-item:last-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-top-right-radius: 0.375rem !important;
            border-bottom-right-radius: 0.375rem !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-item.disabled .page-link,
        .pagination .page-item.disabled .page-link {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-item.active .page-link,
        .pagination .page-item.active .page-link {
            z-index: 3 !important;
            color: #fff !important;
            background-color: #6366f1 !important;
            border-color: #6366f1 !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pagination-wrapper .pagination .page-link i,
        .pagination .page-link i {
            font-size: 0.75rem !important;
        }

        .pagination-wrapper .pagination .page-link span,
        .pagination .page-link span {
            font-size: 0.875rem !important;
        }

        /* Стили для Laravel Tailwind пагинации */
        .pagination-wrapper nav[role="navigation"] a,
        .pagination-wrapper nav[role="navigation"] span[aria-disabled="true"] span,
        .pagination-wrapper nav[role="navigation"] span[aria-current="page"] span {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.4 !important;
            min-width: 32px !important;
            height: 32px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .pagination-wrapper nav[role="navigation"] svg {
            width: 1rem !important;
            height: 1rem !important;
        }

        .pagination-wrapper nav[role="navigation"] .text-sm {
            font-size: 0.875rem !important;
        }

        /* Переопределение Tailwind padding классов в пагинации */
        .pagination-wrapper nav[role="navigation"] .px-4 {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .pagination-wrapper nav[role="navigation"] .py-2 {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
        }

        .pagination-wrapper nav[role="navigation"] .px-2 {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }

        /* Уменьшаем размер иконок в пагинации */
        .pagination-wrapper nav[role="navigation"] .w-5 {
            width: 1rem !important;
        }

        .pagination-wrapper nav[role="navigation"] .h-5 {
            height: 1rem !important;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <i class="fas fa-graduation-cap me-2"></i>
                <span class="brand-text">EduManage</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>{{ __('messages.dashboard') }}</span>
                </a>
            </div>

            @php
                // Проверяем, является ли пользователь реальным админом (даже если переключен на роль)
                $isRealAdminForSidebar = false;
                if (session('original_user_id')) {
                    $originalUser = \App\Models\User::find(session('original_user_id'));
                    $isRealAdminForSidebar = $originalUser && $originalUser->hasRole('admin');
                } elseif (session('role_switched')) {
                    $originalRoles = session('original_roles', []);
                    if (!empty($originalRoles)) {
                        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
                        $isRealAdminForSidebar = $adminRole && in_array($adminRole->id, $originalRoles);
                    }
                } else {
                    $isRealAdminForSidebar = auth()->user()->hasRole('admin');
                }

                // Показываем раздел "Пользователи" если:
                // 1. Пользователь имеет разрешение view_sidebar_users И не имеет hide_user_links
                // 2. ИЛИ пользователь является реальным админом (даже при переключении на роль)
                $showUsersLink = ($isRealAdminForSidebar) ||
                                 (auth()->user()->hasPermission('view_sidebar_users') && !auth()->user()->hasPermission('hide_user_links'));
            @endphp

            @if($showUsersLink)
            <div class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>{{ __('messages.users') }}</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_sidebar_institutions'))
            <div class="nav-item">
                <a href="{{ route('admin.institutions.index') }}" class="nav-link {{ request()->routeIs('admin.institutions.*') ? 'active' : '' }}">
                    <i class="fas fa-university"></i>
                    <span>{{ __('messages.institutions') }}</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_sidebar_programs'))
            <div class="nav-item">
                <a href="{{ route('admin.programs.index') }}" class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>{{ __('messages.programs') }}</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_sidebar_courses'))
            <div class="nav-item">
                <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>{{ __('messages.courses') }}</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('instructor'))
            <div class="nav-item">
                <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Аналитика курсов</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_sidebar_roles'))
            <div class="nav-item">
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i>
                    <span>Роли</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_sidebar_reviews'))
            <div class="nav-item">
                <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i>
                    <span>Отзывы</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_sidebar_certificates'))
            <div class="nav-item">
                <a href="{{ route('admin.certificate-templates.index') }}" class="nav-link {{ request()->routeIs('admin.certificate-templates.*') ? 'active' : '' }}">
                    <i class="fas fa-certificate"></i>
                    <span>Шаблоны сертификатов</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_sidebar_archive'))
            <div class="nav-item">
                <a href="{{ route('admin.user-archive.index') }}" class="nav-link {{ request()->routeIs('admin.user-archive.*') ? 'active' : '' }}">
                    <i class="fas fa-archive"></i>
                    <span>Архив пользователей</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('sync_moodle'))
            <div class="nav-item">
                <a href="{{ route('admin.moodle-sync.index') }}" class="nav-link {{ request()->routeIs('admin.moodle-sync.*') ? 'active' : '' }}">
                    <i class="fas fa-sync-alt"></i>
                    <span>Синхронизация Moodle</span>
                </a>
            </div>
            @endif

            <div class="nav-item mt-4">
                <a href="{{ route('home') }}" class="nav-link">
                    <i class="fas fa-external-link-alt"></i>
                    <span>{{ __('messages.home') }}</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex flex-column">
                    <h4 class="mb-0">@yield('page-title', __('messages.dashboard'))</h4>
                    <small class="text-muted" style="font-size: 0.75rem; line-height: 1.2;">
                        <i class="fas fa-user me-1"></i>{{ auth()->user()->name }}
                        @php
                            $currentRoles = auth()->user()->roles;
                            $roleNames = $currentRoles->pluck('name')->toArray();
                            if (session('role_switched')) {
                                $switchedRole = \App\Models\Role::find(session('switched_role_id'));
                                if ($switchedRole) {
                                    $roleNames = [$switchedRole->name];
                                }
                            }
                        @endphp
                        @if(!empty($roleNames))
                            <span class="ms-2">
                                <i class="fas fa-user-tag me-1"></i>{{ implode(', ', $roleNames) }}
                            </span>
                        @endif
                    </small>
                </div>
            </div>

            <div class="header-right">
                <!-- Language Switcher -->
                <div class="language-switcher">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i>
                            <span id="currentLanguage">{{ app()->getLocale() === 'en' ? 'EN' : 'RU' }}</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'ru' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['lang' => 'ru']) }}">
                                    <i class="fas fa-flag me-2"></i>Русский
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">
                                    <i class="fas fa-flag me-2"></i>English
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                @php
                    // Проверяем, является ли пользователь реальным админом (не переключенным)
                    $isRealAdmin = false;
                    $showSwitchMenu = false;

                    if (session('original_user_id')) {
                        // Переключены на пользователя - проверяем оригинального
                        $originalUser = \App\Models\User::find(session('original_user_id'));
                        $isRealAdmin = $originalUser && $originalUser->hasRole('admin');
                        $showSwitchMenu = $isRealAdmin;
                    } elseif (session('role_switched')) {
                        // Переключены на роль - проверяем оригинальные роли
                        $originalRoles = session('original_roles', []);
                        if (!empty($originalRoles)) {
                            $adminRole = \App\Models\Role::where('slug', 'admin')->first();
                            $isRealAdmin = $adminRole && in_array($adminRole->id, $originalRoles);
                            $showSwitchMenu = $isRealAdmin;
                        }
                    } elseif (!session('is_switched')) {
                        // Не переключены - проверяем текущего пользователя
                        $isRealAdmin = auth()->user()->hasRole('admin');
                        $showSwitchMenu = $isRealAdmin;
                    }
                @endphp

                {{-- Кнопка возврата при переключении на роль (всегда видна в header) --}}
                @if(session('role_switched'))
                <div class="me-3">
                    <a href="{{ route('admin.role-switch.back') }}" class="btn btn-sm btn-warning" title="Вернуться к ролям админа">
                        <i class="fas fa-undo me-1"></i>Вернуться к ролям админа
                    </a>
                </div>
                @endif

                @if($showSwitchMenu || session('role_switched') || session('is_switched'))
                <!-- Переключение пользователей/ролей (только для реальных админов) -->
                <div class="user-switch-menu me-3">
                    @if(session('is_switched'))
                        <div class="alert alert-warning alert-dismissible fade show mb-0 py-1 px-2" role="alert" style="font-size: 0.75rem;">
                            <i class="fas fa-user-secret me-1"></i>
                            Вы работаете под пользователем: <strong>{{ auth()->user()->name }}</strong>
                            <a href="{{ route('admin.user-switch.back') }}" class="btn btn-sm btn-outline-danger ms-2">
                                <i class="fas fa-undo me-1"></i>Вернуться
                            </a>
                        </div>
                    @elseif(session('role_switched'))
                        <div class="alert alert-info alert-dismissible fade show mb-0 py-1 px-2" role="alert" style="font-size: 0.75rem;">
                            <i class="fas fa-user-tag me-1"></i>
                            Вы переключены на роль: <strong>{{ \App\Models\Role::find(session('switched_role_id'))->name ?? 'Неизвестная роль' }}</strong>
                        </div>
                    @elseif($isRealAdmin)
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    id="userSwitchDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-friends me-1"></i>Переключиться
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userSwitchDropdown" style="min-width: 300px;">
                                <li><h6 class="dropdown-header">Переключиться на пользователя</h6></li>
                                <li>
                                    <div class="px-3 py-2">
                                        <input type="text" id="userSearchInput" class="form-control form-control-sm"
                                               placeholder="Поиск пользователя..." autocomplete="off">
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div id="userSwitchList" style="max-height: 300px; overflow-y: auto;">
                                        <div class="text-center py-3 text-muted">
                                            <small>Начните вводить имя или email</small>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Переключиться на роль</h6></li>
                                @foreach(\App\Models\Role::all() as $role)
                                    @php
                                        // Проверяем текущую роль с учетом переключения
                                        $hasCurrentRole = false;
                                        if (session('role_switched') && session('switched_role_slug') === $role->slug) {
                                            $hasCurrentRole = true;
                                        } elseif (!session('role_switched') && auth()->user()->hasRole($role->slug)) {
                                            $hasCurrentRole = true;
                                        }
                                    @endphp
                                    @if(!$hasCurrentRole)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.role-switch.switch', $role) }}">
                                            <i class="fas fa-user-tag me-2"></i>{{ $role->name }}
                                        </a>
                                    </li>
                                    @endif
                                @endforeach
                                @if(session('role_switched'))
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('admin.role-switch.back') }}">
                                        <i class="fas fa-undo me-2"></i>Вернуться к своим ролям
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </div>
                @endif

                <button class="theme-toggle" id="themeToggle" title="{{ __('messages.light_theme') }}">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>

                <div class="user-menu">
                    <div class="user-avatar" id="userMenuToggle">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown-menu" id="userDropdown" style="display: none;">
                        <div class="dropdown-item">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small class="text-muted d-block">{{ auth()->user()->email }}</small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.profile.show') }}">
                            <i class="fas fa-user me-2"></i>{{ __('messages.profile') }}
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                            <i class="fas fa-user-edit me-2"></i>{{ __('messages.edit_profile') }}
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2"></i>{{ __('messages.settings') }}
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>{{ __('messages.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show fade-in-up" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show fade-in-up" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Admin JS -->
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Save sidebar state
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // Load sidebar state
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Load user theme preference from server
        loadUserThemePreference();

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);

            // Save user preference to server
            saveThemePreference(newTheme);
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }
        }

        // Save theme preference to server
        function saveThemePreference(theme) {
            fetch('/admin/save-theme-preference', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ theme: theme })
            }).catch(error => console.log('Theme preference not saved:', error));
        }

        // Load user theme preference from server
        function loadUserThemePreference() {
            fetch('/admin/user-settings')
                .then(response => {
                    // Проверяем, что ответ успешный и является JSON
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Response is not JSON');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.theme) {
                        document.documentElement.setAttribute('data-theme', data.theme);
                        localStorage.setItem('theme', data.theme);
                        updateThemeIcon(data.theme);
                    }

                    if (data && data.sidebar_collapsed) {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('expanded');
                        localStorage.setItem('sidebarCollapsed', 'true');
                    }
                })
                .catch(error => {
                    // Тихая обработка ошибки - используем настройки по умолчанию
                    console.log('User settings not loaded, using defaults:', error.message);
                });
        }

        // User Menu Toggle
        const userMenuToggle = document.getElementById('userMenuToggle');
        const userDropdown = document.getElementById('userDropdown');

        userMenuToggle.addEventListener('click', (e) => {
            e.stopPropagation();

            if (userDropdown.style.display === 'none' || userDropdown.style.display === '') {
                // Показываем меню с анимацией
                userDropdown.style.display = 'block';
                userDropdown.style.opacity = '0';
                userDropdown.style.transform = 'translateY(-10px)';

                // Плавная анимация появления
                setTimeout(() => {
                    userDropdown.style.transition = 'all 0.2s ease';
                    userDropdown.style.opacity = '1';
                    userDropdown.style.transform = 'translateY(0)';
                }, 10);
            } else {
                // Скрываем меню с анимацией
                userDropdown.style.transition = 'all 0.2s ease';
                userDropdown.style.opacity = '0';
                userDropdown.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    userDropdown.style.display = 'none';
                }, 200);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.style.transition = 'all 0.2s ease';
                userDropdown.style.opacity = '0';
                userDropdown.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    userDropdown.style.display = 'none';
                }, 200);
            }
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }

        // Поиск пользователей для переключения
        const userSearchInput = document.getElementById('userSearchInput');
        const userSwitchList = document.getElementById('userSwitchList');
        let searchTimeout;

        if (userSearchInput && userSwitchList) {
            userSearchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.trim();

                clearTimeout(searchTimeout);

                if (searchTerm.length < 2) {
                    userSwitchList.innerHTML = '<div class="text-center py-3 text-muted"><small>Введите минимум 2 символа</small></div>';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`{{ route('admin.user-switch.users') }}?search=${encodeURIComponent(searchTerm)}`)
                        .then(response => response.json())
                        .then(users => {
                            if (users.length === 0) {
                                userSwitchList.innerHTML = '<div class="text-center py-3 text-muted"><small>Пользователи не найдены</small></div>';
                                return;
                            }

                            let html = '';
                            users.forEach(user => {
                                const roles = user.roles ? user.roles.map(r => r.name).join(', ') : '';
                                html += `
                                    <a class="dropdown-item" href="{{ url('/admin/user-switch/switch') }}/${user.id}">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title bg-primary text-white rounded-circle" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                    ${user.name.charAt(0)}
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">${user.name}</div>
                                                <small class="text-muted">${user.email}</small>
                                                ${roles ? `<br><small class="text-muted">${roles}</small>` : ''}
                                            </div>
                                        </div>
                                    </a>
                                `;
                            });
                            userSwitchList.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Ошибка поиска пользователей:', error);
                            userSwitchList.innerHTML = '<div class="text-center py-3 text-danger"><small>Ошибка загрузки</small></div>';
                        });
                }, 300);
            });
        }

        // Fade-in animation disabled to remove floating effect
        // document.addEventListener('DOMContentLoaded', () => {
        //     const content = document.querySelector('main');
        //     content.classList.add('fade-in-up');
        // });
    </script>

    @stack('scripts')
</body>
</html>
