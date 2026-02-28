<?php

namespace App\Orchid\Layouts\Branch;

use App\Models\Branch;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class BranchListLayout extends Table
{
    // الاسم اللي هيستخدمه الـ Screen للـ query
    protected $target = 'branches';

    protected function columns(): iterable
    {
        return [
            TD::make('name', 'اسم الفرع')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn(Branch $branch) => $branch->name),

            TD::make('club_id', 'النادي')
                ->render(fn(Branch $branch) => $branch->club?->name ?? '—'),

            TD::make('city', 'المدينة')
                ->render(fn(Branch $branch) => $branch->city ?? '—'),

            TD::make('phone', 'الهاتف')
                ->render(fn(Branch $branch) => $branch->phone ?? '—'),

            TD::make('active', 'الحالة')
                ->render(fn(Branch $branch) =>
                    $branch->active
                        ? '<span class="badge bg-success">نشط</span>'
                        : '<span class="badge bg-danger">غير نشط</span>'
                ),

            TD::make('actions', 'الإجراءات')
                ->align(TD::ALIGN_CENTER)
                ->width('200px')
                ->render(function (Branch $branch) {
                    return DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            ModalToggle::make('تعديل')
                                ->modal('editBranchModal')
                                ->method('update')
                                ->asyncParameters(['branch' => $branch->id])
                                ->icon('pencil'),

                            Button::make('حذف')
                                ->method('delete')
                                ->confirm("هل أنت متأكد من حذف الفرع {$branch->name}؟")
                                ->parameters(['branch' => $branch->id])
                                ->icon('trash')
                                ->type(Color::DANGER()),
                        ]);
                }),
        ];
    }
}