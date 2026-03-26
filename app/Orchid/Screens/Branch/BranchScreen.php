<?php

namespace App\Orchid\Screens\Branch;

use App\Models\Branch;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Alert;

class BranchScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'branches' => Branch::with('club')->latest()->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'إدارة الفروع';
    }

    public function description(): ?string
    {
        return 'إدارة جميع الفروع التابعة للأندية';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('إضافة فرع جديد')
                ->icon('bs.plus-circle')
                ->route('platform.branch.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('branches', [

                TD::make('name', 'الفرع')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn($b) => "
                        <div>
                            <div style='font-weight:600;font-size:13.5px;color:#1e1b4b'>{$b->name}</div>
                            <div style='font-size:11px;color:#888;margin-top:2px;'>🏆 " . (optional($b->club)->name ?? '—') . "</div>
                        </div>
                    "),

                TD::make('city', 'الموقع')
                    ->render(fn($b) => "
                        <div style='font-size:12.5px;line-height:2'>
                            <div>📍 " . ($b->city ?? '—') . "</div>
                            <div>📞 " . ($b->phone ?? '—') . "</div>
                        </div>
                    "),

                TD::make('address', 'العنوان')
                    ->render(fn($b) => $b->address
                        ? "<span style='font-size:12.5px;color:#555'>🗺 {$b->address}</span>"
                        : "<span style='color:#d1d5db'>—</span>"
                    ),

                TD::make('active', 'الحالة')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn($b) => $b->active
                        ? "<span style='display:inline-flex;align-items:center;gap:5px;background:#dcfce7;color:#166534;border:1px solid #86efac;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600'>
                                <span style='width:6px;height:6px;background:#16a34a;border-radius:50%;display:inline-block'></span>نشط
                           </span>"
                        : "<span style='display:inline-flex;align-items:center;gap:5px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600'>
                                <span style='width:6px;height:6px;background:#dc2626;border-radius:50%;display:inline-block'></span>غير نشط
                           </span>"
                    ),

                TD::make('actions', 'الإجراءات')
                    ->align(TD::ALIGN_RIGHT)
                    ->width('120px')
                    ->render(fn($b) =>
                        "<div style='display:flex;justify-content:flex-end;gap:6px'>"
                        . Link::make('تعديل')
                            ->route('platform.branch.edit', $b->id)
                            ->icon('pencil')
                            ->class('btn btn-sm btn-primary')
                        . Button::make('حذف')
                            ->method('delete')
                            ->confirm('هل أنت متأكد من حذف الفرع ' . $b->name . '؟')
                            ->parameters(['branch' => $b->id])
                            ->icon('trash')
                            ->class('btn btn-sm btn-danger')
                        . "</div>"
                    ),
            ]),
        ];
    }

    public function delete(Request $request)
    {
        $branch = Branch::findOrFail($request->get('branch'));
        $branch->delete();

        Alert::warning('تم حذف الفرع بنجاح!');

        return redirect()->route('platform.branch');
    }
}