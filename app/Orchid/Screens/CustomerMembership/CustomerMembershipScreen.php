<?php

namespace App\Orchid\Screens\CustomerMembership;

use App\Models\Customer;
use App\Models\Membership;
use App\Models\CustomerMembership;
use App\Models\MembershipFreeHour;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class CustomerMembershipScreen extends Screen
{
    public function name(): string
    {
        return 'Customer Memberships';
    }

    public function query(): iterable
    {
        return [
            'customerMemberships' => CustomerMembership::with(['customer', 'membership', 'freeHours'])
                ->latest()
                ->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Assign Membership')
                ->modal('createCustomerMembershipModal')
                ->method('create')
                ->icon('plus')
                ->type(Color::SUCCESS()),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('customerMemberships', [

                // ── Customer ──
                TD::make('customer', 'Customer')
                    ->render(fn($item) => "
                        <div style='display:flex;align-items:center;gap:10px;'>
                            <div style='width:36px;height:36px;border-radius:50%;
                                background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                display:flex;align-items:center;justify-content:center;
                                color:#fff;font-weight:600;'>
                                " . strtoupper(substr($item->customer->first_name, 0, 1)) . "
                            </div>
                            <div style='font-weight:600;color:#1e293b;'>
                                " . e($item->customer->first_name . ' ' . $item->customer->last_name) . "
                            </div>
                        </div>
                    "),

                // ── Membership ──
                TD::make('membership', 'Membership')
                    ->render(fn($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;
                            background:#f8fafc;border:1px solid #e2e8f0;
                            padding:6px 14px;border-radius:10px;'>
                            <span>🏅</span>
                            <span style='font-weight:600;'>
                                " . e($item->membership->name) . "
                            </span>
                        </div>
                    "),

                // ── Duration ──
                TD::make('duration', 'Duration')
                    ->render(fn($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;
                            background:#f8fafc;border:1px solid #e2e8f0;
                            padding:6px 14px;border-radius:10px;'>
                            <span>📅</span>
                            <span>{$item->start_date}</span>
                            <span style='color:#94a3b8;'>→</span>
                            <span>{$item->end_date}</span>
                        </div>
                    "),

                // ── Free Hours ──
                TD::make('free_hours', 'Free Hours')
                    ->align(TD::ALIGN_CENTER)
                    ->render(function ($item) {
                        if (!$item->freeHours) {
                            return "<span style='color:#cbd5e1;'>—</span>";
                        }

                        $remaining = $item->freeHours->total_hours - $item->freeHours->used_hours;

                        return "
                            <div style='display:inline-flex;align-items:center;gap:8px;
                                background:#f8fafc;border:1px solid #e2e8f0;
                                padding:6px 14px;border-radius:10px;'>
                                <span>⏱️</span>
                                <span>{$remaining} hrs</span>
                            </div>
                        ";
                    }),

                // ── Status ──
                TD::make('status', 'Status')
                    ->align(TD::ALIGN_CENTER)
                    ->render(function ($item) {

                        $isActive =
                            $item->status === 'active'
                            && now()->between($item->start_date, $item->end_date);

                        return $isActive
                            ? "<span style='background:#dcfce7;color:#15803d;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                🟢 Active
                               </span>"
                            : "<span style='background:#fee2e2;color:#b91c1c;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                🔴 Expired
                               </span>";
                    }),

                // ── Actions ──
                TD::make('actions')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn($item) =>
                        Button::make('Cancel')
                            ->confirm('Are you sure you want to cancel this membership?')
                            ->method('cancel', ['id' => $item->id])
                            ->icon('ban')
                            ->class('btn btn-sm btn-danger')
                            ->type(Color::DANGER())
                    ),
            ]),

            // ── Create Modal ──
            Layout::modal('createCustomerMembershipModal', Layout::rows([

                Select::make('data.customer_id')
                    ->title('Customer')
                    ->options(
                        Customer::query()
                            ->selectRaw("CONCAT(first_name,' ',IFNULL(last_name,'')) as name, id")
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                Select::make('data.membership_id')
                    ->title('Membership')
                    ->options(Membership::pluck('name', 'id'))
                    ->required(),

                DateTimer::make('data.start_date')
                    ->title('Start Date')
                    ->required(),
            ]))
                ->title('Assign Membership')
                ->applyButton('Assign')
                ->closeButton('Cancel'),
        ];
    }

    // ───────────────────────── CREATE ─────────────────────────
    public function create(Request $request)
    {
        $data = $request->validate([
            'data.customer_id'   => 'required|exists:customers,id',
            'data.membership_id' => 'required|exists:memberships,id',
            'data.start_date'    => 'required|date',
        ])['data'];

        $membership = Membership::with('features')->findOrFail($data['membership_id']);

        $start = Carbon::parse($data['start_date']);

        $end = match ($membership->duration_type) {
            'days'   => $start->copy()->addDays($membership->duration_value),
            'months' => $start->copy()->addMonths($membership->duration_value),
            'years'  => $start->copy()->addYears($membership->duration_value),
        };

        // ✅ always active on creation
        $customerMembership = CustomerMembership::create([
            'customer_id'   => $data['customer_id'],
            'membership_id' => $membership->id,
            'start_date'    => $start,
            'end_date'      => $end,
            'status'        => 'active',
        ]);

        // Free hours feature
        $freeHours = $membership->features()
            ->where('type', 'free_hours')
            ->first();

        if ($freeHours) {
            MembershipFreeHour::create([
                'customer_membership_id' => $customerMembership->id,
                'total_hours'            => (float) $freeHours->value,
                'used_hours'             => 0,
            ]);
        }

        Toast::info('Membership assigned successfully.');
    }

    // ───────────────────────── CANCEL ─────────────────────────
    public function cancel(Request $request)
    {
        $cm = CustomerMembership::findOrFail($request->get('id'));

        $cm->update([
            'status' => 'cancelled',
        ]);

        if ($cm->freeHours) {
            $cm->freeHours->update([
                'used_hours' => $cm->freeHours->total_hours,
            ]);
        }

        Toast::warning('Membership cancelled.');
    }
}