<?php

namespace App\Orchid\Screens\TimeSlot;

use Orchid\Screen\Screen;
use App\Models\CourtTimeSlot;
use App\Models\Court;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use Orchid\Support\Color;

class TimeSlotEditScreen extends Screen
{
   

    public ?CourtTimeSlot $slot = null; 

    public function query(CourtTimeSlot $slot): array
    {
        $this->slot = $slot->load('court');
        return ['slot' => $this->slot];
    }

    public function name(): ?string
    {
        return $this->slot->exists
            ? 'تعديل Time Slot — ' . ($this->slot->court?->name ?? '')
            : 'إضافة Time Slot جديد';
    }

    public function description(): ?string
    {
        return $this->slot->exists
            ? 'تعديل بيانات الوقت المحدد للملعب'
            : 'إنشاء وقت جديد لملعب معين';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('رجوع للقائمة')
                ->route('platform.court.timeslot')
                ->icon('arrow-left'),

            Button::make($this->slot->exists ? 'حفظ التعديلات' : 'إنشاء')
                ->method($this->slot->exists ? 'update' : 'create')
                ->icon($this->slot->exists ? 'check' : 'plus')
                ->type(Color::SUCCESS()),
        ];
    }

    // ... بقية الـ layout, create, update

    public function layout(): iterable
    {
        $dayLabel = [
            'sunday'    => 'الأحد',
            'monday'    => 'الإثنين',
            'tuesday'   => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday'  => 'الخميس',
            'friday'    => 'الجمعة',
            'saturday'  => 'السبت',
        ];

        return [

            // ===== الفورم الرئيسي =====
            Layout::block(
                Layout::rows([

                    // ── الملعب ──
                    Select::make('slot.court_id')
                        ->title('الملعب')
                        ->help('اختر الملعب المرتبط بهذا الوقت')
                        ->fromModel(Court::class, 'name')
                        ->value($this->slot->court_id ?? null)
                        ->required(),

                    // ── اليوم ──
                    Select::make('slot.day')
                        ->title('اليوم')
                        ->help('اليوم الأسبوعي لهذا الوقت')
                        ->options($dayLabel)
                        ->value($this->slot->day ?? null)
                        ->required(),

                ])
            )
                ->title('بيانات الملعب واليوم')
                ->description('اختر الملعب واليوم المرتبطَين بهذا الـ Time Slot')
                ->commands(
                    Button::make('حفظ')
                        ->method($this->slot->exists ? 'update' : 'create')
                        ->type(Color::DEFAULT())
                ),

            Layout::block(
                Layout::rows([

                    // ── وقت البداية ──
                    Input::make('slot.start_time')
                        ->type('time')
                        ->title('وقت البداية')
                        ->help('الساعة التي يبدأ فيها الحجز')
                        ->value($this->slot->start_time ?? null)
                        ->required(),

                    // ── وقت النهاية ──
                    Input::make('slot.end_time')
                        ->type('time')
                        ->title('وقت النهاية')
                        ->help('الساعة التي ينتهي فيها الحجز — يجب أن تكون بعد وقت البداية')
                        ->value($this->slot->end_time ?? null)
                        ->required(),

                ])
            )
                ->title('الوقت')
                ->description('حدد وقت البداية والنهاية للـ Time Slot'),

            Layout::block(
                Layout::rows([

                    Select::make('slot.active')
                        ->title('حالة الـ Time Slot')
                        ->help('الـ Slots غير النشطة لا تظهر للمستخدمين عند الحجز')
                        ->options([
                            1 => '✅  نشط — متاح للحجز',
                            0 => '🔴  غير نشط — مخفي من الحجز',
                        ])
                        ->value($this->slot->active ?? 1)
                        ->required(),

                ])
            )
                ->title('الحالة')
                ->description('تحكم في ظهور هذا الوقت للمستخدمين'),
        ];
    }
public function create(Request $request)
{
    $data = $request->get('slot', []);

    // ✅ توحيد format الوقت
    if (!empty($data['start_time'])) {
        $data['start_time'] = date('H:i:s', strtotime($data['start_time']));
    }
    if (!empty($data['end_time'])) {
        $data['end_time'] = date('H:i:s', strtotime($data['end_time']));
    }

    $validator = validator($data, [
        'court_id'   => 'required|exists:courts,id',
        'day'        => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        'start_time' => 'required|date_format:H:i:s',
        'end_time'   => 'required|date_format:H:i:s|after:start_time',
        'active'     => 'nullable|in:0,1',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    CourtTimeSlot::create($data);

    Alert::success('✅ تم إنشاء Time Slot بنجاح!');

    return redirect()->route('platform.court.timeslot');
}

public function update(Request $request, CourtTimeSlot $slot)
{
    $data = $request->get('slot', []);

    // ✅ توحيد format الوقت
    if (!empty($data['start_time'])) {
        $data['start_time'] = date('H:i:s', strtotime($data['start_time']));
    }
    if (!empty($data['end_time'])) {
        $data['end_time'] = date('H:i:s', strtotime($data['end_time']));
    }

    $validator = validator($data, [
        'court_id'   => 'required|exists:courts,id',
        'day'        => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        'start_time' => 'required|date_format:H:i:s',
        'end_time'   => 'required|date_format:H:i:s|after:start_time',
        'active'     => 'nullable|in:0,1',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // ===== التحقق من نطاق وقت الملعب =====
    $court = \App\Models\Court::findOrFail($data['court_id']);
    if ($data['start_time'] < $court->opens_at || $data['end_time'] > $court->closes_at) {
        return back()->withErrors([
            'start_time' => "الملعب يفتح من {$court->opens_at} إلى {$court->closes_at}. الرجاء اختيار وقت ضمن هذا النطاق."
        ])->withInput();
    }

    // ===== التحقق من التعارض مع TimeSlots أخرى لنفس الملعب ونفس اليوم =====
    $conflict = \App\Models\CourtTimeSlot::where('court_id', $court->id)
        ->where('day', $data['day'])
        ->where('id', '!=', $slot->id) // استثناء الـ slot الحالي
        ->where(function($q) use ($data) {
            $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
              ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
              ->orWhere(function($q2) use ($data) {
                  $q2->where('start_time', '<=', $data['start_time'])
                     ->where('end_time', '>=', $data['end_time']);
              });
        })
        ->exists();

    if ($conflict) {
        return back()->withErrors([
            'start_time' => 'هناك تعارض مع TimeSlot موجود بالفعل لهذا الملعب.'
        ])->withInput();
    }

    // ===== حفظ البيانات بعد كل الـ validation =====
    $slot->update($data);

    Alert::info('✏️ تم تحديث Time Slot بنجاح!');

    return redirect()->route('platform.court.timeslot');
}
}