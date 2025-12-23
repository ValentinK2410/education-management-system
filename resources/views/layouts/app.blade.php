<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Система управления образованием')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        /* Sticky Navigation */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: white !important;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6fd8 0%, #6a4190 100%);
        }
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 0;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
        }

        /* Active Navigation Link */
        .nav-link {
            position: relative;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            color: #475569 !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #667eea !important;
        }

        .nav-link.active {
            color: #667eea !important;
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1rem;
            right: 1rem;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 3px 3px 0 0;
        }

        /* Breadcrumbs */
        .breadcrumb-container {
            background: linear-gradient(to right, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .breadcrumb-container.scrolled {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
            font-size: 0.9rem;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '\f054';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #94a3b8;
            padding: 0 0.75rem;
            font-size: 0.75rem;
        }

        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb-item a:hover {
            color: #667eea;
        }

        .breadcrumb-item.active {
            color: #1e293b;
            font-weight: 600;
        }

        .breadcrumb-item.active a {
            color: #1e293b;
            pointer-events: none;
        }

        .breadcrumb-icon {
            font-size: 0.875rem;
            opacity: 0.7;
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-graduation-cap me-2"></i>
                EduManage
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Главная
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('institutions.*') ? 'active' : '' }}" href="{{ route('institutions.index') }}">
                            <i class="fas fa-university me-1"></i>Учебные заведения
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}" href="{{ route('programs.index') }}">
                            <i class="fas fa-book me-1"></i>Программы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Курсы
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <i class="fas fa-cog me-1"></i>Админ панель
                                </a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                @if(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-home me-2"></i>Панель управления</a></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-2"></i>Мой профиль</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Выйти</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Войти</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="{{ route('register') }}">Регистрация</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumbs -->
    @php
        $routeName = request()->route()->getName();
        $breadcrumbs = [];

        // Главная страница
        if ($routeName == 'home') {
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home']
            ];
        }
        // Учебные заведения
        elseif ($routeName == 'institutions.index') {
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Учебные заведения', 'url' => null, 'icon' => 'fa-university']
            ];
        }
        elseif ($routeName == 'institutions.show') {
            $institution = request()->route('institution');
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Учебные заведения', 'url' => route('institutions.index'), 'icon' => 'fa-university'],
                ['title' => $institution->name ?? 'Учебное заведение', 'url' => null, 'icon' => 'fa-building']
            ];
        }
        // Программы
        elseif ($routeName == 'programs.index') {
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Программы', 'url' => null, 'icon' => 'fa-book']
            ];
        }
        elseif ($routeName == 'programs.show') {
            $program = request()->route('program');
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Программы', 'url' => route('programs.index'), 'icon' => 'fa-book'],
                ['title' => $program->name ?? 'Программа', 'url' => null, 'icon' => 'fa-graduation-cap']
            ];
        }
        // Курсы
        elseif ($routeName == 'courses.index') {
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Курсы', 'url' => null, 'icon' => 'fa-chalkboard-teacher']
            ];
        }
        elseif ($routeName == 'courses.show') {
            $course = request()->route('course');
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Курсы', 'url' => route('courses.index'), 'icon' => 'fa-chalkboard-teacher'],
                ['title' => $course->name ?? 'Курс', 'url' => null, 'icon' => 'fa-book']
            ];
        }
        // Преподаватели
        elseif ($routeName == 'instructors.show') {
            $instructor = request()->route('instructor');
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Преподаватель', 'url' => null, 'icon' => 'fa-user-tie']
            ];
            if ($instructor) {
                $breadcrumbs[1]['title'] = $instructor->name ?? 'Преподаватель';
            }
        }
        // Админ панель
        elseif (str_starts_with($routeName, 'admin.')) {
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home'],
                ['title' => 'Админ панель', 'url' => route('admin.users.index'), 'icon' => 'fa-cog']
            ];
        }
        // По умолчанию
        else {
            $breadcrumbs = [
                ['title' => 'Главная', 'url' => route('home'), 'icon' => 'fa-home']
            ];
        }
    @endphp

    @if(count($breadcrumbs) > 1 || (count($breadcrumbs) == 1 && $routeName != 'home'))
        <div class="breadcrumb-container">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @foreach($breadcrumbs as $index => $breadcrumb)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                @if($breadcrumb['url'] && !$loop->last)
                                    <a href="{{ $breadcrumb['url'] }}">
                                        <i class="fas {{ $breadcrumb['icon'] }} breadcrumb-icon"></i>
                                        {{ $breadcrumb['title'] }}
                                    </a>
                                @else
                                    <span>
                                        <i class="fas {{ $breadcrumb['icon'] }} breadcrumb-icon"></i>
                                        {{ $breadcrumb['title'] }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-graduation-cap me-2"></i>EduManage</h5>
                    <p>Система управления образовательными процессами</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; {{ date('Y') }} EduManage. Все права защищены.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.js"></script>
    <script>
        // Sticky navbar with scroll effect
        (function() {
            const navbar = document.querySelector('.navbar');
            const breadcrumbContainer = document.querySelector('.breadcrumb-container');

            if (!navbar) return;

            function updateNavbar() {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }

            // Update navbar on scroll
            window.addEventListener('scroll', updateNavbar);

            // Update navbar on load (in case page is loaded with scroll position)
            updateNavbar();

            // Update breadcrumb position and scroll effect dynamically
            if (breadcrumbContainer) {
                function updateBreadcrumbPosition() {
                    const navbarHeight = navbar.offsetHeight;
                    breadcrumbContainer.style.top = navbarHeight + 'px';

                    // Add scroll effect to breadcrumbs
                    if (window.scrollY > navbarHeight + 10) {
                        breadcrumbContainer.classList.add('scrolled');
                    } else {
                        breadcrumbContainer.classList.remove('scrolled');
                    }
                }

                // Update on load and resize
                updateBreadcrumbPosition();
                window.addEventListener('resize', updateBreadcrumbPosition);
                window.addEventListener('scroll', updateBreadcrumbPosition);

                // Update when navbar collapses/expands (mobile menu)
                const navbarToggler = document.querySelector('.navbar-toggler');
                if (navbarToggler) {
                    navbarToggler.addEventListener('click', function() {
                        setTimeout(updateBreadcrumbPosition, 350); // Wait for collapse animation
                    });
                }
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
