<?php

namespace App\Orchid\Screens\Customer;

use App\Models\Customer;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class CustomerScreen extends Screen
{
    public $name = 'Customers';
    public $description = 'Manage your customers';

    public function query(): iterable
    {
        return [
            'customers' => Customer::with(['club', 'branch', 'facility'])->paginate(15),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Customer')
                ->icon('bs.plus-circle')
                ->class('btn btn-primary')
                ->route('platform.customer.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('customers', [

           TD::make('first_name', 'First Name')
    ->width('170px')
    ->render(fn (Customer $customer) =>
        '<div style="display:flex;align-items:center;gap:10px;">'
        . '<div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">👤</div>'
        . '<div style="font-weight:600;color:#1e293b;font-size:14px;white-space:nowrap;">' . e($customer->first_name) . '</div>'
        . '</div>'
    ),

TD::make('last_name', 'Last Name')
    ->width('140px')
    ->render(fn (Customer $customer) =>
        '<span style="display:flex;align-items:center;gap:6px;color:#64748b;white-space:nowrap;">👥 ' . e($customer->last_name ?? '—') . '</span>'
    ),

TD::make('email', 'Email')
    ->width('230px')
    ->render(fn (Customer $customer) =>
        $customer->email
            ? '<a href="mailto:' . e($customer->email) . '" style="display:flex;align-items:center;gap:6px;text-decoration:none;white-space:nowrap;">✉️ ' . e($customer->email) . '</a>'
            : '<span style="color:#94a3b8;font-style:italic;">—</span>'
    ),

TD::make('phone', 'Phone')
    ->width('150px')
    ->render(fn (Customer $customer) =>
        $customer->phone
            ? '<span style="display:flex;align-items:center;gap:6px;white-space:nowrap;">📞 ' . e($customer->phone) . '</span>'
            : '<span style="color:#94a3b8;font-style:italic;">—</span>'
    ),

TD::make('status', 'Status')
    ->width('120px')
    ->align(TD::ALIGN_CENTER)
    ->render(function (Customer $customer) {
        [$bg, $text, $emoji] = match ($customer->status) {
            'active'    => ['#dcfce7', '#16a34a', '✅'],
            'inactive'  => ['#f1f5f9', '#64748b', '⏸️'],
            'suspended' => ['#fee2e2', '#dc2626', '🚫'],
            default     => ['#f1f5f9', '#64748b', '⚪'],
        };
        return '<span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;background:' . $bg . ';color:' . $text . ';font-weight:600;font-size:12px;white-space:nowrap;">'
            . $emoji . ' ' . ucfirst($customer->status)
            . '</span>';
    }),

TD::make('club', 'Club')
    ->width('130px')
    ->render(fn (Customer $customer) =>
        $customer->club
            ? '<span style="display:flex;align-items:center;gap:6px;white-space:nowrap;">🛡️ ' . e($customer->club->name) . '</span>'
            : '<span style="color:#94a3b8;font-style:italic;">—</span>'
    ),

TD::make('branch', 'Branch')
    ->width('150px')
    ->render(fn (Customer $customer) =>
        $customer->branch
            ? '<span style="display:flex;align-items:center;gap:6px;white-space:nowrap;">🌿 ' . e($customer->branch->name) . '</span>'
            : '<span style="color:#94a3b8;font-style:italic;">—</span>'
    ),

TD::make('facility', 'Facility')
    ->width('180px')
    ->render(fn (Customer $customer) =>
        $customer->facility
            ? '<span style="display:flex;align-items:center;gap:6px;white-space:nowrap;">📍 ' . e($customer->facility->name) . '</span>'
            : '<span style="color:#94a3b8;font-style:italic;">—</span>'
    ),

TD::make('actions', 'Actions')
    ->align(TD::ALIGN_CENTER)
    ->width('120px')
    ->render(fn (Customer $customer) =>
        '<div style="display:flex;gap:6px;justify-content:center;">'
        . Link::make('Edit')
            ->route('platform.customer.edit', $customer->id)
            ->icon('bs.pencil-square')
            ->class('btn btn-sm btn-primary')
        . Button::make('Delete')
            ->method('delete')
            ->confirm('⚠️ Are you sure you want to delete this customer? This action cannot be undone.')
            ->parameters(['id' => $customer->id])
            ->icon('bs.trash')
            ->class('btn btn-sm btn-danger')
        . '</div>'
    ),

            ]),
        ];
    }

    public function delete(Request $request)
{
    $customer = Customer::findOrFail($request->get('id'));
    $customer->delete();

    Toast::info('🗑️ Customer deleted successfully!');
    return redirect()->route('platform.customer.list');
}
}

