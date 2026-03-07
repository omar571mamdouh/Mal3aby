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
        Layout::table('branches', [
            TD::make('name', 'الفرع')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn($b) => '
                    <div>
                        <div style="font-weight:600;color:#1a1a2e;">' . $b->name . '</div>
                        <div style="font-size:11px;color:#888;margin-top:2px;">🏆 ' . (optional($b->club)->name ?? '—') . '</div>
                    </div>
                '),

            TD::make('city', 'الموقع')
                ->render(fn($b) => '
                    <div>
                        <div style="font-size:12px;">📍 ' . ($b->city ?? '—') . '</div>
                        <div style="font-size:12px;margin-top:3px;">📞 ' . ($b->phone ?? '—') . '</div>
                    </div>
                '),

            TD::make('active', 'الحالة')
                ->align(TD::ALIGN_CENTER)
                ->render(fn($b) => $b->active
                    ? '<span style="display:inline-flex;align-items:center;gap:4px;background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#2e7d32;border-radius:50%;display:inline-block;"></span> نشط</span>'
                    : '<span style="display:inline-flex;align-items:center;gap:4px;background:#ffebee;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#c62828;border-radius:50%;display:inline-block;"></span> غير نشط</span>'
                ),

            TD::make('actions', 'الإجراءات')
                ->align(TD::ALIGN_CENTER)
                ->width('160px')
                ->render(fn($b) =>
                    '<div style="display:flex;gap:6px;justify-content:center;">'
                    . ModalToggle::make('تعديل')
                        ->modal('editBranchModal')
                        ->method('update')
                        ->asyncParameters(['branch' => $b->id])
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
        ]),

        // ===== مودال إضافة =====
        Layout::modal('addBranchModal', [
            Layout::rows([
                Select::make('branch.club_id')
                    ->title('النادي')
                    ->prefix('bs.building')
                    ->placeholder('اختر النادي...')
                    ->fromModel(Club::class, 'name')
                    ->required(),

                Input::make('branch.name')
                    ->title('اسم الفرع')
                    ->prefix('bs.tag')
                    ->placeholder('مثال: فرع المعادي')
                    ->required(),

                Input::make('branch.city')
                    ->title('المدينة')
                    ->prefix('bs.geo-alt')
                    ->placeholder('مثال: القاهرة'),

                Input::make('branch.address')
                    ->title('العنوان')
                    ->prefix('bs.map')
                    ->placeholder('مثال: 15 شارع التحرير'),

                Input::make('branch.phone')
                    ->title('الهاتف')
                    ->prefix('bs.telephone')
                    ->placeholder('مثال: 01012345678'),
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
            ->prefix('bs.building')
            ->placeholder('اختر النادي...')
            ->fromModel(Club::class, 'name')
            ->required(),

        Input::make('branch.name')
            ->title('اسم الفرع')
            ->prefix('bs.tag')
            ->placeholder('مثال: فرع المعادي')
            ->required(),

        Input::make('branch.city')
            ->title('المدينة')
            ->prefix('bs.geo-alt')
            ->placeholder('مثال: القاهرة'),

        Input::make('branch.address')
            ->title('العنوان')
            ->prefix('bs.map')
            ->placeholder('مثال: 15 شارع التحرير'),

        Input::make('branch.phone')
            ->title('الهاتف')
            ->prefix('bs.telephone')
            ->placeholder('مثال: 01012345678'),
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