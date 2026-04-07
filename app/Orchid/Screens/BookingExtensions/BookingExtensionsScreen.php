<?php

namespace App\Orchid\Screens\BookingExtensions;

use App\Models\Booking;
use App\Models\BookingExtension;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class BookingExtensionsScreen extends Screen
{
    public ?Booking $booking = null;

    public function name(): string
    {
        return "Extensions for Booking #" . $this->booking->id;
    }

   public function query(Booking $booking): array
{
    $this->booking = $booking;

    return [
        'extensions' => $booking->extensions()->with('approvedBy')->latest()->get(),
    ];
}

    public function commandBar(): iterable
    {
        return [
            Button::make('Add Extension')
                ->icon('bs.plus-lg')
                ->method('addExtension')
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('extensions', [
                TD::make('extra_minutes', 'Extra Minutes'),
                TD::make('price', 'Price'),
                TD::make('approved_by', 'Approved By')
                    ->render(fn(BookingExtension $e) => $e->approvedBy?->name ?? '—'),
                TD::make('notes', 'Notes'),
                TD::make('created_at', 'Created At')
                    ->render(fn(BookingExtension $e) => $e->created_at->format('M d, Y H:i')),
                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(function (BookingExtension $e) {
                        return Button::make('Delete')
                            ->method('deleteExtension', ['id' => $e->id])
                            ->confirm('Are you sure?');
                    }),
            ]),

            Layout::rows([
                Input::make('extra_minutes')->title('Extra Minutes')->type('number')->required(),
                Input::make('price')->title('Price')->type('number')->step('0.01')->required(),
                TextArea::make('notes')->title('Notes'),
            ]),
        ];
    }

    public function addExtension(Request $request)
    {
        $data = $request->validate([
            'extra_minutes' => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        BookingExtension::create(array_merge($data, [
            'booking_id' => $this->booking->id,
        ]));

        Toast::info('Booking extension added successfully.');
        return redirect()->route('platform.bookings.services', $this->booking->id);
    }

    public function deleteExtension(Request $request)
    {
        BookingExtension::findOrFail($request->get('id'))->delete();
        Toast::info('Booking extension deleted.');
        return back();
    }
}