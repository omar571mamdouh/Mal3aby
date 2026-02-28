<?php

namespace App\Orchid\Screens\Branch;

use App\Models\Branch;
use App\Models\Club;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Alert;
use App\Orchid\Layouts\Branch\BranchListLayout;

class BranchListScreen extends Screen
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
            ModalToggle::make('إضافة فرع')
                ->modal('addBranchModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            BranchListLayout::class, // الجدول
            Layout::modal('addBranchModal', [
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
            ])
                ->title('إضافة فرع جديد')
                ->applyButton('إنشاء')
                ->closeButton('إلغاء'),
        ];
    }

    public function create(Request $request)
    {
        $request->validate([
            'branch.club_id' => 'required|exists:clubs,id',
            'branch.name' => 'required|string|max:255',
        ]);

        Branch::create($request->get('branch'));

        Alert::success('تم إنشاء الفرع بنجاح!');

        return redirect()->route('platform.branch.list');
    }

    public function delete(Request $request)
    {
        $branch = Branch::findOrFail($request->get('branch'));
        $branch->delete();

        Alert::warning('تم حذف الفرع بنجاح!');

        return redirect()->route('platform.branch.list');
    }
}