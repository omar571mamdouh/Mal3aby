<?php

namespace App\Orchid\Screens;

use App\Models\Facility;
use App\Models\Branch;
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

class FacilityScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'facilities' => Facility::with('branch.club')->latest()->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'إدارة المرافق';
    }

    public function description(): ?string
    {
        return 'إدارة جميع المرافق لكل فرع';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('إضافة مرفق')
                ->modal('addFacilityModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [

            // ===== جدول المرافق =====
            Layout::table('facilities', [
               TD::make('name', 'اسم المرفق')
    ->sort()
    ->filter(TD::FILTER_TEXT)
    ->render(fn($facility) => $facility->name),

TD::make('branch', 'الفرع')
    ->render(fn($facility) =>
        $facility->branch?->name ?? '—'
    ),

TD::make('club', 'النادي')
    ->render(fn($facility) =>
        $facility->branch?->club?->name ?? '—'
    ),

TD::make('type', 'النوع')
    ->render(function ($facility) {

        $types = [
            'football' => 'Football',
            'padel'    => 'Padel',
            'gym'      => 'Gym',
            'tennis'   => 'Tennis',
            'other'    => 'Other',
        ];

        return $types[$facility->type] ?? $facility->type;
    }),

TD::make('active', 'الحالة')
    ->render(function ($facility) {

        return $facility->active
            ? '<span class="badge bg-success">نشط</span>'
            : '<span class="badge bg-danger">غير نشط</span>';
    }),

TD::make('actions', 'الإجراءات')
    ->align(TD::ALIGN_CENTER)
    ->width('200px')
    ->render(function ($facility) {

        return DropDown::make()
            ->icon('bs.three-dots-vertical')
            ->list([

                ModalToggle::make('تعديل')
                    ->modal('editFacilityModal')
                    ->method('update')
                    ->asyncParameters([
                        'facility' => $facility->id,
                    ])
                    ->icon('pencil'),

                Button::make('حذف')
                    ->method('delete')
                    ->confirm('هل أنت متأكد من حذف المرفق ' . $facility->name . '؟')
                    ->parameters([
                        'facility' => $facility->id,
                    ])
                    ->icon('trash')
                    ->type(Color::DANGER()),

            ]);
    }),
            ]),

            // ===== مودال إضافة =====
            Layout::modal('addFacilityModal', [
                Layout::rows([
                    Select::make('facility.branch_id')
                        ->title('الفرع')
                        ->fromModel(Branch::class, 'name')
                        ->required(),

                    Input::make('facility.name')
                        ->title('اسم المرفق')
                        ->required(),

                    Select::make('facility.type')
                        ->title('النوع')
                        ->options([
                            'football' => 'Football',
                            'padel' => 'Padel',
                            'gym' => 'Gym',
                            'tennis' => 'Tennis',
                            'other' => 'Other',
                        ])
                        ->required(),

                    Input::make('facility.description')
                        ->title('الوصف'),

                   
                ]),
            ])
                ->title('إضافة مرفق جديد')
                ->applyButton('إنشاء')
                ->closeButton('إلغاء'),

            // ===== مودال تعديل =====
            Layout::modal('editFacilityModal', [
                Layout::rows([
                    Select::make('facility.branch_id')
                        ->title('الفرع')
                        ->fromModel(Branch::class, 'name')
                        ->required(),

                    Input::make('facility.name')
                        ->title('اسم المرفق')
                        ->required(),

                    Select::make('facility.type')
                        ->title('النوع')
                        ->options([
                            'football' => 'Football',
                            'padel' => 'Padel',
                            'gym' => 'Gym',
                            'tennis' => 'Tennis',
                            'other' => 'Other',
                        ])
                        ->required(),

                    Input::make('facility.description')
                        ->title('الوصف'),

                   
                ]),
            ])
                ->title('تعديل المرفق')
                ->applyButton('تحديث')
                ->closeButton('إلغاء')
                ->async('asyncGetFacility'),
        ];
    }

    public function asyncGetFacility(Facility $facility): array
    {
        return ['facility' => $facility];
    }

    public function create(Request $request)
    {
        $request->validate([
            'facility.branch_id' => 'required|exists:branches,id',
            'facility.name' => 'required|string|max:255',
            'facility.type' => 'required|in:football,padel,gym,tennis,other',
        ]);

        Facility::create($request->get('facility'));

        Alert::success('تم إنشاء المرفق بنجاح!');

        return redirect()->route('platform.facility');
    }

    public function update(Request $request, Facility $facility)
    {
        $request->validate([
            'facility.branch_id' => 'required|exists:branches,id',
            'facility.name' => 'required|string|max:255',
            'facility.type' => 'required|in:football,padel,gym,tennis,other',
        ]);

        $facility->update($request->get('facility'));

        Alert::info('تم تحديث المرفق بنجاح!');

        return redirect()->route('platform.facility');
    }

    public function delete(Facility $facility)
    {
        $facility->delete();

        Alert::warning('تم حذف المرفق بنجاح!');

        return redirect()->route('platform.facility');
    }
}