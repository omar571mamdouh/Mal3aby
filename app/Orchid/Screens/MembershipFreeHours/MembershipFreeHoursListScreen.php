<?php

namespace App\Orchid\Screens\MembershipFreeHours;

use App\Models\MembershipFreeHour;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;

class MembershipFreeHoursListScreen extends Screen
{
    public function name(): string
    {
        return 'Membership Free Hours';
    }

    public function description(): ?string
    {
        return 'Manage free hours for customer memberships';
    }

    public function query(): iterable
    {
        return [
            'freeHours' => MembershipFreeHour::with([
                'customerMembership.customer',
                'customerMembership.membership'
            ])->latest()->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
           
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('freeHours', [

                // ── Customer ──
                TD::make('customer', 'Customer')
                    ->render(fn ($item) => "
                        <div style='display:flex;align-items:center;gap:10px;'>
                            <div style='
                                width:36px;height:36px;border-radius:50%;
                                background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                display:flex;align-items:center;justify-content:center;
                                font-size:15px;flex-shrink:0;color:#fff;font-weight:600;
                            '>" . strtoupper(substr($item->customerMembership->customer->first_name ?? '?', 0, 1)) . "</div>
                            <div style='font-weight:600;color:#1e293b;font-size:14px;'>
                                " . e(($item->customerMembership->customer->first_name ?? '') . ' ' . ($item->customerMembership->customer->last_name ?? '')) . "
                            </div>
                        </div>
                    "),

                // ── Membership ──
                TD::make('membership', 'Membership')
                    ->render(fn ($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>🏅</span>
                            <span style='color:#0f172a;font-size:13px;font-weight:600;'>" . e($item->customerMembership->membership->name ?? '-') . "</span>
                        </div>
                    "),

                // ── Total Hours ──
                TD::make('total_hours', 'Total Hours')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn ($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>⏱️</span>
                            <span style='color:#0f172a;font-size:13px;font-weight:600;'>{$item->total_hours} hrs</span>
                        </div>
                    "),

                // ── Used Hours ──
                TD::make('used_hours', 'Used Hours')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn ($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#fff7ed;border:1px solid #fed7aa;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>🔄</span>
                            <span style='color:#c2410c;font-size:13px;font-weight:600;'>{$item->used_hours} hrs</span>
                        </div>
                    "),

                // ── Remaining Hours ──
                TD::make('remaining_hours', 'Remaining Hours')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn ($item) =>
                        $item->remaining_hours > 0
                            ? "<span style='display:inline-flex;align-items:center;gap:5px;background:#dcfce7;color:#15803d;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                <span style='width:6px;height:6px;background:#16a34a;border-radius:50%;display:inline-block;box-shadow:0 0 0 2px #bbf7d0;'></span>
                                {$item->remaining_hours} hrs
                               </span>"
                            : "<span style='display:inline-flex;align-items:center;gap:5px;background:#fee2e2;color:#b91c1c;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                <span style='width:6px;height:6px;background:#dc2626;border-radius:50%;display:inline-block;'></span>
                                No Hours Left
                               </span>"
                    ),

                // ── Last Update ──
                TD::make('updated_at', 'Last Update')
                    ->render(fn ($item) => "
                        <span style='color:#64748b;font-size:13px;'>
                            {$item->updated_at?->format('Y-m-d H:i')}
                        </span>
                    ")
                    ->sort(),

                // ── Actions ──
                TD::make('Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px')
                    ->render(fn ($item) =>
                        Link::make('Edit')
                            ->icon('pencil')
                            ->route('platform.membership.free-hours.edit', $item->id)
                            ->toString()
                    ),
            ]),
        ];
    }
}