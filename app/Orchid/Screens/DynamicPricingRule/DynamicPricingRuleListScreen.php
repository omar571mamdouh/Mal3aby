<?php

namespace App\Orchid\Screens\DynamicPricingRule;

use App\Models\DynamicPricingRule;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class DynamicPricingRuleListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'rules' => DynamicPricingRule::with('court')->paginate()
        ];
    }

    public function name(): ?string
    {
        return 'Dynamic Pricing Rules';
    }

    public function description(): ?string
    {
        return 'Manage all dynamic pricing rules for courts.';
    }

    public function permission(): ?iterable
    {
        return [];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add New Rule')
                ->icon('bs.plus-circle')
                ->class('btn btn-primary')
                ->route('platform.dynamic-pricing.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('rules', [

                TD::make('court', 'Court')
                    ->render(fn (DynamicPricingRule $r) =>
                        '<div style="display:flex;align-items:center;gap:10px;">'
                        . '<div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">🏟️</div>'
                        . '<div style="font-weight:600;color:#1e293b;font-size:14px;">' . e($r->court?->name ?? '—') . '</div>'
                        . '</div>'
                    ),

                TD::make('rule_name', 'Rule Name')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn (DynamicPricingRule $r) =>
                        '<span class="d-flex align-items-center gap-1">'
                        . '<i class="text-secondary" data-bs-feather="tag" style="width:14px;height:14px;"></i> '
                        . '<strong>' . e($r->rule_name) . '</strong>'
                        . '</span>'
                    ),

                TD::make('type', 'Type')
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (DynamicPricingRule $r) {
                        return $r->type === 'percentage'
                            ? '<span class="badge rounded-pill bg-info text-white px-3 py-1">'
                              . '<i class="me-1" data-bs-feather="percent" style="width:11px;height:11px;"></i>Percentage</span>'
                            : '<span class="badge rounded-pill bg-warning text-dark px-3 py-1">'
                              . '<i class="me-1" data-bs-feather="dollar-sign" style="width:11px;height:11px;"></i>Fixed</span>';
                    }),
TD::make('modifier', 'Value')
    ->align(TD::ALIGN_CENTER)
    ->render(function (DynamicPricingRule $r) {
        if ($r->type === 'percentage') {
            // بدل ضربه في 100، اعرضه مباشرة مع %
            $val = $r->modifier . '%';
            return '<span class="badge bg-info bg-opacity-10 text-info fw-semibold px-2 py-1">' . $val . '</span>';
        }
        // لو Fixed
        $val = number_format($r->modifier, 2) . ' EGP';
        return '<span class="badge bg-warning bg-opacity-10 text-warning fw-semibold px-2 py-1">' . $val . '</span>';
    }),

                TD::make('created_at', 'Created At')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (DynamicPricingRule $r) =>
                        '<span class="d-flex align-items-center justify-content-center gap-1 text-muted small">'
                        . '<i data-bs-feather="calendar" style="width:13px;height:13px;"></i> '
                        . ($r->created_at?->format('Y-m-d') ?? '—')
                        . '</span>'
                    ),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px')
                    ->render(function (DynamicPricingRule $r) {
                        return
                            Link::make('Edit')
                                ->route('platform.dynamic-pricing.edit', $r->id)
                                ->icon('bs.pencil-square')
                                ->class('btn btn-sm btn-outline-primary me-1')
                            . Button::make('Delete')
                                ->confirm('Are you sure you want to delete this rule? This action cannot be undone.')
                                ->method('remove', ['id' => $r->id])
                                ->icon('bs.trash3')
                                ->class('btn btn-sm btn-outline-danger');
                    }),

            ])
        ];
    }

    public function remove(int $id): void
    {
        DynamicPricingRule::findOrFail($id)->delete();

        Toast::info('Rule deleted successfully.');
    }
}