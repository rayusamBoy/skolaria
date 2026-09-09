<?php

declare(strict_types=1);

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\Auth\AccountSecurityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserImpersonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Universal Routes
|--------------------------------------------------------------------------
|
| Here you can register the universal routes for your application.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::group(['middleware' => ['universal']], function () {
    Auth::routes();

    // The helper route page only sets theme and skin preferences for the PWA GeneratePwaScreenshots command
    Route::get('pwa-set-preferences', fn () => view('pwa.set_preferences'));

    Route::group(['prefix' => 'impersonation', 'middleware' => ['auth', 'checkForPassUpdate', '2fa']], function () {
        Route::get('/', [UserImpersonationController::class, 'index'])->name('tenant.impersonation');
        Route::get('redirect/{tenant_id}/{user_id}', [UserImpersonationController::class, 'redirect_to_impersonation_route'])->name('tenant.impersonation.redirect');
    });

    /****************** My Account ***************/
    Route::group(['prefix' => 'my-account', 'middleware' => ['auth', 'checkForPassUpdate']], function () {
        Route::get('profile/edit', [MyAccountController::class, 'edit_profile'])->name('my_account.edit');
        Route::put('profile', [MyAccountController::class, 'update_profile'])->name('my_account.update');
        Route::put('password/change', [MyAccountController::class, 'change_pass'])->name('my_account.change_pass');
        Route::put('preferences/update', [MyAccountController::class, 'update_preferences'])->name('my_account.update_preferences');
        Route::post('hidden-alerts/update', [MyAccountController::class, 'update_hidden_alerts'])->name('my_account.update_hidden_alerts');
        Route::post('hidden-alerts/clear', [MyAccountController::class, 'clear_hidden_alerts'])->name('my_account.clear_hidden_alerts');
    });

    /*************** Test *****************/
    Route::get('test', [TestController::class, 'index'])->name('test')->middleware('auth');

    /*************** Others (public pages) *****************/
    Route::get('privacy-policy', [HomeController::class, 'privacy_policy'])->name('pages.privacy_policy');
    Route::get('terms-of-use', [HomeController::class, 'terms_of_use'])->name('pages.terms_of_use');

    Route::group(['middleware' => ['auth', 'checkForPassUpdate']], function () {
        /*************** Two-factor authentication (account-scoped) *****************/
        Route::group(['prefix' => 'two-factor-authentication'], function () {
            Route::get('account/security', [AccountSecurityController::class, 'index'])->name('account_security.index')->middleware('2fa');
            Route::match(['POST', 'GET'], 'account/recovery', [AccountSecurityController::class, 'account_recovery'])->name('account_security.account_recovery');
            Route::get('show', [AccountSecurityController::class, 'show_google_2fa_verification'])->name('two_factor.show');
            // middleware captures OTP and runs authenticate
            Route::post('authenticate', [AccountSecurityController::class, 'authenticate'])->name('two_factor.authenticate')->middleware(['2fa', 'throttle:5,1']);
            Route::post('update/code', [AccountSecurityController::class, 'update_secret_codes'])->name('two_factor.update_secret_codes')->middleware(['2fa', 'password.confirm']);
            Route::patch('code/null/{user?}', [AccountSecurityController::class, 'null_secret_code'])->name('two_factor.null_secret_code')->middleware(['2fa', 'password.confirm']);
            Route::delete('logout/other-browser-sessions', [AccountSecurityController::class, 'logout_other_browser_sessions'])->name('account_security.logout_other_browser_sessions');
        });

        // Require 2FA for the routes below
        Route::group(['middleware' => '2fa'], function () {
            /*************** Permissions and Roles (admin-protected) *****************/
            Route::group(['middleware' => ['auth', 'password.confirm']], function () {
                Route::resource('roles', RoleController::class);
            });

            Route::get('email/verification/notification', function (Request $request) {
                $request->user()->sendEmailVerificationNotification();

                return back()->with('flash_success', __('msg.verification_sent'));
            })->middleware(['throttle:6,1'])->name('verification.send');

            Route::resource('notifications', NotificationController::class);

            /************************ AJAX helper endpoints ****************************/
            Route::prefix('ajax')->group(function () {
                Route::get('state/{state}', [AjaxController::class, 'get_state'])->name('ajax.get_state');
                Route::get('lga/{state}', [AjaxController::class, 'get_lga'])->name('ajax.get_lga');
            });

            /*************** Notifications (user-specific settings) **************/
            Route::group(['prefix' => 'notifications'], function () {
                Route::post('mark/as-read/{notification}', [NotificationController::class, 'mark_as_read'])->name('notifications.mark_as_read');
                Route::put('mark/all-read', [NotificationController::class, 'mark_all_read'])->name('notifications.mark_all_read');
                Route::put('update/notifiable', [NotificationController::class, 'update_notification_preferences'])->name('notifications.update.notification_preferences');
                Route::match(['post', 'put', 'delete'], 'update/firebase/device-token', [NotificationController::class, 'update_firebase_device_token'])->name('notifications.firebase.update_device_token');
            });
        });
    });
});
