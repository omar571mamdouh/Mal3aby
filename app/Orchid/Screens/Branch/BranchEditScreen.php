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
            Layout::rows([
                Select::make('branch.club_id')
                    ->title('النادي')
                    ->fromModel(Club::class, 'name')
                    ->required(),

                Input::make('branch.name')
                    ->title('اسم الفرع')
                    ->required(),

                Input::make('branch.address')
                    ->title('العنوان'),

                Input::make('branch.city')
                    ->title('المدينة'),

                Input::make('branch.phone')
                    ->title('الهاتف'),
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