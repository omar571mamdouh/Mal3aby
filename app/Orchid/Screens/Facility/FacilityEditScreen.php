<?php

namespace App\Orchid\Screens\Facility;

use Orchid\Screen\Screen;
use App\Models\Facility;
use App\Models\Branch;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Orchid\Attachment\Models\Attachment;

class FacilityEditScreen extends Screen
{
    public ?Facility $facility = null;

    public function query(Facility $facility): iterable
    {
        $this->facility = $facility;

        return [
            'facility' => $facility,
        ];
    }

    public function name(): ?string
    {
        return $this->facility && $this->facility->exists
            ? 'تعديل المرفق: ' . $this->facility->name
            : 'إضافة مرفق جديد';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('حفظ')
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
                    Select::make('facility.branch_id')
                        ->title('الفرع')
                        ->fromModel(Branch::class, 'name')
                        ->placeholder('اختر الفرع...')
                        ->required(),

                    Input::make('facility.name')
                        ->title('اسم المرفق')
                        ->placeholder('مثال: ملعب كرة القدم الرئيسي')
                        ->required(),

                    Select::make('facility.type')
                        ->title('نوع المرفق')
                        ->options([
                            'football' => '⚽ Football',
                            'padel'    => '🎾 Padel',
                            'gym'      => '💪 Gym',
                            'tennis'   => '🏸 Tennis',
                            'other'    => '🏟️ Other',
                        ])
                        ->required(),
                ]),

                // العمود الأيمن - وصف وحالة وصورة
                Layout::rows([
                    Input::make('facility.description')
                        ->title('الوصف')
                        ->placeholder('اكتب وصفاً مختصراً للمرفق...'),

                    Select::make('facility.active')
                        ->title('الحالة')
                        ->options([
                            1 => '🟢 نشط',
                            0 => '🔴 غير نشط',
                        ])
                        ->required(),

                    Upload::make('facility.image')
                        ->title('صورة المرفق')
                        ->acceptedFiles('.png,.jpg,.jpeg,.svg')
                        ->maxFiles(1)
                        ->storage('public')
                        ->help('يُفضل صورة بأبعاد 800×600 بكسل'),
                ]),
            ]),
        ];
    }

    /**
     * تعالج الصورة: ترجع الصورة الجديدة لو اختيرت، أو القديمة لو ما تغيرتش
     */
    private function resolveImagePath(Request $request): ?string
    {
        $imageInput = $request->input('facility.image');

        // لو ما اخترتش صورة جديدة، استخدم الحالية
        if (empty($imageInput)) {
            return $this->facility?->image ?? null;
        }

        // لو Array من Attachments
        if (is_array($imageInput)) {
            $attachmentId = collect($imageInput)
                ->filter(fn($v) => is_numeric($v))
                ->first();

            if ($attachmentId) {
                $attachment = Attachment::find($attachmentId);
                if ($attachment) {
                    return $attachment->path . $attachment->name . '.' . $attachment->extension;
                }
            }
        }

        // لو Path مباشر
        if (is_string($imageInput)) {
            return $imageInput;
        }

        return null;
    }

    public function save(Request $request)
    {
        $facilityId = $request->route('facility');
        $facility   = $facilityId ? Facility::findOrFail($facilityId) : new Facility();

        $request->validate([
            'facility.branch_id'   => 'required|exists:branches,id',
            'facility.name'        => 'required|string|max:255',
            'facility.type'        => 'required|in:football,padel,gym,tennis,other',
            'facility.description' => 'nullable|string',
        ]);

        $data = $request->get('facility', []);

        // اعمل resolve للصورة الجديدة أو استخدم القديمة
        $data['image'] = $this->resolveImagePath($request);

        $facility->fill($data)->save();

        Toast::success(
            $facility->wasRecentlyCreated
                ? 'تم إنشاء المرفق بنجاح!'
                : 'تم تحديث المرفق بنجاح!'
        );

        return redirect()->route('platform.facility');
    }
}