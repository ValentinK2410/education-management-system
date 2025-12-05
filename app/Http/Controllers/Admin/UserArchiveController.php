<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EnrollmentHistory;
use App\Models\Payment;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Контроллер для управления архивом пользователей
 *
 * Предоставляет функционал для просмотра истории обучения,
 * платежей и сертификатов пользователей
 */
class UserArchiveController extends Controller
{
    /**
     * Показать список пользователей для выбора
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Поиск по имени или email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.user-archive.index', compact('users'));
    }

    /**
     * Показать историю обучения пользователя
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        // История зачислений/отчислений
        $enrollmentHistory = EnrollmentHistory::where('user_id', $user->id)
            ->with(['course', 'program', 'changedBy'])
            ->orderBy('changed_at', 'desc')
            ->get();

        // История платежей
        $payments = Payment::where('user_id', $user->id)
            ->with(['course', 'program'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Сертификаты
        $certificates = Certificate::where('user_id', $user->id)
            ->with(['course', 'program', 'template'])
            ->orderBy('issued_at', 'desc')
            ->get();

        // Текущие зачисления на курсы
        $courseEnrollments = $user->courses()
            ->with('program')
            ->orderBy('enrolled_at', 'desc')
            ->get();

        // Текущие зачисления на программы
        $programEnrollments = $user->programs()
            ->with('institution')
            ->orderBy('enrolled_at', 'desc')
            ->get();

        // Статистика
        $stats = [
            'total_courses' => $courseEnrollments->count(),
            'completed_courses' => $courseEnrollments->where('pivot.status', 'completed')->count(),
            'active_courses' => $courseEnrollments->where('pivot.status', 'active')->count(),
            'total_programs' => $programEnrollments->count(),
            'completed_programs' => $programEnrollments->where('pivot.status', 'completed')->count(),
            'active_programs' => $programEnrollments->where('pivot.status', 'active')->count(),
            'total_certificates' => $certificates->count(),
            'total_payments' => $payments->count(),
            'paid_amount' => $payments->where('status', 'paid')->sum('amount'),
        ];

        return view('admin.user-archive.show', compact(
            'user',
            'enrollmentHistory',
            'payments',
            'certificates',
            'courseEnrollments',
            'programEnrollments',
            'stats'
        ));
    }

    /**
     * Скачать сертификат
     *
     * @param User $user
     * @param Certificate $certificate
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadCertificate(User $user, Certificate $certificate)
    {
        // Проверяем, что сертификат принадлежит пользователю
        if ($certificate->user_id !== $user->id) {
            abort(403, 'Сертификат не принадлежит этому пользователю');
        }

        $path = storage_path('app/public/' . $certificate->image_path);

        if (!file_exists($path)) {
            abort(404, 'Файл сертификата не найден.');
        }

        return response()->download($path, 'certificate_' . $certificate->certificate_number . '.jpg');
    }
}
