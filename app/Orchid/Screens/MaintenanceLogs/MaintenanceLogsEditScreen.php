<?php

namespace App\Orchid\Screens\MaintenanceLogs;

use App\Models\MaintenanceLog;
use App\Models\Court;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;

class MaintenanceLogsEditScreen extends Screen
{
    public $log;

    public function query(MaintenanceLog $log): iterable
    {
        return [
            'log' => $log,
        ];
    }

    public function name(): ?string
    {
        return $this->log->exists
            ? 'Edit Maintenance Log'
            : 'Create Maintenance Log';
    }

    public function description(): ?string
    {
        return $this->log->exists
            ? 'Update the details of this maintenance record.'
            : 'Record a new maintenance event for a court.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Back to List')
                ->icon('bs.arrow-left')
                ->class('btn btn-outline-secondary')
                ->route('platform.maintenance-logs.list'),

            Button::make($this->log->exists ? 'Update Log' : 'Save Log')
                ->icon($this->log->exists ? 'bs.check-circle' : 'bs.plus-circle')
                ->class('btn btn-primary')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [

            // ── Card: Basic Info ──────────────────────────────────────────
            Layout::block(
                Layout::rows([

                    Relation::make('log.court_id')
                        ->title('Court')
                        ->help('Select the court this maintenance was performed on.')
                        ->fromModel(Court::class, 'name')
                        ->required(),

                    DateTimer::make('log.maintenance_date')
                        ->title('Maintenance Date')
                        ->help('The date the maintenance was carried out.')
                        ->format('Y-m-d')
                        ->allowInput()
                        ->required(),

                ])
            )
            ->title('Maintenance Details')
            ->description('Basic information about this maintenance event.'),

            // ── Card: Notes ───────────────────────────────────────────────
            Layout::block(
                Layout::rows([

                    TextArea::make('log.note')
                        ->title('Note')
                        ->placeholder('Describe what was done during this maintenance...')
                        ->help('Optional notes or details about the maintenance work.')
                        ->rows(5),

                ])
            )
            ->title('Notes')
            ->description('Any additional details or remarks about this maintenance.'),

        ];
    }

    public function save(Request $request, MaintenanceLog $log): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'log.court_id'         => ['required', 'exists:courts,id'],
            'log.maintenance_date' => ['required', 'date'],
            'log.note'             => ['nullable', 'string'],
        ]);

        $log->fill($data['log'])->save();

        Toast::info($log->wasRecentlyCreated
            ? 'Maintenance log created successfully.'
            : 'Maintenance log updated successfully.'
        );

        return redirect()->route('platform.maintenance-logs.list');
    }
}