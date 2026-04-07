<?php
namespace App\Orchid\Screens\BookingService;

use App\Models\BookingService;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;

class AllServicesScreen extends Screen
{
    public function name(): string
    {
        return 'All Booking Services';
    }

  // الأنظف على الإطلاق — استخدم property بدل استدعاء query() مرتين
private array $stats = [];

public function query(): iterable
{
    $this->stats = [
        'total_revenue' => number_format(BookingService::sum('price'), 0),
        'total_count'   => BookingService::count(),
        'avg_price'     => number_format((float) BookingService::avg('price'), 0),
    ];

    return [
        'services' => BookingService::with('booking')->latest()->paginate(15),
        ...$this->stats,
    ];
}

    public function layout(): iterable
    {
        $revenue = $this->query()['total_revenue'] ?? 0;
        $count   = $this->query()['total_count']   ?? 0;
        $avg     = $this->query()['avg_price']      ?? 0;

        $statsHtml = <<<HTML
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:1.25rem 0;">
            <div style="background:var(--bs-tertiary-bg);border-radius:8px;padding:1rem;">
                <div style="font-size:12px;color:var(--bs-secondary-color);margin-bottom:6px;">💰 Total Revenue</div>
                <div style="font-size:22px;font-weight:500;">{$revenue} <span style="font-size:13px;font-weight:400;color:var(--bs-secondary-color);">EGP</span></div>
            </div>
            <div style="background:var(--bs-tertiary-bg);border-radius:8px;padding:1rem;">
                <div style="font-size:12px;color:var(--bs-secondary-color);margin-bottom:6px;">📋 Total Services</div>
                <div style="font-size:22px;font-weight:500;">{$count}</div>
            </div>
            <div style="background:var(--bs-tertiary-bg);border-radius:8px;padding:1rem;">
                <div style="font-size:12px;color:var(--bs-secondary-color);margin-bottom:6px;">📊 Avg Price</div>
                <div style="font-size:22px;font-weight:500;">{$avg} <span style="font-size:13px;font-weight:400;color:var(--bs-secondary-color);">EGP</span></div>
            </div>
        </div>
        HTML;

        return [
          Layout::view('orchid.stats', ['statsHtml' => $statsHtml]),

            Layout::table('services', [

                TD::make('booking', 'Booking')
                    ->render(fn($s) =>
                        "<span style='display:inline-flex;align-items:center;gap:6px;background:#E6F1FB;color:#0C447C;font-size:12px;font-weight:500;padding:4px 10px;border-radius:99px;border:0.5px solid #B5D4F4;'>
                            <svg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round'><rect x='3' y='4' width='18' height='18' rx='2'/><path d='M16 2v4M8 2v4M3 10h18'/></svg>
                            #" . ($s->booking?->id ?? '—') . "
                        </span>"
                    ),

                TD::make('name', 'Service Name')
                    ->render(function ($s) {
                        static $index = 0;
                        $colors = ['#1D9E75','#378ADD','#7F77DD','#D85A30','#BA7517'];
                        $color  = $colors[$index % count($colors)];
                        $index++;
                        return "<div style='display:flex;align-items:center;gap:8px;'>
                                    <span style='width:7px;height:7px;border-radius:50%;background:{$color};flex-shrink:0;display:inline-block;'></span>
                                    <span style='font-size:13px;'>{$s->name}</span>
                                </div>";
                    }),

                TD::make('price', 'Price')
                    ->render(fn($s) =>
                        "<span style='display:inline-flex;align-items:center;gap:5px;background:#E1F5EE;color:#0F6E56;font-size:12px;font-weight:500;padding:4px 10px;border-radius:8px;'>
                            " . number_format($s->price, 2) . " EGP
                        </span>"
                    ),

                TD::make('quantity', 'Qty')
                    ->render(fn($s) =>
                        "<span style='display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#FAEEDA;color:#854F0B;font-size:13px;font-weight:500;border:0.5px solid #FAC775;'>
                            {$s->quantity}
                        </span>"
                    ),

                TD::make('notes', 'Notes')
                    ->width('200px')
                    ->render(fn($s) =>
                        "<span style='color:var(--bs-secondary-color);font-size:12px;font-style:italic;'>
                            " . ($s->notes ?: '—') . "
                        </span>"
                    ),

                TD::make('created_at', 'Created At')
                    ->render(fn($s) =>
                        "<div style='display:flex;align-items:center;gap:6px;color:var(--bs-secondary-color);font-size:12px;'>
                            <svg width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round'><circle cx='12' cy='12' r='10'/><path d='M12 6v6l4 2'/></svg>
                            " . $s->created_at->format('M d, Y H:i') . "
                        </div>"
                    ),
            ]),
        ];
    }
}