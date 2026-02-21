<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::published()
            ->orderBy('start_date');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($dateFrom = $request->input('date_from')) {
            $query->where('start_date', '>=', $dateFrom);
        } else {
            // Default to upcoming events only
            $query->upcoming();
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('start_date', '<=', $dateTo . ' 23:59:59');
        }

        $events = $query->paginate(12)->withQueryString();

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
