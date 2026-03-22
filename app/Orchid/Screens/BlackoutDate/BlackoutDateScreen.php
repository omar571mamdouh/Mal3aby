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
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Color;
use Orchid\Screen\TD;
use Illuminate\Support\Str;

class BlackoutDateScreen extends Screen
{
    public function name(): ?string
    {
        return 'Blackout Dates';
    }

    public function description(): ?string
    {
        return 'إدارة أوقات إغلاق الملاعب';
    }

    public function query(): iterable
    {
        return [
            'blackout_dates' => BlackoutDate::with('court')->latest()->paginate(10),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('إضافة جديد')
                ->icon('bs.plus-circle')
                ->route('platform.blackout-dates.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('blackout_dates', [

                TD::make('court_id', 'الملعب')
                    ->render(fn($b) => '
                        <div>
                            <div style="font-weight:600;color:#1a1a2e;">🏟️ ' . ($b->court?->name ?? '—') . '</div>
                        </div>
                    '),
TD::make('start_date', 'الفترة')
    ->render(function($b) {
        $startDate = $b->start_date ? $b->start_date->format('Y-m-d') : null;
        $endDate   = $b->end_date ? $b->end_date->format('Y-m-d') : null;

        $startTime = $b->start_time ? $b->start_time : null;
        $endTime   = $b->end_time ? $b->end_time : null;

        // لو اليوم واحد وفيه وقت محدد
        if ($startTime && $endTime && $startDate === $endDate) {
            return '
                <div style="font-size:12px;">
                    <span style="color:#1565c0;">🕐 ' . $startTime . '</span>
                    <span style="color:#888;margin:0 4px;">→</span>
                    <span style="color:#c62828;">🕐 ' . $endTime . '</span>
                </div>
            ';
        }

        // لو فترة متعددة أيام أو بدون وقت
        return '
            <div style="font-size:12px;">
                <span style="color:#1565c0;">📅 ' . $startDate . '</span>' .
                ($endDate ? '<span style="color:#888;margin:0 4px;">→</span><span style="color:#c62828;">📅 ' . $endDate . '</span>' : '') . '
            </div>
        ';
    }),

                TD::make('start_time', 'الوقت')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn($b) => $b->start_time
                        ? '
                            <div style="font-size:12px;">
                                <span style="color:#1565c0;">🕐 ' . $b->start_time->format('H:i') . '</span>
                                ' . ($b->end_time ? '<span style="color:#888;margin:0 4px;">→</span><span style="color:#c62828;">🕐 ' . $b->end_time->format('H:i') . '</span>' : '') . '
                            </div>
                        '
                        : '<span style="display:inline-flex;align-items:center;gap:4px;background:#f5f5f5;color:#9e9e9e;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">⏰ يوم كامل</span>'
                    ),

                TD::make('type', 'النوع')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn($b) => match($b->type) {
                        'maintenance' => '<span style="background:#fff3e0;color:#e65100;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🔧 Maintenance</span>',
                        'holiday'     => '<span style="background:#e8f5e9;color:#2e7d32;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🎉 Holiday</span>',
                        'event'       => '<span style="background:#e3f2fd;color:#1565c0;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">📅 Event</span>',
                        'manual'      => '<span style="background:#f3e5f5;color:#6a1b9a;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">✋ Manual</span>',
                        default       => '<span style="background:#f5f5f5;color:#555;padding:4px 10px;border-radius:20px;font-size:12px;">' . ucfirst($b->type) . '</span>',
                    }),

                TD::make('reason', 'السبب')
                    ->render(fn($b) => $b->reason
                        ? '<span style="font-size:12px;color:#555;">💬 ' . Str::limit($b->reason, 40) . '</span>'
                        : '<span style="color:#aaa;">—</span>'
                    ),

                TD::make('active', 'الحالة')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn($b) => $b->active
                        ? '<span style="display:inline-flex;align-items:center;gap:4px;background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#2e7d32;border-radius:50%;display:inline-block;"></span> نشط</span>'
                        : '<span style="display:inline-flex;align-items:center;gap:4px;background:#ffebee;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#c62828;border-radius:50%;display:inline-block;"></span> متوقف</span>'
                    ),

                TD::make('actions', 'الإجراءات')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(fn($b) =>
                        '<div style="display:flex;gap:6px;justify-content:center;">'
                        . Link::make('تعديل')
                            ->route('platform.blackout-dates.edit', $b->id)
                            ->icon('pencil')
                            ->class('btn btn-sm btn-primary')
                        . Button::make('حذف')
                            ->method('delete')
                            ->confirm('هل أنت متأكد من حذف هذا السجل؟')
                            ->parameters(['blackout' => $b->id])
                            ->icon('trash')
                            ->class('btn btn-sm btn-danger')
                        . '</div>'
                    ),
            ]),
        ];
    }

    public function delete(BlackoutDate $blackout)
    {
        $blackout->delete();

        Alert::warning('تم الحذف بنجاح!');

        return redirect()->route('platform.blackout-dates');
    }
}