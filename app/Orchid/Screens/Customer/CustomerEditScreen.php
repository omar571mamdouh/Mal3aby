<?php

namespace App\Orchid\Screens\Customer;

use App\Models\Customer;
use App\Models\Club;
use App\Models\Branch;
use App\Models\Facility;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;

class CustomerEditScreen extends Screen
{
    public $customer;

    public function name(): string
    {
        return $this->customer?->exists ? '✏️ Edit Customer' : '➕ Add New Customer';
    }

    public function description(): string
    {
        return $this->customer?->exists
            ? 'Update details for ' . $this->customer->first_name . ' ' . $this->customer->last_name
            : 'Fill in the details to create a new customer';
    }

    public function query(Customer $customer): iterable
    {
        $this->customer = $customer;
        return [
            'customer' => $customer,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save Customer')
                ->icon('bs.save')
                ->class('btn btn-primary')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('customer.first_name')
                    ->title('👤 First Name')
                    ->placeholder('Enter first name')
                    ->required(),

                Input::make('customer.last_name')
                    ->title('👥 Last Name')
                    ->placeholder('Enter last name'),

                Input::make('customer.email')
                    ->title('✉️ Email Address')
                    ->type('email')
                    ->placeholder('example@email.com'),

                Input::make('customer.phone')
                    ->title('📞 Phone Number')
                    ->placeholder('+20 1xx xxx xxxx'),

                Select::make('customer.club_id')
                    ->fromModel(Club::class, 'name')
                    ->title('🛡️ Club')
                    ->empty('— Select Club —'),

                Select::make('customer.branch_id')
                    ->fromModel(Branch::class, 'name')
                    ->title('🌿 Branch')
                    ->empty('— Select Branch —'),

                Select::make('customer.facility_id')
                    ->fromModel(Facility::class, 'name')
                    ->title('📍 Facility')
                    ->empty('— Select Facility —'),

                Select::make('customer.status')
                    ->options([
                        'active'    => '✅ Active',
                        'inactive'  => '⏸️ Inactive',
                        'suspended' => '🚫 Suspended',
                    ])
                    ->title('🔖 Status'),
            ]),
        ];
    }

    public function save(Customer $customer, Request $request)
    {
        $customer->fill($request->get('customer'))->save();
        Toast::info('✅ Customer saved successfully!');
        return redirect()->route('platform.customer.list');
    }
}