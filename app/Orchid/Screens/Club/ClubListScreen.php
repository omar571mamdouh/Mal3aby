<?php

namespace App\Orchid\Screens\Club;

use App\Models\Club;
use App\Orchid\Layouts\Club\ClubListLayout;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class ClubListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'clubs' => Club::latest()->paginate(15), // ✅ هنا
        ];
    }

    public function name(): ?string
    {
        return 'إدارة الأندية';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('إضافة نادي')
                ->route('platform.club.edit')
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ClubListLayout::class, // ✅ هنا
        ];
    }

    public function deleteClub(Request $request): void // ✅ هنا
    {
        Club::findOrFail($request->get('id'))->delete();
        Toast::info('تم حذف النادي بنجاح!');
    }
}