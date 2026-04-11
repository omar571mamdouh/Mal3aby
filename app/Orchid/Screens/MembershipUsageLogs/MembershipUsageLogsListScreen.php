<?php

namespace App\Orchid\Screens\MembershipUsageLogs;

use App\Models\MembershipUsageLog;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;

class MembershipUsageLogsListScreen extends Screen
{
    public function name(): string
    {
        return 'Membership Usage Logs';
    }

    public function description(): ?string
    {
        return 'Track membership usage and discounts';
    }

    public function query(): iterable
    {
        return [
            'logs' => MembershipUsageLog::with([
                'customerMembership.customer',
                'customerMembership.membership',
                'booking'
            ])->latest()->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add New')
                ->icon('plus')
                ->route('platform.membership.usage-logs.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('logs', [

                // ── Customer ──
                TD::make('customer', 'Customer')
                    ->render(fn ($item) => "
                        <div style='display:flex;align-items:center;gap:10px;'>
                            <div style='
                                width:36px;height:36px;border-radius:50%;
                                background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                display:flex;align-items:center;justify-content:center;
                                font-size:15px;flex-shrink:0;color:#fff;font-weight:600;
                            '>" . strtoupper(substr($item->customerMembership?->customer?->first_name ?? '?', 0, 1)) . "</div>
                            <div style='font-weight:600;color:#1e293b;font-size:14px;'>
                                " . e(($item->customerMembership?->customer?->first_name ?? '') . ' ' . ($item->customerMembership?->customer?->last_name ?? '')) . "
                            </div>
                        </div>
                    "),

                // ── Membership ──
                TD::make('membership', 'Membership')
                    ->render(fn ($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>🏅</span>
                            <span style='color:#0f172a;font-size:13px;font-weight:600;'>" . e($item->customerMembership?->membership?->name ?? '-') . "</span>
                        </div>
                    "),

                // ── Booking ──
                TD::make('booking', 'Booking')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn ($item) =>
                        $item->booking_id
                            ? "<div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                                <span style='font-size:13px;'>📋</span>
                                <span style='color:#0f172a;font-size:13px;font-weight:600;'>#{$item->booking_id}</span>
                               </div>"
                            : "<span style='color:#cbd5e1;font-size:13px;'>—</span>"
                    ),

                // ── Used Hours ──
                TD::make('used_hours', 'Used Hours')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn ($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#fff7ed;border:1px solid #fed7aa;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>⏱️</span>
                            <span style='color:#c2410c;font-size:13px;font-weight:600;'>{$item->used_hours} hrs</span>
                        </div>
                    "),

                // ── Discount ──
                TD::make('discount_amount', 'Discount')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn ($item) =>
                        $item->discount_amount > 0
                            ? "<div style='display:inline-flex;align-items:center;gap:8px;background:#dcfce7;border:1px solid #bbf7d0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                                <span style='font-size:13px;'>💸</span>
                                <span style='color:#15803d;font-size:13px;font-weight:600;'>" . number_format($item->discount_amount, 2) . " EGP</span>
                               </div>"
                            : "<span style='color:#cbd5e1;font-size:13px;'>—</span>"
                    ),

                // ── Date ──
                TD::make('created_at', 'Date')
                    ->render(fn ($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>📆</span>
                            <span style='color:#0f172a;font-size:13px;font-weight:600;'>{$item->created_at->format('Y-m-d H:i')}</span>
                        </div>
                    ")
                    ->sort(),

                // ── Actions ──
                TD::make('Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px')
                    ->render(fn ($item) =>
                        Link::make('Edit')
                            ->icon('pencil')
                            ->route('platform.membership.usage-logs.edit', $item->id)
                            ->toString()
                    ),
            ]),
        ];
    }
}