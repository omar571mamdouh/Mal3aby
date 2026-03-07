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

            TD::make('name', 'النادي')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn($club) => '
                    <div style="font-weight:600;color:#1a1a2e;">' . $club->name . '</div>
                '),

            TD::make('phone', 'التواصل')
                ->render(fn($club) => '
                    <div>
                        <div style="font-size:12px;">📞 ' . ($club->phone ?? '—') . '</div>
                        <div style="font-size:12px;margin-top:3px;">✉️ ' . ($club->email ?? '—') . '</div>
                    </div>
                '),

            TD::make('active', 'الحالة')
                ->align(TD::ALIGN_CENTER)
                ->render(fn($club) => $club->active
                    ? '<span style="display:inline-flex;align-items:center;gap:4px;background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#2e7d32;border-radius:50%;display:inline-block;"></span> نشط</span>'
                    : '<span style="display:inline-flex;align-items:center;gap:4px;background:#ffebee;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#c62828;border-radius:50%;display:inline-block;"></span> غير نشط</span>'
                ),

            TD::make('actions', 'الإجراءات')
                ->align(TD::ALIGN_CENTER)
                ->width('160px')
                ->render(fn($club) =>
                    '<div style="display:flex;gap:6px;justify-content:center;">'
                    . Link::make('تعديل')
                        ->route('platform.club.edit', $club)
                        ->icon('pencil')
                        ->class('btn btn-sm btn-primary')
                    . Button::make('حذف')
                        ->method('delete')
                        ->confirm('هل أنت متأكد من حذف النادي ' . $club->name . '؟')
                        ->parameters(['club' => $club->id])
                        ->icon('trash')
                        ->class('btn btn-sm btn-danger')
                    . '</div>'
                ),
        ]),

        // مودال إضافة نادي جديد
        Layout::modal('addClubModal', [
            Layout::rows([
                Input::make('club.name')
                    ->title('اسم النادي')
                    ->prefix('bs.building')
                    ->placeholder('مثال: نادي الأهلي')
                    ->required(),

                Input::make('club.phone')
                    ->title('الهاتف')
                    ->prefix('bs.telephone')
                    ->placeholder('مثال: 01012345678'),

                Input::make('club.email')
                    ->title('البريد الإلكتروني')
                    ->prefix('bs.envelope')
                    ->placeholder('example@club.com')
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
                    ->title('اسم النادي')
                    ->prefix('bs.building')
                    ->placeholder('مثال: نادي الأهلي')
                    ->required(),

                Input::make('club.phone')
                    ->title('الهاتف')
                    ->prefix('bs.telephone')
                    ->placeholder('مثال: 01012345678'),

                Input::make('club.email')
                    ->title('البريد الإلكتروني')
                    ->prefix('bs.envelope')
                    ->placeholder('example@club.com')
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


