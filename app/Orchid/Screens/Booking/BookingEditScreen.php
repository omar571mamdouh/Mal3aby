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
use App\Services\PricingService;

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
                    ->options(fn() => CourtTimeSlot::with('court')
                        ->where('active', 1)
                        ->orderBy('court_id')
                        ->orderBy('start_time')
                        ->get()
                        ->mapWithKeys(fn($slot) => [
                            $slot->id => ($slot->court?->name ?? '?') . ' — ' .
                                ucfirst($slot->day) . ' — ' .   // 👈 اليوم هنا
                                $slot->start_time . ' – ' . $slot->end_time,
                        ]))
                    ->empty('— Select time slot —')
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


public function save(Booking $booking, Request $request, PricingService $pricingService)
{
    $data = $request->validate([
        'booking.customer_id'  => 'required|exists:customers,id',
        'booking.court_id'     => 'required|exists:courts,id',
        'booking.booking_date' => 'required|date',
        'booking.time_slot_id' => 'required|exists:court_time_slots,id',
        // ❌ شيلنا السعر من هنا
        'booking.status'       => 'required|in:pending,confirmed,cancelled,completed',
    ]);

    // ✅ هات الـ slot
    $slot = CourtTimeSlot::findOrFail($data['booking']['time_slot_id']);

    // ✅ تحقق اليوم
    $bookingDay = strtolower(\Carbon\Carbon::parse($data['booking']['booking_date'])->format('l'));

    if ($slot->day !== $bookingDay) {
        return back()->withErrors([
            'booking.time_slot_id' => 'الـ Time Slot المختار مش بتاع يوم ' . $bookingDay . ' — اختار slot صح.',
        ])->withInput();
    }

    // ✅ تحقق Blackout
    $blackout = \App\Models\BlackoutDate::where('court_id', $data['booking']['court_id'])
        ->where('active', 1)
        ->where('start_date', '<=', $data['booking']['booking_date'])
        ->where(function ($q) use ($data) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $data['booking']['booking_date']);
        })
        ->first();

    if ($blackout) {
        $reason = $blackout->reason ?? match ($blackout->type) {
            'maintenance' => 'الملعب تحت الصيانة',
            'holiday'     => 'يوم إجازة',
            'event'       => 'يوجد حدث خاص',
            'manual'      => 'الملعب مغلق',
            default       => 'الملعب غير متاح',
        };

        return back()->withErrors([
            'booking.booking_date' => "❌ هذا التاريخ غير متاح للحجز — {$reason}",
        ])->withInput();
    }

    // ✅ هات الـ court
    $court = Court::findOrFail($data['booking']['court_id']);

    // 🔥 احسب السعر أوتوماتيك
    $calculatedPrice = $pricingService->calculate(
        $court,
        $slot,
        $data['booking']['booking_date']
    );

    // ✅ حط السعر في البيانات
    $data['booking']['price'] = $calculatedPrice;

    // 💾 حفظ
    $booking->fill($data['booking'])->save();


    //  تحديث التاج تلقائيًا بعد الحفظ
$tagService = new \App\Services\CustomerTagService();
$tagService->updateTag($booking->customer_id);

    Toast::info(
        ($booking->wasRecentlyCreated
            ? 'Booking created successfully! '
            : 'Booking updated successfully! ')
        . "💰 Price: {$calculatedPrice} EGP"
    );

    return redirect()->route('platform.bookings.list');
}
}
