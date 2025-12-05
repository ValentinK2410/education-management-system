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
    </style>

    @stack('styles')
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

            <div class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>{{ __('messages.users') }}</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.institutions.index') }}" class="nav-link {{ request()->routeIs('admin.institutions.*') ? 'active' : '' }}">
                    <i class="fas fa-university"></i>
                    <span>{{ __('messages.institutions') }}</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.programs.index') }}" class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>{{ __('messages.programs') }}</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>{{ __('messages.courses') }}</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i>
                    <span>Роли</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i>
                    <span>Отзывы</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.certificate-templates.index') }}" class="nav-link {{ request()->routeIs('admin.certificate-templates.*') ? 'active' : '' }}">
                    <i class="fas fa-certificate"></i>
                    <span>Шаблоны сертификатов</span>
                </a>
            </div>

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
                <h4 class="mb-0">@yield('page-title', __('messages.dashboard'))</h4>
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
                        <a class="dropdown-item" href="{{ route('admin.test-profile') }}">
                            <i class="fas fa-user me-2"></i>{{ __('messages.profile') }}
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.test-profile-edit') }}">
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

        // Add fade-in animation to content
        document.addEventListener('DOMContentLoaded', () => {
            const content = document.querySelector('main');
            content.classList.add('fade-in-up');
        });
    </script>

    @stack('scripts')
</body>
</html>
