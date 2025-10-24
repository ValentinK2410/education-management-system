<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Контроллер для управления событиями (админка)
 */
class EventController extends Controller
{
    /**
     * Показать список событий
     */
    public function index()
    {
        $events = Event::orderBy('start_date', 'desc')->paginate(15);
        
        return view('admin.events.index', compact('events'));
    }

    /**
     * Показать форму создания события
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Сохранить новое событие
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'required_with:price|string|size:3',
            'registration_url' => 'nullable|url',
        ]);

        $data = $request->all();

        // Обработка изображения
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        // Преобразование boolean полей
        $data['is_published'] = $request->has('is_published');
        $data['is_featured'] = $request->has('is_featured');

        Event::create($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие успешно создано');
    }

    /**
     * Показать детали события
     */
    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    /**
     * Показать форму редактирования события
     */
    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Обновить событие
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'required_with:price|string|size:3',
            'registration_url' => 'nullable|url',
        ]);

        $data = $request->all();

        // Обработка изображения
        if ($request->hasFile('image')) {
            // Удаляем старое изображение
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        // Преобразование boolean полей
        $data['is_published'] = $request->has('is_published');
        $data['is_featured'] = $request->has('is_featured');

        $event->update($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие успешно обновлено');
    }

    /**
     * Удалить событие
     */
    public function destroy(Event $event)
    {
        // Удаляем изображение
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие успешно удалено');
    }

    /**
     * Переключить статус публикации
     */
    public function togglePublished(Event $event)
    {
        $event->update(['is_published' => !$event->is_published]);

        $status = $event->is_published ? 'опубликовано' : 'снято с публикации';
        
        return redirect()->back()
            ->with('success', "Событие {$status}");
    }

    /**
     * Переключить статус рекомендуемого
     */
    public function toggleFeatured(Event $event)
    {
        $event->update(['is_featured' => !$event->is_featured]);

        $status = $event->is_featured ? 'добавлено в рекомендуемые' : 'убрано из рекомендуемых';
        
        return redirect()->back()
            ->with('success', "Событие {$status}");
    }
}
