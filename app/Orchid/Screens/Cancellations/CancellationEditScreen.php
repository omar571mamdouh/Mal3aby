<?php

namespace App\Orchid\Screens\Cancellations;

use App\Models\Cancellation;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;

class CancellationEditScreen extends Screen
{
    public ?Cancellation $cancellation = null;

    public function query(Cancellation $cancellation): array
    {
        return [
            'cancellation' => $cancellation,
        ];
    }

    public function name(): ?string
    {
        return 'Edit Cancellation Reason';
    }

    public function description(): ?string
    {
        return 'Booking #' . ($this->cancellation?->booking_id ?? '—');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Cancel')
                ->icon('bs.x-lg')
                ->route('platform.cancellations')
                ->class('btn btn-outline-secondary'),

            Button::make('Save Reason')
                ->icon('bs.check-lg')
                ->method('save')
                ->class('btn btn-primary px-4'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([

                // ── Read Only Fields ──────────────────────────────
                Input::make('cancellation.booking_id')
                    ->title('📋 Booking ID')
                    ->value('#' . $this->cancellation?->booking_id)
                    ->readonly()
                    ->horizontal(),

                Input::make('cancellation.customer')
                    ->title('👤 Customer')
                    ->value($this->cancellation?->booking?->customer?->first_name ?? '—')
                    ->readonly()
                    ->horizontal(),

                Input::make('cancellation.court')
                    ->title('🏟️ Court')
                    ->value($this->cancellation?->booking?->court?->name ?? '—')
                    ->readonly()
                    ->horizontal(),

                Input::make('cancellation.cancelled_at')
                    ->title('🕐 Cancelled At')
                    ->value($this->cancellation?->cancelled_at?->format('M d, Y — H:i'))
                    ->readonly()
                    ->horizontal(),

                // ── Editable Field ────────────────────────────────
                TextArea::make('cancellation.reason')
                    ->title('📝 Reason')
                    ->placeholder('Enter reason for cancellation...')
                    ->rows(5)
                    ->horizontal(),
            ]),
        ];
    }

    public function save(Cancellation $cancellation, Request $request)
    {
        $cancellation->update([
            'reason' => $request->input('cancellation.reason'),
        ]);

        Toast::info('Reason updated successfully.');

        return redirect()->route('platform.cancellations');
    }
}