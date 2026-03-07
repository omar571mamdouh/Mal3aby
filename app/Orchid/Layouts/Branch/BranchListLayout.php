<?php

namespace App\Orchid\Layouts\Branch;

use App\Models\Branch;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class BranchListLayout extends Table
{
    protected $target = 'branches';

    protected function columns(): iterable
    {
        return [
            TD::make('name', 'الفرع')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn(Branch $b) => '
                    <div>
                        <div style="font-weight:600;color:#1a1a2e;">' . $b->name . '</div>
                        <div style="font-size:11px;color:#888;margin-top:2px;">🏆 ' . ($b->club?->name ?? '—') . '</div>
                    </div>
                '),

            TD::make('city', 'الموقع')
                ->render(fn(Branch $b) => '
                    <div>
                        <div style="font-size:12px;">📍 ' . ($b->city ?? '—') . '</div>
                        <div style="font-size:12px;margin-top:3px;">📞 ' . ($b->phone ?? '—') . '</div>
                    </div>
                '),

            TD::make('active', 'الحالة')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(Branch $b) => $b->active
                    ? '<span style="display:inline-flex;align-items:center;gap:4px;background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#2e7d32;border-radius:50%;display:inline-block;"></span> نشط</span>'
                    : '<span style="display:inline-flex;align-items:center;gap:4px;background:#ffebee;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#c62828;border-radius:50%;display:inline-block;"></span> غير نشط</span>'
                ),

            TD::make('actions', 'الإجراءات')
                ->align(TD::ALIGN_CENTER)
                ->width('160px')
                ->render(fn(Branch $b) =>
                    '<div style="display:flex;gap:6px;justify-content:center;">'
                    . Link::make('تعديل')
                        ->route('platform.branch.edit', $b)
                        ->icon('pencil')
                        ->class('btn btn-sm btn-primary')
                    . Button::make('حذف')
                        ->method('delete')
                        ->confirm('هل أنت متأكد من حذف الفرع ' . $b->name . '؟')
                        ->parameters(['branch' => $b->id])
                        ->icon('trash')
                        ->class('btn btn-sm btn-danger')
                    . '</div>'
                ),
        ];
    }
}