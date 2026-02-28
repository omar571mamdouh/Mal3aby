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
            Layout::rows([
                Input::make('club.name')
                    ->title('الاسم')
                    ->required(),

                Input::make('club.phone')
                    ->title('الهاتف'),

                Input::make('club.email')
                    ->title('البريد الإلكتروني')
                    ->type('email'),

               
Picture::make('club.logo')
    ->title('شعار النادي')
    ->acceptedFiles('image/*'),
            ]),
            
              // 👇 جدول الفروع
     Layout::table('branches', [

    TD::make('name', 'اسم الفرع')
        ->render(fn($branch) => $branch->name),

    TD::make('city', 'المدينة')
        ->render(fn($branch) => $branch->city ?? '-'),

    TD::make('phone', 'الهاتف')
        ->render(fn($branch) => $branch->phone ?? '-'),

    TD::make('created_at', 'تاريخ الإضافة')
        ->render(fn($branch) =>
            $branch->created_at
                ? $branch->created_at->format('Y-m-d')
                : '-'
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