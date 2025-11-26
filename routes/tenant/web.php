<?php

declare(strict_types=1);

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Auth\AccountSecurityController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookRequestController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DormController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MyAccountController;
use App\Http\Controllers\MyClassController;
use App\Http\Controllers\MyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\QueryBuilderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SoftDeleteController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StudentRecordController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\UserController;
use App\Http\Requests\CustomEmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::group(["middleware" => ["universal"]], function () {
    Auth::routes();

    /****************** My Account ***************/
    Route::prefix('my-account')->group(function () {
        Route::get('profile/edit', [MyAccountController::class, 'edit_profile'])->name('my_account.edit');
        Route::put('profile', [MyAccountController::class, 'update_profile'])->name('my_account.update');
        Route::put('password/change', [MyAccountController::class, 'change_pass'])->name('my_account.change_pass');
        Route::put('other/update', [MyAccountController::class, 'other'])->name('my_account.other');
        Route::post('hidden-alerts/update', [MyAccountController::class, 'update_hidden_alerts'])->name('my_account.update_hidden_alerts');
        Route::post('hidden-alerts/clear', [MyAccountController::class, 'clear_hidden_alerts'])->name('my_account.clear_hidden_alerts');
    });

    /*************** Test *****************/
    Route::get('test', [TestController::class, 'index'])->name('test');

    /*************** Others (public pages) *****************/
    Route::get('privacy-policy', [HomeController::class, 'privacy_policy'])->name('pages.privacy_policy');
    Route::get('terms-of-use', [HomeController::class, 'terms_of_use'])->name('pages.terms_of_use');
});

