<?php

namespace App\Orchid\Screens\BlackoutDate;

use App\Models\BlackoutDate;
use App\Models\Court;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;

class BlackoutDateEditScreen extends Screen
{
    public ?BlackoutDate $blackout = null;

    public function query(?BlackoutDate $blackout = null): iterable
    {
        $blackout ??= new BlackoutDate();
        $this->blackout = $blackout;

        return [
            'blackout' => $blackout, // ✅ Orchid بيملي الـ fields من هنا
        ];
    }
    public function name(): ?string
    {
        return $this->blackout?->exists
            ? 'تعديل Blackout Date: ' . $this->blackout->court?->name
            : 'إضافة Blackout Date';
    }

    public function description(): ?string
    {
        return 'إدارة أوقات إغلاق الملاعب';
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

                // === العمود الأيسر - بيانات الملعب والنوع ===
                Layout::rows([
                    Select::make('blackout.court_id')
                        ->title('الملعب')
                        ->options(Court::pluck('name', 'id'))
                        ->required(),

                    Select::make('blackout.type')
                        ->title('نوع الإغلاق')
                        ->options([
                            'maintenance' => '🔧 Maintenance',
                            'holiday'     => '🎉 Holiday',
                            'event'       => '📅 Event',
                            'manual'      => '✋ Manual',
                        ])
                        ->value($this->blackout->type) // ✅ هنا القيمة الحالية عشان تظهر في edit
                        ->help('سبب إغلاق الملعب'),

                    TextArea::make('blackout.reason')
                        ->title('السبب')
                        ->placeholder('اكتب سبب الإغلاق هنا...')
                        ->rows(3)
                        ->help('وصف اختياري لسبب الإغلاق'),

                    CheckBox::make('blackout.active')
                        ->title('نشط')
                        ->sendTrueOrFalse()
                        ->help('هل هذا الإغلاق فعّال حالياً؟'),
                ]),

                // === العمود الأيمن - التواريخ والأوقات ===
                Layout::rows([
                    DateTimer::make('blackout.start_date')
                        ->title('تاريخ البداية')
                        ->format('Y-m-d')
                        ->help('تاريخ بداية الإغلاق')
                        ->required(),

                    DateTimer::make('blackout.end_date')
                        ->title('تاريخ النهاية')
                        ->format('Y-m-d')
                        ->help('تاريخ نهاية الإغلاق (اختياري)'),

                    DateTimer::make('blackout.start_time')
                        ->title('وقت البداية')
                        ->format('H:i')
                        ->enableTime()
                        ->withoutSeconds()
                        ->help('وقت بداية الإغلاق (اختياري)'),

                    DateTimer::make('blackout.end_time')
                        ->title('وقت النهاية')
                        ->format('H:i')
                        ->enableTime()
                        ->withoutSeconds()
                        ->help('وقت نهاية الإغلاق (اختياري)'),
                ]),

            ]),
        ];
    }
    public function asyncGetBlackout(BlackoutDate $blackout): array
    {
        return [
            'blackout' => $blackout,
        ];
    }

    public function save(Request $request)
    {
        $request->validate([
            'blackout.court_id'   => 'required|exists:courts,id',
            'blackout.start_date' => 'required|date',
        ]);

        $id = $request->route('blackout');
        $blackout = $id ? BlackoutDate::findOrFail($id) : new BlackoutDate();

        $blackout->fill($request->get('blackout'))->save();

        Toast::success('تم الحفظ بنجاح!');

        return redirect()->route('platform.blackout-dates');
    }
}
