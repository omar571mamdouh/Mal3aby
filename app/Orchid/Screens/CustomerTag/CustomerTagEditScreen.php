<?php

namespace App\Orchid\Screens\CustomerTag;

use App\Models\CustomerTag;
use App\Models\Customer;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class CustomerTagEditScreen extends Screen
{
    public $tag;

    public function name(): string
    {
        return $this->tag?->exists ? '✏️ Edit Tag' : '➕ Add New Tag';
    }

    public function description(): string
    {
        return $this->tag?->exists
            ? 'Update this customer tag'
            : 'Fill in the details to create a new tag';
    }

    public function query(CustomerTag $tag): array
    {
        $this->tag = $tag;
        return [
            'tag' => $tag,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Delete')
                ->icon('bs.trash')
                ->class('btn btn-danger')
                ->method('delete')
                ->confirm('⚠️ Are you sure you want to delete this tag?')
                ->canSee($this->tag?->exists),

            Button::make('Save Tag')
                ->icon('bs.save')
                ->class('btn btn-primary')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('tag.customer_id')
                    ->title('👤 Customer')
                    ->empty('— Select Customer —')
                    ->options(fn() => Customer::all()->pluck('first_name', 'id'))
                    ->required(),

                Input::make('tag.tag')
                    ->title('🏷️ Tag Name')
                    ->placeholder('e.g. VIP, New, Frequent')
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request, CustomerTag $tag)
    {
        $data = $request->input('tag');

        $request->validate([
            'tag.customer_id' => 'required|exists:customers,id',
            'tag.tag'         => 'required|string|max:255',
        ]);

        if ($tag->exists) {
            $tag->update([
                'customer_id' => $data['customer_id'],
                'tag'         => $data['tag'],
            ]);
        } else {
            CustomerTag::create([
                'customer_id' => $data['customer_id'],
                'tag'         => $data['tag'],
            ]);
        }

        Toast::info('✅ Tag saved successfully!');
        return redirect()->route('platform.customer.tags.list');
    }

    public function delete(CustomerTag $tag)
    {
        $tag->delete();
        Toast::info('🗑️ Tag deleted successfully!');
        return redirect()->route('platform.customer.tags.list');
    }
}