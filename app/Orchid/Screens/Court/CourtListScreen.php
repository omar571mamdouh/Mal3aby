<?php

namespace App\Orchid\Screens\Court;

use App\Models\Court;
use App\Orchid\Layouts\Court\CourtListLayout;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use App\Models\Facility;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class CourtListScreen extends Screen
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
            CourtListLayout::class,

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
                            'grass'  => 'Grass',
                            'turf'   => 'Turf',
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
          
        ];
    }

    public function asyncGetCourt(Court $court): array
    {
        return [
            'court' => [
                'facility_id'   => $court->facility_id,
                'name'          => $court->name,
                'surface_type'  => $court->surface_type,
                'price_per_hour'=> $court->price_per_hour,
                'opens_at'      => $court->opens_at,
                'closes_at'     => $court->closes_at,
                'capacity'      => $court->capacity,
            ],
        ];
    }

    public function create(Request $request)
    {
        $request->validate([
            'court.facility_id'   => 'required|exists:facilities,id',
            'court.name'          => 'required|string|max:255',
            'court.surface_type'  => 'required|in:grass,turf,indoor',
            'court.price_per_hour'=> 'nullable|numeric|min:0',
            'court.capacity'      => 'nullable|integer|min:1',
        ]);

        Court::create($request->get('court'));

        Alert::success('تم إنشاء الملعب بنجاح!');

        return redirect()->route('platform.court');
    }

    public function update(Request $request, Court $court)
    {
        $request->validate([
            'court.facility_id'   => 'required|exists:facilities,id',
            'court.name'          => 'required|string|max:255',
            'court.surface_type'  => 'required|in:grass,turf,indoor',
            'court.price_per_hour'=> 'nullable|numeric|min:0',
            'court.capacity'      => 'nullable|integer|min:1',
        ]);

        $court->update($request->get('court'));

        Alert::info('تم تحديث الملعب بنجاح!');

        return redirect()->route('platform.court');
    }

    public function delete(Request $request): void
    {
        Court::findOrFail($request->get('id'))->delete();

        Toast::warning('تم حذف الملعب بنجاح!');
    }
}