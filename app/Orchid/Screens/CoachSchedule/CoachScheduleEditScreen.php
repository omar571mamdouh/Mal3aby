<?php

namespace App\Orchid\Screens\CoachSchedule;

use App\Models\CoachSchedule;
use App\Models\Coach;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class CoachScheduleEditScreen extends Screen
{
    public $schedule;

    public function query(CoachSchedule $schedule): iterable
    {
        $this->schedule = $schedule;

        return [
            'schedule' => $schedule,
        ];
    }

    public function name(): ?string
    {
        return $this->schedule->exists
            ? 'Edit Coach Schedule'
            : 'Create Coach Schedule';
    }

    public function description(): ?string
    {
        return 'Define coach availability schedule (auto branch from coach)';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save Schedule')
                ->icon('bs.check-circle')
                ->method('save')
                ->type(Color::PRIMARY),

            Button::make('Remove')
                ->icon('bs.trash')
                ->confirm('Are you sure you want to delete this schedule?')
                ->method('remove')
                ->type(Color::DANGER)
                ->canSee($this->schedule->exists),
        ];
    }

    public function layout(): iterable
    {
        return [

            // ── Assignment ─────────────────────────────
            Layout::block(
                Layout::rows([
                    Select::make('schedule.coach_id')
                        ->title('Coach')
                        ->fromModel(Coach::class, 'name')
                        ->searchable()
                        ->required()
                        ->icon('bs.person-badge')
                        ->horizontal(),
                ])
            )
                ->title('Assignment')
                ->description('Select coach (branch is auto assigned).'),

            // ── Time Slot ──────────────────────────────
            Layout::block(
                Layout::rows([
                    Select::make('schedule.day_of_week')
                        ->title('Day of Week')
                        ->options([
                            0 => 'Sunday',
                            1 => 'Monday',
                            2 => 'Tuesday',
                            3 => 'Wednesday',
                            4 => 'Thursday',
                            5 => 'Friday',
                            6 => 'Saturday',
                        ])
                        ->required()
                        ->horizontal(),

                    Input::make('schedule.start_time')
                        ->title('Start Time')
                        ->type('time')
                        ->required()
                        ->horizontal(),

                    Input::make('schedule.end_time')
                        ->title('End Time')
                        ->type('time')
                        ->required()
                        ->horizontal(),
                ])
            )
                ->title('Time Slot'),

            // ── Availability ───────────────────────────
            Layout::block(
                Layout::rows([
                    CheckBox::make('schedule.is_available')
                        ->title('Available')
                        ->sendTrueOrFalse()
                        ->value(true)
                        ->horizontal(),

                    TextArea::make('schedule.notes')
                        ->title('Notes')
                        ->rows(3)
                        ->horizontal(),
                ])
            )
                ->title('Details'),
        ];
    }

    public function save(Request $request, CoachSchedule $schedule)
{
    // نظف الثواني من الوقت لو المتصفح بعتها
    $data = $request->all();
    foreach (['start_time', 'end_time'] as $field) {
        if (!empty($data['schedule'][$field])) {
            $data['schedule'][$field] = substr($data['schedule'][$field], 0, 5);
        }
    }
    $request->replace($data);

    $validated = $request->validate([
        'schedule.coach_id'     => 'required|exists:coaches,id',
        'schedule.day_of_week'  => 'required|integer|between:0,6',
        'schedule.start_time'   => 'required|date_format:H:i',
        'schedule.end_time'     => 'required|date_format:H:i|after:schedule.start_time',
        'schedule.is_available' => 'boolean',
        'schedule.notes'        => 'nullable|string',
    ]);

    // branch و club تلقائي من الـ coach
    $coach = Coach::findOrFail($validated['schedule']['coach_id']);
    $validated['schedule']['branch_id'] = $coach->branch_id;
    $validated['schedule']['club_id']   = $coach->club_id;

    $schedule->fill($validated['schedule'])->save();

    Toast::success('Coach schedule saved successfully.');

    return redirect()->route('platform.coach.schedules');
}

public function remove(CoachSchedule $schedule)
{
    $schedule->delete();

    Toast::info('Schedule deleted successfully.');

    return redirect()->route('platform.coach.schedules');
}
}