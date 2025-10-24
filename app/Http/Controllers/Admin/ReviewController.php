<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

/**
 * Контроллер для управления отзывами (админка)
 */
class ReviewController extends Controller
{
    /**
     * Показать список всех отзывов
     */
    public function index()
    {
        $reviews = Review::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Показать детали отзыва
     */
    public function show(Review $review)
    {
        $review->load(['user', 'course']);
        
        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Одобрить отзыв
     */
    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);

        return redirect()->back()
            ->with('success', 'Отзыв одобрен и опубликован');
    }

    /**
     * Отклонить отзыв
     */
    public function reject(Review $review)
    {
        $review->update(['is_approved' => false]);

        return redirect()->back()
            ->with('success', 'Отзыв отклонен');
    }

    /**
     * Удалить отзыв
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Отзыв удален');
    }

    /**
     * Показать только неодобренные отзывы
     */
    public function pending()
    {
        $reviews = Review::with(['user', 'course'])
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.reviews.pending', compact('reviews'));
    }

    /**
     * Показать только одобренные отзывы
     */
    public function approved()
    {
        $reviews = Review::with(['user', 'course'])
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.reviews.approved', compact('reviews'));
    }
}
