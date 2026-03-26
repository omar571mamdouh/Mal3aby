<?php

namespace App\Orchid\Screens\CustomerNote;

use App\Models\CustomerNote;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class CustomerNoteListScreen extends Screen
{
    public $name = 'Customer Notes';
    public $description = 'List of all customer notes';

    public function query(): iterable
    {
        return [
            'notes' => CustomerNote::with('customer')->paginate(15),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Note')
                ->icon('bs.plus-circle')
                ->class('btn btn-primary')
                ->route('platform.customer.notes.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('notes', [

                TD::make('customer', 'Customer')
                    ->render(fn(CustomerNote $note) =>
                        '<div style="display:flex;align-items:center;gap:10px;">'
                        . '<div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">👤</div>'
                        . '<div style="font-weight:600;color:#1e293b;font-size:14px;">' . e($note->customer?->first_name . ' ' . $note->customer?->last_name ?? '—') . '</div>'
                        . '</div>'
                    ),

                TD::make('note', 'Note')
                    ->render(fn(CustomerNote $note) =>
                        '<span style="display:flex;align-items:center;gap:6px;color:#475569;">📝 '
                        . e(strlen($note->note ?? '') > 60
                            ? substr($note->note, 0, 60) . '...'
                            : ($note->note ?? '—'))
                        . '</span>'
                    ),

                TD::make('created_at', 'Created')
                    ->render(fn(CustomerNote $note) =>
                        '<span style="display:flex;align-items:center;gap:6px;color:#64748b;white-space:nowrap;">📅 '
                        . e($note->created_at?->format('Y-m-d H:i') ?? '—')
                        . '</span>'
                    ),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(fn(CustomerNote $note) =>
                        '<div style="display:flex;gap:6px;justify-content:center;">'
                        . Link::make('Edit')
                            ->route('platform.customer.notes.edit', $note->id)
                            ->icon('bs.pencil-square')
                            ->class('btn btn-sm btn-primary')
                        . Button::make('Delete')
                            ->method('delete')
                            ->confirm('⚠️ Are you sure you want to delete this note?')
                            ->parameters(['id' => $note->id])
                            ->icon('bs.trash')
                            ->class('btn btn-sm btn-danger')
                        . '</div>'
                    ),
            ]),
        ];
    }

    public function delete(Request $request)
    {
        $note = CustomerNote::findOrFail($request->get('id'));
        $note->delete();

        Toast::info('🗑️ Note deleted successfully!');
        return redirect()->route('platform.customer.notes');
    }
}