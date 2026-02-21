<?php

namespace App\Http\Controllers;

use App\Models\Event;

class PublicEventController extends Controller
{
    public function index()
    {
        $events = Event::published()
            ->upcoming()
            ->orderBy('start_date')
            ->paginate(12);

        $featuredEvents = Event::published()
            ->upcoming()
            ->featured()
            ->orderBy('start_date')
            ->take(3)
            ->get();

        return view('events.index', compact('events', 'featuredEvents'));
    }

    public function show(Event $event)
    {
        if ($event->status->value !== 'published' && ! auth()->user()?->hasRole('admin')) {
            abort(404);
        }

        $event->load([
            'pricingCategories' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'pricingCategories.packages' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'formFields' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'formFields.options' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return view('events.show', compact('event'));
    }
}
