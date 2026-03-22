<?php

namespace App\Orchid\Screens\SeasonalPricing;

use App\Models\SeasonalPricing;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Orchid\Screen\TD;

class SeasonalPricingScreen extends Screen
{
    /**
     * عنوان الشاشة
     */
    public function name(): ?string
    {
        return 'Seasonal Pricing';
    }

    /**
     * وصف الشاشة
     */
    public function description(): ?string
    {
        return 'إدارة أسعار الملاعب حسب الموسم';
    }

    /**
     * البيانات اللي هتظهر في الشاشة
     */
    public function query(): iterable
    {
        return [
            'seasonal_pricing' => SeasonalPricing::with('court')->latest()->paginate(10),
        ];
    }

    /**
     * أزرار التحكم فوق الشاشة
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('إضافة جديد')
                ->icon('bs.plus-circle')
                ->route('platform.seasonal-pricing.create'),
        ];
    }

    /**
     * تصميم الشاشة
     */
   public function layout(): iterable
{
    return [
        Layout::table('seasonal_pricing', [
            TD::make('court_id', 'الملعب')
                ->render(fn(SeasonalPricing $p) => '
                    <div style="font-weight:600;color:#1a1a2e;">🏟️ ' . ($p->court?->name ?? '—') . '</div>
                '),

            TD::make('season', 'الموسم')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(SeasonalPricing $p) => match($p->season) {
                    'summer' => '<span style="background:#fff3e0;color:#e65100;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">☀️ صيف</span>',
                    'winter' => '<span style="background:#e3f2fd;color:#1565c0;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">❄️ شتاء</span>',
                    'spring' => '<span style="background:#e8f5e9;color:#2e7d32;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🌸 ربيع</span>',
                    'autumn' => '<span style="background:#f3e5f5;color:#6a1b9a;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🍂 خريف</span>',
                    default  => '<span style="background:#f5f5f5;color:#555;padding:4px 10px;border-radius:20px;font-size:12px;">' . $p->season . '</span>',
                }),

            TD::make('start_date', 'الفترة')
                ->render(fn(SeasonalPricing $p) => '
                    <div style="font-size:12px;">
                        <span style="color:#1565c0;">📅 ' . $p->start_date->format('Y-m-d') . '</span>
                        ' . ($p->end_date ? '<span style="color:#888;margin:0 4px;">→</span><span style="color:#c62828;">📅 ' . $p->end_date->format('Y-m-d') . '</span>' : '') . '
                    </div>
                '),

            TD::make('price', 'السعر')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(SeasonalPricing $p) => '
                    <div style="font-weight:600;color:#2e7d32;">💰 ' . number_format($p->price, 2) . ' ج.م</div>
                '),

            TD::make('actions', 'الإجراءات')
                ->align(TD::ALIGN_CENTER)
                ->width('160px')
                ->render(fn(SeasonalPricing $p) =>
                    '<div style="display:flex;gap:6px;justify-content:center;">'
                    . Link::make('تعديل')
                        ->route('platform.seasonal-pricing.edit', $p->id)
                        ->icon('pencil')
                        ->class('btn btn-sm btn-primary')
                    . Button::make('حذف')
                        ->method('delete')
                        ->confirm('هل أنت متأكد من حذف هذا السجل؟')
                        ->parameters(['seasonal_pricing' => $p->id])
                        ->icon('trash')
                        ->class('btn btn-sm btn-danger')
                    . '</div>'
                ),
        ]),
    ];
}

    /**
     * حذف عنصر من الجدول
     */
    public function delete(SeasonalPricing $seasonal_pricing)
    {
        $seasonal_pricing->delete();
        Alert::warning('تم الحذف بنجاح!');
        return redirect()->route('platform.seasonal-pricing');
    }
}