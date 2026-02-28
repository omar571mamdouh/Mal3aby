<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use App\Models\Court;
use App\Models\Facility;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use Orchid\Support\Color;

class CourtScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'courts' => Court::with('facility.branch.club')->latest()->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'إدارة الملاعب';
    }

    public function description(): ?string
    {
        return 'إدارة جميع الملاعب لكل مرفق';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('إضافة ملعب')
                ->modal('addCourtModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('courts', [
                TD::make('name', 'اسم الملعب')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Court $c) => $c->name),

                TD::make('facility', 'المرفق')
                    ->render(fn(Court $c) => $c->facility?->name ?? '—'),

                TD::make('branch', 'الفرع')
                    ->render(fn(Court $c) => $c->facility?->branch?->name ?? '—'),

                TD::make('club', 'النادي')
                    ->render(fn(Court $c) => $c->facility?->branch?->club?->name ?? '—'),

                TD::make('surface_type', 'نوع الأرضية')
                    ->render(fn(Court $c) => ucfirst($c->surface_type)),

                TD::make('price_per_hour', 'سعر الساعة')
                    ->render(fn(Court $c) => number_format($c->price_per_hour, 2) . ' EGP'),

                TD::make('opens_at', 'يفتح الساعة')
                    ->render(fn(Court $c) => $c->opens_at),

                TD::make('closes_at', 'يغلق الساعة')
                    ->render(fn(Court $c) => $c->closes_at),

                TD::make('capacity', 'السعة')
                    ->render(fn(Court $c) => $c->capacity),

                TD::make('active', 'الحالة')
                    ->align(TD::ALIGN_CENTER)
                    ->render(
                        fn(Court $c) =>
                        $c->active
                            ? '<span class="badge bg-success">نشط</span>'
                            : '<span class="badge bg-danger">غير نشط</span>'
                    ),

                TD::make('actions', 'الإجراءات')
                    ->align(TD::ALIGN_CENTER)
                    ->width('200px')
                    ->render(function (Court $c) {
                        return DropDown::make()
                            ->icon('bs.three-dots-vertical')
                            ->list([
                                ModalToggle::make('تعديل')
                                    ->modal('editCourtModal')
                                    ->method('update')
                                    ->asyncParameters(['court' => $c->id])
                                    ->icon('pencil'),

                                Button::make('حذف')
                                    ->method('delete')
                                    ->confirm('هل أنت متأكد من حذف الملعب ' . $c->name . '؟')
                                    ->parameters(['court' => $c->id])
                                    ->icon('trash')
                                    ->type(Color::DANGER()),
                            ]);
                    }),
            ]),

            // ===== مودال إضافة ملعب جديد =====
            Layout::modal('addCourtModal', [
                Layout::rows([
                    Select::make('court.facility_id')
                        ->title('المرفق')
                        ->fromModel(Facility::class, 'name')
                        ->required(),

                    Input::make('court.name')
                        ->title('اسم الملعب')
                        ->required(),

                    Select::make('court.surface_type')
                        ->title('نوع الأرضية')
                        ->options([
                            'grass' => 'Grass',
                            'turf' => 'Turf',
                            'indoor' => 'Indoor',
                        ])
                        ->required(),

                    Input::make('court.price_per_hour')
                        ->title('سعر الساعة')
                        ->type('number')
                        ->step('0.01'),

                    Input::make('court.opens_at')
                        ->title('يفتح الساعة')
                        ->type('time'),

                    Input::make('court.closes_at')
                        ->title('يغلق الساعة')
                        ->type('time'),

                    Input::make('court.capacity')
                        ->title('السعة')
                        ->type('number')
                        ->min(1),
                ]),
            ])
                ->title('إضافة ملعب جديد')
                ->applyButton('إنشاء')
                ->closeButton('إلغاء'),

            // ===== مودال تعديل ملعب =====
            Layout::modal('editCourtModal', [
                Layout::rows([
                    Select::make('court.facility_id')
                        ->title('المرفق')
                        ->fromModel(Facility::class, 'name')
                        ->required(),

                    Input::make('court.name')
                        ->title('اسم الملعب')
                        ->required(),

                    Select::make('court.surface_type')
                        ->title('نوع الأرضية')
                        ->options([
                            'grass' => 'Grass',
                            'turf' => 'Turf',
                            'indoor' => 'Indoor',
                        ])
                        ->required(),

                    Input::make('court.price_per_hour')
                        ->title('سعر الساعة')
                        ->type('number')
                        ->step('0.01'),

                    Input::make('court.opens_at')
                        ->title('يفتح الساعة')
                        ->type('time'),

                    Input::make('court.closes_at')
                        ->title('يغلق الساعة')
                        ->type('time'),

                    Input::make('court.capacity')
                        ->title('السعة')
                        ->type('number')
                        ->min(1),
                ]),
            ])
                ->title('تعديل الملعب')
                ->applyButton('تحديث')
                ->closeButton('إلغاء')
                ->async('asyncGetCourt'),
        ];
    }

    public function asyncGetCourt(Court $court): array
    {
        return ['court' => $court];
    }

    public function create(Request $request)
    {
        $request->validate([
            'court.facility_id' => 'required|exists:facilities,id',
            'court.name' => 'required|string|max:255',
            'court.surface_type' => 'required|in:grass,turf,indoor',
        ]);

        Court::create($request->get('court'));

        Alert::success('تم إنشاء الملعب بنجاح!');

        return redirect()->route('platform.court');
    }

    public function update(Request $request, Court $court)
    {
        $request->validate([
            'court.facility_id' => 'required|exists:facilities,id',
            'court.name' => 'required|string|max:255',
            'court.surface_type' => 'required|in:grass,turf,indoor',
        ]);

        $court->update($request->get('court'));

        Alert::info('تم تحديث الملعب بنجاح!');

        return redirect()->route('platform.court');
    }

    public function delete(Court $court)
    {
        $court->delete();

        Alert::warning('تم حذف الملعب بنجاح!');

        return redirect()->route('platform.court');
    }
}
