<?php

namespace App\Orchid\Screens\CustomerAddress;

use App\Models\CustomerAddress;
use App\Models\Customer;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;

class CustomerAddressEditScreen extends Screen
{
    public $address;

    public function name(): string
    {
        return $this->address?->exists ? '✏️ Edit Address' : '➕ Add New Address';
    }

    public function description(): string
    {
        return $this->address?->exists
            ? 'Update address details'
            : 'Fill in the details to create a new address';
    }

    public function query(CustomerAddress $address): iterable
    {
        $this->address = $address;
        return [
            'address' => $address,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Delete')
                ->icon('bs.trash')
                ->class('btn btn-danger')
                ->method('delete')
                ->confirm('⚠️ Are you sure you want to delete this address?')
                ->canSee($this->address?->exists),

            Button::make('Save Address')
                ->icon('bs.save')
                ->class('btn btn-primary')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('address.customer_id')
                    ->fromModel(Customer::class, 'first_name')
                    ->title('👤 Customer')
                    ->empty('— Select Customer —')
                    ->required(),

                Select::make('address.type')
                    ->options([
                        'home'  => '🏠 Home',
                        'work'  => '🏢 Work',
                        'other' => '📍 Other',
                    ])
                    ->title('🏷️ Address Type')
                    ->required(),

                Input::make('address.address_line1')
                    ->title('📌 Address Line 1')
                    ->placeholder('Street address, P.O. box')
                    ->required(),

                Input::make('address.address_line2')
                    ->title('📌 Address Line 2')
                    ->placeholder('Apartment, suite, unit, building (optional)'),

                Input::make('address.city')
                    ->title('🏙️ City')
                    ->placeholder('Enter city'),

                Input::make('address.state')
                    ->title('🗺️ State')
                    ->placeholder('Enter state / governorate'),

                Input::make('address.postal_code')
                    ->title('📮 Postal Code')
                    ->placeholder('Enter postal code'),

                Input::make('address.country')
                    ->title('🌍 Country')
                    ->placeholder('Enter country'),
            ]),
        ];
    }

    public function save(CustomerAddress $address, Request $request)
    {
        $address->fill($request->get('address'))->save();
        Toast::info('✅ Address saved successfully!');
        return redirect()->route('platform.customer.address.list');
    }

    public function delete(CustomerAddress $address)
    {
        $address->delete();
        Toast::info('🗑️ Address deleted successfully!');
        return redirect()->route('platform.customer.address.list');
    }
}