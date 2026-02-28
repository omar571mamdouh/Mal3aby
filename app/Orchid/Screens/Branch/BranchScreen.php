<?php

namespace App\Orchid\Screens\Branch;

use App\Models\Branch;
use App\Models\Club;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Color;

class BranchScreen extends Screen
{
    public ?Branch $branch = null;

    // ===== Query =====
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

    // ===== Command Bar =====
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('إضافة فرع')
                ->modal('addBranchModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    // ===== Layout =====
    public function layout(): iterable
    {
        return [

            // ===== جدول الفروع =====
            Layout::table('branches', [
                TD::make('name', 'اسم الفرع')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn($branch) => $branch->name),

                TD::make('club_id', 'النادي')
                    ->render(fn($branch) => optional($branch->club)->name ?? '—'),

                TD::make('city', 'المدينة')
                    ->render(fn($branch) => $branch->city ?? '—'),

                TD::make('phone', 'الهاتف')
                    ->render(fn($branch) => $branch->phone ?? '—'),

                // TD::make('active', 'الحالة')
                //     ->render(fn($branch) => $branch->active
                //         ? '<span class="badge bg-success">نشط</span>'
                //         : '<span class="badge bg-danger">غير نشط</span>'),

                TD::make('actions', 'الإجراءات')
                    ->align(TD::ALIGN_CENTER)
                    ->width('200px')
                    ->render(fn($branch) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            ModalToggle::make('تعديل')
                                ->modal('editBranchModal')
                                ->method('update')
                                ->asyncParameters(['branch' => $branch->id])
                                ->icon('pencil'),

                            Button::make('حذف')
                                ->method('delete')
                                ->confirm('هل أنت متأكد من حذف الفرع ' . $branch->name . '؟')
                                ->parameters(['branch' => $branch->id])
                                ->icon('trash')
                                ->type(Color::DANGER()),
                        ])
                    ),
            ]),

            // ===== مودال إضافة =====
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
                ])
            ])
            ->title('إضافة فرع جديد')
            ->applyButton('إنشاء')
            ->closeButton('إلغاء'),

            // ===== مودال تعديل =====
            Layout::modal('editBranchModal', [
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
                ])
            ])
            ->title('تعديل الفرع')
            ->applyButton('تحديث')
            ->closeButton('إلغاء')
            ->async('asyncGetBranch'),
        ];
    }

    // ===== Async Load بيانات التعديل =====
    public function asyncGetBranch(Branch $branch): array
    {
        return [
            'branch' => $branch,
        ];
    }

    // ===== إنشاء =====
    public function create(Request $request)
    {
        $request->validate([
            'branch.club_id' => 'required|exists:clubs,id',
            'branch.name'    => 'required|string|max:255',
        ]);

        Branch::create($request->input('branch'));

        Alert::success('تم إنشاء الفرع بنجاح!');

        return redirect()->route('platform.branch');
    }

    // ===== تحديث =====
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'branch.club_id' => 'required|exists:clubs,id',
            'branch.name'    => 'required|string|max:255',
        ]);

        $branch->update((array) $request->input('branch', []));

        Alert::info('تم تحديث الفرع بنجاح!');

        return redirect()->route('platform.branch');
    }

    // ===== حذف =====
    public function delete(Request $request)
    {
        $branch = Branch::findOrFail($request->get('branch'));
        $branch->delete();

        Alert::warning('تم حذف الفرع بنجاح!');

        return redirect()->route('platform.branch');
    }
}