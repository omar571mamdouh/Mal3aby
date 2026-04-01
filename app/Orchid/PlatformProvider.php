<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Organization')
                ->icon('bs.collection')
                ->list([
                    Menu::make('Clubs')
                        ->icon('bs.building')
                        ->route('platform.club'),

                    Menu::make('Branches')
                        ->icon('shop')
                        ->route('platform.branch'),

                    Menu::make('Facility')
                        ->icon('bs.door-closed')
                        ->route('platform.facility'),

                    Menu::make('Court')
                        ->icon('bs.dribbble')
                        ->route('platform.court'),
                ]),



            Menu::make('Scheduling')
                ->icon('bs.calendar')
                ->list([
                    Menu::make('Time Slots')
                        ->icon('bs.clock')
                        ->route('platform.court.timeslot'),

                    Menu::make('Blackout Dates')
                        ->icon('bs.calendar-x')
                        ->route('platform.blackout-dates'),


                    Menu::make('Seasonal Pricing')
                        ->icon('bs.cash-stack')
                        ->route('platform.seasonal-pricing'),


                    Menu::make('Dynamic Pricing Rules')
                        ->icon('bs.bar-chart')
                        ->route('platform.dynamic-pricing.list'),

                    Menu::make('Maintenance Logs')
                        ->icon('bs.wrench')
                        ->route('platform.maintenance-logs.list'),

                ]),

            Menu::make('Customer Details')
                ->icon('bs.people-fill')          // ← الجروب
                ->list([
                    Menu::make('Customer')
                        ->icon('bs.person-lines-fill')  // ← مختلف عن الجروب
                        ->route('platform.customer.list'),

                    Menu::make('Customer Address')
                        ->icon('bs.geo-alt-fill')      // ← لوكيشن للعنوان
                        ->route('platform.customer.address.list'),
                    Menu::make('Customer Notes')
                        ->icon('bs.sticky')
                        ->route('platform.customer.notes'),
                    Menu::make('Customer Tags')
                        ->icon('bs.tag')
                        ->route('platform.customer.tags.list'),
                ]),

            Menu::make('Booking Management')
                ->icon('bs.calendar-check') // أيقونة حجز
                ->list([

                    Menu::make('Bookings')
                        ->icon('bs.calendar2-week') // جدول حجوزات
                        ->route('platform.bookings.list'),
                    Menu::make('Booking Logs')
                        ->icon('bs.clock-history')
                        ->route('platform.booking.logs'),

                    Menu::make('Cancellations')
                        ->icon('bs.calendar-x') // إلغاء
                        ->route('platform.cancellations'),
                   
                ]),
               Menu::make('Dashboard')
    ->icon('bs.speedometer2')   // أيقونة مناسبة
    ->route('platform.dashboard') // الاسم اللي هنسجله في web.php
    ->title(__('Dashboard')),   // Optional عنوان للمجموعة
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
