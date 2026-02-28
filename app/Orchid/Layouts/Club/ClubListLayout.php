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
        TD::make('name', 'الاسم')
            ->sort()
            ->filter(TD::FILTER_TEXT)
            ->render(fn(Club $club) => $club->name), // ✅ render

        TD::make('phone', 'الهاتف')
            ->render(fn(Club $club) => $club->phone ?? '—'), // ✅ render

        TD::make('email', 'البريد الإلكتروني')
            ->render(fn(Club $club) => $club->email ?? '—'), // ✅ render

        TD::make('logo', 'شعار النادي')
            ->align(TD::ALIGN_CENTER)
            ->render(function (Club $club) { // ✅ render
                if ($club->logo) {
                    $url = asset('storage/' . $club->logo);
                    return "<img src='{$url}' width='50' height='50'
                                style='object-fit:cover; border-radius:8px;'>";
                }
                return '—';
            }),

        TD::make('actions', 'الإجراءات')
            ->align(TD::ALIGN_CENTER)
            ->render(function (Club $club) {
                return DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make('تعديل')
                            ->route('platform.club.edit', $club)
                            ->icon('pencil'),

                        Button::make('حذف')
                            ->method('deleteClub')
                            ->confirm('هل أنت متأكد من حذف ' . $club->name . '؟')
                            ->parameters(['id' => $club->id])
                            ->icon('trash')
                            ->type(Color::DANGER()),
                    ]);
            }),
    ];
}
}