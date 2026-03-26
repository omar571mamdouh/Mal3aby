<?php

namespace App\Orchid\Screens\CustomerAddress;

use App\Models\CustomerAddress;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class CustomerAddressScreen extends Screen
{
    public $name = 'Customer Addresses';
    public $description = 'Manage addresses for customers';

    public function query(): iterable
    {
        return [
            'addresses' => CustomerAddress::with('customer')->paginate(15),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Address')
                ->icon('bs.plus-circle')
                ->class('btn btn-primary')
                ->route('platform.customer.address.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('addresses', [

                TD::make('customer', 'Customer')
                    ->render(fn(CustomerAddress $address) =>
                        '<div style="display:flex;align-items:center;gap:10px;">'
                        . '<div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">👤</div>'
                        . '<div style="font-weight:600;color:#1e293b;font-size:14px;">' . e($address->customer?->first_name . ' ' . $address->customer?->last_name ?? '—') . '</div>'
                        . '</div>'
                    ),

                TD::make('type', 'Type')
                    ->align(TD::ALIGN_CENTER)
                    ->render(function(CustomerAddress $address) {
                        [$bg, $color, $emoji] = match($address->type) {
                            'home'  => ['#dcfce7', '#15803d', '🏠'],
                            'work'  => ['#dbeafe', '#1d4ed8', '🏢'],
                            'other' => ['#f3e8ff', '#7e22ce', '📍'],
                            default => ['#f1f5f9', '#64748b', '🏷️'],
                        };
                        return '<span style="display:inline-flex;align-items:center;gap:5px;background:' . $bg . ';color:' . $color . ';padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">'
                            . $emoji . ' ' . ucfirst($address->type)
                            . '</span>';
                    }),

                TD::make('address_line1', 'Address Line 1')
                    ->render(fn(CustomerAddress $address) =>
                        '<span style="display:flex;align-items:center;gap:6px;">📌 ' . e($address->address_line1 ?? '—') . '</span>'
                    ),

                TD::make('address_line2', 'Address Line 2')
                    ->render(fn(CustomerAddress $address) =>
                        $address->address_line2
                            ? '<span style="display:flex;align-items:center;gap:6px;">📌 ' . e($address->address_line2) . '</span>'
                            : '<span style="color:#94a3b8;font-style:italic;">—</span>'
                    ),

                TD::make('city', 'City')
                    ->render(fn(CustomerAddress $address) =>
                        '<span style="display:flex;align-items:center;gap:6px;">🏙️ ' . e($address->city ?? '—') . '</span>'
                    ),

                TD::make('state', 'State')
                    ->render(fn(CustomerAddress $address) =>
                        '<span style="display:flex;align-items:center;gap:6px;">🗺️ ' . e($address->state ?? '—') . '</span>'
                    ),

                TD::make('postal_code', 'Postal Code')
                    ->render(fn(CustomerAddress $address) =>
                        '<span style="display:flex;align-items:center;gap:6px;">📮 ' . e($address->postal_code ?? '—') . '</span>'
                    ),

                TD::make('country', 'Country')
                    ->render(fn(CustomerAddress $address) =>
                        '<span style="display:flex;align-items:center;gap:6px;">🌍 ' . e($address->country ?? '—') . '</span>'
                    ),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(fn(CustomerAddress $address) =>
                        '<div style="display:flex;gap:6px;justify-content:center;">'
                        . Link::make('Edit')
                            ->route('platform.customer.address.edit', $address->id)
                            ->icon('bs.pencil-square')
                            ->class('btn btn-sm btn-primary')
                        . Button::make('Delete')
                            ->method('delete')
                            ->confirm('⚠️ Are you sure you want to delete this address?')
                            ->parameters(['id' => $address->id])
                            ->icon('bs.trash')
                            ->class('btn btn-sm btn-danger')
                        . '</div>'
                    ),
            ]),
        ];
    }

    public function delete(Request $request)
    {
        $address = CustomerAddress::findOrFail($request->get('id'));
        $address->delete();

        Toast::info('🗑️ Address deleted successfully!');
        return redirect()->route('platform.customer.address.list');
    }
}