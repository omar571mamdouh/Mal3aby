<?php

namespace App\Orchid\Screens\CustomerTag;

use App\Models\CustomerTag;
use App\Models\Customer;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class CustomerTagScreen extends Screen
{
    public $name = 'Customer Tags';
    public $description = 'Loyalty tags assigned to customers';

    public function query(): iterable
    {
        $customers = Customer::withCount('bookings')->get()->keyBy('id');

        $tags = CustomerTag::with('customer')->paginate(15);

        $tags->getCollection()->transform(function ($tag) use ($customers) {
            if ($tag->customer) {
                $tag->customer->bookings_count = $customers[$tag->customer->id]->bookings_count ?? 0;
            }
            return $tag;
        });

        return [
            'tags' => $tags,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Delete Recent Tags')
                ->icon('bs.trash')
                ->class('btn btn-outline-danger')
                ->confirm('Are you sure you want to delete the 15 most recent tags?')
                ->method('deleteRecent'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('tags', [

                TD::make('customer', '👤 Customer')
                    ->width('280px')
                    ->render(function (CustomerTag $tag) {
                        $name     = trim(($tag->customer?->first_name ?? '') . ' ' . ($tag->customer?->last_name ?? '')) ?: '—';
                        $initials = strtoupper(substr($tag->customer?->first_name ?? '?', 0, 1) . substr($tag->customer?->last_name ?? '', 0, 1));

                        return "
                            <div style='display:flex;align-items:center;gap:11px;'>
                                <div style='width:36px;height:36px;border-radius:50%;
                                            background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                            color:#fff;font-size:13px;font-weight:700;
                                            display:flex;align-items:center;justify-content:center;flex-shrink:0;'>
                                    {$initials}
                                </div>
                                <div>
                                    <div style='font-weight:600;font-size:13.5px;color:#1e1b4b;'>{$name}</div>
                                    <div style='font-size:11px;color:#9ca3af;margin-top:1px;'>Customer</div>
                                </div>
                            </div>";
                    }),

                TD::make('tag', '🏷️ Loyalty Tag')
                    ->width('160px')
                    ->render(function (CustomerTag $tag) {
                        $map = [
                            'new'     => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'label' => 'New'],
                            'bronze'  => ['bg' => '#fdf4e7', 'color' => '#92400e', 'border' => '#fcd9a0', 'label' => 'Bronze'],
                            'silver'  => ['bg' => '#f8fafc', 'color' => '#475569', 'border' => '#94a3b8', 'label' => 'Silver'],
                            'gold'    => ['bg' => '#fefce8', 'color' => '#854d0e', 'border' => '#fde047', 'label' => 'Gold'],
                            'plat'    => ['bg' => '#f0fdfa', 'color' => '#115e59', 'border' => '#99f6e4', 'label' => 'Platinum'],
                            'diamond' => ['bg' => '#eff6ff', 'color' => '#1e40af', 'border' => '#bfdbfe', 'label' => 'Diamond'],
                            'vip'     => ['bg' => '#fff1f2', 'color' => '#9f1239', 'border' => '#fda4af', 'label' => 'VIP'],
                        ];

                        $s     = strtolower($tag->tag);
                        $style = $map[$s] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'border' => '#e5e7eb', 'label' => ucfirst($s)];

                        $icons = [
                            'new'     => '🌱',
                            'bronze'  => '🥉',
                            'silver'  => '🥈',
                            'gold'    => '🥇',
                            'plat'    => '💎',
                            'diamond' => '💠',
                            'vip'     => '👑',
                        ];
                        $icon = $icons[$s] ?? '🏷️';

                        return "
                            <span style='display:inline-flex;align-items:center;gap:5px;
                                         background:{$style['bg']};color:{$style['color']};
                                         border:1px solid {$style['border']};
                                         font-size:12px;font-weight:700;padding:4px 12px;
                                         border-radius:20px;letter-spacing:.02em;'>
                                {$icon} {$style['label']}
                            </span>";
                    }),

                TD::make('booking_count', '📊 Total Bookings')
                    ->width('150px')
                    ->render(function (CustomerTag $tag) {
                        $count = $tag->customer?->bookings_count ?? 0;

                        $color = match(true) {
                            $count >= 20 => ['bg' => '#dcfce7', 'color' => '#15803d', 'border' => '#86efac'],
                            $count >= 10 => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'border' => '#93c5fd'],
                            $count >= 5  => ['bg' => '#fef9c3', 'color' => '#854d0e', 'border' => '#fde047'],
                            default      => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'border' => '#e5e7eb'],
                        };

                        return "
                            <span style='display:inline-flex;align-items:center;gap:5px;
                                         background:{$color['bg']};color:{$color['color']};
                                         border:1px solid {$color['border']};
                                         font-size:12px;font-weight:700;padding:4px 12px;
                                         border-radius:20px;'>
                                {$count} bookings
                            </span>";
                    }),
            ]),
        ];
    }

    private function tagColor(string $tag): string
    {
        return match($tag) {
            'new'     => '#e5e7eb',
            'bronze'  => '#cd7f32',
            'silver'  => '#c0c0c0',
            'gold'    => '#ffd700',
            'plat'    => '#e5e4e2',
            'diamond' => '#b9f2ff',
            'vip'     => '#ff4500',
            default   => '#f3f4f6',
        };
    }

    public function deleteRecent()
    {
        CustomerTag::latest()->take(15)->get()->each->delete();

        Toast::info('Recent tags deleted successfully.');

        return redirect()->route('platform.customer.tags.list');
    }
}