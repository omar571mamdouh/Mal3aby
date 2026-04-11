<?php

namespace App\Orchid\Screens\MembershipFeature;

use App\Models\Membership;
use App\Models\MembershipFeature;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class MembershipFeatureScreen extends Screen
{
    public function name(): string
    {
        return 'Membership Features';
    }

    public function query(): iterable
    {
        return [
            'features' => MembershipFeature::with('membership')->latest()->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Feature')
                ->modal('createFeatureModal')
                ->method('create')
                ->icon('plus')
                ->type(Color::SUCCESS()),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('features', [

                // ── Membership ──
                TD::make('membership', 'Membership')
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
                                " . e($item->membership->name) . "
                            </div>
                        </div>
                    "),

                // ── Type ──
                TD::make('type', 'Type')
                    ->render(function ($item) {
                        $types = [
                            'free_hours' => ['label' => 'Free Hours', 'bg' => '#dbeafe', 'color' => '#1d4ed8', 'icon' => '⏱️'],
                            'discount'   => ['label' => 'Discount',   'bg' => '#dcfce7', 'color' => '#15803d', 'icon' => '🏷️'],
                            'priority'   => ['label' => 'Priority',   'bg' => '#fef9c3', 'color' => '#a16207', 'icon' => '⭐'],
                        ];

                        $t = $types[$item->type] ?? ['label' => $item->type, 'bg' => '#f1f5f9', 'color' => '#475569', 'icon' => '📌'];

                        return "
                            <span style='
                                display:inline-flex;align-items:center;gap:5px;
                                background:{$t['bg']};color:{$t['color']};
                                padding:5px 14px;border-radius:20px;
                                font-size:13px;font-weight:600;white-space:nowrap;
                            '>{$t['icon']} {$t['label']}</span>
                        ";
                    }),

                // ── Value ──
                TD::make('value', 'Value')
                    ->render(function ($item) {
                        [$text, $icon] = match ($item->type) {
                            'free_hours' => [$item->value . ' Hours', '🕐'],
                            'discount'   => [$item->value . '%',      '💸'],
                            'priority'   => ['Yes',                   '✅'],
                            default      => [$item->value,            '📋'],
                        };

                        return "
                            <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                                <span style='font-size:13px;'>{$icon}</span>
                                <span style='color:#0f172a;font-size:13px;font-weight:600;'>{$text}</span>
                            </div>
                        ";
                    }),

                // ── Actions ──
                TD::make('Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(
                        fn($item) =>
                        '<div style="display:flex;gap:6px;justify-content:center;">'
                            . ModalToggle::make('Edit')
                            ->modal('editFeatureModal')
                            ->method('update')
                            ->asyncParameters(['feature' => $item->id])
                            ->icon('pencil')
                            ->class('btn btn-sm btn-primary')
                            . ' '
                            . Button::make('Delete')
                            ->confirm('Are you sure you want to delete this feature?')
                            ->method('delete', ['id' => $item->id])
                            ->icon('trash')
                            ->class('btn btn-sm btn-danger')
                            ->type(Color::DANGER())
                            . '</div>'
                    ),
            ]),

            // Create Modal
            Layout::modal('createFeatureModal', Layout::rows([
                Select::make('feature.membership_id')
                    ->title('Membership')
                    ->fromModel(Membership::class, 'name')
                    ->required(),

                Select::make('feature.type')
                    ->title('Type')
                    ->options([
                        'free_hours' => 'Free Hours',
                        'discount'   => 'Discount',
                        'priority'   => 'Priority',
                    ])
                    ->required(),

                Input::make('feature.value')
                    ->title('Value')
                    ->type('number'),
            ]))->title('Create Feature')->applyButton('Create')->closeButton('Cancel'),

            // Edit Modal
            Layout::modal('editFeatureModal', Layout::rows([
                Select::make('feature.membership_id')
                    ->title('Membership')
                    ->fromModel(Membership::class, 'name')
                    ->required(),

                Select::make('feature.type')
                    ->title('Type')
                    ->options([
                        'free_hours' => 'Free Hours',
                        'discount'   => 'Discount',
                        'priority'   => 'Priority',
                    ])
                    ->required(),

                Input::make('feature.value')
                    ->title('Value')
                    ->type('number'),
            ]))
                ->async('asyncGetFeature')
                ->title('Edit Feature')
                ->applyButton('Update')
                ->closeButton('Cancel'),
        ];
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'feature.membership_id' => 'required|exists:memberships,id',
            'feature.type'          => 'required|in:free_hours,discount,priority',
            'feature.value'         => 'required|numeric|min:0',
        ])['feature'];

        try {
            MembershipFeature::create($data);

            Toast::info('Feature added successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            Toast::error('This feature already exists for this membership.');
        }
    }

    public function asyncGetFeature(MembershipFeature $feature): array
    {
        return [
            'feature' => $feature
        ];
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'feature.id'           => 'required|exists:membership_features,id',
            'feature.membership_id' => 'required|exists:memberships,id',
            'feature.type'         => 'required|in:free_hours,discount,priority',
            'feature.value'        => 'required|numeric|min:0',
        ])['feature'];

        $feature = MembershipFeature::findOrFail($data['id']);

        unset($data['id']);

        $feature->update($data);

        Toast::info('Feature updated successfully.');
    }
}
