<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);
    }

    public function menu(): array
    {
        return [

            // Dashboard
            Menu::make('Dashboard')
                ->icon('bs.speedometer2')
                ->route('platform.dashboard')
                ->title(__('Dashboard'))
                ->reload(),

            // Organization
            Menu::make('Clubs')
                ->icon('bs.building')
                ->route('platform.club')
                ->title(__('Organization')),

            Menu::make('Branches')
                ->icon('shop')
                ->route('platform.branch'),

            Menu::make('Facility')
                ->icon('bs.door-closed')
                ->route('platform.facility'),

            Menu::make('Court')
                ->icon('bs.dribbble')
                ->route('platform.court'),

            // Scheduling
            Menu::make('Time Slots')
                ->icon('bs.clock')
                ->route('platform.court.timeslot')
                ->title(__('Scheduling')),

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

            // Customer Details
            Menu::make('Customer')
                ->icon('bs.person-lines-fill')
                ->route('platform.customer.list')
                ->title(__('Customer Details')),

            Menu::make('Customer Address')
                ->icon('bs.geo-alt-fill')
                ->route('platform.customer.address.list'),

            Menu::make('Customer Notes')
                ->icon('bs.sticky')
                ->route('platform.customer.notes'),

            Menu::make('Customer Tags')
                ->icon('bs.tag')
                ->route('platform.customer.tags.list'),

            // Booking Management
            Menu::make('Bookings')
                ->icon('bs.calendar2-week')
                ->route('platform.bookings.list')
                ->title(__('Booking Management')),

            Menu::make('Booking Logs')
                ->icon('bs.clock-history')
                ->route('platform.booking.logs'),

            Menu::make('Cancellations')
                ->icon('bs.calendar-x')
                ->route('platform.cancellations'),

            Menu::make('Services')
                ->icon('bs.box-seam')
                ->route('platform.services.all'),

            Menu::make('Extensions')
                ->icon('bs.clock-history')
                ->route('platform.extensions.all'),

            // Memberships Module
            Menu::make('Memberships')
                ->icon('people') // أيقونة مناسبة
                ->route('platform.memberships')
                ->title(__('Membership Management')),

            Menu::make('Membership Features')
                ->icon('star') // أيقونة واضحة للمميزات
                ->route('platform.membership.features'),

            Menu::make('Customer Memberships')
                ->icon('clock') // تمثل الوقت المجاني أو الاشتراك
                ->route('platform.customer.memberships'),

            Menu::make('Membership Free Hours')
                ->icon('stopwatch')
                ->route('platform.membership.free-hours.list'),

            Menu::make('Membership Usage Logs')
                ->icon('journal-text')
                ->route('platform.membership.usage-logs.list'),

            Menu::make('Coaches')
                ->icon('person-badge')
                ->route('platform.coaches')
                ->title(__('Coaches Management')),

            Menu::make('Coach Schedules')
                ->icon('bs.calendar-week')
                ->route('platform.coach.schedules'),

            // System Access (Roles & Users)
            Menu::make('Users')
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make('Roles')
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),

        ];
    }

    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
