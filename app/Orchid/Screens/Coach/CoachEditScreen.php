<?php

namespace App\Orchid\Screens\Coach;

use App\Models\Coach;
use App\Models\Club;
use App\Models\Branch;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class CoachEditScreen extends Screen
{
    public $coach;

    public function query(Coach $coach): iterable
    {
        return [
            'coach' => $coach,
        ];
    }

    public function name(): ?string
    {
        return $this->coach->exists ? 'Edit Coach' : 'Create Coach';
    }

    public function description(): ?string
    {
        return $this->coach->exists
            ? 'Update coach information, commission settings and status'
            : 'Fill in the details to register a new coach';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save Coach')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Remove')
                ->icon('bs.trash')
                ->confirm('Are you sure you want to permanently delete this coach?')
                ->method('remove')
                ->canSee($this->coach->exists),
        ];
    }

    public function layout(): iterable
    {
        return [

            // ── Section 1 : Basic information ──────────────────────────────
            Layout::block(
                Layout::rows([
                    Input::make('coach.name')
                        ->title('Full Name')
                        ->placeholder('e.g. Ahmed Mohamed')
                        ->icon('bs.person')
                        ->required()
                        ->horizontal(),

                    Input::make('coach.specialty')
                        ->title('Specialty')
                        ->placeholder('e.g. Strength & Conditioning, Yoga, Swimming…')
                        ->icon('bs.award')
                        ->horizontal(),

                    Input::make('coach.phone')
                        ->title('Phone')
                        ->placeholder('+20 1xx xxx xxxx')
                        ->icon('bs.telephone')
                        ->horizontal(),

                    Input::make('coach.email')
                        ->title('Email')
                        ->placeholder('coach@example.com')
                        ->icon('bs.envelope')
                        ->type('email')
                        ->horizontal(),

                    DateTimer::make('coach.hire_date')
                        ->title('Hire Date')
                        ->placeholder('Select hire date')
                        ->icon('bs.calendar3')
                        ->format('Y-m-d')
                        ->allowInput()
                        ->horizontal(),

                    TextArea::make('coach.bio')
                        ->title('Bio')
                        ->placeholder('Write a short bio — experience, achievements, coaching style…')
                        ->rows(4)
                        ->horizontal(),
                ])
            )
                ->title('Basic Information')
                ->description('Personal details and contact info for the coach.')
                ->commands(
                    Button::make('Save')
                        ->icon('bs.check-circle')
                        ->method('save')
                ),

            // ── Section 2 : Club & Branch ──────────────────────────────────
            Layout::block(
                Layout::rows([
                    Select::make('coach.club_id')
                        ->title('Club')
                        ->empty('— Select a club —')
                        ->fromModel(Club::class, 'name')
                        ->icon('bs.building')
                        ->required()
                        ->horizontal(),

                    Select::make('coach.branch_id')
                        ->title('Branch')
                        ->empty('— Select a branch —')
                        ->fromModel(Branch::class, 'name')
                        ->icon('bs.geo-alt')
                        ->required()
                        ->horizontal(),
                ])
            )
                ->title('Club & Branch')
                ->description('Assign the coach to a specific club and branch location.'),

            // ── Section 3 : Commission & Status ───────────────────────────
            Layout::block(
                Layout::rows([
                    Select::make('coach.commission_type')
                        ->title('Commission Type')
                        ->icon('bs.percent')
                        ->options([
                            'percentage' => '% Percentage of revenue',
                            'fixed'      => 'Fixed amount per session',
                        ])
                        ->required()
                        ->horizontal(),

                    Input::make('coach.commission_value')
                        ->title('Commission Value')
                        ->placeholder('e.g. 15  (for 15%)  or  250  (fixed)')
                        ->icon('bs.cash-coin')
                        ->type('number')
                        ->step('0.01')
                        ->min('0')
                        ->required()
                        ->horizontal(),

                    RadioButtons::make('coach.status')
                        ->title('Status')
                        ->options([
                            'active'   => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->required()
                        ->horizontal(),
                ])
            )
                ->title('Commission & Status')
                ->description('Define how this coach is compensated and their current status.'),
        ];
    }

    public function save(Request $request, Coach $coach): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'coach.club_id'          => 'required|exists:clubs,id',
            'coach.branch_id'        => 'required|exists:branches,id',
            'coach.name'             => 'required|string|max:255',
            'coach.phone'            => 'nullable|string|max:20',
            'coach.email'            => 'nullable|email|max:255',
            'coach.specialty'        => 'nullable|string|max:255',
            'coach.bio'              => 'nullable|string',
            'coach.hire_date'        => 'nullable|date',
            'coach.commission_type'  => 'required|in:percentage,fixed',
            'coach.commission_value' => 'required|numeric|min:0',
            'coach.status'           => 'required|in:active,inactive',
        ]);

        $coach->fill($validated['coach'])->save();

        Toast::info('Coach saved successfully.');

        return redirect()->route('platform.coaches');
    }

    public function remove(Coach $coach): \Illuminate\Http\RedirectResponse
    {
        $coach->delete();

        Toast::info('Coach deleted successfully.');

        return redirect()->route('platform.coaches');
    }
}