<?php

namespace App\Orchid\Screens\Booking;

use App\Models\Booking;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class BookingScreen extends Screen
{
    public $name = 'Bookings';
    public $description = 'Manage all court reservations';

    public function query(): iterable
    {
        return [
            'bookings' => Booking::with(['customer', 'court', 'timeSlot'])->latest()->paginate(15),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('New Booking')
                ->icon('bs.plus-lg')
                ->route('platform.bookings.create')
                ->class('btn btn-primary px-4'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('bookings', [

                TD::make('customer', 'Customer')
                    ->render(function (Booking $b) {
                        $name     = $b->customer?->first_name ?? '?';
                        $initials = strtoupper(substr($name, 0, 2));
                        return "
                            <div style='display:flex;align-items:center;gap:10px'>
                                <div style='width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                            color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;
                                            justify-content:center;flex-shrink:0'>{$initials}</div>
                                <span style='font-weight:600;font-size:13.5px;color:#1e1b4b'>{$name}</span>
                            </div>";
                    }),

                TD::make('court', 'Court')
                    ->render(fn(Booking $b) =>
                        "<span style='display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#166534;
                                      border:1px solid #bbf7d0;border-radius:6px;padding:3px 10px;font-size:12.5px;font-weight:600'>
                            🏟️ " . ($b->court?->name ?? '—') . "
                        </span>"
                    ),

                TD::make('date', 'Date')
                    ->render(function (Booking $b) {
                        $date      = $b->booking_date ? \Carbon\Carbon::parse($b->booking_date) : null;
                        $formatted = $date?->format('M d, Y') ?? '—';
                        $day       = $date?->format('l') ?? '';
                        return "<div style='font-weight:600;font-size:13px;color:#374151'>
                                    {$formatted}
                                    <div style='font-size:11px;color:#9ca3af;margin-top:2px'>{$day}</div>
                                </div>";
                    }),

                TD::make('slot', 'Time')
                    ->render(fn(Booking $b) =>
                        "<span style='display:inline-flex;align-items:center;gap:5px;background:#eff6ff;color:#1d4ed8;
                                      border:1px solid #bfdbfe;border-radius:6px;padding:3px 10px;font-size:12.5px;font-weight:600'>
                            ⏰ " . ($b->timeSlot?->start_time ?? '—') . " – " . ($b->timeSlot?->end_time ?? '') . "
                        </span>"
                    ),

                TD::make('price', 'Price')
                    ->render(fn(Booking $b) =>
                        "<span style='font-size:14px;font-weight:700;color:#065f46'>"
                        . number_format($b->price, 2) . " EGP</span>"
                    ),

                TD::make('status', 'Status')
                    ->render(function (Booking $b) {
                        $styles = [
                            'pending'   => 'background:#fef9c3;color:#854d0e;border:1px solid #fde68a',
                            'confirmed' => 'background:#dcfce7;color:#166534;border:1px solid #86efac',
                            'cancelled' => 'background:#fee2e2;color:#991b1b;border:1px solid #fca5a5',
                            'completed' => 'background:#ede9fe;color:#4c1d95;border:1px solid #c4b5fd',
                        ];
                        $icons = [
                            'pending'   => '⏳',
                            'confirmed' => '✅',
                            'cancelled' => '✖',
                            'completed' => '🏁',
                        ];
                        $s     = $b->status ?? 'pending';
                        $style = $styles[$s] ?? $styles['pending'];
                        $icon  = $icons[$s]  ?? '•';
                        $label = ucfirst($s);
                        return "<span style='display:inline-flex;align-items:center;gap:5px;border-radius:20px;
                                             padding:3px 12px;font-size:12px;font-weight:600;{$style}'>
                                    {$icon} {$label}
                                </span>";
                    }),

                TD::make('actions', '')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(function (Booking $b) {
                        $editUrl = route('platform.bookings.edit', $b->id);

                        $edit = "<a href='{$editUrl}'
                                    style='width:30px;height:30px;border-radius:6px;background:#eff6ff;border:1px solid #bfdbfe;
                                           color:#2563eb;display:inline-flex;align-items:center;justify-content:center;
                                           text-decoration:none;transition:all .15s'
                                    title='Edit'>✏️</a>";

                        $delete = Button::make('🗑️')
                            ->confirm('Are you sure you want to delete this booking?')
                            ->method('remove', ['id' => $b->id])
                            ->class('btn btn-sm')
                            ->style('width:30px;height:30px;border-radius:6px;background:#fff1f2;border:1px solid #fecaca;
                                    color:#dc2626;display:inline-flex;align-items:center;justify-content:center;padding:0')
                            ->render();

                        return "<div style='display:flex;justify-content:flex-end;gap:6px'>{$edit}{$delete}</div>";
                    }),
            ]),
        ];
    }

    public function remove(Request $request): void
    {
        Booking::findOrFail($request->get('id'))->delete();
        Toast::info('Booking deleted.');
    }
}