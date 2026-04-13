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

class MembershipFreeHoursEditScreen extends Screen
{
    public MembershipFreeHour $freeHour;

    public function query(MembershipFreeHour $freeHour): iterable
    {
        $this->freeHour = $freeHour;
        return ['freeHour' => $freeHour];
    }

    public function name(): string
    {
        return $this->freeHour->exists ? 'Edit Free Hours' : 'Create Free Hours';
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

    // ✅ بيتعامل مع MembershipFreeHour بس — مش usage logs
    public function save(Request $request, MembershipFreeHour $freeHour)
    {
        $data = $request->validate([
            'freeHour.customer_membership_id' => 'required|exists:customer_memberships,id',
            'freeHour.total_hours'             => 'required|numeric|min:0',
            'freeHour.used_hours'              => 'required|numeric|min:0',
        ])['freeHour'];

        // ✅ تأكد إن used_hours مش أكبر من total_hours
        if ($data['used_hours'] > $data['total_hours']) {
            Toast::error('Used hours cannot exceed total hours.');
            return;
        }

        $freeHour->fill($data)->save();

        Toast::info('Free hours saved successfully.');

        return redirect()->route('platform.membership.free-hours.list');
    }

    public function delete(MembershipFreeHour $freeHour)
    {
        $freeHour->delete();

        Toast::warning('Free hours record deleted.');

        return redirect()->route('platform.membership.free-hours.list');
    }
}