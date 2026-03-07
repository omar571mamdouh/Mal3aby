<?php

namespace App\Orchid\Screens\Club;

use Orchid\Screen\Screen;
use App\Models\Club;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Orchid\Support\Color;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Picture;

class ClubEditScreen extends Screen
{
    /**
     * Property عامة لتخزين النادي الحالي
     */
    public ?Club $club = null;

    /**
     * Query data for the screen.
     */
    public function query(Club $club): iterable
{
    $this->club = $club;

    return [
        'club' => $club,
        'branches' => $club->exists
            ? $club->branches()->latest()->get()
            : collect(),
    ];
}

    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return $this->club && $this->club->exists
            ? 'تعديل النادي: ' . $this->club->name
            : 'إضافة نادي';
    }

    /**
     * Display command bar buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('حفظ')
                ->type(Color::SUCCESS())
                ->method('save'),
        ];
    }

    /**
     * Layouts for the screen.
     */
   public function layout(): iterable
{
    return [
        Layout::columns([
            // === العمود الأيسر - المعلومات الأساسية ===
            Layout::rows([
                Input::make('club.name')
                    ->title('اسم النادي')
                    ->prefix('bs.building')
                    ->placeholder('مثال: نادي الأهلي')
                    ->help('الاسم الرسمي للنادي')
                    ->required(),

                Input::make('club.phone')
                    ->title('الهاتف')
                    ->prefix('bs.telephone')
                    ->placeholder('مثال: 01012345678')
                    ->help('رقم التواصل الرئيسي'),

                Input::make('club.email')
                    ->title('البريد الإلكتروني')
                    ->prefix('bs.envelope')
                    ->placeholder('example@club.com')
                    ->help('البريد الرسمي للنادي')
                    ->type('email'),
            ]),

            // === العمود الأيمن - الشعار ===
            Layout::rows([
                Picture::make('club.logo')
                    ->title('شعار النادي')
                    ->acceptedFiles('image/*')
                    ->help('يُفضل صورة مربعة بحجم 200×200 بكسل'),
            ]),
        ]),

        // === جدول الفروع ===
        Layout::table('branches', [
            TD::make('name', 'الفرع')
                ->render(fn($b) => '
                    <div style="font-weight:600;color:#1a1a2e;">' . $b->name . '</div>
                '),

            TD::make('city', 'المدينة')
                ->render(fn($b) => $b->city
                    ? '<span style="font-size:12px;">📍 ' . $b->city . '</span>'
                    : '<span style="color:#aaa;">—</span>'
                ),

            TD::make('phone', 'الهاتف')
                ->render(fn($b) => $b->phone
                    ? '<span style="font-size:12px;">📞 ' . $b->phone . '</span>'
                    : '<span style="color:#aaa;">—</span>'
                ),

            TD::make('created_at', 'تاريخ الإضافة')
                ->render(fn($b) => $b->created_at
                    ? '<span style="font-size:12px;color:#888;">📅 ' . $b->created_at->format('Y-m-d') . '</span>'
                    : '<span style="color:#aaa;">—</span>'
                ),
        ])->title('فروع النادي')
          ->canSee($this->club?->exists),
    ];
}

    /**
     * Save or update club.
     */
public function save(Request $request)
{
    $clubId = $request->route('club');
    $club   = $clubId ? Club::findOrFail($clubId) : new Club();

    $data = $request->input('club', []);

    // نجيب الـ attachment ID
    $logoIds = (array) $request->input('club.logo', []);

    if (!empty($logoIds)) {

        $attachment = \Orchid\Attachment\Models\Attachment::find(end($logoIds));

        if ($attachment) {

            $newPath = $attachment->path . $attachment->name . '.' . $attachment->extension;

            // لو في صورة قديمة واتغيرت
            if ($club->exists && $club->logo && $club->logo !== $newPath) {
                $oldFile = storage_path('app/public/' . $club->logo);
                if (file_exists($oldFile)) {
                    unlink($oldFile); // حذف الصورة القديمة
                }
            }

            $data['logo'] = $newPath; // نخزن path فقط
        }

    } else {
        // المستخدم ضغط Remove → نحذف الصورة القديمة من السيرفر والـ DB
        if ($club->exists && $club->logo) {
            $oldFile = storage_path('app/public/' . $club->logo);
            if (file_exists($oldFile)) {
                unlink($oldFile); // حذف الصورة القديمة
            }
        }
        $data['logo'] = null; // نحذف path الصورة من قاعدة البيانات
    }

    $club->fill($data)->save();

    Toast::success(
        $club->wasRecentlyCreated
            ? 'تم إنشاء النادي'
            : 'تم تحديث النادي'
    );

    return redirect()->route('platform.club');
}
}