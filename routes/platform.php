<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;
use App\Orchid\Screens\Club\ClubScreen;
use App\Orchid\Screens\Club\ClubEditScreen;
use App\Orchid\Screens\Branch\BranchScreen;
use App\Orchid\Screens\Branch\BranchEditScreen;
use App\Orchid\Screens\Branch\BranchListScreen;
use App\Orchid\Screens\Facility\FacilityScreen;
use App\Orchid\Screens\Facility\FacilityEditScreen;
use App\Orchid\Screens\Facility\FacilityListScreen;
use App\Orchid\Screens\Court\CourtScreen;
use App\Orchid\Screens\Court\CourtEditScreen;
use App\Orchid\Screens\Court\CourtListScreen;
use App\Orchid\Screens\TimeSlot\TimeSlotScreen;
use App\Orchid\Screens\TimeSlot\TimeSlotEditScreen;
use App\Orchid\Screens\BlackoutDate\BlackoutDateScreen;
use App\Orchid\Screens\BlackoutDate\BlackoutDateEditScreen;
use App\Orchid\Screens\SeasonalPricing\SeasonalPricingScreen;
use App\Orchid\Screens\SeasonalPricing\SeasonalPricingEditScreen;
use App\Orchid\Screens\DynamicPricingRule\DynamicPricingRuleListScreen;
use App\Orchid\Screens\DynamicPricingRule\DynamicPricingRuleEditScreen;
use App\Orchid\Screens\MaintenanceLogs\MaintenanceLogsListScreen;
use  App\Orchid\Screens\MaintenanceLogs\MaintenanceLogsEditScreen;
use App\Orchid\Screens\Customer\CustomerScreen;
use App\Orchid\Screens\Customer\CustomerEditScreen;
use App\Orchid\Screens\CustomerAddress\CustomerAddressScreen;
use App\Orchid\Screens\CustomerAddress\CustomerAddressEditScreen;
use App\Orchid\Screens\CustomerNote\CustomerNoteListScreen;
use App\Orchid\Screens\CustomerNote\CustomerNoteEditScreen;
use App\Orchid\Screens\CustomerTag\CustomerTagScreen;
use App\Orchid\Screens\Booking\BookingScreen;
use App\Orchid\Screens\Booking\BookingEditScreen;
use App\Orchid\Screens\BookingStatusLog\BookingStatusLogListScreen;
use App\Orchid\Screens\Cancellations\CancellationListScreen;
use App\Orchid\Screens\Dashboard\DashboardScreen;
use App\Orchid\Screens\BookingService\BookingServiceScreen;
use App\Orchid\Screens\BookingExtensions\BookingExtensionsScreen;


