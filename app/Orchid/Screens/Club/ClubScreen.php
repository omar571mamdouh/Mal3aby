<?php

namespace App\Orchid\Screens\Club;

use Orchid\Screen\Screen;
use App\Models\Club;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Support\Facades\Alert;
use Illuminate\Http\Request;
use Orchid\Support\Color;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Link;

class ClubScreen extends Screen
{
    /**
     * Query data for the screen.
     */
    public function query(Club $club): iterable
    {
        return [
             'clubs' => Club::with('branches')->latest()->paginate(15),
        ];
    }

    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'إدارة الأندية';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'إدارة جميع الأندية في النظام';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('إضافة نادي')
                ->modal('addClubModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    /**
     * Layouts for the screen.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('clubs', [
                TD::make('name', 'الاسم')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn($club) => $club->name),

                TD::make('phone', 'الهاتف')
                    ->render(fn($club) => $club->phone ?? '-'),

                TD::make('email', 'البريد الإلكتروني')
                    ->render(fn($club) => $club->email ?? '-'),
            
             TD::make('logo', 'شعار النادي')
    ->width('100px')
    ->align(TD::ALIGN_CENTER)
    ->render(function ($club) {

        if (!$club->logo) {
            return '<span class="text-muted">—</span>';
        }

        // لو متخزن رابط كامل
        if (str_starts_with($club->logo, 'http')) {
            $url = $club->logo;
        } else {
            $url = asset('storage/' . $club->logo);
        }

        return "
            <img src='{$url}'
                 width='50'
                 height='50'
                 style='object-fit:cover;
                        border-radius:8px;
                        border:1px solid #eee;'>
        ";
    }),
TD::make('actions', 'الإجراءات')
    ->align(TD::ALIGN_CENTER)
    ->width('200px')
    ->render(function ($club) {
        return DropDown::make()
            ->icon('bs.three-dots-vertical')
            ->list([
                Link::make('تعديل')
                    ->route('platform.club.edit', $club) // يروح على صفحة التعديل
                    ->icon('pencil'),

                Button::make('حذف')
                    ->method('delete')
                    ->confirm('هل أنت متأكد من حذف النادي ' . $club->name . '؟')
                    ->parameters(['club' => $club->id])
                    ->icon('trash')
                    ->type(Color::DANGER()),
            ]);
    }),
            ]),

            // مودال إضافة نادي جديد
            Layout::modal('addClubModal', [
                Layout::rows([
                    Input::make('club.name')
                        ->title('الاسم')
                        ->placeholder('أدخل اسم النادي')
                        ->required(),

                    Input::make('club.phone')
                        ->title('الهاتف')
                        ->placeholder('أدخل رقم الهاتف'),

                    Input::make('club.email')
                        ->title('البريد الإلكتروني')
                        ->placeholder('أدخل البريد الإلكتروني')
                        ->type('email'),

                    Upload::make('club.logo')
                        ->title('شعار النادي')
                        ->acceptedFiles('.png,.jpg,.jpeg,.svg')
                        ->maxFiles(1)
                        ->storage('public'),
                ]),
            ])
                ->title('إضافة نادي جديد')
                ->applyButton('إنشاء')
                ->closeButton('إلغاء'),

            // مودال تعديل النادي
            Layout::modal('editClubModal', [
                Layout::rows([
                    Input::make('club.name')
                        ->title('الاسم')
                        ->required(),

                    Input::make('club.phone')
                        ->title('الهاتف'),

                    Input::make('club.email')
                        ->title('البريد الإلكتروني')
                        ->type('email'),

                    Upload::make('club.logo')
                        ->title('شعار النادي')
                        ->acceptedFiles('.png,.jpg,.jpeg,.svg')
                        ->maxFiles(1)
                        ->storage('public'),
                ]),
            ])
                ->title('تعديل النادي')
                ->applyButton('تحديث')
                ->closeButton('إلغاء')
                ->async('asyncGetClub'),
        ];

        Layout::table('branches', [

    TD::make('id', 'ID'),

    TD::make('name', 'اسم الفرع'),

    TD::make('city', 'المدينة'),

    TD::make('phone', 'الهاتف'),

    TD::make('active', 'الحالة')
        ->render(fn($branch) =>
            $branch->active
                ? '<span class="text-success fw-bold">نشط</span>'
                : '<span class="text-danger fw-bold">غير نشط</span>'
        ),

]);
    }

    /**
     * Async data for edit modal.
     */
    public function asyncGetClub(Club $club): array
    {
        return [
            'club' => $club,
        ];
    }

    /**
     * ===== مساعد لتحويل attachment ID إلى path =====
     */
    private function resolveLogoPath(Request $request): ?string
    {
        $attachmentId = $request->input('club.logo')[0] ?? null;

        if (!$attachmentId) {
            return null;
        }

        $attachment = Attachment::find($attachmentId);

        if (!$attachment) {
            return null;
        }

        // بيرجع مسار زي: 2025/02/filename.jpg
        return $attachment->path . $attachment->name . '.' . $attachment->extension;
    }

    /**
     * Create a new club.
     */
    public function create(Request $request)
    {
        $request->validate([
            'club.name'  => 'required|string|max:255',
            'club.email' => 'nullable|email|unique:clubs,email',
            'club.phone' => 'nullable|string|max:20',
        ]);

        $data = $request->get('club');

        // ===== الحل: نحول الـ ID لـ path حقيقي =====
        $logoPath = $this->resolveLogoPath($request);
        if ($logoPath) {
            $data['logo'] = $logoPath;
        } else {
            unset($data['logo']);
        }

        Club::create($data);

        Alert::success('تم إنشاء النادي بنجاح!');

        return redirect()->route('platform.club');
    }

    /**
     * Update existing club.
     */
    public function update(Request $request, Club $club)
    {
        $request->validate([
            'club.name'  => 'required|string|max:255',
            'club.email' => 'nullable|email|unique:clubs,email,' . $club->id,
            'club.phone' => 'nullable|string|max:20',
        ]);

        $data = $request->get('club');

        // ===== الحل: نحول الـ ID لـ path حقيقي =====
        $logoPath = $this->resolveLogoPath($request);
        if ($logoPath) {
            $data['logo'] = $logoPath;
        } else {
            // لو مفيش صورة جديدة، احتفظ بالقديمة
            unset($data['logo']);
        }

        $club->update($data);

        Alert::info('تم تحديث بيانات النادي بنجاح!');

        return redirect()->route('platform.club');
    }

    /**
     * Delete club.
     */
    public function delete(Club $club)
    {
        $club->delete();

        Alert::warning('تم حذف النادي بنجاح!');

        return redirect()->route('platform.club');
    }
}