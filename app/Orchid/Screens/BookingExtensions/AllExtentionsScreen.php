<?php
namespace App\Orchid\Screens\BookingExtensions;

use App\Models\BookingExtension;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;

class AllExtentionsScreen extends Screen
{
    public function name(): string
    {
        return 'All Booking Extensions';
    }

    public function query(): iterable
    {
        return [
            'extensions' => BookingExtension::with('booking', 'approvedBy')->latest()->paginate(15),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('extensions', [
                TD::make('booking', 'Booking')
                    ->render(fn($e) => "<span style='font-weight:600;color:#1e40af'>Booking #{$e->booking?->id}</span>"),

                TD::make('extra_minutes', 'Extra Minutes')
                    ->render(fn($e) => "<span style='font-weight:500;color:#16a34a'>{$e->extra_minutes} mins</span>"),

                TD::make('price', 'Price')
                    ->render(fn($e) => "<span style='font-weight:700;color:#065f46'>" . number_format($e->price, 2) . " EGP</span>"),

                TD::make('approved_by', 'Approved By')
                    ->render(fn($e) => "<span>" . ($e->approvedBy?->name ?? '—') . "</span>"),

                TD::make('notes', 'Notes')
                    ->render(fn($e) => "<span style='color:#6b7280'>{$e->notes}</span>"),

                TD::make('created_at', 'Created At')
                    ->render(fn($e) => "<span style='font-size:12px;color:#374151'>{$e->created_at->format('M d, Y H:i')}</span>"),
            ]),
        ];
    }
}