/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn(Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn(Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');



Route::screen('clubs', ClubScreen::class)->name('platform.club');

Route::screen('clubs/create', ClubEditScreen::class)->name('platform.club.create');
Route::screen('clubs/{club}/edit', ClubEditScreen::class)->name('platform.club.edit');


Route::screen('branches', BranchScreen::class)
    ->name('platform.branch');

Route::screen('branches/create', BranchEditScreen::class)
    ->name('platform.branch.create');

Route::screen('branches/{branch}/edit', BranchEditScreen::class)
    ->name('platform.branch.edit');

// صفحة عرض كل المرافق
Route::screen('facilities', FacilityListScreen::class)
    ->name('platform.facility');

// صفحة تعديل/إضافة مرفق (منفصلة)
Route::screen('facilities/{facility}/edit', FacilityEditScreen::class)
    ->name('platform.facility.edit');

Route::screen('courts', CourtListScreen::class)
    ->name('platform.court');

Route::screen('courts/{court}/edit', CourtEditScreen::class)
    ->name('platform.court.edit');

Route::screen('court-timeslots', TimeSlotScreen::class)
    ->name('platform.court.timeslot');

Route::screen('timeslots/{slot}/edit', TimeSlotEditScreen::class)
    ->name('platform.court.timeslot.edit');

// Blackout Dates
Route::screen('blackout-dates/create', BlackoutDateEditScreen::class)
    ->name('platform.blackout-dates.create');

Route::screen('blackout-dates/{blackout}/edit', BlackoutDateEditScreen::class)
    ->name('platform.blackout-dates.edit');

Route::screen('blackout-dates', BlackoutDateScreen::class)
    ->name('platform.blackout-dates');


Route::screen('seasonal-pricing', SeasonalPricingScreen::class)
    ->name('platform.seasonal-pricing');

Route::screen('seasonal-pricing/{pricing}/edit', SeasonalPricingEditScreen::class)
    ->name('platform.seasonal-pricing.edit');

Route::screen('seasonal-pricing/create', SeasonalPricingEditScreen::class)
    ->name('platform.seasonal-pricing.create');


Route::screen('dynamic-pricing', DynamicPricingRuleListScreen::class)
    ->name('platform.dynamic-pricing.list');

Route::screen('dynamic-pricing/create', DynamicPricingRuleEditScreen::class)
    ->name('platform.dynamic-pricing.create');

Route::screen('dynamic-pricing/{rule}/edit', DynamicPricingRuleEditScreen::class)
    ->name('platform.dynamic-pricing.edit');


Route::screen('maintenance-logs', MaintenanceLogsListScreen::class)
    ->name('platform.maintenance-logs.list');

Route::screen('maintenance-logs/create', MaintenanceLogsEditScreen::class)
    ->name('platform.maintenance-logs.create');

Route::screen('maintenance-logs/{log}/edit', MaintenanceLogsEditScreen::class)
    ->name('platform.maintenance-logs.edit');

Route::screen('/customers', CustomerScreen::class)
    ->name('platform.customer.list');

Route::screen('/customers/{customer?}/edit', CustomerEditScreen::class)
    ->name('platform.customer.edit');

Route::screen('/customers/create', CustomerEditScreen::class)
    ->name('platform.customer.create');

// List Screen
Route::screen('/customer-addresses', CustomerAddressScreen::class)
    ->name('platform.customer.address.list');

// Create
Route::screen('/customer-addresses/create', CustomerAddressEditScreen::class)
    ->name('platform.customer.address.create');

// Edit
Route::screen('/customer-addresses/{address}/edit', CustomerAddressEditScreen::class)
    ->name('platform.customer.address.edit');

Route::screen('customer-notes', CustomerNoteListScreen::class)
    ->name('platform.customer.notes');

Route::screen('customer-notes/create', CustomerNoteEditScreen::class)
    ->name('platform.customer.notes.create');

Route::screen('customer-notes/{note}/edit', CustomerNoteEditScreen::class)
    ->name('platform.customer.notes.edit');

Route::screen('customer-tags', CustomerTagScreen::class)
    ->name('platform.customer.tags.list');

Route::screen('bookings', BookingScreen::class)
    ->name('platform.bookings.list');

Route::screen('bookings/create', BookingEditScreen::class)
    ->name('platform.bookings.create');

Route::screen('bookings/{booking}/edit', BookingEditScreen::class)
    ->name('platform.bookings.edit');


Route::screen('booking-logs', BookingStatusLogListScreen::class)
    ->name('platform.booking.logs');

// routes/platform.php
Route::screen('cancellations', CancellationListScreen::class)
    ->name('platform.cancellations');

Route::screen('cancellations/{cancellation}/edit', \App\Orchid\Screens\Cancellations\CancellationEditScreen::class)
    ->name('platform.cancellations.edit');

Route::screen('dashboard', DashboardScreen::class)
    ->name('platform.dashboard');

Route::screen('services/all', \App\Orchid\Screens\BookingService\AllServicesScreen::class)
    ->name('platform.services.all');

Route::screen('extensions/all', \App\Orchid\Screens\BookingExtensions\AllExtentionsScreen::class)
    ->name('platform.extensions.all');

Route::screen('bookings/{booking}/services', BookingServiceScreen::class)
    ->name('platform.bookings.services');

Route::screen('bookings/{booking}/extensions', BookingExtensionsScreen::class)
    ->name('platform.bookings.extensions');
