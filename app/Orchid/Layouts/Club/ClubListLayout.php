<?php

namespace App\Orchid\Layouts\Club;

use App\Models\Club;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table; // ← وارث من Table مش Rows
use Orchid\Screen\TD;
use Orchid\Support\Color;

class ClubListLayout extends Table
{
    protected $target = 'clubs'; // ← اسم الـ key في الـ query()

 protected function columns(): iterable
{
    return [
        TD::make('logo', '')
            ->width('60px')
            ->align(TD::ALIGN_CENTER)
            ->render(fn(Club $club) => $club->logo
                ? '<img src="' . asset('storage/' . $club->logo) . '" style="width:45px;height:45px;object-fit:cover;border-radius:50%;border:2px solid #e8e8e8;box-shadow:0 2px 4px rgba(0,0,0,0.1);">'
                : '<div style="width:45px;height:45px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;margin:auto;"><span style="color:white;font-size:18px;">🏆</span></div>'
            ),

        TD::make('name', 'النادي')
            ->sort()
            ->filter(TD::FILTER_TEXT)
            ->render(fn(Club $club) => '
                <div>
                    <div style="font-weight:600;color:#1a1a2e;">' . $club->name . '</div>
                    <div style="font-size:11px;color:#888;margin-top:2px;">📍 ' . ($club->address ?? '—') . '</div>
                </div>
            '),

        TD::make('phone', 'التواصل')
            ->render(fn(Club $club) => '
                <div>
                    <div style="font-size:12px;">📞 ' . ($club->phone ?? '—') . '</div>
                    <div style="font-size:12px;margin-top:3px;">✉️ ' . ($club->email ?? '—') . '</div>
                </div>
            '),

        TD::make('actions', 'الإجراءات')
            ->align(TD::ALIGN_CENTER)
            ->width('160px')
            ->render(fn(Club $club) =>
                '<div style="display:flex;gap:6px;justify-content:center;">'
                . Link::make('تعديل')
                    ->route('platform.club.edit', $club)
                    ->icon('pencil')
                    ->class('btn btn-sm btn-primary')
                . Button::make('حذف')
                    ->method('deleteClub')
                    ->confirm('هل أنت متأكد من حذف ' . $club->name . '؟')
                    ->parameters(['id' => $club->id])
                    ->icon('trash')
                    ->class('btn btn-sm btn-danger')
                . '</div>'
            ),
    ];
}
}