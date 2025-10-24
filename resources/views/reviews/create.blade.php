@extends('layouts.app')

@section('title', 'Оставить отзыв - ' . $course->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Оставить отзыв о курсе
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Информация о курсе -->
                    <div class="course-info mb-4 p-3 bg-light rounded">
                        <h5 class="mb-2">{{ $course->name }}</h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-building me-1"></i>
                            {{ $course->program->institution->name ?? 'Не указано' }}
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reviews.store', $course) }}" method="POST">
                        @csrf
                        
                        <!-- Рейтинг -->
                        <div class="mb-4">
                            <label for="rating" class="form-label">
                                <strong>Оценка курса</strong> <span class="text-danger">*</span>
                            </label>
                            <div class="rating-input">
                                @for ($i = 1; $i <= 5; $i++)
                                    <input type="radio" 
                                           name="rating" 
                                           id="rating{{ $i }}" 
                                           value="{{ $i }}"
                                           {{ old('rating') == $i ? 'checked' : '' }}
                                           class="rating-radio">
                                    <label for="rating{{ $i }}" class="rating-star">
                                        <i class="fas fa-star"></i>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Комментарий -->
                        <div class="mb-4">
                            <label for="comment" class="form-label">
                                <strong>Ваш отзыв</strong> <span class="text-danger">*</span>
                            </label>
                            <textarea name="comment" 
                                      id="comment" 
                                      class="form-control @error('comment') is-invalid @enderror" 
                                      rows="6" 
                                      placeholder="Поделитесь своим опытом изучения курса. Что вам понравилось? Что можно улучшить?"
                                      required>{{ old('comment') }}</textarea>
                            <div class="form-text">
                                Минимум 10 символов, максимум 1000 символов
                            </div>
                            @error('comment')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Кнопки -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Назад к курсу
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>
                                Отправить отзыв
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Информация о модерации -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Информация о модерации
                    </h6>
                    <p class="card-text text-muted mb-0">
                        Ваш отзыв будет отправлен на модерацию. После проверки администратором 
                        он будет опубликован на странице курса. Обычно это занимает 1-2 рабочих дня.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    gap: 5px;
    margin-top: 10px;
}

.rating-radio {
    display: none;
}

.rating-star {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
}

.rating-star:hover,
.rating-star:hover ~ .rating-star {
    color: #ffc107;
}

.rating-radio:checked ~ .rating-star {
    color: #ffc107;
}

.rating-radio:checked + .rating-star {
    color: #ffc107;
}

/* Эффект при наведении */
.rating-input:hover .rating-star {
    color: #ffc107;
}

.rating-input:hover .rating-star:hover ~ .rating-star {
    color: #ddd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('.rating-radio');
    const ratingStars = document.querySelectorAll('.rating-star');
    
    ratingInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            // Обновляем визуальное отображение звезд
            ratingStars.forEach((star, starIndex) => {
                if (starIndex <= index) {
                    star.style.color = '#ffc107';
                } else {
                    star.style.color = '#ddd';
                }
            });
        });
    });
});
</script>
@endsection
