<?php

namespace App\Orchid\Screens\MembershipUsageLogs;

use App\Models\MembershipUsageLog;
use App\Models\MembershipFreeHour;
use App\Models\CustomerMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;

class MembershipUsageLogsEditScreen extends Screen
{
    public MembershipUsageLog $log;

    public function query(MembershipUsageLog $log): iterable
    {
        $this->log = $log;
        return ['log' => $log];
    }

    public function name(): string
    {
        return $this->log->exists ? 'Edit Usage Log' : 'Create Usage Log';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('check')
                ->method('save'),

            Button::make('Delete')
                ->icon('trash')
                ->method('delete')
                ->canSee($this->log->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('log.customer_membership_id')
                    ->title('Customer Membership')
                    ->options(
                        CustomerMembership::with(['customer', 'membership'])
                            ->get()
                            ->mapWithKeys(fn($model) => [
                                $model->id =>
                                    optional($model->customer)->first_name . ' ' .
                                    optional($model->customer)->last_name .
                                    ' - ' .
                                    optional($model->membership)->name
                            ])
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),

                Input::make('log.booking_id')
                    ->type('number')
                    ->title('Booking ID (Optional)'),

                Input::make('log.used_hours')
                    ->type('number')
                    ->title('Used Hours')
                    ->required(),

                Input::make('log.discount_amount')
                    ->type('number')
                    ->step('0.01')
                    ->title('Discount Amount (EGP)')
                    ->value(0),
            ]),
        ];
    }

    public function save(Request $request, MembershipUsageLog $log)
    {
        $data = $request->validate([
            'log.customer_membership_id' => 'required|exists:customer_memberships,id',
            'log.booking_id'             => 'nullable|exists:bookings,id',
            'log.used_hours'             => 'required|numeric|min:0',
            'log.discount_amount'        => 'nullable|numeric|min:0',
        ])['log'];

        DB::transaction(function () use ($data, $log) {

            // ✅ لو تعديل: رجّع الساعات القديمة الأول
            $oldHours = $log->exists ? (float) $log->used_hours : 0;
            $newHours = (float) $data['used_hours'];

            $log->fill($data)->save();

            // ✅ حدّث used_hours في MembershipFreeHour
            $freeHour = MembershipFreeHour::where(
                'customer_membership_id', $data['customer_membership_id']
            )->first();

            if ($freeHour) {
                $diff = $newHours - $oldHours;
                $freeHour->used_hours = max(0, $freeHour->used_hours + $diff);
                $freeHour->save();
            }
        });

        Toast::info('Usage log saved successfully.');

        return redirect()->route('platform.membership.usage-logs.list');
    }

    public function delete(MembershipUsageLog $log)
    {
        DB::transaction(function () use ($log) {

            // ✅ رجّع الساعات لما تحذف اللوج
            $freeHour = MembershipFreeHour::where(
                'customer_membership_id', $log->customer_membership_id
            )->first();

            if ($freeHour) {
                $freeHour->used_hours = max(0, $freeHour->used_hours - $log->used_hours);
                $freeHour->save();
            }

            $log->delete();
        });

        Toast::warning('Usage log deleted.');

        return redirect()->route('platform.membership.usage-logs.list');
    }
}