<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер для управления отзывами
 */
class ReviewController extends Controller
{
    /**
     * Показать форму создания отзыва
     */
    public function create(Course $course)
    {
        // Проверяем, может ли пользователь оставить отзыв
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему для оставления отзыва');
        }

        // Проверяем, не оставлял ли пользователь уже отзыв на этот курс
        $existingReview = Review::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingReview) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Вы уже оставили отзыв на этот курс');
        }

        return view('reviews.create', compact('course'));
    }

    /**
     * Сохранить новый отзыв
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему');
        }

        // Проверяем, не оставлял ли пользователь уже отзыв
        $existingReview = Review::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingReview) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Вы уже оставили отзыв на этот курс');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        Review::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false, // По умолчанию не одобрен
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Отзыв отправлен на модерацию. Спасибо за ваш отзыв!');
    }

    /**
     * Показать форму редактирования отзыва
     */
    public function edit(Review $review)
    {
        $user = Auth::user();
        
        if (!$user || $user->id !== $review->user_id) {
            abort(403, 'У вас нет прав для редактирования этого отзыва');
        }

        return view('reviews.edit', compact('review'));
    }

    /**
     * Обновить отзыв
     */
    public function update(Request $request, Review $review)
    {
        $user = Auth::user();
        
        if (!$user || $user->id !== $review->user_id) {
            abort(403, 'У вас нет прав для редактирования этого отзыва');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false, // Сбрасываем одобрение при редактировании
        ]);

        return redirect()->route('courses.show', $review->course)
            ->with('success', 'Отзыв обновлен и отправлен на повторную модерацию');
    }

    /**
     * Удалить отзыв
     */
    public function destroy(Review $review)
    {
        $user = Auth::user();
        
        if (!$user || $user->id !== $review->user_id) {
            abort(403, 'У вас нет прав для удаления этого отзыва');
        }

        $course = $review->course;
        $review->delete();

        return redirect()->route('courses.show', $course)
            ->with('success', 'Отзыв удален');
    }
}
