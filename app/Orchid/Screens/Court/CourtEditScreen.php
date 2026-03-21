<?php

namespace App\Orchid\Screens\Court;

use Orchid\Screen\Screen;
use App\Models\Court;
use App\Models\Facility;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Orchid\Support\Color;

class CourtEditScreen extends Screen
{
    public ?Court $court = null;

    public function query(Court $court): iterable
    {
        $this->court = $court;

        return [
            'court' => $court,
        ];
    }

    public function name(): ?string
    {
        return $this->court && $this->court->exists
            ? 'تعديل الملعب: ' . $this->court->name
            : 'إضافة ملعب';
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
                // === العمود الأيسر - المعلومات الأساسية ===
                Layout::rows([
                    Select::make('court.facility_id')
                        ->title('المرفق')
                        ->prefix('bs.building')
                        ->placeholder('اختر المرفق...')
                        ->fromModel(Facility::class, 'name')
                        ->help('المرفق التابع له هذا الملعب')
                        ->required(),

                    Input::make('court.name')
                        ->title('اسم الملعب')
                        ->prefix('bs.tag')
                        ->placeholder('مثال: ملعب A')
                        ->help('اسم واضح ومميز للملعب')
                        ->required(),

                    Select::make('court.surface_type')
                        ->title('نوع الأرضية')
                        ->prefix('bs.grid')
                        ->placeholder('اختر نوع الأرضية...')
                        ->options([
                            'grass'  => '🌿 Grass',
                            'turf'   => '🟦 Turf',
                            'indoor' => '🏠 Indoor',
                        ])
                        ->help('نوع سطح الملعب')
                        ->required(),

                    Select::make('court.active')
                        ->title('الحالة')
                        ->prefix('bs.toggle-on')
                        ->options([
                            1 => '🟢 نشط',
                            0 => '🔴 غير نشط',
                        ])
                        ->help('هل الملعب متاح للحجز حالياً؟'),
                ]),

                // === العمود الأيمن - التفاصيل ===
                Layout::rows([
                    Input::make('court.price_per_hour')
                        ->title('سعر الساعة')
                        ->prefix('bs.currency-dollar')
                        ->placeholder('0.00')
                        ->type('number')
                        ->step('0.01')
                        ->help('السعر بالجنيه المصري'),

                    Input::make('court.capacity')
                        ->title('السعة')
                        ->prefix('bs.people')
                        ->placeholder('عدد اللاعبين...')
                        ->type('number')
                        ->min(1)
                        ->help('الحد الأقصى لعدد اللاعبين'),

                    Input::make('court.opens_at')
                        ->title('يفتح الساعة')
                        ->prefix('bs.clock')
                        ->type('time')
                        ->help('وقت فتح الملعب'),

                    Input::make('court.closes_at')
                        ->title('يغلق الساعة')
                        ->prefix('bs.clock-history')
                        ->type('time')
                        ->help('وقت إغلاق الملعب'),
                ]),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $courtId = $request->route('court');
        $court   = $courtId ? Court::findOrFail($courtId) : new Court();

       $data = array_merge(
    $request->input('bs_building.court', []),
    $request->input('bs_tag.court', []),
    $request->input('bs_grid.court', []),
    $request->input('bs_toggle-on.court', []),
    $request->input('bs_currency-dollar.court', []),
    $request->input('bs_people.court', []),
    $request->input('bs_clock.court', []),
    $request->input('bs_clock-history.court', []),
);

// ✅ توحيد format الوقت قبل الـ validation
if (!empty($data['opens_at'])) {
    $data['opens_at'] = date('H:i:s', strtotime($data['opens_at']));
}
if (!empty($data['closes_at'])) {
    $data['closes_at'] = date('H:i:s', strtotime($data['closes_at']));
}


        // ✅ Validation على الداتا المجمعة
        $validator = validator($data, [
            'facility_id'    => 'required|exists:facilities,id',
            'name'           => 'required|string|max:255',
            'surface_type'   => 'required|in:grass,turf,indoor',
            'price_per_hour' => 'nullable|numeric|min:0',
            'capacity'       => 'nullable|integer|min:1',
            'opens_at' => 'nullable|date_format:H:i:s',
            'closes_at' => 'nullable|date_format:H:i:s',
            'active'         => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $court->fill($data)->save();

        Toast::success(
            $court->wasRecentlyCreated
                ? 'تم إنشاء الملعب بنجاح!'
                : 'تم تحديث الملعب بنجاح!'
        );

        return redirect()->route('platform.court');
    }
}
