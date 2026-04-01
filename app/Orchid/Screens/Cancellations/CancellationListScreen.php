<?php

namespace App\Orchid\Screens\Cancellations;

use App\Models\Cancellation;
use App\Models\Court;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;

class CancellationListScreen extends Screen
{
    public function query(): iterable
    {
        $courtId   = request('court_id');
        $dateStart = request('date_start');
        $dateEnd   = request('date_end');

        $query = Cancellation::with('booking.customer', 'booking.court')
            ->latest('cancelled_at');

        if ($courtId) {
            $query->whereHas('booking', fn($q) => $q->where('court_id', $courtId));
        }

        if ($dateStart) {
            $query->whereDate('cancelled_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->whereDate('cancelled_at', '<=', $dateEnd);
        }

        return [
            'cancellations' => $query->paginate(15),
            'total'         => Cancellation::count(),
            'this_month'    => Cancellation::whereMonth('cancelled_at', now()->month)->count(),
            'today'         => Cancellation::whereDate('cancelled_at', today())->count(),
        ];
    }

    public function name(): ?string
    {
        return 'Cancellations';
    }

    public function description(): ?string
    {
        return 'Automatically tracked booking cancellations';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $courts = Court::orderBy('name')->pluck('name', 'id')->toArray();

        return [

            // ── Stats Cards ───────────────────────────────────────
            Layout::view('orchid.cancellation-stats'),

            // ── Filters ───────────────────────────────────────────
            Layout::rows([
                Select::make('court_id')
                    ->title('🏟️ Court')
                    ->options($courts)
                    ->empty('— All Courts —')
                    ->value(request('court_id'))
                    ->horizontal(),

                Input::make('date_start')
                    ->type('date')
                    ->title('📅 From')
                    ->value(request('date_start'))
                    ->horizontal(),

                Input::make('date_end')
                    ->type('date')
                    ->title('📅 To')
                    ->value(request('date_end'))
                    ->horizontal(),

                Button::make('Apply Filter')
                    ->icon('bs.funnel')
                    ->method('applyFilter')
                    ->class('btn btn-primary'),

                Button::make('Reset')
                    ->icon('bs.x')
                    ->method('resetFilter')
                    ->class('btn btn-outline-secondary'),
            ]),

            // ── Table ─────────────────────────────────────────────
            Layout::table('cancellations', [

                TD::make('customer', '👤 Customer')
                    ->width('220px')
                    ->render(function (Cancellation $c) {
                        $name     = $c->booking?->customer?->first_name ?? '—';
                        $initials = strtoupper(substr($name, 0, 2));
                        $id       = $c->booking?->id ?? '—';

                        return "
                            <div style='display:flex;align-items:center;gap:10px'>
                                <div style='width:34px;height:34px;border-radius:50%;
                                            background:linear-gradient(135deg,#dc2626,#f87171);
                                            color:#fff;font-size:12px;font-weight:700;
                                            display:flex;align-items:center;justify-content:center;flex-shrink:0'>
                                    {$initials}
                                </div>
                                <div>
                                    <div style='font-weight:600;font-size:13.5px;color:#1e1b4b'>{$name}</div>
                                    <div style='font-size:11px;color:#9ca3af;margin-top:1px'>Booking #{$id}</div>
                                </div>
                            </div>";
                    }),

                TD::make('court', '🏟️ Court')
                    ->width('160px')
                    ->render(fn(Cancellation $c) =>
                        "<span style='display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#166534;
                                      border:1px solid #bbf7d0;border-radius:6px;padding:3px 10px;font-size:12.5px;font-weight:600'>
                            🏟️ " . ($c->booking?->court?->name ?? '—') . "
                        </span>"
                    ),
// ── Reason Column ─────────────────────────────────────────────
TD::make('reason', '📝 Reason')
    ->render(function (Cancellation $c) {
        $reason = $c->reason
            ? "<span style='font-size:13px;font-weight:600;color:#374151'>{$c->reason}</span>"
            : "<span style='font-size:13px;color:#9ca3af'>No reason provided</span>";

        $editUrl = route('platform.cancellations.edit', $c->id);

        $editBtn = "<a href='{$editUrl}'
                        style='width:28px;height:28px;border-radius:6px;background:#eff6ff;
                               border:1px solid #bfdbfe;color:#2563eb;
                               display:inline-flex;align-items:center;justify-content:center;
                               text-decoration:none;'
                        title='Edit Reason'>✏️</a>";

        return "<div style='display:flex;align-items:center;gap:8px;'>
                    {$reason}
                    {$editBtn}
                </div>";
    }),
                TD::make('cancelled_at', '🕐 Cancelled At')
                    ->width('170px')
                    ->render(function (Cancellation $c) {
                        $date = $c->cancelled_at->format('M d, Y');
                        $time = $c->cancelled_at->format('H:i');
                        $day  = $c->cancelled_at->format('l');
                        return "
                            <div style='font-weight:600;font-size:13px;color:#374151'>
                                {$date}
                                <div style='font-size:11px;color:#9ca3af;margin-top:2px'>{$day} · {$time}</div>
                            </div>";
                    }),

                TD::make('actions', '')
                    ->width('80px')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn(Cancellation $c) =>
                        Button::make('🗑️')
                            ->confirm('Delete this cancellation record?')
                            ->method('delete', ['id' => $c->id])
                            ->class('btn btn-sm')
                            ->style('width:30px;height:30px;border-radius:6px;background:#fff1f2;border:1px solid #fecaca;
                                    color:#dc2626;display:inline-flex;align-items:center;justify-content:center;padding:0')
                    ),
            ]),


            Layout::modal('editReasonModal', [
    Layout::rows([
        Input::make('id')
            ->type('hidden'),

        TextArea::make('reason')
            ->title('Reason for Cancellation')
            ->placeholder('Enter reason...')
            ->rows(4),
    ]),
])
->async('asyncGetCancellation')  // ← مهم جداً
->title('Edit Cancellation Reason')
->applyButton('Save')
->closeButton('Cancel'),
    
        ];

        
    }


    

    // ── Filter Methods ────────────────────────────────────────────
    public function applyFilter(Request $request)
    {
        return redirect()->route('platform.cancellations', array_filter([
            'court_id'   => $request->input('court_id'),
            'date_start' => $request->input('date_start'),
            'date_end'   => $request->input('date_end'),
        ]));
    }

    public function resetFilter()
    {
        return redirect()->route('platform.cancellations');
    }

    public function asyncGetCancellation(Cancellation $cancellation): array
{
    return [
        'id'     => $cancellation->id,
        'reason' => $cancellation->reason,
    ];
}

    // ── Update Reason ─────────────────────────────────────────────
public function updateReason(Request $request)
{
    Cancellation::findOrFail($request->input('id'))
        ->update(['reason' => $request->input('reason')]);

    Toast::info('Reason updated successfully.');

    return redirect()->route('platform.cancellations');
}

    // ── Delete ────────────────────────────────────────────────────
    public function delete(Request $request): void
    {
        Cancellation::findOrFail($request->get('id'))->delete();
        Toast::info('Cancellation record deleted.');
    }
}