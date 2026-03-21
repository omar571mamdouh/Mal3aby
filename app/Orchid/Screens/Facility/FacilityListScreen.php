<?php

namespace App\Orchid\Screens\Facility;

use App\Models\Facility;
use App\Models\Branch;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Link;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Upload;
use Orchid\Attachment\Models\Attachment;

class FacilityListScreen extends Screen
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
            Layout::table('facilities', [
             TD::make('image', '')
    ->width('120px')
    ->align(TD::ALIGN_CENTER)
    ->render(fn($f) => $f->image
        ? '<img src="' . asset('storage/' . $f->image) . '" style="width:100px;height:65px;object-fit:cover;border-radius:8px;border:2px solid #e8e8e8;box-shadow:0 2px 6px rgba(0,0,0,0.12);">'
        : '<div style="width:100px;height:65px;border-radius:8px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;margin:auto;"><span style="font-size:28px;">🏟️</span></div>'
    ),

TD::make('name', 'المرفق')
    ->sort()
    ->filter(TD::FILTER_TEXT)
    ->render(fn($f) => '
        <div>
            <div style="font-weight:600;color:#1a1a2e;">' . $f->name . '</div>
            <div style="font-size:11px;color:#888;margin-top:2px;">' . ($f->description ? Str::limit($f->description, 40) : '—') . '</div>
        </div>
    '),

TD::make('branch', 'الفرع / النادي')
    ->render(fn($f) => '
        <div>
            <div style="font-weight:500;">' . ($f->branch?->name ?? '—') . '</div>
            <div style="font-size:11px;color:#888;margin-top:2px;">🏢 ' . ($f->branch?->club?->name ?? '—') . '</div>
        </div>
    '),

TD::make('type', 'النوع')
    ->align(TD::ALIGN_CENTER)
    ->render(fn($f) => match($f->type) {
        'football' => '<span style="background:#e8f5e9;color:#2e7d32;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">⚽ Football</span>',
        'padel'    => '<span style="background:#e3f2fd;color:#1565c0;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🎾 Padel</span>',
        'gym'      => '<span style="background:#fce4ec;color:#c62828;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">💪 Gym</span>',
        'tennis'   => '<span style="background:#fff8e1;color:#f57f17;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🏸 Tennis</span>',
        default    => '<span style="background:#f3e5f5;color:#6a1b9a;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🏟️ ' . $f->type . '</span>',
    }),

TD::make('active', 'الحالة')
    ->align(TD::ALIGN_CENTER)
    ->render(fn($f) => $f->active
        ? '<span style="display:inline-flex;align-items:center;gap:4px;background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
               <span style="width:6px;height:6px;background:#2e7d32;border-radius:50%;display:inline-block;"></span> نشط
           </span>'
        : '<span style="display:inline-flex;align-items:center;gap:4px;background:#ffebee;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
               <span style="width:6px;height:6px;background:#c62828;border-radius:50%;display:inline-block;"></span> غير نشط
           </span>'
    ),
TD::make('actions', 'الإجراءات')
    ->align(TD::ALIGN_CENTER)
    ->width('160px')
    ->render(fn($f) =>
        '<div style="display:flex;gap:6px;justify-content:center;">'
        . Link::make('تعديل')
            ->route('platform.facility.edit', $f)
            ->icon('pencil')
            ->class('btn btn-sm btn-primary')
        . Button::make('حذف')
            ->method('delete')
            ->confirm('هل أنت متأكد من حذف المرفق ' . $f->name . '؟')
            ->parameters(['facility' => $f->id])
            ->icon('trash')
            ->class('btn btn-sm btn-danger')
        . '</div>'
    ),
]),

            // ===== مودال إضافة مرفق جديد =====
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

                    Select::make('facility.active')
                        ->title('الحالة')
                        ->options([1 => 'نشط', 0 => 'غير نشط'])
                        ->required(),
                     Upload::make('facility.image')
            ->title('صورة المرفق')
            ->acceptedFiles('.png,.jpg,.jpeg,.svg')
            ->maxFiles(1)
            ->storage('public')
            ->help('يُفضل صورة بأبعاد 800×600 بكسل'),
                ]),
            ])
            ->title('إضافة مرفق جديد')
            ->applyButton('إنشاء')
            ->closeButton('إلغاء'),
        ];
    }

 public function create(Request $request)
{
    $request->validate([
        'facility.branch_id' => 'required|exists:branches,id',
        'facility.name'      => 'required|string|max:255',
        'facility.type'      => 'required|in:football,padel,gym,tennis,other',
    ]);

    $data = $request->get('facility', []);

    // ===== تحويل الـ image ID إلى path نسبي زي الفايديت =====
    $imageIds = (array) $request->input('facility.image', []);
    if (!empty($imageIds)) {
        $attachment = Attachment::find($imageIds[0]); // ناخد أول صورة فقط
        if ($attachment) {
            $data['image'] = $attachment->path . $attachment->name . '.' . $attachment->extension;
        }
    }

    Facility::create($data);

    Alert::success('تم إنشاء المرفق بنجاح!');

    return redirect()->route('platform.facility');
}

    public function delete(Request $request)
    {
        $facility = Facility::findOrFail($request->get('facility'));
        $facility->delete();

        Alert::warning('تم حذف المرفق بنجاح!');
    }
}