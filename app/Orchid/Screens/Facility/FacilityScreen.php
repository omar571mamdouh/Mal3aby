<?php

namespace App\Orchid\Screens\Facility;

use App\Models\Facility;
use App\Models\Branch;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Color;
use Orchid\Screen\Fields\Upload;
use Orchid\Attachment\Models\Attachment;
use Illuminate\Support\Arr;

class FacilityScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'facilities' => Facility::with('branch.club')->latest()->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'إدارة المرافق';
    }

    public function description(): ?string
    {
        return 'إدارة جميع المرافق لكل فرع';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('إضافة مرفق')
                ->modal('addFacilityModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [

            // ===== جدول المرافق =====
            Layout::table('facilities', [
               TD::make('name', 'اسم المرفق')
    ->sort()
    ->filter(TD::FILTER_TEXT)
    ->render(fn($facility) => $facility->name),

TD::make('branch', 'الفرع')
    ->render(fn($facility) =>
        $facility->branch?->name ?? '—'
    ),

TD::make('club', 'النادي')
    ->render(fn($facility) =>
        $facility->branch?->club?->name ?? '—'
    ),

TD::make('type', 'النوع')
    ->render(function ($facility) {

        $types = [
            'football' => 'Football',
            'padel'    => 'Padel',
            'gym'      => 'Gym',
            'tennis'   => 'Tennis',
            'other'    => 'Other',
        ];

        return $types[$facility->type] ?? $facility->type;
    }),

TD::make('active', 'الحالة')
    ->render(function ($facility) {

        return $facility->active
            ? '<span class="badge bg-success">نشط</span>'
            : '<span class="badge bg-danger">غير نشط</span>';
    }),

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
    ->render(function ($facility) {

       
    }),
            ]),

            // ===== مودال إضافة =====
            Layout::modal('addFacilityModal', [
                Layout::rows([
                    Select::make('facility.branch_id')
                        ->title('الفرع')
                        ->fromModel(Branch::class, 'name')
                        ->required(),

                    Input::make('facility.name')
                        ->title('اسم المرفق')
                        ->required(),

                    Select::make('facility.type')
                        ->title('النوع')
                        ->options([
                            'football' => 'Football',
                            'padel' => 'Padel',
                            'gym' => 'Gym',
                            'tennis' => 'Tennis',
                            'other' => 'Other',
                        ])
                        ->required(),

                    Input::make('facility.description')
                        ->title('الوصف'),
                      // مودال الإضافة
Upload::make('facility.image')
    ->title('صورة المرفق')
    ->acceptedFiles('.png,.jpg,.jpeg,.svg')
    ->maxFiles(1)
    ->storage('public'),

Select::make('facility.active')
    ->title('الحالة')
    ->options([
        1 => 'نشط',
        0 => 'غير نشط',
    ])
    ->required(),
                   
                ]),
            ])
                ->title('إضافة مرفق جديد')
                ->applyButton('إنشاء')
                ->closeButton('إلغاء'),

            // ===== مودال تعديل =====
            Layout::modal('editFacilityModal', [
                Layout::rows([
                    Select::make('facility.branch_id')
                        ->title('الفرع')
                        ->fromModel(Branch::class, 'name')
                        ->required(),

                    Input::make('facility.name')
                        ->title('اسم المرفق')
                        ->required(),

                    Select::make('facility.type')
                        ->title('النوع')
                        ->options([
                            'football' => 'Football',
                            'padel' => 'Padel',
                            'gym' => 'Gym',
                            'tennis' => 'Tennis',
                            'other' => 'Other',
                        ])
                        ->required(),

                    Input::make('facility.description')
                        ->title('الوصف'),
                   Upload::make('facility.image')
    ->title('صورة المرفق')
    ->acceptedFiles('.png,.jpg,.jpeg,.svg')
    ->maxFiles(1)
    ->storage('public')
                   
                ]),
            ])
                ->title('تعديل المرفق')
                ->applyButton('تحديث')
                ->closeButton('إلغاء')
                ->async('asyncGetFacility'),
        ];
    }

    public function asyncGetFacility(Facility $facility): array
    {
        return ['facility' => $facility];
    }

    /**
 * مساعد لتحويل attachment ID إلى path
 */
private function resolveImagePath(Request $request): ?string
{
    $imageInput = $request->input('facility.image');

    if (empty($imageInput)) {
        return null;
    }

    // لو string حوّله لـ array
    if (is_string($imageInput)) {
        $imageInput = [$imageInput];
    }

    $attachmentId = $imageInput[0] ?? null;

    if (!$attachmentId) {
        return null;
    }

    $attachment = Attachment::find($attachmentId);

    if (!$attachment) {
        return null;
    }

    return $attachment->path . $attachment->name . '.' . $attachment->extension;
}

    public function create(Request $request)
{
    $request->validate([
        'facility.branch_id' => 'required|exists:branches,id',
        'facility.name'      => 'required|string|max:255',
        'facility.type'      => 'required|in:football,padel,gym,tennis,other',
    ]);

    $data = $request->get('facility');

    // معالجة الصورة
    $imagePath = $this->resolveImagePath($request);
    if ($imagePath) {
        $data['image'] = $imagePath;
    } else {
        unset($data['image']);
    }

    Facility::create($data);

    Alert::success('تم إنشاء المرفق بنجاح!');

    return redirect()->route('platform.facility');
}

public function update(Request $request, Facility $facility)
{
    // ← شيل dd() من هنا

    $request->validate([
        'facility.name'        => 'required|string|max:255',
        'facility.branch_id'   => 'required|exists:branches,id',
        'facility.type'        => 'required|in:football,padel,gym,tennis,other',
        'facility.description' => 'nullable|string',
    ]);

    $data = $request->get('facility', []);

    $imagePath = $this->resolveImagePath($request);
    if ($imagePath) {
        $data['image'] = $imagePath;
    } else {
        unset($data['image']);
    }

    $facility->update($data);

    Alert::info('تم تحديث بيانات الملعب / المرفق بنجاح!');

    return redirect()->route('platform.facility');
}
    public function delete(Facility $facility)
    {
        $facility->delete();

        Alert::warning('تم حذف المرفق بنجاح!');

        return redirect()->route('platform.facility');
    }
}