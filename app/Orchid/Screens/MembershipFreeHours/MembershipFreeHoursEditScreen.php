<?php

namespace App\Orchid\Screens\MembershipFreeHours;

use App\Models\MembershipFreeHour;
use App\Models\CustomerMembership;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use App\Models\MembershipUsageLog;
use Illuminate\Support\Facades\DB;

class MembershipFreeHoursEditScreen extends Screen
{
    public $freeHour;

    public function query(MembershipFreeHour $freeHour): iterable
    {
        // تعيين الخاصية لاستخدامها في باقي الدوال
        $this->freeHour = $freeHour;

        return [
            'freeHour' => $freeHour,
        ];
    }

    public function name(): string
    {
        return $this->freeHour->exists
            ? 'Edit Membership Free Hours'
            : 'Create Membership Free Hours';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('check')
                ->method('save'),

            Button::make('Remove')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->freeHour->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('freeHour.customer_membership_id')
                    ->title('Customer Membership')
                    ->options(
                        CustomerMembership::with('customer', 'membership')
                            ->get()
                            ->mapWithKeys(function ($model) {
                                return [
                                    $model->id => $model->customer->name . ' - ' . $model->membership->name
                                ];
                            })
                            ->toArray()
                    )
                    ->required(),

                Input::make('freeHour.total_hours')
                    ->type('number')
                    ->title('Total Hours')
                    ->required(),

                Input::make('freeHour.used_hours')
                    ->type('number')
                    ->title('Used Hours')
                    ->value(0),
            ]),
        ];
    }

   public function save(Request $request, MembershipUsageLog $log)
{
    $data = $request->validate([
        'log.customer_membership_id' => 'required|exists:customer_memberships,id',
        'log.booking_id'             => 'nullable|exists:bookings,id',
        'log.used_hours'             => 'required|integer|min:0',
        'log.discount_amount'        => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($data, $log) {
        $oldHours = $log->exists ? $log->used_hours : 0; // لو edit نرجع القديم الأول

        $log->fill($data['log'])->save();

        // ── تحديث MembershipFreeHour تلقائياً ──
        $freeHour = MembershipFreeHour::where(
            'customer_membership_id', $data['log']['customer_membership_id']
        )->first();

        if ($freeHour) {
            $freeHour->increment('used_hours', $data['log']['used_hours'] - $oldHours);
        }
    });

    Toast::info('Usage log saved successfully');

    return redirect()->route('platform.membership.usage-logs.list');
}

   public function delete(MembershipUsageLog $log)
{
    DB::transaction(function () use ($log) {
        // ── رجّع الساعات لما تحذف الـ log ──
        $freeHour = MembershipFreeHour::where(
            'customer_membership_id', $log->customer_membership_id
        )->first();

        if ($freeHour) {
            $freeHour->decrement('used_hours', $log->used_hours);
        }

        $log->delete();
    });

    Toast::info('Deleted successfully');

    return redirect()->route('platform.membership.usage-logs.list');
}
}
