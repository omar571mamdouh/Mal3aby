<?php

namespace App\Orchid\Screens\Booking;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Court;
use App\Models\CourtTimeSlot;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;

class BookingEditScreen extends Screen
{
    public ?Booking $booking = null;

    public function name(): string
    {
        return $this->booking?->exists
            ? 'Edit Booking #' . $this->booking->id
            : 'New Booking';
    }

    public function description(): string
    {
        return $this->booking?->exists
            ? 'Update booking for ' . ($this->booking->customer?->first_name ?? 'customer')
            : 'Fill in the details to create a new booking';
    }

    public function query(Booking $booking): array
    {
        return [
            'booking' => $booking,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Cancel')
                ->icon('bs.x-lg')
                ->route('platform.bookings.list')
                ->class('btn btn-outline-secondary'),

            Button::make($this->booking?->exists ? 'Update Booking' : 'Create Booking')
                ->method('save')
                ->icon('bs.check-lg')
                ->class('btn btn-primary px-4'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([

                Input::make('booking.id')->type('hidden'),

                Select::make('booking.customer_id')
                    ->title('Customer')
                    ->options(fn() => Customer::orderBy('first_name')->pluck('first_name', 'id'))
                    ->empty('— Select customer —')
                    ->required(),

                Select::make('booking.court_id')
                    ->title('Court')
                    ->options(fn() => Court::orderBy('name')->pluck('name', 'id'))
                    ->empty('— Select court —')
                    ->required(),

                Input::make('booking.booking_date')
                    ->type('date')
                    ->title('Booking Date')
                    ->required(),

                Select::make('booking.time_slot_id')
                    ->title('Time Slot')
                    ->options(fn() => CourtTimeSlot::orderBy('start_time')
                        ->get()
                        ->mapWithKeys(fn($slot) => [
                            $slot->id => $slot->start_time . ' – ' . $slot->end_time,
                        ]))
                    ->empty('— Select time slot —')
                    ->required(),

                Input::make('booking.price')
                    ->type('number')
                    ->title('Price (EGP)')
                    ->placeholder('0.00')
                    ->required(),

                Select::make('booking.status')
                    ->title('Status')
                    ->options([
                        'pending'   => '⏳ Pending',
                        'confirmed' => '✅ Confirmed',
                        'cancelled' => '✖ Cancelled',
                        'completed' => '🏁 Completed',
                    ])
                    ->required(),
            ]),
        ];
    }

    public function save(Booking $booking, Request $request)
    {
        $data = $request->validate([
            'booking.customer_id'  => 'required|exists:customers,id',
            'booking.court_id'     => 'required|exists:courts,id',
            'booking.booking_date' => 'required|date',
            'booking.time_slot_id' => 'required|exists:court_time_slots,id',
            'booking.price'        => 'required|numeric|min:0',
            'booking.status'       => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $booking->fill($data['booking'])->save();

        Toast::info($booking->wasRecentlyCreated
            ? 'Booking created successfully!'
            : 'Booking updated successfully!'
        );

        return redirect()->route('platform.bookings.list');
    }
}