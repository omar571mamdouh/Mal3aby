<?php

namespace App\Orchid\Screens\Club;

use Orchid\Screen\Screen;
use App\Models\Club;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;
use Orchid\Attachment\Models\Attachment;
use Illuminate\Http\Request;

class ClubEditScreen extends Screen
{
    public ?Club $club = null;

    /**
     * Query data for the screen
     */
    public function query(?Club $club = null): iterable
    {
        $this->club = $club ?? new Club();

        return [
            'club'     => $this->club,
            'branches' => $this->club->exists ? $this->club->branches()->latest()->get() : collect(),
        ];
    }

    public function name(): ?string
    {
        return $this->club->exists
            ? 'تعديل النادي: ' . $this->club->name
            : 'إضافة نادي جديد';
    }

    public function description(): ?string
    {
        return $this->club->exists
            ? 'تعديل بيانات النادي'
            : 'إنشاء نادي جديد في النظام';
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

                // العمود الأيسر - البيانات الأساسية
                Layout::rows([
                    Input::make('club.name')
                        ->title('اسم النادي')
                        ->addon('bs.building')
                        ->placeholder('مثال: نادي الأهلي')
                        ->required(),

                    Input::make('club.phone')
                        ->title('الهاتف')
                        ->addon('bs.telephone')
                        ->placeholder('مثال: 01012345678')
                        ->help('رقم التواصل الرئيسي'),

                    Input::make('club.email')
                        ->title('البريد الإلكتروني')
                        ->addon('bs.envelope')
                        ->placeholder('example@club.com')
                        ->type('email')
                        ->help('البريد الرسمي للنادي'),

                    Select::make('club.active')
                        ->title('الحالة')
                        ->options([1 => 'نشط', 0 => 'غير نشط'])
                        ->value($this->club?->active ?? 1)
                        ->required(),
                ]),

                // العمود الأيمن - الشعار
                Layout::rows([
                    Picture::make('club.logo')
                        ->title('شعار النادي')
                        ->acceptedFiles('image/*')
                        ->storage('public')
                        ->help('يفضل صورة مربعة بحجم 200×200 بكسل'),

                    // خيار إزالة الشعار إذا موجود
                    CheckBox::make('club.remove_logo')
                        ->value(false)
                        ->title('إزالة الشعار الحالي')
                        ->canSee($this->club?->logo !== null)
                        ->help('اختر لإزالة الشعار الحالي'),
                ]),
            ]),

            // جدول الفروع - يظهر فقط عند التعديل
            Layout::table('branches', [
                \Orchid\Screen\TD::make('name', 'الفرع')
                    ->render(fn($b) => "<strong>{$b->name}</strong>"),

                \Orchid\Screen\TD::make('city', 'المدينة')
                    ->render(fn($b) => $b->city ? "<span style='color:#16a34a'>📍 {$b->city}</span>" : '—'),

                \Orchid\Screen\TD::make('phone', 'الهاتف')
                    ->render(fn($b) => $b->phone ? "📞 {$b->phone}" : '—'),

                \Orchid\Screen\TD::make('created_at', 'تاريخ الإضافة')
                    ->render(fn($b) => $b->created_at ? $b->created_at->format('Y-m-d') : '—'),
            ])
            ->title('فروع النادي')
            ->canSee($this->club?->exists ?? false),
        ];
    }

    /**
     * حفظ أو تعديل النادي
     */
    public function save(Request $request)
    {
        $request->validate([
            'club.name'   => 'required|string|max:255',
            'club.email'  => 'nullable|email',
            'club.phone'  => 'nullable|string|max:20',
            'club.active' => 'required|boolean',
            'club.logo'   => 'nullable',
        ]);

        $data = [
            'name'   => $request->input('club.name'),
            'email'  => $request->input('club.email'),
            'phone'  => $request->input('club.phone'),
            'active' => $request->input('club.active', 1),
        ];

        // معالجة الشعار
        if ($request->filled('club.remove_logo')) {
            $data['logo'] = null;
        } else {
            $logoValue = $request->input('club.logo');
            if ($logoValue) {
                if (is_numeric($logoValue)) {
                    $attachment = Attachment::find($logoValue);
                    if ($attachment) {
                        $data['logo'] = $attachment->path . $attachment->name . '.' . $attachment->extension;
                    }
                } else {
                    $data['logo'] = $logoValue;
                }
            }
        }

        $club = $this->club ?? new Club();
        $club->fill($data)->save();

        Toast::success(
            $request->filled('club.remove_logo') ? 'تم إزالة الشعار بنجاح!' :
            ($club->wasRecentlyCreated ? 'تم إنشاء النادي بنجاح!' : 'تم تحديث النادي بنجاح!')
        );

        return redirect()->route('platform.club');
    }
}