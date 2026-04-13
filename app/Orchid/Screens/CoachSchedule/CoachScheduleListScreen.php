<?php

namespace App\Orchid\Screens\CoachSchedule;

use App\Models\CoachSchedule;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class CoachScheduleListScreen extends Screen
{
    public function name(): ?string
    {
        return 'Coach Schedules';
    }

    public function description(): ?string
    {
        return 'Manage all coach availability schedules across branches';
    }

    public function query(): iterable
    {
        $total       = CoachSchedule::count();
        $available   = CoachSchedule::where('is_available', true)->count();
        $unavailable = CoachSchedule::where('is_available', false)->count();
        $coaches     = CoachSchedule::distinct('coach_id')->count('coach_id');

        return [
            'schedules'              => CoachSchedule::with(['coach', 'branch'])->latest()->paginate(),
            'metrics.total'          => number_format($total),
            'metrics.available'      => number_format($available),
            'metrics.unavailable'    => number_format($unavailable),
            'metrics.coaches'        => number_format($coaches),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Schedule')
                ->icon('bs.plus-circle')
                ->route('platform.coach.schedules.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Schedules' => 'metrics.total',
                'Available'       => 'metrics.available',
                'Unavailable'     => 'metrics.unavailable',
                'Coaches Assigned'=> 'metrics.coaches',
            ]),

            Layout::table('schedules', [

                TD::make('coach', 'Coach')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(function (CoachSchedule $schedule) {
                        $name = e($schedule->coach?->name ?? '—');
                        $colors = ['#E6F1FB|#0C447C', '#E1F5EE|#085041', '#EEEDFE|#3C3489', '#FAECE7|#712B13', '#EAF3DE|#3B6D11'];
                        $pick   = $colors[($schedule->coach_id ?? 0) % count($colors)];
                        [$bg, $fg] = explode('|', $pick);
                        $initials = collect(explode(' ', $schedule->coach?->name ?? '?'))
                            ->map(fn($w) => strtoupper($w[0] ?? ''))
                            ->take(2)
                            ->implode('');

                        return "<div style=\"display:flex;align-items:center;gap:10px;\">
                            <div style=\"width:32px;height:32px;border-radius:50%;background:{$bg};color:{$fg};
                                display:inline-flex;align-items:center;justify-content:center;
                                font-size:11px;font-weight:600;flex-shrink:0;\">{$initials}</div>
                            <span style=\"font-weight:500;font-size:14px;\">{$name}</span>
                        </div>";
                    }),

                TD::make('branch', 'Branch')
                    ->sort()
                    ->render(function (CoachSchedule $schedule) {
                        $branch = e($schedule->branch?->name ?? '—');
                        return "<span style=\"
                            display:inline-block;padding:2px 10px;border-radius:6px;
                            font-size:12px;background:#F1EFE8;color:#5F5E5A;
                            border:0.5px solid #D3D1C7;
                        \">{$branch}</span>";
                    }),

                TD::make('day_of_week', 'Day')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        0 => 'Sunday',
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                    ])
                    ->render(function (CoachSchedule $schedule) {
                        $days = [
                            0 => ['Sunday',    '#EEEDFE', '#3C3489'],
                            1 => ['Monday',    '#E6F1FB', '#0C447C'],
                            2 => ['Tuesday',   '#E1F5EE', '#085041'],
                            3 => ['Wednesday', '#EAF3DE', '#3B6D11'],
                            4 => ['Thursday',  '#FAEEDA', '#633806'],
                            5 => ['Friday',    '#FAECE7', '#712B13'],
                            6 => ['Saturday',  '#FBEAF0', '#72243E'],
                        ];
                        [$label, $bg, $fg] = $days[$schedule->day_of_week] ?? ['—', '#F1EFE8', '#5F5E5A'];
                        return "<span style=\"
                            display:inline-block;padding:2px 10px;border-radius:999px;
                            font-size:12px;font-weight:500;background:{$bg};color:{$fg};
                        \">{$label}</span>";
                    }),

                TD::make('start_time', 'Time Slot')
                    ->sort()
                    ->render(function (CoachSchedule $schedule) {
                        $start = date('h:i A', strtotime($schedule->start_time));
                        $end   = date('h:i A', strtotime($schedule->end_time));
                        return "<div style=\"display:flex;align-items:center;gap:6px;font-size:13px;\">
                            <span style=\"color:#185FA5;font-weight:500;\">{$start}</span>
                            <span style=\"color:#bbb;font-size:11px;\">→</span>
                            <span style=\"color:#185FA5;font-weight:500;\">{$end}</span>
                        </div>";
                    }),

                TD::make('is_available', 'Availability')
                    ->filter(TD::FILTER_SELECT, [
                        1 => 'Available',
                        0 => 'Unavailable',
                    ])
                    ->render(function (CoachSchedule $schedule) {
                        if ($schedule->is_available) {
                            return '<span style="
                                display:inline-flex;align-items:center;gap:5px;
                                padding:3px 12px;border-radius:999px;
                                font-size:12px;font-weight:500;
                                background:#EAF3DE;color:#3B6D11;
                            ">
                                <span style="width:6px;height:6px;border-radius:50%;background:#639922;display:inline-block;"></span>
                                Available
                            </span>';
                        }
                        return '<span style="
                            display:inline-flex;align-items:center;gap:5px;
                            padding:3px 12px;border-radius:999px;
                            font-size:12px;font-weight:500;
                            background:#FCEBEB;color:#A32D2D;
                        ">
                            <span style="width:6px;height:6px;border-radius:50%;background:#E24B4A;display:inline-block;"></span>
                            Unavailable
                        </span>';
                    }),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (CoachSchedule $schedule) {
                        $edit = Link::make('')
                            ->icon('bs.pencil')
                            ->route('platform.coach.schedules.edit', $schedule)
                            ->render();

                        $delete = Button::make('')
                            ->icon('bs.trash')
                            ->confirm('Are you sure you want to delete this schedule?')
                            ->method('remove', ['id' => $schedule->id])
                            ->render();

                        return "<div style=\"display:flex;gap:6px;justify-content:center;\">{$edit}{$delete}</div>";
                    }),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request): void
    {
        CoachSchedule::findOrFail($request->get('id'))->delete();
        Toast::info('Schedule deleted successfully.');
    }

    private function getDayName(int $day): string
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ][$day] ?? '—';
    }
}