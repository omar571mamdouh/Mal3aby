<?php

namespace App\Orchid\Screens\Branch;

use App\Models\Branch;
use App\Models\Club;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;
use Illuminate\Http\Request;

class BranchEditScreen extends Screen
{
    public ?Branch $branch = null;

    public function query(Branch $branch = null): iterable
    {
        $branch ??= new Branch();
        $this->branch = $branch;

        return [
            'branch' => $branch,
        ];
    }

    public function name(): ?string
    {
        return $this->branch?->exists
            ? 'تعديل الفرع: ' . $this->branch->name
            : 'إضافة فرع جديد';
    }

    public function description(): ?string
    {
        return $this->branch?->exists
            ? 'تعديل بيانات الفرع'
            : 'إنشاء فرع جديد في النظام';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('رجوع')
                ->icon('bs.arrow-left')
                ->route('platform.branch'),

            Button::make('حفظ')
                ->icon('bs.check-circle')
                ->type(Color::SUCCESS())
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::columns([

                // === العمود الأيسر ===
                Layout::rows([
                    Select::make('branch.club_id')
                        ->title('النادي')
                        ->addon('bs.building')
                        ->placeholder('اختر النادي...')
                        ->fromModel(Club::class, 'name')
                        ->help('النادي التابع له هذا الفرع')
                        ->required(),

                    Input::make('branch.name')
                        ->title('اسم الفرع')
                        ->addon('bs.tag')
                        ->placeholder('مثال: فرع المعادي')
                        ->help('اسم واضح ومميز للفرع')
                        ->required(),
                ]),

                // === العمود الأيمن ===
                Layout::rows([
                    Input::make('branch.city')
                        ->title('المدينة')
                        ->addon('bs.geo-alt')
                        ->placeholder('مثال: القاهرة')
                        ->help('المدينة التي يقع فيها الفرع'),

                    Input::make('branch.address')
                        ->title('العنوان')
                        ->addon('bs.map')
                        ->placeholder('مثال: 15 شارع التحرير')
                        ->help('العنوان التفصيلي للفرع'),

                    Input::make('branch.phone')
                        ->title('الهاتف')
                        ->addon('bs.telephone')
                        ->placeholder('مثال: 01012345678')
                        ->help('رقم تواصل الفرع'),
                    
    Select::make('branch.active')   // ← زوده هنا
        ->title('الحالة')
        ->options([1 => 'نشط', 0 => 'غير نشط'])
        ->value($this->branch?->active ?? 1)
        ->required(),
                ]),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $request->validate([
            'branch.club_id' => 'required|exists:clubs,id',
            'branch.name'    => 'required|string|max:255',
            'branch.active'  => 'nullable|in:0,1',
        ]);

        $branchId = $request->route('branch');
        $branch   = $branchId ? Branch::findOrFail($branchId) : new Branch();

        $branch->fill([
            'club_id' => $request->input('branch.club_id'),
            'name'    => $request->input('branch.name'),
            'city'    => $request->input('branch.city'),
            'address' => $request->input('branch.address'),
            'phone'   => $request->input('branch.phone'),
            'active'  => (bool) $request->input('branch.active', 1),
        ])->save();

        Toast::success($branch->wasRecentlyCreated ? 'تم إنشاء الفرع بنجاح!' : 'تم تحديث الفرع بنجاح!');

        return redirect()->route('platform.branch');
    }
}