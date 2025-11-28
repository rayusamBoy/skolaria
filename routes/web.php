<?php

declare(strict_types=1);

use App\Http\Controllers\Central\ArtisanCommandsController as CentralArtisanCommandsController;
use App\Http\Controllers\Central\HomeController as CentralHomeController;
use App\Http\Controllers\Central\LogController as CentralLogController;
use App\Http\Controllers\Central\SettingController as CentralSettingController;
use App\Http\Controllers\Central\TenantController as CentralTenantController;
use App\Http\Controllers\Central\UserController as CentralUserController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Routes
|--------------------------------------------------------------------------
|
| Here you can register the central routes for your application.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::group(['middleware' => ['auth', 'checkForPassUpdate', 'handleCookie']], function () {
    // Require 2FA for the routes below
    Route::group(['middleware' => '2fa'], function () {
        /*************** Central namespace (central admin) ***************/
        Route::group(['namespace' => 'Central'], function () {

            /*************** Artisan commands UI (shared-hosting helper) ***************/
            Route::prefix('artisan-commands')->group(function () {
                Route::get('/', [CentralArtisanCommandsController::class, 'index'])->name('artisan_commands.index');

                // Cache / clear helpers
                Route::post('optimize', [CentralArtisanCommandsController::class, 'optimize'])->name('artisan_commands.optimize');
                Route::post('optimize/clear', [CentralArtisanCommandsController::class, 'optimize_clear'])->name('artisan_commands.optimize_clear');
                Route::post('route/cache', [CentralArtisanCommandsController::class, 'route_cache'])->name('artisan_commands.route_cache');
                Route::post('route/clear', [CentralArtisanCommandsController::class, 'route_clear'])->name('artisan_commands.route_clear');
                Route::post('config/cache', [CentralArtisanCommandsController::class, 'config_cache'])->name('artisan_commands.config_cache');
                Route::post('config/clear', [CentralArtisanCommandsController::class, 'config_clear'])->name('artisan_commands.config_clear');
                Route::post('event/cache', [CentralArtisanCommandsController::class, 'event_cache'])->name('artisan_commands.event_cache');
                Route::post('event/clear', [CentralArtisanCommandsController::class, 'event_clear'])->name('artisan_commands.event_clear');
                Route::post('view/cache', [CentralArtisanCommandsController::class, 'view_cache'])->name('artisan_commands.view_cache');
                Route::post('view/clear', [CentralArtisanCommandsController::class, 'view_clear'])->name('artisan_commands.view_clear');
                Route::post('publish/log-viewer', [CentralArtisanCommandsController::class, 'publish_log_viewer'])->name('artisan_commands.publish_log_viewer');
                Route::post('clean/activity-logs', [CentralArtisanCommandsController::class, 'clean_activity_logs'])->name('logs.clean_activities');
                Route::post('storage/link', [CentralArtisanCommandsController::class, 'storage_link'])->name('logs.storage_link');
                Route::post('storage/unlink', [CentralArtisanCommandsController::class, 'storage_unlink'])->name('logs.storage_unlink');
            });

            /*************** Dashboard **************/
            Route::get('/', [CentralHomeController::class, 'dashboard'])->name('central_index');
            Route::get('home', [CentralHomeController::class, 'dashboard'])->name('central_home');
            Route::get('dashboard', [CentralHomeController::class, 'dashboard'])->name('central_dashboard');

            /************* Logs ************/
            Route::prefix('logs')->group(function () {
                Route::get('/', [CentralLogController::class, 'index'])->name('central_logs.index');
                Route::get('login-history/reset/{user}', [CentralLogController::class, 'reset_login_hist'])->name('logs.login_history_reset');
                Route::post('delete-activity/{log}', [CentralLogController::class, 'delete_activity'])->name('logs.activity_log_delete');
            });

            /************* Settings ************/
            Route::group(['prefix' => 'settings'], function () {
                Route::get('app', [CentralSettingController::class, 'index'])->name('settings_non_tenancy.index')->middleware('password.confirm');
                Route::put('update', [CentralSettingController::class, 'update'])->name('settings_non_tenancy.update');
                Route::post('analytics/google/disable', [CentralSettingController::class, 'disable_analytics'])->name('settings_non_tenancy.disable_analytics');
                Route::post('analytics/google/enable', [CentralSettingController::class, 'enable_analytics'])->name('settings_non_tenancy.enable_analytics');
                Route::get('login-form/preview', [CentralSettingController::class, 'preview_login_form'])->name('settings_non_tenancy.preview_login_form');
            });
        });

        /*************** Users (central admin) *****************/
        Route::prefix('central-users')->group(function () {
            Route::get('password/reset/{user}', [CentralUserController::class, 'reset_pass'])->name('central_users.password.reset');
            Route::post('user/update/staff/data/edit_state', [CentralUserController::class, 'update_staff_data_edit_state'])->name('central_users.update_staff_data_edit_state');
            Route::post('user/update/user/blocked_state', [CentralUserController::class, 'update_user_blocked_state'])->name('central_users.update_user_blocked_state');
        });

        /*************** Tenants ***************/
        Route::prefix('tenants')->group(function () {
            Route::post('run-command/{command}/{tenant}', [CentralArtisanCommandsController::class, 'run_command'])->name('tenants.run_command');
        });

        /*************** Controller's resources **************/
        Route::resource('central_users', CentralUserController::class);
        Route::resource('tenants', CentralTenantController::class);

        /*************** Tickets (central support actions) **************/
        Route::prefix('tickets')->group(function () {
            Route::get('index', [TicketController::class, 'index_central'])->name('tickets.index_central');
            Route::post('properties/update', [TicketController::class, 'update_properties'])->name('tickets.update_properties');

            // lock & unlock are state changes: use POST/PATCH
            Route::post('lock/{ticket}', [TicketController::class, 'lock_ticket'])->name('tickets.lock');
            Route::post('unlock/{ticket}', [TicketController::class, 'unlock_ticket'])->name('tickets.unlock');

            Route::get('answer/{ticket}', [TicketController::class, 'answer'])->name('tickets.answer');
            Route::put('store/operator-message', [TicketController::class, 'store_operator_message'])->name('tickets.store_operator_message');
            Route::put('update/assignee/{ticket}', [TicketController::class, 'update_assignee'])->name('tickets.update_assignee');

            Route::delete('delete/{ticket}', [TicketController::class, 'delete'])->name('tickets.delete');
        });
    });
});
