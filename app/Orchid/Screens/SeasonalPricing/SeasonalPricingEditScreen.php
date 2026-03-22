<?php

namespace App\Orchid\Screens\SeasonalPricing;

use App\Models\SeasonalPricing;
use App\Models\Court;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Color;

class SeasonalPricingEditScreen extends Screen
{
    public ?SeasonalPricing $pricing = null;

    public function query(?SeasonalPricing $pricing = null): iterable
    {
        $pricing ??= new SeasonalPricing();
        $this->pricing = $pricing;

        return [
            'pricing' => $pricing,
        ];
    }

    public function name(): ?string
    {
        return $this->pricing?->exists
            ? 'تعديل سعر الموسم: ' . $this->pricing->court?->name
            : 'إضافة سعر موسم جديد';
    }

    public function description(): ?string
    {
        return 'حدد الفترة والسعر لكل ملعب';
    }

    public function commandBar(): iterable
    {
        return [
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
                Layout::rows([
                    Select::make('pricing.court_id')
                        ->title('الملعب')
                        ->options(Court::pluck('name', 'id'))
                        ->required()
                        ->value($this->pricing->court_id),

                    Select::make('pricing.season')
                        ->title('الموسم')
                        ->options([
                            'summer' => 'صيف',
                            'winter' => 'شتاء',
                            'spring' => 'ربيع',
                            'autumn' => 'خريف',
                        ])
                        ->value($this->pricing->season)
                        ->required(),

                    DateTimer::make('pricing.start_date')
                        ->title('تاريخ البداية')
                        ->format('Y-m-d')
                        ->value($this->pricing->start_date)
                        ->required(),

                    DateTimer::make('pricing.end_date')
                        ->title('تاريخ النهاية')
                        ->format('Y-m-d')
                        ->value($this->pricing->end_date),

                    Input::make('pricing.price')
                        ->title('السعر')
                        ->type('number')
                        ->step(0.01)
                        ->value($this->pricing->price)
                        ->required(),
                ]),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'pricing.court_id' => 'required|exists:courts,id',
            'pricing.season' => 'required|string',
            'pricing.start_date' => 'required|date',
            'pricing.end_date' => 'nullable|date',
            'pricing.price' => 'required|numeric',
        ]);

        $pricing = $this->pricing ?? new SeasonalPricing();
        $pricing->fill($data['pricing'])->save();

        Toast::success('تم الحفظ بنجاح!');
        return redirect()->route('platform.seasonal-pricing');
    }
}