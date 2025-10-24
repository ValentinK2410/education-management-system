@extends('layouts.app')

@section('title', 'EduManage - Система управления образовательными процессами')

@section('content')
<style>
    :root {
        --primary-blue: #1e40af;
        --light-blue: #dbeafe;
        --dark-blue: #1e3a8a;
        --accent-blue: #3b82f6;
        --text-dark: #1f2937;
        --text-gray: #6b7280;
        --white: #ffffff;
        --light-gray: #f9fafb;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
    }

    /* Top Banner */
    .top-banner {
        background: var(--light-blue);
        padding: 0.75rem 0;
        font-size: 0.9rem;
        color: var(--primary-blue);
    }

    .top-banner .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .banner-btn {
        background: var(--primary-blue);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .banner-btn:hover {
        background: var(--dark-blue);
        color: white;
    }

    /* Navigation */
    .navbar {
        background: var(--white);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1rem 0;
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-blue);
        text-decoration: none;
    }

    .logo-circle {
        width: 40px;
        height: 40px;
        background: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        margin-right: 0.75rem;
    }

    .nav-link {
        color: var(--text-dark);
        font-weight: 500;
        margin: 0 1rem;
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: var(--primary-blue);
    }

    .nav-icons {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .nav-icon {
        width: 40px;
        height: 40px;
        background: var(--light-gray);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-gray);
        transition: all 0.3s ease;
    }

    .nav-icon:hover {
        background: var(--primary-blue);
        color: white;
    }

    /* Hero Section */
    .hero {
        background: linear-gradient(135deg, var(--light-blue) 0%, #e0f2fe 100%);
        padding: 4rem 0;
        position: relative;
        overflow: hidden;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 50%;
        height: 100%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(59,130,246,0.1)" points="0,0 1000,1000 1000,0"/></svg>');
        background-size: cover;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero h1 {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary-blue);
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .hero p {
        font-size: 1.125rem;
        color: var(--text-gray);
        margin-bottom: 2rem;
        max-width: 600px;
    }

    .hero-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 2rem;
    }

    .tag {
        background: var(--light-blue);
        color: var(--primary-blue);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease;
    }

    .tag:hover {
        background: var(--primary-blue);
        color: white;
    }

    .tag-primary {
        background: var(--primary-blue);
        color: white;
    }

    .hero-image {
        position: relative;
        z-index: 2;
    }

    .hero-person {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 1rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    /* Stats Section */
    .stats {
        background: var(--white);
        padding: 3rem 0;
        margin-top: -2rem;
        position: relative;
        z-index: 3;
    }

    .stats-card {
        background: var(--white);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        border: 1px solid #e5e7eb;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-blue);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 1rem;
        color: var(--text-gray);
        font-weight: 500;
    }

    /* Education Section */
    .education {
        padding: 4rem 0;
        background: var(--light-gray);
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 2rem;
        text-align: center;
    }

    .education-tabs {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 3rem;
    }

    .tab-btn {
        background: var(--white);
        color: var(--text-gray);
        border: 1px solid #e5e7eb;
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .tab-btn.active {
        background: var(--primary-blue);
        color: white;
        border-color: var(--primary-blue);
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .course-card {
        background: var(--white);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .course-image {
        height: 120px;
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }

    .course-content {
        padding: 1.5rem;
    }

    .course-tags {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .course-tag {
        background: var(--light-blue);
        color: var(--primary-blue);
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .course-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .course-meta {
        font-size: 0.875rem;
        color: var(--text-gray);
    }

    .btn-primary {
        background: var(--primary-blue);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: var(--dark-blue);
        transform: translateY(-2px);
    }

    /* Program Selection */
    .program-selection {
        padding: 4rem 0;
        background: var(--white);
    }

    .program-content {
        display: flex;
        align-items: center;
        gap: 3rem;
    }

    .program-text h2 {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 1.5rem;
    }

    .program-text p {
        font-size: 1.125rem;
        color: var(--text-gray);
        margin-bottom: 2rem;
    }

    .benefits-list {
        list-style: none;
        margin-bottom: 2rem;
    }

    .benefits-list li {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 1rem;
        color: var(--text-dark);
    }

    .benefits-list li::before {
        content: '✓';
        background: var(--primary-blue);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-weight: 700;
    }

    .program-image {
        flex-shrink: 0;
    }

    .program-person {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 1rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    /* Benefits Cards */
    .benefits {
        padding: 4rem 0;
        background: var(--light-gray);
    }

    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
    }

    .benefit-card {
        background: var(--primary-blue);
        color: white;
        border-radius: 1.5rem;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .benefit-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .benefit-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .benefit-card p {
        margin-bottom: 1.5rem;
        opacity: 0.9;
    }

    .benefit-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .benefit-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    /* Events Section */
    .events {
        padding: 4rem 0;
        background: var(--white);
    }

    .event-card {
        background: var(--white);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .event-image {
        width: 200px;
        height: 150px;
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }

    .event-content {
        padding: 1.5rem;
        flex: 1;
    }

    .event-date {
        font-size: 0.875rem;
        color: var(--primary-blue);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .event-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1rem;
    }

    .event-description {
        color: var(--text-gray);
        line-height: 1.6;
    }

    /* Campus Life */
    .campus-life {
        padding: 4rem 0;
        background: var(--light-gray);
    }

    .video-container {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .video-thumbnail {
        width: 100%;
        height: 400px;
        background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 4rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .video-thumbnail:hover {
        transform: scale(1.02);
    }

    /* Testimonials */
    .testimonials {
        padding: 4rem 0;
        background: var(--white);
    }

    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .testimonial-card {
        background: var(--white);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .testimonial-avatar {
        width: 80px;
        height: 80px;
        background: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 auto 1rem;
    }

    .testimonial-name {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .testimonial-location {
        font-size: 0.875rem;
        color: var(--text-gray);
        margin-bottom: 1rem;
    }

    .testimonial-text {
        color: var(--text-gray);
        line-height: 1.6;
        font-style: italic;
    }

    /* Faculty */
    .faculty {
        padding: 4rem 0;
        background: var(--light-gray);
    }

    .faculty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }

    .faculty-member {
        text-align: center;
    }

    .faculty-avatar {
        width: 120px;
        height: 120px;
        background: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin: 0 auto 1rem;
    }

    .faculty-name {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .faculty-title {
        font-size: 0.875rem;
        color: var(--text-gray);
    }

    /* Contact Form */
    .contact-form {
        padding: 4rem 0;
        background: var(--primary-blue);
        color: white;
    }

    .contact-content {
        display: flex;
        align-items: center;
        gap: 3rem;
    }

    .contact-text h2 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        width: 100%;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        color: white;
        outline: none;
    }

    .form-row {
        display: flex;
        gap: 1rem;
    }

    .form-row .form-group {
        flex: 1;
    }

    .btn-white {
        background: white;
        color: var(--primary-blue);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-white:hover {
        background: var(--light-gray);
        transform: translateY(-2px);
    }

    /* Footer */
    .footer {
        background: var(--text-dark);
        color: white;
        padding: 3rem 0 1rem;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .footer-logo {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .footer-logo-circle {
        width: 50px;
        height: 50px;
        background: var(--primary-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        margin-right: 1rem;
    }

    .footer-section h5 {
        font-size: 1.125rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .footer-section ul {
        list-style: none;
    }

    .footer-section ul li {
        margin-bottom: 0.5rem;
    }

    .footer-section ul li a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-section ul li a:hover {
        color: white;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 2rem;
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
    }

    .scroll-top {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        background: var(--accent-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .scroll-top:hover {
        background: var(--primary-blue);
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2rem;
        }
        
        .hero-tags {
            justify-content: center;
        }
        
        .program-content {
            flex-direction: column;
            text-align: center;
        }
        
        .contact-content {
            flex-direction: column;
        }
        
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }
        
        .form-row {
            flex-direction: column;
        }
    }
</style>

<!-- Top Banner -->
<div class="top-banner">
    <div class="container">
        <span>{{ __('messages.enrollment_bachelor') }}</span>
        <a href="#" class="banner-btn">{{ __('messages.details') }}</a>
    </div>
</div>

<!-- Navigation -->
<nav class="navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center w-100">
            <a href="/" class="navbar-brand">
                <div class="logo-circle">ЕМ</div>
                EduManage
            </a>
            
            <div class="d-none d-lg-flex align-items-center">
                <a href="#programs" class="nav-link">{{ __('messages.programs') }}</a>
                <a href="#courses" class="nav-link">{{ __('messages.courses') }}</a>
                <a href="#testimonials" class="nav-link">{{ __('messages.student_testimonials') }}</a>
                <a href="#contacts" class="nav-link">{{ __('messages.contacts') }}</a>
            </div>
            
            <div class="nav-icons">
                <div class="nav-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="nav-icon d-lg-none">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1>{{ __('messages.education_based_on_modern_technologies') }}</h1>
                    <p>{{ __('messages.modern_education_system_description') }}</p>
                    
                    <div class="hero-tags">
                        <button class="tag">{{ __('messages.programming') }}</button>
                        <button class="tag">{{ __('messages.web_development') }}</button>
                        <button class="tag">{{ __('messages.design') }}</button>
                        <button class="tag">{{ __('messages.marketing') }}</button>
                        <button class="tag tag-primary">{{ __('messages.all_courses') }}</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 500'><rect width='400' height='500' fill='%23e0f2fe'/><circle cx='200' cy='150' r='60' fill='%233b82f6'/><rect x='150' y='220' width='100' height='200' fill='%231e40af'/><rect x='120' y='420' width='160' height='60' fill='%233b82f6'/></svg>" alt="{{ __('messages.instructor') }}" class="hero-person">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4">
                <div class="stats-card">
                    <div class="stat-number">5+</div>
                    <div class="stat-label">{{ __('messages.years_teaching_experience') }}</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="stats-card">
                    <div class="stat-number">15+</div>
                    <div class="stat-label">{{ __('messages.study_areas') }}</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="stats-card">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">{{ __('messages.graduates') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Education Section -->
<section id="programs" class="education">
    <div class="container">
        <h2 class="section-title">{{ __('messages.education_in_system') }}</h2>
        
        <div class="education-tabs">
            <button class="tab-btn active">{{ __('messages.education_in_system') }}</button>
            <button class="tab-btn">{{ __('messages.study_programs') }}</button>
            <button class="tab-btn">{{ __('messages.useful_resources') }}</button>
        </div>
        
        <div id="courses" class="course-grid">
            <div class="course-card">
                <div class="course-image">
                    <i class="fas fa-code"></i>
                </div>
                <div class="course-content">
                    <div class="course-tags">
                        <span class="course-tag">{{ __('messages.basics') }}</span>
                        <span class="course-tag">{{ __('messages.programming') }}</span>
                    </div>
                    <h3 class="course-title">{{ __('messages.web_development_study') }}</h3>
                    <p class="course-meta">{{ __('messages.start_january_15') }} / {{ __('messages.six_months') }}</p>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-image">
                    <i class="fas fa-palette"></i>
                </div>
                <div class="course-content">
                    <div class="course-tags">
                        <span class="course-tag">{{ __('messages.design') }}</span>
                        <span class="course-tag">UI/UX</span>
                    </div>
                    <h3 class="course-title">{{ __('messages.ui_design') }}</h3>
                    <p class="course-meta">{{ __('messages.start_january_20') }} / {{ __('messages.four_months') }}</p>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-image">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="course-content">
                    <div class="course-tags">
                        <span class="course-tag">{{ __('messages.analytics') }}</span>
                        <span class="course-tag">{{ __('messages.data') }}</span>
                    </div>
                    <h3 class="course-title">{{ __('messages.data_analysis_ml') }}</h3>
                    <p class="course-meta">{{ __('messages.start_january_25') }} / {{ __('messages.eight_months') }}</p>
                </div>
            </div>
            
            <div class="course-card">
                <div class="course-image">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="course-content">
                    <div class="course-tags">
                        <span class="course-tag">{{ __('messages.mobile') }}</span>
                        <span class="course-tag">{{ __('messages.applications') }}</span>
                    </div>
                    <h3 class="course-title">{{ __('messages.mobile_app_development') }}</h3>
                    <p class="course-meta">{{ __('messages.start_january_30') }} / {{ __('messages.five_months') }}</p>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <button class="btn btn-primary">{{ __('messages.all_courses') }}</button>
        </div>
    </div>
</section>

<!-- Program Selection -->
<section class="program-selection">
    <div class="container">
        <div class="program-content">
            <div class="program-text">
                <h2>{{ __('messages.choose_study_program') }}</h2>
                <p>{{ __('messages.system_help_find_program') }}</p>
                
                <ul class="benefits-list">
                    <li>{{ __('messages.personal_approach') }}</li>
                    <li>{{ __('messages.modern_methods') }}</li>
                    <li>{{ __('messages.practical_projects') }}</li>
                    <li>{{ __('messages.mentor_support') }}</li>
                    <li>{{ __('messages.flexible_schedule') }}</li>
                </ul>
                
                <button class="btn btn-primary">{{ __('messages.take_test') }}</button>
            </div>
            <div class="program-image">
                <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect width='400' height='300' fill='%23e0f2fe'/><rect x='100' y='50' width='200' height='150' fill='%233b82f6'/><circle cx='200' cy='125' r='30' fill='%231e40af'/></svg>" alt="{{ __('messages.student_at_work') }}" class="program-person">
            </div>
        </div>
    </div>
</section>

<!-- Benefits -->
<section class="benefits">
    <div class="container">
        <div class="benefits-grid">
            <div class="benefit-card">
                <h3>{{ __('messages.official_diploma') }}</h3>
                <p>{{ __('messages.recognized_diploma_description') }}</p>
                <button class="benefit-btn">{{ __('messages.details') }}</button>
            </div>
            
            <div class="benefit-card">
                <h3>{{ __('messages.mentorship') }}</h3>
                <p>{{ __('messages.personal_mentor_description') }}</p>
                <button class="benefit-btn">{{ __('messages.become_mentor') }}</button>
            </div>
            
            <div class="benefit-card">
                <h3>{{ __('messages.high_level_recognition') }}</h3>
                <p>{{ __('messages.graduates_work_description') }}</p>
                <button class="benefit-btn">{{ __('messages.details') }}</button>
            </div>
        </div>
    </div>
</section>

<!-- Events -->
<section class="events">
    <div class="container">
        <h2 class="section-title">{{ __('messages.upcoming_events') }}</h2>
        <p class="text-center text-muted mb-4">{{ __('messages.all_important_in_one_place') }}</p>
        
        <div class="event-card">
            <div class="event-image">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="event-content">
                <div class="event-date">20.01.2024</div>
                <h3 class="event-title">{{ __('messages.open_day_edumanage') }}</h3>
                <p class="event-description">{{ __('messages.open_day_description') }}</p>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-primary">{{ __('messages.all_events') }}</button>
        </div>
    </div>
</section>

<!-- Campus Life -->
<section class="campus-life">
    <div class="container">
        <h2 class="section-title">{{ __('messages.campus_life_more_than_study') }}</h2>
        <p class="text-center text-muted mb-4">{{ __('messages.learn_about_education_process') }}</p>
        
        <div class="video-container">
            <div class="video-thumbnail">
                <i class="fas fa-play"></i>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section id="testimonials" class="testimonials">
    <div class="container">
        <h2 class="section-title">{{ __('messages.student_testimonials') }}</h2>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-avatar">АК</div>
                <h4 class="testimonial-name">{{ __('messages.alexander_kodrashevich') }}</h4>
                <p class="testimonial-location">{{ __('messages.moscow_city') }}</p>
                <p class="testimonial-text">{{ __('messages.alexander_testimonial') }}</p>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-avatar">МП</div>
                <h4 class="testimonial-name">{{ __('messages.maria_petrova') }}</h4>
                <p class="testimonial-location">{{ __('messages.spb_city') }}</p>
                <p class="testimonial-text">{{ __('messages.maria_testimonial') }}</p>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-avatar">ДС</div>
                <h4 class="testimonial-name">{{ __('messages.dmitry_sidorov') }}</h4>
                <p class="testimonial-location">{{ __('messages.ekaterinburg_city') }}</p>
                <p class="testimonial-text">{{ __('messages.dmitry_testimonial') }}</p>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-avatar">ЕВ</div>
                <h4 class="testimonial-name">{{ __('messages.elena_volkova') }}</h4>
                <p class="testimonial-location">{{ __('messages.novosibirsk_city') }}</p>
                <p class="testimonial-text">{{ __('messages.elena_testimonial') }}</p>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-primary">{{ __('messages.view_all_testimonials') }}</button>
        </div>
    </div>
</section>

<!-- Faculty -->
<section class="faculty">
    <div class="container">
        <h2 class="section-title">{{ __('messages.our_faculty') }}</h2>
        <p class="text-center text-muted mb-4">{{ __('messages.experienced_specialists_description') }}</p>
        
        <div class="faculty-grid">
            <div class="faculty-member">
                <div class="faculty-avatar">АЕ</div>
                <h4 class="faculty-name">{{ __('messages.andrey_efimov') }}</h4>
                <p class="faculty-title">{{ __('messages.professor_programming') }}</p>
            </div>
            
            <div class="faculty-member">
                <div class="faculty-avatar">СК</div>
                <h4 class="faculty-name">{{ __('messages.sergey_kozlov') }}</h4>
                <p class="faculty-title">{{ __('messages.web_development_teacher') }}</p>
            </div>
            
            <div class="faculty-member">
                <div class="faculty-avatar">ОМ</div>
                <h4 class="faculty-name">{{ __('messages.olga_morozova') }}</h4>
                <p class="faculty-title">{{ __('messages.ui_ux_specialist') }}</p>
            </div>
            
            <div class="faculty-member">
                <div class="faculty-avatar">ВН</div>
                <h4 class="faculty-name">{{ __('messages.vladimir_novikov') }}</h4>
                <p class="faculty-title">{{ __('messages.data_analysis_expert') }}</p>
            </div>
            
            <div class="faculty-member">
                <div class="faculty-avatar">ИЗ</div>
                <h4 class="faculty-name">{{ __('messages.irina_zaharova') }}</h4>
                <p class="faculty-title">{{ __('messages.mobile_development_teacher') }}</p>
            </div>
            
            <div class="faculty-member">
                <div class="faculty-avatar">ПК</div>
                <h4 class="faculty-name">{{ __('messages.pavel_kuznetsov') }}</h4>
                <p class="faculty-title">{{ __('messages.devops_specialist') }}</p>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-primary">{{ __('messages.all_faculty') }}</button>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section id="contacts" class="contact-form">
    <div class="container">
        <div class="contact-content">
            <div class="contact-text">
                <h2>{{ __('messages.answer_all_questions') }}</h2>
                <p>{{ __('messages.leave_request_description') }}</p>
            </div>
            
            <div class="contact-form-container">
                <form>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="email" class="form-control" placeholder="{{ __('messages.email') }}">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="{{ __('messages.name_surname') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="{{ __('messages.question') }}">
                    </div>
                    <button type="submit" class="btn btn-white">{{ __('messages.send') }}</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <div class="footer-logo-circle">ЕМ</div>
                    <div>
                        <h5>EduManage</h5>
                        <p>{{ __('messages.modern_education_system_description') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-section">
                <h5>{{ __('messages.information') }}</h5>
                <ul>
                    <li><a href="#">{{ __('messages.about_system') }}</a></li>
                    <li><a href="#">{{ __('messages.news') }}</a></li>
                    <li><a href="#">{{ __('messages.events') }}</a></li>
                    <li><a href="#">{{ __('messages.contacts') }}</a></li>
                    <li><a href="#">{{ __('messages.faculty') }}</a></li>
                    <li><a href="#">{{ __('messages.cooperation') }}</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h5>{{ __('messages.education') }}</h5>
                <ul>
                    <li><a href="#">{{ __('messages.courses') }}</a></li>
                    <li><a href="#">{{ __('messages.programs') }}</a></li>
                    <li><a href="#">{{ __('messages.modules') }}</a></li>
                    <li><a href="#">{{ __('messages.bachelor') }}</a></li>
                    <li><a href="#">{{ __('messages.it_courses') }}</a></li>
                    <li><a href="#">{{ __('messages.web_development') }}</a></li>
                    <li><a href="#">{{ __('messages.design') }}</a></li>
                    <li><a href="#">{{ __('messages.library') }}</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <button class="btn btn-white mb-3">{{ __('messages.call_me') }}</button>
                <p>{{ __('messages.version_for_visually_impaired') }}</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; EduManage 2024 | <a href="#">{{ __('messages.privacy_policy') }}</a> | <a href="#">{{ __('messages.terms_of_service') }}</a></p>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<div class="scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
    <i class="fas fa-arrow-up"></i>
</div>

<script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                // Получаем высоту навигации для корректного позиционирования
                const navbar = document.querySelector('.navbar');
                const navbarHeight = navbar ? navbar.offsetHeight : 0;
                
                // Вычисляем позицию с учетом высоты навигации
                const targetPosition = target.offsetTop - navbarHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Tab functionality
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Scroll to top button visibility
    window.addEventListener('scroll', function() {
        const scrollTop = document.querySelector('.scroll-top');
        if (window.scrollY > 300) {
            scrollTop.style.display = 'flex';
        } else {
            scrollTop.style.display = 'none';
        }
    });
</script>
@endsection
