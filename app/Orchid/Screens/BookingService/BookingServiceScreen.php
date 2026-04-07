<?php

namespace App\Orchid\Screens\BookingService;

use App\Models\Booking;
use App\Models\BookingService;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Actions\Link;

class BookingServiceScreen extends Screen
{
    public ?Booking $booking = null;

    public function query(Booking $booking): array
    {
        $this->booking = $booking;

        return [
            'booking' => $this->booking,
        ];
    }

   public function name(): string
{
    return 'Booking Services for #' . ($this->booking?->id ?? '...');
}

public function description(): string
{
    return 'Add or manage services linked to this booking';
}
   public function commandBar(): iterable
{
    return [
      
// ✓ بعد - خليه Back button بس
Link::make('Back')
    ->route('platform.bookings.list')
    ->icon('bs.arrow-left'),

        Button::make('Save Services')
            ->icon('bs.check-lg')
            ->method('saveServices')
            ->class('btn btn-primary px-4'),
    ];
}

   public function layout(): iterable
{
    $services = $this->booking?->services ?? collect();

    $rows = [];

    // لو في services موجودة، اعرضها
    foreach ($services as $index => $service) {
        $rows[] = Layout::rows([
            Input::make("services.{$index}.id")->type('hidden'),

            Input::make("services.{$index}.name")
                ->title('Service Name')
                ->value($service->name)
                ->required(),

            Select::make("services.{$index}.pricing_type")
                ->title('Pricing Type')
                ->options([
                    'fixed'      => 'Fixed',
                    'per_hour'   => 'Per Hour',
                    'per_person' => 'Per Person',
                ])
                ->value($service->pricing_type)
                ->required(),

            Input::make("services.{$index}.price")
                ->title('Price')
                ->type('number')
                ->step('0.01')
                ->value($service->price)
                ->required(),

            Input::make("services.{$index}.quantity")
                ->title('Quantity')
                ->type('number')
                ->min(1)
                ->value($service->quantity ?? 1)
                ->required(),

            TextArea::make("services.{$index}.notes")
                ->title('Notes')
                ->value($service->notes)
                ->rows(1),
        ]);
    }

    // فورم لإضافة service جديدة
    $rows[] = Layout::rows([
        Input::make('new_service.name')
            ->title('➕ New Service Name')
            ->placeholder('Enter service name'),

        Select::make('new_service.pricing_type')
            ->title('Pricing Type')
            ->options([
                'fixed'      => 'Fixed',
                'per_hour'   => 'Per Hour',
                'per_person' => 'Per Person',
            ]),

        Input::make('new_service.price')
            ->title('Price')
            ->type('number')
            ->step('0.01'),

        Input::make('new_service.quantity')
            ->title('Quantity')
            ->type('number')
            ->min(1)
            ->value(1),

        TextArea::make('new_service.notes')
            ->title('Notes')
            ->rows(1),
    ]);

    return $rows;
}

  public function saveServices(Request $request)
{
    $bookingId = $request->route('booking');
    $booking   = Booking::findOrFail($bookingId);

    // تحديث الموجودين
    foreach ($request->input('services', []) as $serviceData) {
        if (!empty($serviceData['id'])) {
            $existing = BookingService::find($serviceData['id']);
            if ($existing && $existing->booking_id === $booking->id) {
                $existing->update($serviceData);
            }
        }
    }

    // إضافة جديد لو فيه بيانات
    $new = $request->input('new_service', []);
    if (!empty($new['name']) && !empty($new['price'])) {
        unset($new['id']);
        $booking->services()->create($new);
    }

    Toast::info('Services updated successfully!');

    return redirect()->route('platform.bookings.services', $booking->id);
}
}
