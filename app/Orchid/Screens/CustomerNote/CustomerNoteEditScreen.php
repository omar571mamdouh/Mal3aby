<?php

namespace App\Orchid\Screens\CustomerNote;

use App\Models\CustomerNote;
use App\Models\Customer;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class CustomerNoteEditScreen extends Screen
{
    public $note;

    public function name(): string
    {
        return $this->note?->exists ? '✏️ Edit Note' : '➕ Add New Note';
    }

    public function description(): string
    {
        return $this->note?->exists
            ? 'Update this customer note'
            : 'Fill in the details to create a new note';
    }

    public function query(CustomerNote $note): array
    {
        $this->note = $note;
        return [
            'note' => $note,
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Delete')
                ->icon('bs.trash')
                ->class('btn btn-danger')
                ->method('delete')
                ->confirm('⚠️ Are you sure you want to delete this note?')
                ->canSee($this->note?->exists),

            Button::make('Save Note')
                ->icon('bs.save')
                ->class('btn btn-primary')
                ->method('save'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Select::make('note.customer_id')
                    ->fromModel(Customer::class, 'first_name')
                    ->title('👤 Customer')
                    ->empty('— Select Customer —')
                    ->required(),

                TextArea::make('note.note')
                    ->title('📝 Note')
                    ->placeholder('Write your note here...')
                    ->rows(5)
                    ->required(),
            ]),
        ];
    }

    public function save(CustomerNote $note, Request $request)
    {
        $note->fill($request->get('note'))->save();
        Toast::info('✅ Note saved successfully!');
        return redirect()->route('platform.customer.notes');
    }

    public function delete(CustomerNote $note)
    {
        $note->delete();
        Toast::info('🗑️ Note deleted successfully!');
        return redirect()->route('platform.customer.notes');
    }
}