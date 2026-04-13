<?php

namespace App\Orchid\Screens\Coach;

use App\Models\Coach;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class CoachListScreen extends Screen
{
    public function name(): ?string
    {
        return 'Coaches';
    }

    public function description(): ?string
    {
        return 'Manage all registered coaches across clubs and branches';
    }

    public function query(): iterable
    {
        $total    = Coach::count();
        $active   = Coach::where('status', 'active')->count();
        $inactive = Coach::where('status', 'inactive')->count();
        $clubs    = Coach::distinct('club_id')->count('club_id');

        return [
            'coaches'        => Coach::with(['club', 'branch'])->paginate(),
            'metrics.total'    => number_format($total),
            'metrics.active'   => number_format($active),
            'metrics.inactive' => number_format($inactive),
            'metrics.clubs'    => number_format($clubs),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Coach')
                ->icon('bs.plus-circle')
                ->route('platform.coaches.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Coaches'    => 'metrics.total',
                'Active'           => 'metrics.active',
                'Inactive'         => 'metrics.inactive',
                'Clubs Covered'    => 'metrics.clubs',
            ]),

            Layout::table('coaches', [

                TD::make('name', 'Coach')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(function (Coach $coach) {
                        $colors = ['#E6F1FB|#0C447C', '#E1F5EE|#085041', '#EEEDFE|#3C3489', '#FAECE7|#712B13', '#EAF3DE|#3B6D11'];
                        $pick   = $colors[$coach->id % count($colors)];
                        [$bg, $fg] = explode('|', $pick);
                        $initials = collect(explode(' ', $coach->name))
                            ->map(fn($w) => strtoupper($w[0] ?? ''))
                            ->take(2)
                            ->implode('');

                        $avatar = "<div style=\"
                            width:34px;height:34px;border-radius:50%;
                            background:{$bg};color:{$fg};
                            display:inline-flex;align-items:center;justify-content:center;
                            font-size:12px;font-weight:600;flex-shrink:0;
                        \">{$initials}</div>";

                        $nameHtml = Link::make($coach->name)
                            ->route('platform.coaches.edit', $coach)
                            ->render();

                        $email = e($coach->email ?? '');

                        return "<div style=\"display:flex;align-items:center;gap:10px;\">
                            {$avatar}
                            <div>
                                <div style=\"font-weight:500;font-size:14px;\">{$nameHtml}</div>
                                " . ($email ? "<div style=\"font-size:12px;color:#888;\">{$email}</div>" : '') . "
                            </div>
                        </div>";
                    }),

                TD::make('club_id', 'Club / Branch')
                    ->render(function (Coach $coach) {
                        $club   = e($coach->club?->name   ?? '—');
                        $branch = e($coach->branch?->name ?? '—');
                        return "<div style=\"font-size:13px;font-weight:500;\">{$club}</div>
                                <div style=\"font-size:12px;color:#888;\">{$branch}</div>";
                    }),

                TD::make('specialty', 'Specialty')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(function (Coach $coach) {
                        if (!$coach->specialty) {
                            return '<span style="color:#bbb;">—</span>';
                        }
                        $spec = e($coach->specialty);
                        return "<span style=\"
                            display:inline-block;
                            padding:2px 10px;
                            border-radius:6px;
                            font-size:12px;
                            background:#F1EFE8;
                            color:#5F5E5A;
                            border:0.5px solid #D3D1C7;
                        \">{$spec}</span>";
                    }),

                TD::make('commission_value', 'Commission')
                    ->render(function (Coach $coach) {
                        if ($coach->commission_type === 'percentage') {
                            $val = e($coach->commission_value);
                            return "<span style=\"color:#185FA5;font-weight:600;font-size:13px;\">{$val}%</span>";
                        }
                        $val = number_format($coach->commission_value, 2);
                        return "<span style=\"color:#3B6D11;font-weight:600;font-size:13px;\">{$val}</span>";
                    }),

                TD::make('status', 'Status')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        'active'   => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->render(function (Coach $coach) {
                        if ($coach->status === 'active') {
                            return '<span style="
                                display:inline-flex;align-items:center;gap:5px;
                                padding:3px 12px;border-radius:999px;
                                font-size:12px;font-weight:500;
                                background:#EAF3DE;color:#3B6D11;
                            ">
                                <span style="width:6px;height:6px;border-radius:50%;background:#639922;display:inline-block;"></span>
                                Active
                            </span>';
                        }
                        return '<span style="
                            display:inline-flex;align-items:center;gap:5px;
                            padding:3px 12px;border-radius:999px;
                            font-size:12px;font-weight:500;
                            background:#FCEBEB;color:#A32D2D;
                        ">
                            <span style="width:6px;height:6px;border-radius:50%;background:#E24B4A;display:inline-block;"></span>
                            Inactive
                        </span>';
                    }),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (Coach $coach) {
                        $edit = Link::make('')
                            ->icon('bs.pencil')
                            ->route('platform.coaches.edit', $coach)
                            ->render();

                        $delete = Button::make('')
                            ->icon('bs.trash')
                            ->confirm('Are you sure you want to delete this coach?')
                            ->method('remove', ['id' => $coach->id])
                            ->render();

                        return "<div style=\"display:flex;gap:6px;justify-content:center;\">{$edit}{$delete}</div>";
                    }),
            ]),
        ];
    }

    public function remove(\Illuminate\Http\Request $request): void
    {
        Coach::findOrFail($request->get('id'))->delete();
        Toast::info('Coach deleted successfully.');
    }
}