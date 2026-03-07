<?php

namespace App\Orchid\Layouts\Court;

use App\Models\Court;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class CourtListLayout extends Table
{
    protected $target = 'courts';

    protected function columns(): iterable
    {
        return [
            TD::make('name', 'الملعب')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(fn(Court $c) => '
                    <div>
                        <div style="font-weight:600;color:#1a1a2e;">' . $c->name . '</div>
                        <div style="font-size:11px;color:#888;margin-top:2px;">🏢 ' . ($c->facility?->name ?? '—') . '</div>
                    </div>
                '),

            TD::make('branch', 'الفرع / النادي')
                ->render(fn(Court $c) => '
                    <div>
                        <div style="font-weight:500;">' . ($c->facility?->branch?->name ?? '—') . '</div>
                        <div style="font-size:11px;color:#888;margin-top:2px;">🏆 ' . ($c->facility?->branch?->club?->name ?? '—') . '</div>
                    </div>
                '),

            TD::make('surface_type', 'الأرضية')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(Court $c) => match($c->surface_type) {
                    'grass'  => '<span style="background:#e8f5e9;color:#2e7d32;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🌿 Grass</span>',
                    'turf'   => '<span style="background:#e3f2fd;color:#1565c0;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🟦 Turf</span>',
                    'indoor' => '<span style="background:#f3e5f5;color:#6a1b9a;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">🏠 Indoor</span>',
                    default  => '<span style="background:#f5f5f5;color:#555;padding:4px 10px;border-radius:20px;font-size:12px;">' . $c->surface_type . '</span>',
                }),

            TD::make('price_per_hour', 'السعر / الساعة')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(Court $c) => '
                    <div style="font-weight:600;color:#2e7d32;">💰 ' . number_format($c->price_per_hour, 2) . ' EGP</div>
                '),

            TD::make('hours', 'أوقات العمل')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(Court $c) => '
                    <div style="font-size:12px;">
                        <span style="color:#1565c0;">🕐 ' . ($c->opens_at ?? '—') . '</span>
                        <span style="color:#888;margin:0 4px;">→</span>
                        <span style="color:#c62828;">🕐 ' . ($c->closes_at ?? '—') . '</span>
                    </div>
                '),

            TD::make('capacity', 'السعة')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(Court $c) => $c->capacity
                    ? '<span style="background:#fff8e1;color:#f57f17;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;">👥 ' . $c->capacity . '</span>'
                    : '<span style="color:#aaa;">—</span>'
                ),

            TD::make('active', 'الحالة')
                ->align(TD::ALIGN_CENTER)
                ->render(fn(Court $c) => $c->active
                    ? '<span style="display:inline-flex;align-items:center;gap:4px;background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#2e7d32;border-radius:50%;display:inline-block;"></span> نشط</span>'
                    : '<span style="display:inline-flex;align-items:center;gap:4px;background:#ffebee;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><span style="width:6px;height:6px;background:#c62828;border-radius:50%;display:inline-block;"></span> غير نشط</span>'
                ),

            TD::make('actions', 'الإجراءات')
                ->align(TD::ALIGN_CENTER)
                ->width('160px')
                ->render(fn(Court $c) =>
                    '<div style="display:flex;gap:6px;justify-content:center;">'
                    . Link::make('تعديل')
                        ->route('platform.court.edit', $c)
                        ->icon('pencil')
                        ->class('btn btn-sm btn-primary')   
                    . Button::make('حذف')
                        ->method('delete')
                        ->confirm('هل أنت متأكد من حذف الملعب ' . $c->name . '؟')
                        ->parameters(['id' => $c->id])
                        ->icon('trash')
                         ->class('btn btn-sm btn-danger') 
                    . '</div>'
                ),
        ];
    }
}