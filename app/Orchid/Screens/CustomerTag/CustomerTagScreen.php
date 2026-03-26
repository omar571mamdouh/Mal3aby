<?php

namespace App\Orchid\Screens\CustomerTag;

use App\Models\CustomerTag;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class CustomerTagScreen extends Screen
{
    public $name = 'Customer Tags';
    public $description = 'Manage tags for your customers';

    public function query(): iterable
    {
        return [
            'tags' => CustomerTag::with('customer')->paginate(15),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add Tag')
                ->icon('bs.plus-circle')
                ->class('btn btn-primary')
                ->route('platform.customer.tags.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('tags', [

                TD::make('customer', 'Customer')
                    ->render(fn(CustomerTag $tag) =>
                        '<div style="display:flex;align-items:center;gap:10px;">'
                        . '<div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">👤</div>'
                        . '<div style="font-weight:600;color:#1e293b;font-size:14px;">' . e($tag->customer?->first_name . ' ' . $tag->customer?->last_name ?? '—') . '</div>'
                        . '</div>'
                    ),

                TD::make('tag', 'Tag')
                    ->render(fn(CustomerTag $tag) =>
                        '<span style="display:inline-flex;align-items:center;gap:5px;background:#f3e8ff;color:#7e22ce;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">🏷️ ' . e($tag->tag) . '</span>'
                    ),

                TD::make('created_at', 'Created')
                    ->render(fn(CustomerTag $tag) =>
                        '<span style="display:flex;align-items:center;gap:6px;color:#64748b;white-space:nowrap;">📅 ' . e($tag->created_at?->format('Y-m-d H:i') ?? '—') . '</span>'
                    ),

                TD::make('actions', 'Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(fn(CustomerTag $tag) =>
                        '<div style="display:flex;gap:6px;justify-content:center;">'
                        . Link::make('Edit')
                            ->route('platform.customer.tags.edit', $tag->id)
                            ->icon('bs.pencil-square')
                            ->class('btn btn-sm btn-primary')
                        . Button::make('Delete')
                            ->method('delete')
                            ->confirm('⚠️ Are you sure you want to delete this tag?')
                            ->parameters(['id' => $tag->id])
                            ->icon('bs.trash')
                            ->class('btn btn-sm btn-danger')
                        . '</div>'
                    ),
            ]),
        ];
    }

    public function delete(Request $request)
    {
        $tag = CustomerTag::findOrFail($request->get('id'));
        $tag->delete();

        Toast::info('🗑️ Tag deleted successfully!');
        return redirect()->route('platform.customer.tags.list');
    }
}