Route::group(['middleware' => ['auth', 'checkForPassUpdate', 'handleCookie']], function () {
    /*************** Two-factor authentication (account-scoped) *****************/
    Route::group(['prefix' => 'two-factor-authentication', 'middleware' => 'universal'], function () {
        Route::get('account/security', [AccountSecurityController::class, 'index'])->name('account_security.index')->middleware('2fa');
        Route::match(['POST', 'GET'], 'account/recovery', [AccountSecurityController::class, 'account_recovery'])->name('account_security.account_recovery');
        Route::get('show', [AccountSecurityController::class, 'showGoogle2FAVerification'])->name('two_factor.show');
        // middleware captures OTP and runs authenticate
        Route::post('authenticate', [AccountSecurityController::class, 'authenticate'])->name('two_factor.authenticate')->middleware(['2fa', 'throttle:5,1']);
        Route::post('update/code', [AccountSecurityController::class, 'update_secret_codes'])->name('two_factor.update_secret_codes')->middleware('2fa');
        Route::patch('{user?}/code/null', [AccountSecurityController::class, 'null_secret_code'])->name('two_factor.null_secret_code')->middleware('2fa');
        Route::delete('logout/other-browser-sessions', [AccountSecurityController::class, 'logout_other_browser_sessions'])->name('account_security.logout_other_browser_sessions');
    });

    // Require 2FA for the routes below
    Route::group(['middleware' => '2fa'], function () {
        /*************** Permissions and Roles (admin-protected) *****************/
        Route::group(['middleware' => ['auth', 'password.confirm', 'universal'], 'prefix' => 'settings'], function () {
            Route::resource('roles', RoleController::class);
        });

        /*************** Dashboard **************/
        Route::group(['middleware' => 'universal'], function () {
            Route::get('/', [HomeController::class, 'dashboard'])->name('index');
            Route::get('home', [HomeController::class, 'dashboard'])->name('home');
            Route::get('dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        });

        /*************** Calendar Events **************/
        Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');

        // Events: RESTful endpoints under schedule/events
        Route::prefix('schedule/events')->group(function () {
            Route::post('/', [ScheduleController::class, 'create_event'])->name('schedule.events.create');
            Route::get('{event}/edit', [ScheduleController::class, 'edit_event'])->name('schedule.events.edit');
            Route::patch('{event}', [ScheduleController::class, 'update_event'])->name('schedule.events.update');
            Route::delete('{event}', [ScheduleController::class, 'delete_event'])->name('schedule.events.delete');
        });

        /****************** Email Verification ***************/
        Route::get('email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) {
            $request->fulfill();
            return redirect('/home')->with('pop_success', __('msg.email_verified'));
        })->middleware(['signed'])->name('verification.verify');

        Route::get('email/verification/notification', function (Request $request) {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('flash_success', __('msg.verification_sent'));
        })->middleware(['throttle:6,1', 'universal'])->name('verification.send');

        /*************** Statistics **************/
        Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');

        /*************** Students *****************/
        Route::prefix('students')->group(function () {
            Route::get('{student}/password/reset', [StudentRecordController::class, 'reset_pass'])->name('students.password.reset');
            Route::get('graduated', [StudentRecordController::class, 'graduated'])->name('students.graduated');
            Route::put('{student}/{prom_id}/not-graduated', [StudentRecordController::class, 'not_graduated'])->name('students.not_graduated');

            Route::get('class/{class_id}/list', [StudentRecordController::class, 'list_by_class'])->name('students.list_by_class');

            /* ID Cards */
            Route::get('id-cards', [StudentRecordController::class, 'id_cards'])->name('students.id_cards');
            Route::post('id-cards/manage/{class?}/{issued_date?}/{expire_date?}/{theme?}', [StudentRecordController::class, 'id_cards_manage'])->name('students.id_cards.manage');
            Route::get('id-cards/print/class/{class?}/{issued?}/{expire?}/{theme?}/{phone?}/{class_from?}/{class_to?}/{motto?}/{brightness?}/{website_link?}', [StudentRecordController::class, 'print_class_id_cards'])->name('students.id_cards.print_class');
            Route::post('id-cards/selected/print', [StudentRecordController::class, 'print_selected_id_cards'])->name('students.id_cards.print_selected');

            /* Promotions */
            Route::post('promote/selector', [PromotionController::class, 'selector'])->name('students.promote.selector');
            Route::get('promotion/manage', [PromotionController::class, 'manage'])->name('students.promotion.manage');
            Route::delete('promotion/{promotion}/single/reset', [PromotionController::class, 'reset'])->name('students.promotion.reset');
            Route::delete('promotions/reset', [PromotionController::class, 'reset_all'])->name('students.promotion.reset_all');
            Route::get('promotion/{status?}/{fc?}/{fs?}/{tc?}/{ts?}', [PromotionController::class, 'promotion'])->name('students.promotion');
            Route::post('promote/{status}/{fc}/{fs}/{tc}/{ts}', [PromotionController::class, 'promote'])->name('students.promote');
            Route::post('promotion/remarks/{id}', [PromotionController::class, 'remarks'])->name('students.promotion.remarks');

            /* Block / Unblock groups of students */
            Route::post('block/class', [StudentRecordController::class, 'block_all_class'])->name('students.block_all_class');
            Route::post('unblock/class', [StudentRecordController::class, 'unblock_all_class'])->name('students.unblock_all_class');
            Route::post('block/graduated', [StudentRecordController::class, 'block_all_graduated'])->name('students.block_all_graduated');
            Route::post('unblock/graduated', [StudentRecordController::class, 'unblock_all_graduated'])->name('students.unblock_all_graduated');

            /* Batch import */
            Route::get('batch/template', [StudentRecordController::class, 'batch_template'])->name('students.batch_template');
            Route::put('batch', [StudentRecordController::class, 'batch_store'])->name('students.batch_add');
        });

        /*************** Users (admin operations) *****************/
        Route::prefix('users')->group(function () {
            Route::get('password/reset/{user}', [UserController::class, 'reset_pass'])->name('users.password.reset');
            Route::post('user/update/staff/data/edit_state', [UserController::class, 'update_staff_data_edit_state'])->name('users.update_staff_data_edit_state');
            Route::post('user/update/user/blocked_state', [UserController::class, 'update_user_blocked_state'])->name('users.update_user_blocked_state');
        });

        /*************** Query Builder (reports / printing) *****************/
        Route::prefix('query-builder')->group(function () {
            Route::get('/', [QueryBuilderController::class, 'index'])->name('query_builder.index');
            Route::get('select/query', [QueryBuilderController::class, 'select'])->name('query_builder.select');
            Route::post('staff/data/print', [QueryBuilderController::class, 'print_staff_data'])->name('query_builder.print_staff_data');
        });

        /*************** TimeTables *****************/
        Route::prefix('timetables')->group(function () {
            Route::get('/', [TimeTableController::class, 'index'])->name('timetables.index');

            Route::post('store', [TimeTableController::class, 'store'])->name('timetables.store');
            Route::put('{timetable}', [TimeTableController::class, 'update'])->name('timetables.update');
            Route::delete('{timetable}', [TimeTableController::class, 'delete'])->name('timetables.delete');

            /*************** TimeTable Records (records are under timetables) *****************/
            Route::prefix('records')->group(function () {
                Route::get('manage/{record}', [TimeTableController::class, 'manage'])->name('ttr.manage');
                Route::post('store', [TimeTableController::class, 'store_record'])->name('ttr.store');
                Route::get('{record}/edit', [TimeTableController::class, 'edit_record'])->name('ttr.edit');
                Route::put('{record}', [TimeTableController::class, 'update_record'])->name('ttr.update');

                Route::get('{record}', [TimeTableController::class, 'show_record'])->name('ttr.show');
                Route::get('{record}/print', [TimeTableController::class, 'print_record'])->name('ttr.print');
                Route::delete('{record}', [TimeTableController::class, 'delete_record'])->name('ttr.destroy');
            });

            /*************** Time Slots *****************/
            Route::prefix('time-slots')->group(function () {
                Route::post('store', [TimeTableController::class, 'store_time_slot'])->name('ts.store');
                Route::post('use/{record}', [TimeTableController::class, 'use_time_slot'])->name('ts.use');
                Route::get('{time_slot}/edit', [TimeTableController::class, 'edit_time_slot'])->name('ts.edit');
                Route::delete('{time_slot}', [TimeTableController::class, 'delete_time_slot'])->name('ts.destroy');
                Route::put('{time_slot}', [TimeTableController::class, 'update_time_slot'])->name('ts.update');
            });
        });

        /*************** Notices (CRUD + mark viewed) *****************/
        Route::prefix('notices')->group(function () {
            Route::post('set-viewed', [\App\Http\Controllers\NoticeController::class, 'set_as_viewed'])->name('notices.set_viewed');
            Route::get('/', [\App\Http\Controllers\NoticeController::class, 'index'])->name('notices.index');
            Route::post('store', [\App\Http\Controllers\NoticeController::class, 'store'])->name('notices.store');
            Route::delete('{notice}', [\App\Http\Controllers\NoticeController::class, 'destroy'])->name('notices.destroy');
            Route::get('{notice}/edit', [\App\Http\Controllers\NoticeController::class, 'edit'])->name('notices.edit');
            Route::post('{notice}', [\App\Http\Controllers\NoticeController::class, 'update_record'])->name('notices.update');
        });

        /*************** Payments (invoices, receipts, actions) *****************/
        Route::prefix('payments')->group(function () {
            Route::get('manage/{class?}', [PaymentController::class, 'manage'])->name('payments.manage');
            Route::get('invoice/{payment}/{year?}', [PaymentController::class, 'invoice'])->name('payments.invoice');
            Route::get('status/{payment}/{year?}', [PaymentController::class, 'status'])->name('payments.status');
            Route::get('receipts/{payment}/{notification_id?}', [PaymentController::class, 'receipts'])->name('payments.receipts');
            Route::get('receipts/pdf/{payment}', [PaymentController::class, 'pdf_receipts'])->name('payments.pdf_receipts');
            Route::post('select/year', [PaymentController::class, 'select_year'])->name('payments.select_year');
            Route::post('select/class', [PaymentController::class, 'select_class'])->name('payments.select_class');
            Route::delete('record/{payment}/reset', [PaymentController::class, 'reset_record'])->name('payments.reset_record');
            Route::post('pay-now/{payment}', [PaymentController::class, 'pay_now'])->name('payments.pay_now');
        });

        /*************** Pins *****************/
        Route::prefix('pins')->group(function () {
            Route::get('/', [PinController::class, 'index'])->name('pins.index');
            Route::get('create', [PinController::class, 'create'])->name('pins.create');
            Route::post('store', [PinController::class, 'store'])->name('pins.store');
            Route::get('enter/{pin}', [PinController::class, 'enter_pin'])->name('pins.enter');
            Route::post('verify/{pin}', [PinController::class, 'verify'])->name('pins.verify');
            Route::delete('/', [PinController::class, 'destroy'])->name('pins.destroy');
        });

        /*************** Marks (tabulation, batch operations) *****************/
        Route::prefix('marks')->group(function () {
            Route::get('batch', [MarkController::class, 'batch'])->name('marks.batch');
            Route::put('batch', [MarkController::class, 'batch_update'])->name('marks.batch_update');
            Route::get('batch/template', [MarkController::class, 'batch_template'])->name('marks.batch_template');
            Route::post('batch/delete', [MarkController::class, 'batch_delete'])->name('marks.batch_delete');
            Route::put('batch/upload', [MarkController::class, 'batch_upload'])->name('marks.batch_upload');

            // Prefer query params for optional filters: /marks/tabulation?exam=&class=&section=&year=
            Route::get('tabulation', [MarkController::class, 'tabulation'])->name('marks.tabulation');
            Route::post('tabulation/select', [MarkController::class, 'tabulation_select'])->name('marks.tabulation_select');
            Route::get('tabulation/print/{exam}/{class}/{section?}/{year?}', [MarkController::class, 'print_tabulation'])->name('marks.print_tabulation');

            Route::get('/', [MarkController::class, 'index'])->name('marks.index');
            Route::get('convert', [MarkController::class, 'convert'])->name('marks.convert');
            Route::get('manage/{exam}/{class}/{subject}/{section?}', [MarkController::class, 'manage'])->name('marks.manage');
            Route::put('update/{exam}/{class}/{subject}/{section?}', [MarkController::class, 'update'])->name('marks.update');
            Route::put('comment/{exr_id}', [MarkController::class, 'comment_update'])->name('marks.comment_update');
            Route::put('skills/{skill}/{exr_id}', [MarkController::class, 'skills_update'])->name('marks.skills_update');
            Route::post('selector', [MarkController::class, 'selector'])->name('marks.selector');
            Route::get('bulk/{class?}/{section?}', [MarkController::class, 'bulk'])->name('marks.bulk');
            Route::post('bulk/select', [MarkController::class, 'bulk_select'])->name('marks.bulk_select');

            Route::get('selector/year/{id}', [MarkController::class, 'year_selector'])->name('marks.year_selector');
            Route::post('selected/year/{id}', [MarkController::class, 'year_select'])->name('marks.year_select');
            Route::get('show/{id}/{year}', [MarkController::class, 'show'])->name('marks.show');
            Route::get('print/{id}/{exam_id}/{year}', [MarkController::class, 'print_view'])->name('marks.print');
        });

        /*************** Continuous Assessments *****************/
        Route::prefix('assessments')->group(function () {
            Route::get('batch/fix', [AssessmentController::class, 'batch_fix'])->name('assessments.batch_fix');
            Route::put('batch', [AssessmentController::class, 'batch_update'])->name('assessments.batch_update');

            Route::get('/', [AssessmentController::class, 'index'])->name('assessments.index');
            Route::get('list', [AssessmentController::class, 'list'])->name('assessments.list');
            Route::get('progressive', [AssessmentController::class, 'progressive'])->name('assessments.progressive'); // filters via query params
            Route::post('progressive/select', [AssessmentController::class, 'progressive_select'])->name('assessments.progressive_select');
            Route::get('progressive/print/{exam}/{class}/{sec_id}', [AssessmentController::class, 'print_progressive'])->name('assessments.print_progressive');
            Route::get('manage/{exam}/{class}/{subject}/{section?}', [AssessmentController::class, 'manage'])->name('assessments.manage');
            Route::put('update/{exam}/{class}/{subject}/{section?}', [AssessmentController::class, 'update'])->name('assessments.update');
            Route::post('selector', [AssessmentController::class, 'selector'])->name('assessments.selector');
            Route::get('bulk/{class?}/{section?}', [AssessmentController::class, 'bulk'])->name('assessments.bulk');
            Route::post('bulk', [AssessmentController::class, 'bulk_select'])->name('assessments.bulk_select');
            Route::get('print/{exam}/{class}/{subject}/{year}/{sec_id?}', [AssessmentController::class, 'print_assessments'])->name('assessments.print_assessments');
            Route::get('print/cover/{student}', [AssessmentController::class, 'print_cover'])->name('assessments.print_cover');
            Route::put('comment/{as_id}', [AssessmentController::class, 'comment_update'])->name('assessments.comment_update');
            Route::put('skills/{skill}/{as_id}', [AssessmentController::class, 'skills_update'])->name('assessments.skills_update');

            Route::get('show/{id}/{year}', [AssessmentController::class, 'show'])->name('assessments.show');
            Route::get('selector/year/{id}', [AssessmentController::class, 'year_selector'])->name('assessments.year_selector');
            Route::post('selected/year/{id}', [AssessmentController::class, 'year_select'])->name('assessments.year_select');
            Route::get('detailed/print/{id}/{exam_id}/{year}', [AssessmentController::class, 'print_detailed'])->name('assessments.print_detailed');
            Route::get('minimal/print/{id}/{exam_id}/{year}', [AssessmentController::class, 'print_minimal'])->name('assessments.print_minimal');
        });

        // Exam states and publication actions
        Route::prefix('exams')->group(function () {
            Route::post('update/edit-state', [ExamController::class, 'update_edit_state'])->name('exams.update_edit_state');
            Route::post('update/lock-state', [ExamController::class, 'update_lock_state'])->name('exams.update_lock_state');
            Route::post('{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
            Route::post('announce', [ExamController::class, 'announce'])->name('exams.announce');
        });

        /*************** Messages (threads & participants) **************/
        Route::prefix('messages')->group(function () {
            Route::delete('participant/{thread}/{participant}', [MessageController::class, 'remove_participant'])->name('messages.remove_participant');
            Route::post('participant/activate/all/thread/{thread}', [MessageController::class, 'activate_all_participants'])->name('messages.activate_all_participants');
            Route::delete('delete/{message}', [MessageController::class, 'user_delete'])->name('messages.user_delete');
            Route::post('participant/{thread}/last-read', [MessageController::class, 'update_participant_last_read'])->name('messages.update_participant_last_read');
            Route::post('previous/fetch/{thread}/{current_first_msg_id_in_view}', [MessageController::class, 'fetch_previous'])->name('messages.fetch_previous');
        });

        /*************** Subject Record operations **********************/
        Route::prefix('subject')->group(function () {
            Route::get('record/{record}/edit', [SubjectController::class, 'edit_record'])->name('subjects.edit_record');
            Route::delete('record/{subject}/{record}', [SubjectController::class, 'delete_record'])->name('subjects.delete_record');
            Route::put('record/{record}', [SubjectController::class, 'update_record'])->name('subjects.update_record');
        });

        /*************** Core resources **************/
        Route::resource('students', StudentRecordController::class);
        Route::resource('books', BookController::class);
        Route::resource('book_requests', BookRequestController::class);
        Route::resource('users', UserController::class);
        Route::resource('classes', MyClassController::class);
        Route::resource('sections', SectionController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('grades', GradeController::class);
        Route::resource('exams', ExamController::class);
        Route::resource('dorms', DormController::class);
        Route::resource('payments', PaymentController::class);
        Route::resource('divisions', DivisionController::class);
        Route::resource('messages', MessageController::class);
        Route::resource('notifications', NotificationController::class)->middleware('universal');

        /************************ AJAX helper endpoints ****************************/
        Route::prefix('ajax')->group(function () {
            Route::group(['middleware' => 'universal'], function () {
                Route::get('state/{state}', [AjaxController::class, 'get_state'])->name('ajax.get_state');
                Route::get('lga/{state}', [AjaxController::class, 'get_lga'])->name('ajax.get_lga');
            });

            Route::get('class-sections/{class}', [AjaxController::class, 'get_class_sections'])->name('ajax.get_class_sections');
            Route::get('class-students/{class}', [AjaxController::class, 'get_class_students'])->name('ajax.get_class_students');
            Route::get('pre-defined-subjects', [AjaxController::class, 'get_pre_defined_subjects'])->name('ajax.get_pre_defined_subjects');
            Route::get('teacher-class-sections/{class}', [AjaxController::class, 'get_teacher_class_sections'])->name('ajax.get_teacher_class_sections');
            Route::get('subject-section-teacher/{subject}/{section}', [AjaxController::class, 'get_subject_section_teacher'])->name('ajax.get_subject_section_teacher');
            Route::get('class-subjects/{class}', [AjaxController::class, 'get_class_subjects'])->name('ajax.get_class_subjects');
            Route::get('table-columns/{table}', [AjaxController::class, 'get_table_columns'])->name('ajax.get_table_columns');
            Route::get('year-exams/{year}', [AjaxController::class, 'get_year_exams'])->name('ajax.get_year_exams');
        });

        /************************ SUPER ADMIN ****************************/
        Route::group([], function () {
            /************* Settings and app-level actions ************/
            Route::group(['prefix' => 'settings'], function () {
                Route::get('app', [SettingController::class, 'index'])->name('settings.index')->middleware('password.confirm');
                Route::put('update', [SettingController::class, 'update'])->name('settings.update');
                Route::get('analytics/google/disable', [SettingController::class, 'disable_analytics'])->name('settings.disable_analytics');
                Route::get('analytics/google/enable', [SettingController::class, 'enable_analytics'])->name('settings.enable_analytics');
                Route::get('login-form/preview', [SettingController::class, 'preview_login_form'])->name('settings.preview_login_form');
            });

            // Bin / soft-delete management
            Route::delete('model/{model}/soft-deleted/empty', [SoftDeleteController::class, 'empty_soft_deleted_model'])->name('model.empty_soft_deleted');
            Route::get('bin', [SoftDeleteController::class, 'bin'])->name('bin')->middleware('password.confirm');

            // Payments soft-delete actions
            Route::post('payment/{payment}/restore', [SoftDeleteController::class, 'payment_restore'])->name('payments.restore');
            Route::post('payment/{payment}/force-delete', [SoftDeleteController::class, 'payment_force_delete'])->name('payments.force_delete');

            // Users soft-delete actions
            Route::post('user/{user}/restore', [SoftDeleteController::class, 'user_restore'])->name('user.restore');
            Route::post('user/{user}/force-delete', [SoftDeleteController::class, 'user_force_delete'])->name('user.force_delete');

            // My Classes soft-delete actions
            Route::post('my-class/{class}/restore', [SoftDeleteController::class, 'my_class_restore'])->name('my_class.restore');
            Route::post('my-class/{class}/force-delete', [SoftDeleteController::class, 'my_class_force_delete'])->name('my_class.force_delete');

            // Exams soft-delete actions
            Route::post('exam/{exam}/restore', [SoftDeleteController::class, 'exam_restore'])->name('exam.restore');
            Route::post('exam/{exam}/force-delete', [SoftDeleteController::class, 'exam_force_delete'])->name('exam.force_delete');

            // Message Thread soft-delete actions
            Route::post('thread/{thread}/restore', [SoftDeleteController::class, 'thread_restore'])->name('thread.restore');
            Route::post('thread/{thread}/force-delete', [SoftDeleteController::class, 'thread_force_delete'])->name('thread.force_delete');

            /************* Logs ************/
            Route::prefix('logs')->group(function () {
                Route::get('/', [LogController::class, 'index'])->name('logs.index');
                Route::post('login-history/reset/{user}', [LogController::class, 'reset_login_histories'])->name('login_histories.reset');
                Route::post('delete-activity/{log}', [LogController::class, 'delete_activity'])->name('activity_log.delete');
                Route::post('activity-log/clean', [LogController::class, 'clean_activity_log'])->name('activity_log.clean');
            });
        });

        /************************ PARENT ****************************/
        Route::prefix('parent')->group(function () {
            Route::get('my-children', [MyController::class, 'children'])->name('parent.my_children');
        });

        /*************** Analytics (tracking & fetch) **************/
        Route::prefix('analytics')->group(function () {
            Route::get('/', [AnalyticController::class, 'index'])->name('analytics.index');
            Route::post('google/setup', [AnalyticController::class, 'google_setup'])->name('analytics.google_setup');
            Route::get('data/google/fetch', [AnalyticController::class, 'fetch_data'])->name('analytics.fetch_data');
        });

        /*************** Notifications (user-specific settings) **************/
        Route::prefix('notifications')->group(function () {
            Route::post('mark/as-read/{notification}', [NotificationController::class, 'mark_as_read'])->name('notifications.mark_as_read');
            Route::put('mark/all-read', [NotificationController::class, 'mark_all_read'])->name('notifications.mark_all_read');
            Route::put('update/notifiable', [NotificationController::class, 'update_is_notifiable'])->name('notifications.update_is_notifiable');
            Route::match(['post', 'put', 'delete'], 'update/firebase/device-token', [NotificationController::class, 'update_firebase_device_token'])->name('notifications.firebase.update_device_token');
        })->middleware('universal');

        /*************** Tickets (support workflow) **************/
        Route::prefix('tickets')->group(function () {
            Route::get('reply/{ticket}', [TicketController::class, 'reply'])->name('tickets.reply');
            Route::put('store/message', [TicketController::class, 'store_message'])->name('tickets.store_message');
            Route::patch('{ticket}/close', [TicketController::class, 'close_ticket'])->name('tickets.close');
            Route::patch('{ticket}/archive', [TicketController::class, 'archive_ticket'])->name('tickets.archive');
            Route::patch('{ticket}/unarchive', [TicketController::class, 'unarchive_ticket'])->name('tickets.unarchive');
        });

        // Resourceful controller for tickets
        Route::resource('tickets', TicketController::class);
    });
});
