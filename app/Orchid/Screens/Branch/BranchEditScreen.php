<?php

namespace App\Orchid\Screens\Branch;

use App\Models\Branch;
use App\Models\Club;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;
use Illuminate\Http\Request;

class BranchEditScreen extends Screen
{
    public ?Branch $branch = null;

    public function query(Branch $branch = null): iterable
    {
        $this->branch = $branch;
        return [
            'branch' => $branch ?? new Branch(),
        ];
    }

    public function name(): ?string
    {
        return $this->branch && $this->branch->exists
            ? 'تعديل الفرع: ' . $this->branch->name
            : 'إضافة فرع';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('حفظ')
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
                    ->prefix('bs.building')
                    ->placeholder('اختر النادي...')
                    ->fromModel(Club::class, 'name')
                    ->help('النادي التابع له هذا الفرع')
                    ->required(),

                Input::make('branch.name')
                    ->title('اسم الفرع')
                    ->prefix('bs.tag')
                    ->placeholder('مثال: فرع المعادي')
                    ->help('اسم واضح ومميز للفرع')
                    ->required(),
            ]),

            // === العمود الأيمن ===
            Layout::rows([
                Input::make('branch.city')
                    ->title('المدينة')
                    ->prefix('bs.geo-alt')
                    ->placeholder('مثال: القاهرة')
                    ->help('المدينة التي يقع فيها الفرع'),

                Input::make('branch.address')
                    ->title('العنوان')
                    ->prefix('bs.map')
                    ->placeholder('مثال: 15 شارع التحرير')
                    ->help('العنوان التفصيلي للفرع'),

                Input::make('branch.phone')
                    ->title('الهاتف')
                    ->prefix('bs.telephone')
                    ->placeholder('مثال: 01012345678')
                    ->help('رقم تواصل الفرع'),
            ]),
        ]),
    ];
}

    public function save(Request $request)
    {
        $branchId = $request->route('branch');
        $branch = $branchId ? Branch::findOrFail($branchId) : new Branch();
        $branch->fill($request->get('branch'))->save();

        Toast::success($branch->wasRecentlyCreated ? 'تم إنشاء الفرع' : 'تم تحديث الفرع');

        return redirect()->route('platform.branch.list');
    }
}