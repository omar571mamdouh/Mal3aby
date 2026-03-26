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
use Orchid\Support\Facades\Toast;

class BookingEditScreen extends Screen
{
    public $name = 'Booking';
    public $description = 'Create or edit booking';

    public function query(?Booking $booking): array
    {
        return [
            'booking' => $booking ?? new Booking(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->method('save')
                ->icon('bs.check'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([

                Select::make('booking.customer_id')
                    ->title('Customer')
                    ->options(fn() => Customer::pluck('first_name', 'id'))
                    ->required(),

                Select::make('booking.court_id')
                    ->title('Court')
                    ->options(fn() => Court::pluck('name', 'id'))
                    ->required(),

                Input::make('booking.booking_date')
                    ->type('date')
                    ->title('Date')
                    ->required(),

                Select::make('booking.time_slot_id')
                    ->title('Time Slot')
                    ->options(fn() => CourtTimeSlot::pluck('start_time', 'id'))
                    ->required(),

                Input::make('booking.price')
                    ->type('number')
                    ->title('Price')
                    ->required(),

                Select::make('booking.status')
                    ->title('Status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ])
                    ->required(),

            ]),
        ];
    }

   public function save(Request $request)
{
    $data = $request->get('booking');

    $booking = Booking::findOrNew($data['id'] ?? null);

    $booking->fill($data);
    $booking->save();

    Toast::info('Booking saved successfully!');

    return redirect()->route('platform.bookings.list');
}
}