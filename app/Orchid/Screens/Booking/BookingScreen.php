<?php

namespace App\Orchid\Screens\Booking;

use App\Models\Booking;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;

class BookingScreen extends Screen
{
    public $name = 'Bookings';
    public $description = 'Manage bookings';

    public function query(): iterable
    {
        return [
            'bookings' => Booking::with(['customer', 'court', 'timeSlot'])->paginate(15),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Booking')
                ->icon('bs.plus-circle')
                ->route('platform.bookings.create')
                ->class('btn btn-primary'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('bookings', [

                TD::make('customer', 'Customer')
                    ->render(fn(Booking $b) =>
                        '👤 ' . ($b->customer?->first_name ?? '-')
                    ),

                TD::make('court', 'Court')
                    ->render(fn(Booking $b) =>
                        '🏟️ ' . ($b->court?->name ?? '-')
                    ),

                TD::make('date', 'Date')
                    ->render(fn(Booking $b) =>
                        '📅 ' . ($b->booking_date ?? '-')
                    ),

                TD::make('slot', 'Time')
                    ->render(fn(Booking $b) =>
                        '⏰ ' . ($b->timeSlot?->start_time ?? '-') . ' - ' . ($b->timeSlot?->end_time ?? '-')
                    ),

                TD::make('price', 'Price')
                    ->render(fn(Booking $b) =>
                        '💰 ' . $b->price
                    ),

                TD::make('status', 'Status')
                    ->render(fn(Booking $b) =>
                        '📌 ' . $b->status
                    ),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn(Booking $b) =>
                        '<a class="btn btn-sm btn-primary" href="' .
                        route('platform.bookings.edit', $b->id) .
                        '">✏️ Edit</a>'
                    ),
            ]),
        ];
    }
}