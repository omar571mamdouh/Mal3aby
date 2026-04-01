<?php

namespace App\Orchid\Screens\DynamicPricingRule;

use App\Models\DynamicPricingRule;
use App\Models\Court;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;

class DynamicPricingRuleEditScreen extends Screen
{
    public $rule;

    public function query(DynamicPricingRule $rule): iterable
    {
        return [
            'rule' => $rule
        ];
    }

    public function name(): ?string
    {
        return $this->rule->exists
            ? 'Edit Pricing Rule'
            : 'Create Pricing Rule';
    }

    public function description(): ?string
    {
        return $this->rule->exists
            ? 'Update the details of this dynamic pricing rule.'
            : 'Define a new dynamic pricing rule for a court.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Back to List')
                ->icon('bs.arrow-left')
                ->class('btn btn-outline-secondary')
                ->route('platform.dynamic-pricing.list'),

            Button::make($this->rule->exists ? 'Update Rule' : 'Create Rule')
                ->icon($this->rule->exists ? 'bs.check-circle' : 'bs.plus-circle')
                ->class('btn btn-primary')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [

            // ── Card: Basic Info ─────────────────────────────────────────
            Layout::block(
                Layout::rows([

                    Relation::make('rule.court_id')
                        ->title('Court')
                        ->help('Select the court this rule applies to.')
                        ->fromModel(Court::class, 'name')
                        ->required(),

                    Input::make('rule.rule_name')
                        ->title('Rule Name')
                        ->placeholder('e.g. Peak Hours Surcharge')
                        ->help('A short, descriptive name for this rule.')
                        ->required(),

                ])
            )
            ->title('Basic Information')
            ->description('General details about the pricing rule.'),

            // ── Card: Pricing Config ──────────────────────────────────────
            Layout::block(
                Layout::rows([

                    Select::make('rule.type')
                        ->options([
                            'percentage' => 'Percentage  (%)',
                            'fixed'      => 'Fixed Amount  (EGP)',
                        ])
                        ->title('Modifier Type')
                        ->help('Choose whether the modifier is a percentage or a fixed amount.')
                        ->empty('— Select type —')
                        ->required(),
Input::make('rule.modifier')
    ->title('Modifier Value')
    ->placeholder('e.g. 20 for 20%  or  50 for 50 EGP discount')
    ->help('For percentage, enter 20 = 20%. For fixed, this value will be subtracted from price.')
    ->type('number')
    ->step(0.01)
    ->required(),

                ])
            )
            ->title('Pricing Configuration')
            ->description('Set the modifier type and value for this rule.'),

        ];
    }

    public function save(Request $request, DynamicPricingRule $rule): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'rule.court_id'  => ['required', 'exists:courts,id'],
            'rule.rule_name' => ['required', 'string', 'max:255'],
            'rule.type'      => ['required', 'in:percentage,fixed'],
            'rule.modifier'  => ['required', 'numeric'],
        ]);

        $rule->fill($data['rule'])->save();

        Toast::info($rule->wasRecentlyCreated ? 'Rule created successfully.' : 'Rule updated successfully.');

        return redirect()->route('platform.dynamic-pricing.list');
    }
}