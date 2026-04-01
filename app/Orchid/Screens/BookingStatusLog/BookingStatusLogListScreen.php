<?php

namespace App\Orchid\Screens\BookingStatusLog;

use App\Models\BookingStatusLog;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;

class BookingStatusLogListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'logs' => BookingStatusLog::with('booking.customer')->latest()->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Booking Status Logs';
    }

    public function description(): ?string
    {
        return 'Automatically tracked status changes';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('logs', [

                TD::make('customer', '👤 Booking')
                    ->width('230px')
                    ->render(function (BookingStatusLog $log) {
                        $name     = $log->booking?->customer?->first_name ?? '—';
                        $initials = $log->booking?->customer
                            ? strtoupper(substr($name, 0, 2))
                            : '?';
                        $id = $log->booking?->id ?? '—';

                        return "
                            <div style='display:flex;align-items:center;gap:10px'>
                                <div style='width:34px;height:34px;border-radius:50%;
                                            background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                            color:#fff;font-size:12px;font-weight:700;
                                            display:flex;align-items:center;justify-content:center;flex-shrink:0'>
                                    {$initials}
                                </div>
                                <div>
                                    <div style='font-weight:600;font-size:13.5px;color:#1e1b4b'>{$name}</div>
                                    <div style='font-size:11px;color:#9ca3af;margin-top:1px'>Booking #{$id}</div>
                                </div>
                            </div>";
                    }),

                TD::make('status', '🏷️ New Status')
                    ->width('150px')
                    ->render(function (BookingStatusLog $log) {
                        $map = [
                            'confirmed' => [
                                'style' => 'background:#dcfce7;color:#166534;border:1px solid #86efac',
                                'icon'  => '✅',
                                'label' => 'Confirmed',
                            ],
                            'cancelled' => [
                                'style' => 'background:#fee2e2;color:#991b1b;border:1px solid #fca5a5',
                                'icon'  => '✖',
                                'label' => 'Cancelled',
                            ],
                            'pending' => [
                                'style' => 'background:#fef9c3;color:#854d0e;border:1px solid #fde68a',
                                'icon'  => '⏳',
                                'label' => 'Pending',
                            ],
                            'completed' => [
                                'style' => 'background:#ede9fe;color:#4c1d95;border:1px solid #c4b5fd',
                                'icon'  => '🏁',
                                'label' => 'Completed',
                            ],
                        ];

                        $s     = $log->status;
                        $style = $map[$s] ?? [
                            'style' => 'background:#f3f4f6;color:#374151;border:1px solid #e5e7eb',
                            'icon'  => '•',
                            'label' => ucfirst($s),
                        ];

                        return "
                            <span style='display:inline-flex;align-items:center;gap:5px;border-radius:20px;
                                         padding:3px 12px;font-size:12px;font-weight:600;{$style['style']}'>
                                {$style['icon']} {$style['label']}
                            </span>";
                    }),

                TD::make('note', '📝 Note')
                    ->render(function (BookingStatusLog $log) {
                        $note = $log->note ?? '—';
                        return "<span style='font-size:13px;font-weight:600;color:#374151;line-height:1.5'>
                                    {$note}
                                </span>";
                    }),

                TD::make('created_at', '🕐 Date')
                    ->width('170px')
                    ->render(function (BookingStatusLog $log) {
                        $date = $log->created_at->format('M d, Y');
                        $time = $log->created_at->format('H:i');
                        $day  = $log->created_at->format('l');
                        return "
                            <div style='font-weight:600;font-size:13px;color:#374151'>
                                {$date}
                                <div style='font-size:11px;color:#9ca3af;margin-top:2px'>{$day} · {$time}</div>
                            </div>";
                    }),

                TD::make('actions', '')
                    ->width('80px')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn(BookingStatusLog $log) =>
                        Button::make('🗑️')
                            ->confirm('Are you sure you want to delete this log?')
                            ->method('delete', ['id' => $log->id])
                            ->class('btn btn-sm')
                            ->style('width:30px;height:30px;border-radius:6px;background:#fff1f2;border:1px solid #fecaca;
                                    color:#dc2626;display:inline-flex;align-items:center;justify-content:center;padding:0')
                    ),

            ]),
        ];
    }

    public function delete(Request $request): void
    {
        BookingStatusLog::findOrFail($request->get('id'))->delete();
        Toast::info('Log deleted successfully.');
    }
}