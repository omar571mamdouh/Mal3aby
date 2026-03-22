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
            ->icon('bs.bar-chart'),
            
        Menu::make('Maintenance Logs')
            ->icon('bs.wrench'),
           
    ]),


    
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
