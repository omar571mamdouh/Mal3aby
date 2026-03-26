<?php

namespace App\Orchid\Screens\Club;

use Orchid\Screen\Screen;
use App\Models\Club;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Alert;

class ClubScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'clubs' => Club::with('branches')->latest()->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'إدارة الأندية';
    }

    public function description(): ?string
    {
        return 'إدارة جميع الأندية في النظام';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('إضافة نادي جديد')
                ->icon('bs.plus-circle')
                ->route('platform.club.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('clubs', [

                TD::make('logo', 'الشعار')
                    ->width('70px')
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (Club $club) {
                        if (!$club->logo) {
                            $initials = strtoupper(mb_substr($club->name, 0, 2));
                            return "<div style='width:42px;height:42px;border-radius:50%;
                                               background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                               color:#fff;font-size:13px;font-weight:700;
                                               display:flex;align-items:center;justify-content:center;
                                               margin:auto'>{$initials}</div>";
                        }
                        $url = str_starts_with($club->logo, 'http')
                            ? $club->logo
                            : asset('storage/' . $club->logo);
                        return "<img src='{$url}' width='42' height='42'
                                     style='object-fit:cover;border-radius:50%;
                                            border:2px solid #e5e7eb;
                                            box-shadow:0 1px 4px rgba(0,0,0,.08);
                                            display:block;margin:auto'>";
                    }),

                TD::make('name', 'النادي')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Club $club) =>
                        "<span style='font-weight:600;font-size:13.5px;color:#1e1b4b'>{$club->name}</span>"
                    ),

                TD::make('phone', 'التواصل')
                    ->render(fn(Club $club) =>
                        "<div style='font-size:12.5px;line-height:1.8'>
                            <div>📞 " . ($club->phone ?? '—') . "</div>
                            <div>✉️ " . ($club->email ?? '—') . "</div>
                        </div>"
                    ),

                TD::make('active', 'الحالة')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn(Club $club) => $club->active
                        ? "<span style='display:inline-flex;align-items:center;gap:5px;
                                        background:#dcfce7;color:#166534;border:1px solid #86efac;
                                        padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600'>
                                <span style='width:6px;height:6px;background:#16a34a;border-radius:50%;display:inline-block'></span>
                                نشط
                           </span>"
                        : "<span style='display:inline-flex;align-items:center;gap:5px;
                                        background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;
                                        padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600'>
                                <span style='width:6px;height:6px;background:#dc2626;border-radius:50%;display:inline-block'></span>
                                غير نشط
                           </span>"
                    ),

                TD::make('actions', 'الإجراءات')
                    ->align(TD::ALIGN_RIGHT)
                    ->width('110px')
                    ->render(fn(Club $club) =>
                        "<div style='display:flex;justify-content:flex-end;gap:6px'>"
                        . Link::make('تعديل')
                            ->route('platform.club.edit', $club)
                            ->icon('pencil')
                            ->class('btn btn-sm btn-primary')
                        . Button::make('حذف')
                            ->method('delete')
                            ->confirm('هل أنت متأكد من حذف النادي ' . $club->name . '؟')
                            ->parameters(['club' => $club->id])
                            ->icon('trash')
                            ->class('btn btn-sm btn-danger')
                        . "</div>"
                    ),
            ]),
        ];
    }

    public function delete(Club $club)
    {
        $club->delete();

        Alert::warning('تم حذف النادي بنجاح!');

        return redirect()->route('platform.club');
    }
}