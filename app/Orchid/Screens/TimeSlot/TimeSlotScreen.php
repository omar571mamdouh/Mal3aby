<?php

namespace App\Orchid\Screens\TimeSlot;

use Orchid\Screen\Screen;
use App\Models\CourtTimeSlot;
use App\Models\Court;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use Orchid\Support\Color;

class TimeSlotScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'slots' => CourtTimeSlot::with('court')->latest()->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'إدارة أوقات الملاعب';
    }

    public function description(): ?string
    {
        return 'إدارة كل الـ Time Slots للملاعب';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('إضافة وقت')
                ->modal('addSlotModal')
                ->method('create')
                ->icon('plus')
                ->type(Color::SUCCESS()),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('slots', [

                // ── الملعب ──
                TD::make('court', 'الملعب')
                    ->render(fn($slot) => "
                        <div style='display:flex;align-items:center;gap:10px;'>
                            <div style='
                                width:36px;height:36px;
                                border-radius:10px;
                                background:linear-gradient(135deg,#6366f1,#8b5cf6);
                                display:flex;align-items:center;justify-content:center;
                                font-size:16px;flex-shrink:0;
                            '>🏟️</div>
                            <div style='font-weight:600;color:#1e293b;font-size:14px;'>
                                " . ($slot->court?->name ?? '—') . "
                            </div>
                        </div>
                    "),

                // ── اليوم ──
                TD::make('day', 'اليوم')
                    ->render(function ($slot) {
                        $days = [
                            'sunday'    => ['label' => 'الأحد',    'bg' => '#ede9fe', 'color' => '#6d28d9', 'icon' => '☀️'],
                            'monday'    => ['label' => 'الإثنين',  'bg' => '#dbeafe', 'color' => '#1d4ed8', 'icon' => '🌙'],
                            'tuesday'   => ['label' => 'الثلاثاء', 'bg' => '#dcfce7', 'color' => '#15803d', 'icon' => '🌿'],
                            'wednesday' => ['label' => 'الأربعاء', 'bg' => '#fef9c3', 'color' => '#a16207', 'icon' => '⭐'],
                            'thursday'  => ['label' => 'الخميس',   'bg' => '#fee2e2', 'color' => '#b91c1c', 'icon' => '🔥'],
                            'friday'    => ['label' => 'الجمعة',   'bg' => '#f3e8ff', 'color' => '#7e22ce', 'icon' => '✨'],
                            'saturday'  => ['label' => 'السبت',    'bg' => '#fce7f3', 'color' => '#be185d', 'icon' => '🎯'],
                        ];

                        $key = strtolower(trim($slot->day ?? ''));
                        $d   = $days[$key] ?? null;

                        if (!$d) {
                            return '<span style="color:#94a3b8;font-size:13px;">' . ($slot->day ?? '—') . '</span>';
                        }

                        return "
                            <span style='
                                display:inline-flex;
                                align-items:center;
                                gap:5px;
                                background:{$d['bg']};
                                color:{$d['color']};
                                padding:5px 14px;
                                border-radius:20px;
                                font-size:13px;
                                font-weight:600;
                                white-space:nowrap;
                            '>{$d['icon']} {$d['label']}</span>
                        ";
                    }),

                // ── الوقت ──
                TD::make('time', 'الوقت')
                    ->render(function ($slot) {
                        $start = \Carbon\Carbon::parse($slot->start_time)->format('h:i A');
                        $end   = \Carbon\Carbon::parse($slot->end_time)->format('h:i A');

                        return "
                            <div style='display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;padding:6px 14px;border-radius:10px;white-space:nowrap;'>
                                <span style='font-size:13px;'>🕐</span>
                                <span style='color:#0f172a;font-size:13px;font-weight:600;'>{$start}</span>
                                <span style='color:#cbd5e1;font-size:12px;font-weight:500;'>→</span>
                                <span style='color:#0f172a;font-size:13px;font-weight:600;'>{$end}</span>
                            </div>
                        ";
                    }),

                // ── الحالة ──
                TD::make('active', 'الحالة')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn($slot) =>
                        $slot->active
                            ? "<span style='display:inline-flex;align-items:center;gap:5px;background:#dcfce7;color:#15803d;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                <span style='width:6px;height:6px;background:#16a34a;border-radius:50%;display:inline-block;box-shadow:0 0 0 2px #bbf7d0;'></span>نشط
                               </span>"
                            : "<span style='display:inline-flex;align-items:center;gap:5px;background:#fee2e2;color:#b91c1c;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;'>
                                <span style='width:6px;height:6px;background:#dc2626;border-radius:50%;display:inline-block;'></span>غير نشط
                               </span>"
                    ),

                // ── الإجراءات ──
                TD::make('actions', 'الإجراءات')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(fn($slot) =>
                        '<div style="display:flex;gap:6px;justify-content:center;">'
                            . Link::make('تعديل')
                                ->route('platform.court.timeslot.edit', ['slot' => $slot->id])
                                ->icon('pencil')
                                ->class('btn btn-sm btn-primary')
                            . Button::make('حذف')
                                ->method('delete')
                                ->confirm('هل أنت متأكد من حذف هذا الـ Time Slot؟')
                                ->parameters(['slot' => $slot->id])
                                ->icon('trash')
                                ->class('btn btn-sm btn-danger')
                                ->type(Color::DANGER())
                            . '</div>'
                    ),
            ]),

            // ===== مودال إضافة =====
            Layout::modal('addSlotModal', [
                Layout::rows([
                    Select::make('slot.court_id')
                        ->title('الملعب')
                        ->fromModel(Court::class, 'name')
                        ->required(),

                    Select::make('slot.day')
                        ->title('اليوم')
                        ->options([
                            'sunday'    => 'الأحد',
                            'monday'    => 'الإثنين',
                            'tuesday'   => 'الثلاثاء',
                            'wednesday' => 'الأربعاء',
                            'thursday'  => 'الخميس',
                            'friday'    => 'الجمعة',
                            'saturday'  => 'السبت',
                        ])
                        ->required(),

                    Input::make('slot.start_time')
                        ->type('time')
                        ->title('وقت البداية')
                        ->required(),

                    Input::make('slot.end_time')
                        ->type('time')
                        ->title('وقت النهاية')
                        ->required(),

                    Select::make('slot.active')
                        ->title('الحالة')
                        ->options([
                            1 => 'نشط',
                            0 => 'غير نشط',
                        ])
                        ->required(),
                ]),
            ])
                ->title('إضافة Time Slot')
                ->applyButton('إنشاء')
                ->closeButton('إلغاء'),
        ];
    }
public function create(Request $request)
{
    $request->validate([
        'slot.court_id'   => 'required|exists:courts,id',
        'slot.day'        => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        'slot.start_time' => 'required|date_format:H:i',
        'slot.end_time'   => 'required|date_format:H:i|after:slot.start_time',
    ]);

    $court = Court::findOrFail($request->input('slot.court_id'));

    // ✅ التحقق من ساعات الملعب
    $startTime = $request->input('slot.start_time');
    $endTime   = $request->input('slot.end_time');

    if ($startTime < $court->opens_at || $endTime > $court->closes_at) {
        return back()->withErrors([
            'slot.start_time' => "الملعب يفتح من {$court->opens_at} إلى {$court->closes_at}. الرجاء اختيار وقت ضمن هذا النطاق."
        ])->withInput();
    }

    CourtTimeSlot::create($request->get('slot'));

    Alert::success('تم إنشاء Time Slot بنجاح!');
    return redirect()->route('platform.court.timeslot');
}

    public function delete(CourtTimeSlot $slot)
    {
        $slot->delete();

        Alert::warning('تم حذف Time Slot بنجاح!');

        return redirect()->route('platform.court.timeslot');
    }
}