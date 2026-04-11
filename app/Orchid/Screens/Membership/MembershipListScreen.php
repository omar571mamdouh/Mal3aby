<?php

namespace App\Orchid\Screens\Membership;

use App\Models\Membership;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class MembershipListScreen extends Screen
{
    public function name(): string
    {
        return 'Membership Plans';
    }

    public function description(): string
    {
        return 'Manage all membership plans';
    }

    public function query(): iterable
    {
        return [
            'memberships' => Membership::latest()->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Membership')
                ->modal('createMembershipModal')
                ->method('create')
                ->icon('plus')
                ->type(Color::SUCCESS()),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('memberships', [

                // ── Name ──
                TD::make('name', 'Name')
                    ->render(fn($item) => "
                        <div style='display:flex;align-items:center;gap:10px;'>
                            <div style='
                                width:36px;height:36px;
                                border-radius:10px;
                                background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                display:flex;align-items:center;justify-content:center;
                                font-size:16px;flex-shrink:0;
                            '>🏅</div>
                            <div style='font-weight:600;color:#1e293b;font-size:14px;'>
                                " . e($item->name) . "
                            </div>
                        </div>
                    "),

                // ── Price ──
                TD::make('price', 'Price')
                    ->render(fn($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>💰</span>
                            <span style='color:#0f172a;font-size:13px;font-weight:600;'>" . number_format($item->price, 2) . "</span>
                            <span style='color:#94a3b8;font-size:12px;font-weight:500;'>EGP</span>
                        </div>
                    "),

                // ── Duration ──
                TD::make('duration', 'Duration')
                    ->render(function ($item) {
                        $types = [
                            'days'   => ['label' => 'Days',   'bg' => '#dbeafe', 'color' => '#1d4ed8', 'icon' => '📅'],
                            'months' => ['label' => 'Months', 'bg' => '#dcfce7', 'color' => '#15803d', 'icon' => '🗓️'],
                            'years'  => ['label' => 'Years',  'bg' => '#fef9c3', 'color' => '#a16207', 'icon' => '⏳'],
                        ];

                        $key = strtolower(trim($item->duration_type ?? ''));
                        $d   = $types[$key] ?? ['label' => $item->duration_type, 'bg' => '#f1f5f9', 'color' => '#475569', 'icon' => '⏱️'];

                        return "
                            <span style='
                                display:inline-flex;
                                align-items:center;
                                gap:5px;
                                background:{$d['bg']};
                                color:{$d['color']};
                                padding:5px 14px;
                                border-radius:20px;
                                font-size:13px;
                                font-weight:600;
                                white-space:nowrap;
                            '>{$d['icon']} {$item->duration_value} {$d['label']}</span>
                        ";
                    }),

                // ── Description ──
                TD::make('description', 'Description')
                    ->render(
                        fn($item) =>
                        $item->description
                            ? "<span style='color:#475569;font-size:13px;'>"
                            . e(\Illuminate\Support\Str::limit($item->description, 40))
                            . "</span>"
                            : "<span style='color:#cbd5e1;font-size:13px;'>—</span>"
                    ),

                // ── Status ──
                TD::make('is_active', 'Status')
                    ->align(TD::ALIGN_CENTER)
                    ->render(
                        fn($item) =>
                        $item->is_active
                            ? "<span style='display:inline-flex;align-items:center;gap:5px;background:#dcfce7;color:#15803d;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                <span style='width:6px;height:6px;background:#16a34a;border-radius:50%;display:inline-block;box-shadow:0 0 0 2px #bbf7d0;'></span>Active
                               </span>"
                            : "<span style='display:inline-flex;align-items:center;gap:5px;background:#fee2e2;color:#b91c1c;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                <span style='width:6px;height:6px;background:#dc2626;border-radius:50%;display:inline-block;'></span>Inactive
                               </span>"
                    ),

                // ── Created ──
                TD::make('created_at', 'Created')
                    ->render(fn($item) => "
                        <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                            <span style='font-size:13px;'>📆</span>
                            <span style='color:#0f172a;font-size:13px;font-weight:600;'>" . $item->created_at->format('Y-m-d') . "</span>
                        </div>
                    "),

                // ── Actions ──
                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(
                        fn($item) =>
                        '<div style="display:flex;gap:6px;justify-content:center;">'
                            . ModalToggle::make('Edit')
                            ->modal('editMembershipModal')
                            ->method('update')
                            ->asyncParameters(['membership' => $item->id])
                            ->icon('pencil')
                            ->class('btn btn-sm btn-primary')
                            . ' '
                            . Button::make('Delete')
                            ->confirm('Are you sure you want to delete this membership plan?')
                            ->method('delete', ['id' => $item->id])
                            ->icon('trash')
                            ->class('btn btn-sm btn-danger')
                            ->type(Color::DANGER())
                            . '</div>'
                    ),
            ]),

            // ── Create Modal ──
            Layout::modal('createMembershipModal', Layout::rows([
                Input::make('membership.name')
                    ->title('Name')
                    ->placeholder('e.g. Basic, Premium...')
                    ->required(),

                Input::make('membership.price')
                    ->title('Price (EGP)')
                    ->type('number')
                    ->placeholder('0.00')
                    ->required(),

                Select::make('membership.duration_type')
                    ->title('Duration Type')
                    ->options([
                        'days'   => 'Days',
                        'months' => 'Months',
                        'years'  => 'Years',
                    ])
                    ->required(),

                Input::make('membership.duration_value')
                    ->title('Duration Value')
                    ->type('number')
                    ->required(),

                TextArea::make('membership.description')
                    ->title('Description')
                    ->rows(3),

                Select::make('membership.is_active')
                    ->title('Status')
                    ->options([1 => 'Active', 0 => 'Inactive'])
                    ->required(),
            ]))
                ->title('Create Membership')
                ->applyButton('Create')
                ->closeButton('Cancel'),

            // ── Edit Modal ──
            Layout::modal('editMembershipModal', Layout::rows([
                Input::make('membership.name')->title('Name')->required(),

                Input::make('membership.price')
                    ->title('Price (EGP)')
                    ->type('number')
                    ->required(),

                Select::make('membership.duration_type')
                    ->title('Duration Type')
                    ->options([
                        'days'   => 'Days',
                        'months' => 'Months',
                        'years'  => 'Years',
                    ])
                    ->required(),

                Input::make('membership.duration_value')
                    ->title('Duration Value')
                    ->type('number')
                    ->required(),

                TextArea::make('membership.description')
                    ->title('Description')
                    ->rows(3),

                Select::make('membership.is_active')
                    ->title('Status')
                    ->options([1 => 'Active', 0 => 'Inactive'])
                    ->required(),
            ]))
                ->async('asyncGetMembership')
                ->title('Edit Membership')
                ->applyButton('Update')
                ->closeButton('Cancel'),
        ];
    }

    public function create(Request $request)
    {
        Membership::create($request->get('membership'));
        Toast::info('Membership created successfully.');
    }

    public function asyncGetMembership(Membership $membership): array
    {
        return [
            'membership' => $membership->only([
                'id',
                'name',
                'price',
                'duration_type',
                'duration_value',
                'description',
                'is_active'
            ])
        ];
    }

    public function update(Request $request)
    {
        Membership::findOrFail($request->get('membership')['id'])
            ->update($request->get('membership'));
        Toast::info('Membership updated successfully.');
    }

    public function delete(Request $request)
    {
        Membership::findOrFail($request->get('id'))->delete();
        Toast::warning('Membership deleted.');
    }
}
