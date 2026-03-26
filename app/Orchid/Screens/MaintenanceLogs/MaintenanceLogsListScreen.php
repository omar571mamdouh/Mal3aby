<?php

namespace App\Orchid\Screens\MaintenanceLogs;

use App\Models\MaintenanceLog;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class MaintenanceLogsListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'logs' => MaintenanceLog::with('court')
                ->orderByDesc('maintenance_date')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Maintenance Logs';
    }

    public function description(): ?string
    {
        return 'Track and manage court maintenance records.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Log')
                ->icon('bs.plus-circle')
                ->class('btn btn-primary')
                ->route('platform.maintenance-logs.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('logs', [

                TD::make('id', '#')
                    ->width('60px')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (MaintenanceLog $log) =>
                        '<span class="text-muted small fw-bold">' . $log->id . '</span>'
                    ),

                TD::make('court', 'Court')
                    ->render(fn (MaintenanceLog $log) =>
                        '<div style="display:flex;align-items:center;gap:10px;">'
                        . '<div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">🏟️</div>'
                        . '<div style="font-weight:600;color:#1e293b;font-size:14px;">' . e($log->court?->name ?? '—') . '</div>'
                        . '</div>'
                    ),

                TD::make('maintenance_date', 'Date')
                    ->sort()
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (MaintenanceLog $log) =>
                        '<span class="d-flex align-items-center justify-content-center gap-1">'
                        . '<i data-bs-feather="calendar" style="width:13px;height:13px;color:#6366f1;"></i> '
                        . '<span class="fw-semibold">' . \Carbon\Carbon::parse($log->maintenance_date)->format('Y-m-d') . '</span>'
                        . '</span>'
                    ),

                TD::make('note', 'Note')
                    ->render(fn (MaintenanceLog $log) =>
                        $log->note
                            ? '<span class="d-flex align-items-center gap-1 text-muted">'
                              . '<i data-bs-feather="file-text" style="width:13px;height:13px;"></i> '
                              . e(\Illuminate\Support\Str::limit($log->note, 60))
                              . '</span>'
                            : '<span class="text-muted fst-italic small">No note</span>'
                    ),

                TD::make('created_at', 'Created At')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (MaintenanceLog $log) =>
                        '<span class="text-muted small">'
                        . ($log->created_at?->format('Y-m-d') ?? '—')
                        . '</span>'
                    ),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px')
                    ->render(function (MaintenanceLog $log) {
                        return
                            Link::make('Edit')
                                ->route('platform.maintenance-logs.edit', $log->id)
                                ->icon('bs.pencil-square')
                                ->class('btn btn-sm btn-outline-primary me-1')
                            . Button::make('Delete')
                                ->confirm('Are you sure you want to delete this log? This action cannot be undone.')
                                ->method('remove', ['id' => $log->id])
                                ->icon('bs.trash3')
                                ->class('btn btn-sm btn-outline-danger');
                    }),

            ]),
        ];
    }

    public function remove(int $id): void
    {
        MaintenanceLog::findOrFail($id)->delete();

        Toast::info('Maintenance log deleted successfully.');
    }
}