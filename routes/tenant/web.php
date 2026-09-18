<?php

declare(strict_types=1);

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookRequestController;
use App\Http\Controllers\ChildrenController;
use App\Http\Controllers\ClassTypeController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DormController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\MessageThreadController;
use App\Http\Controllers\MyClassController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\QueryBuilderController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SoftDeleteController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StudentIDCardController;
use App\Http\Controllers\StudentRecordController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserImpersonationController;
use App\Http\Controllers\UserInvitationController;
use App\Http\Requests\CustomEmailVerificationRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::get('/impersonate/{token}', [UserImpersonationController::class, 'impersonate'])->name('tenant.impersonate');

Route::group(['middleware' => ['auth', 'checkForPassUpdate']], function () {
    // Require 2FA for the routes below
    Route::group(['middleware' => '2fa'], function () {
        /*************** Impersonation **************/
        // Route::post('auth/back-to-central', [TenantAuthController::class, 'back_to_central'])->name('tenant.back_to_central');

        /*************** Dashboard **************/
        Route::get('/', [HomeController::class, 'dashboard'])->name('index');
        Route::get('home', [HomeController::class, 'dashboard'])->name('home');
        Route::get('dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

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

        /*************** Statistics **************/
        Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');

        /*************** Students *****************/
        Route::prefix('students')->group(function () {
            Route::get('{student}/password/reset', [StudentRecordController::class, 'reset_password'])->name('students.password.reset')->middleware('decodeRouteParams');
            Route::get('graduated', [StudentRecordController::class, 'graduated'])->name('students.graduated');
            Route::put('{student}/{prom_id}/mark-not-graduated', [StudentRecordController::class, 'mark_not_graduated'])->name('students.mark_not_graduated')->middleware('decodeRouteParams');
            Route::get('class/{class_id}/list', [StudentRecordController::class, 'list_by_class'])->name('students.list_by_class');
            Route::get('my-profile/{student}', [StudentRecordController::class, 'show_profile'])->name('students.show_profile')->middleware('decodeRouteParams');

            /* ID Cards */
            Route::get('id-cards', [StudentIDCardController::class, 'index'])->name('students.id_cards');
            Route::post('id-cards/manage/{class?}/{issued_date?}/{expire_date?}/{theme?}', [StudentIDCardController::class, 'manage'])->name('students.id_cards.manage');
            Route::get('id-cards/print/class/{class?}/{issued?}/{expire?}/{theme?}/{phone?}/{class_from?}/{class_to?}/{motto?}/{brightness?}/{website_link?}', [StudentIDCardController::class, 'print_class'])->name('students.id_cards.print_class');
            Route::post('id-cards/selected/print', [StudentIDCardController::class, 'print_selected'])->name('students.id_cards.print_selected');

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

        /*************** Users *****************/
        Route::prefix('users')->group(function () {
            Route::get('password/reset/{user}', [UserController::class, 'reset_password'])->name('users.password.reset');
            Route::post('toggle/staff-data-editing', [UserController::class, 'toggle_staff_data_editing'])->name('users.toggle_staff_data_editing');
            Route::post('update/blocked-state', [UserController::class, 'toggle_user_block'])->name('users.toggle_user_block');
            Route::get('my-profile/{user}', [UserController::class, 'show_profile'])->name('users_profile.show')->middleware('decodeRouteParams');
            Route::get('invitations', [UserInvitationController::class, 'view_registrations'])->name('users.invitations.index');
            Route::post('invite', [UserInvitationController::class, 'invite_user'])->name('users.invitations.store');
            Route::post('registrations/{id}/approve', [UserInvitationController::class, 'approve'])->name('users.registrations.approve');
            Route::post('registrations/{id}/reject', [UserInvitationController::class, 'reject'])->name('users.registrations.reject');
            Route::post('registrations/{id}/cancel', [UserInvitationController::class, 'cancel'])->name('users.registrations.cancel');
        });

        /*************** Query Builder (reports / printing) *****************/
        Route::prefix('query-builder')->group(function () {
            Route::get('/', [QueryBuilderController::class, 'index'])->name('query_builder.index');
            Route::get('select/query', [QueryBuilderController::class, 'select'])->name('query_builder.select');
            Route::post('users/data/print', [QueryBuilderController::class, 'print_users_data'])->name('query_builder.print_users_data');
        });

        /*************** TimeTables *****************/
        Route::prefix('timetables')->group(function () {
            Route::get('/', [TimeTableController::class, 'index'])->name('timetables.index');

            Route::post('store', [TimeTableController::class, 'store'])->name('timetables.store');
            Route::put('{timetable}', [TimeTableController::class, 'update'])->name('timetables.update');
            Route::delete('{timetable}', [TimeTableController::class, 'delete'])->name('timetables.delete');

            /*************** TimeTable Records (records are under timetables) *****************/
            Route::prefix('records')->group(function () {
                Route::get('manage/{record}', [TimeTableController::class, 'manage_record'])->name('ttr.manage');
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
            Route::post('set-viewed', [NoticeController::class, 'mark_as_viewed'])->name('notices.mark_as_viewed');
            Route::get('/', [NoticeController::class, 'index'])->name('notices.index');
            Route::post('store', [NoticeController::class, 'store'])->name('notices.store');
            Route::delete('{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');
            Route::get('{notice}/edit', [NoticeController::class, 'edit'])->name('notices.edit')->middleware('decodeRouteParams');
            Route::post('update/{notice}', [NoticeController::class, 'update'])->name('notices.update');
        });

        /*************** Payments (invoices, receipts, actions) *****************/
        Route::prefix('payments')->group(function () {
            Route::get('manage/{class?}', [PaymentController::class, 'manage'])->name('payments.manage');
            Route::post('select/year', [PaymentController::class, 'select_year'])->name('payments.select_year');
            Route::post('select/class', [PaymentController::class, 'select_class'])->name('payments.select_class');

            Route::middleware('decodeRouteParams')->group(function () {
                Route::get('invoice/{student}/{year?}', [PaymentController::class, 'invoice'])->name('payments.invoice');
                Route::get('status/{student}/{year?}', [PaymentController::class, 'status'])->name('payments.status');
                Route::get('receipts/{payment}/{notification_id?}', [PaymentController::class, 'receipts'])->name('payments.receipts');
                Route::delete('record/{payment}/reset', [PaymentController::class, 'reset_record'])->name('payments.reset_record');
                Route::post('pay-now/{payment}', [PaymentController::class, 'pay_now'])->name('payments.pay_now');
            });
        });

        /*************** Pins *****************/
        Route::prefix('pins')->group(function () {
            Route::get('/', [PinController::class, 'index'])->name('pins.index');
            Route::get('create', [PinController::class, 'create'])->name('pins.create');
            Route::post('store', [PinController::class, 'store'])->name('pins.store');
            Route::get('enter/{pin}', [PinController::class, 'enter_pin'])->name('pins.enter')->middleware('decodeRouteParams');
            Route::post('verify/{pin}', [PinController::class, 'verify'])->name('pins.verify')->middleware('decodeRouteParams');
            Route::delete('/', [PinController::class, 'destroy'])->name('pins.destroy');
        });

        /*************** Marks (tabulation, batch operations) *****************/
        Route::prefix('marks')->group(function () {
            Route::get('batch', [MarkController::class, 'batch'])->name('marks.batch');
            Route::put('batch', [MarkController::class, 'batch_update'])->name('marks.batch_update');
            Route::put('batch/initialize', [MarkController::class, 'initialize_marks'])->name('marks.batch_initialize');
            Route::get('batch/template', [MarkController::class, 'batch_template'])->name('marks.batch_template');
            Route::post('batch/delete', [MarkController::class, 'batch_delete'])->name('marks.batch_delete');
            Route::put('batch/upload', [MarkController::class, 'batch_upload'])->name('marks.batch_upload');

            Route::get('tabulation/{exam?}/{class?}/{section?}/{year?}', [MarkController::class, 'tabulation'])->name('marks.tabulation');
            Route::post('tabulation/select', [MarkController::class, 'tabulation_select'])->name('marks.tabulation_select');
            Route::get('tabulation/print/{exam}/{class}/{section?}/{year?}', [MarkController::class, 'print_tabulation'])->name('marks.print_tabulation');

            Route::get('manage/{exam?}/{class?}/{subject?}/{section?}/{selected?}', [MarkController::class, 'manage'])->name('marks.manage');
            Route::put('update/{exam}/{class}/{subject}/{section?}', [MarkController::class, 'update'])->name('marks.update');
            Route::put('comment/{exr_id}', [MarkController::class, 'comment_update'])->name('marks.comment_update');
            Route::put('skills/{skill}/{exr_id}', [MarkController::class, 'skills_update'])->name('marks.skills_update');
            Route::post('selector', [MarkController::class, 'selector'])->name('marks.selector');
            Route::get('sheets/{class?}/{section?}', [MarkController::class, 'sheets'])->name('marks.sheets');
            Route::post('sheets/select', [MarkController::class, 'sheets_select'])->name('marks.sheets_select');

            Route::group(['middleware' => 'decodeRouteParams'], function () {
                Route::get('year-selector/{student}', [MarkController::class, 'year_selector'])->name('marks.year_selector');
                Route::post('selected/year/{student}', [MarkController::class, 'year_selected'])->name('marks.year_selected');
                Route::get('show/{student}/{year}', [MarkController::class, 'show'])->name('marks.show');
                Route::get('print/{student}/{exam_id}/{year}', [MarkController::class, 'print_view'])->name('marks.print');
            });
        });

        /*************** Continuous Assessments *****************/
        Route::prefix('assessments')->group(function () {
            Route::get('/', [AssessmentController::class, 'index'])->name('assessments.index');
            Route::get('list', [AssessmentController::class, 'list'])->name('assessments.list');
            Route::get('progressive/{assessment?}/{class?}/{section?}', [AssessmentController::class, 'progressive'])->name('assessments.progressive'); // filters via query params
            Route::post('progressive/select', [AssessmentController::class, 'progressive_select'])->name('assessments.progressive_select');
            Route::get('progressive/print/{assessment?}/{class?}/{section?}', [AssessmentController::class, 'print_progressive'])->name('assessments.print_progressive');
            Route::get('manage/{assessment}/{class}/{subject}/{section?}', [AssessmentController::class, 'manage'])->name('assessments.manage');
            Route::put('update/{assessment}/{class}/{subject}/{section?}', [AssessmentController::class, 'update'])->name('assessments.update');
            Route::post('selector', [AssessmentController::class, 'selector'])->name('assessments.selector');
            Route::get('sheets/{class?}/{section?}', [AssessmentController::class, 'sheets'])->name('assessments.sheets');
            Route::post('sheets/select', [AssessmentController::class, 'sheets_select'])->name('assessments.sheets_select');
            Route::get('print/{assessment}/{class}/{subject}/{year}/{section?}', [AssessmentController::class, 'print'])->name('assessments.print');
            Route::get('print/cover/{student}', [AssessmentController::class, 'print_cover'])->name('assessments.print_cover')->middleware('decodeRouteParams');
            Route::put('comment/{as_id}', [AssessmentController::class, 'comment_update'])->name('assessments.comment_update');
            Route::put('skills/{skill}/{as_id}', [AssessmentController::class, 'skills_update'])->name('assessments.skills_update');

            Route::group(['middleware' => 'decodeRouteParams'], function () {
                Route::get('show/{student}/{year}', [AssessmentController::class, 'show'])->name('assessments.show');
                Route::get('selector/year/{student}', [AssessmentController::class, 'year_selector'])->name('assessments.year_selector');
                Route::post('selected/year/{student}', [AssessmentController::class, 'year_selected'])->name('assessments.year_selected');
                Route::get('detailed/print/{student}/{assessment}/{year}', [AssessmentController::class, 'print_detailed'])->name('assessments.print_detailed');
                Route::get('minimal/print/{student}/{assessment}/{year}', [AssessmentController::class, 'print_minimal'])->name('assessments.print_minimal');
            });
        });

        // Exam states and publication actions
        Route::prefix('exams')->group(function () {
            Route::post('update/lock-state', [ExamController::class, 'update_lock_state'])->name('exams.update_lock_state');
            Route::post('{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
            Route::post('announce', [ExamController::class, 'announce'])->name('exams.announce');
            Route::post('generate-numbers', [ExamController::class, 'generate_exam_numbers'])->name('exams.generate_numbers');
            Route::get('assign', [ExamController::class, 'assign_view'])->name('exams.exam_numbers.assign');
            Route::get('exam-numbers/load', [ExamController::class, 'exam_numbers_load'])->name('exams.exam_numbers.load');
            Route::post('exam-numbers/save', [ExamController::class, 'exam_numbers_save'])->name('exams.exam_numbers.save');
        });

        /*************** Messages (threads & participants) **************/
        Route::prefix('threads')->group(function () {
            // Participants
            Route::delete('{thread}/participants/{participant}', [MessageThreadController::class, 'remove_participant'])->name('threads.participants.remove');
            Route::post('{thread}/participants/activate-all', [MessageThreadController::class, 'activate_all_participants'])->name('threads.participants.activate_all');
            Route::post('{thread}/participants/update/last-read', [MessageThreadController::class, 'update_participant_last_read'])->name('threads.participants.update.last_read');
            // Messages
            Route::delete('messages/{message}/user-delete', [MessageThreadController::class, 'user_delete_message'])->name('threads.messages.user_delete');
            Route::post('{thread}/messages/previous/{current_first_msg_id}', [MessageThreadController::class, 'load_previous_messages'])->name('threads.messages.previous');
            Route::post('{thread}/add-message', [MessageThreadController::class, 'add_message'])->name('threads.messages.add');
        });

        /*************** Subject Record operations **********************/
        Route::prefix('subject-records')->group(function () {
            Route::get('index', [SubjectController::class, 'index_record'])->name('subject_records.index');
            Route::post('store', [SubjectController::class, 'store_record'])->name('subject_records.store');
            Route::get('{record}/edit', [SubjectController::class, 'edit_record'])->name('subject_records.edit');
            Route::delete('/{record}', [SubjectController::class, 'delete_record'])->name('subject_records.delete');
            Route::put('{record}', [SubjectController::class, 'update_record'])->name('subject_records.update');
        });

        /*************** Core resources **************/
        Route::resource('students', StudentRecordController::class)->middleware('decodeRouteParams');
        Route::resource('books', BookController::class)->middleware('decodeRouteParams');
        Route::resource('book_requests', BookRequestController::class)->middleware('decodeRouteParams');
        Route::resource('users', UserController::class)->middleware('decodeRouteParams');
        Route::resource('classes', MyClassController::class);
        Route::resource('sections', SectionController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('grades', GradeController::class);
        Route::resource('exams', ExamController::class);
        Route::resource('dorms', DormController::class);
        Route::resource('payments', PaymentController::class);
        Route::resource('divisions', DivisionController::class);
        Route::resource('message_threads', MessageThreadController::class);
        Route::resource('class_types', ClassTypeController::class);

        /************************ AJAX helper endpoints ****************************/
        Route::prefix('ajax')->group(function () {
            Route::get('class-sections/{class}', [AjaxController::class, 'get_class_sections'])->name('ajax.get_class_sections');
            Route::get('class-students/{class}', [AjaxController::class, 'get_class_students'])->name('ajax.get_class_students');
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
                Route::post('delete-activity/{log}', [LogController::class, 'delete_activity'])->name('activity_log.delete');
                Route::post('activity-log/clean', [LogController::class, 'clean_activity_log'])->name('activity_log.clean');
            });
        });

        /************************ PARENT ****************************/
        Route::prefix('parent')->middleware('role:parent')->group(function () {
            Route::get('my-children', [ChildrenController::class, 'children'])->name('parent.my_children');
        });

        /*************** Analytics (tracking & fetch) **************/
        Route::prefix('analytics')->group(function () {
            Route::get('/', [AnalyticController::class, 'index'])->name('analytics.index');
            Route::post('google/setup', [AnalyticController::class, 'google_setup'])->name('analytics.google_setup');
            Route::get('data/google/fetch', [AnalyticController::class, 'fetch_data'])->name('analytics.fetch_data');
        });

        /*************** Tickets (support workflow) **************/
        Route::prefix('tickets')->group(function () {
            Route::get('reply/{ticket}', [TicketController::class, 'reply'])->name('tickets.reply')->middleware('decodeRouteParams');
            Route::put('store/message', [TicketController::class, 'store_message'])->name('tickets.store_message');
            Route::get('{ticket}/close', [TicketController::class, 'close_ticket'])->name('tickets.close')->middleware('decodeRouteParams');
            Route::patch('{ticket}/archive', [TicketController::class, 'archive_ticket'])->name('tickets.archive');
            Route::patch('{ticket}/unarchive', [TicketController::class, 'unarchive_ticket'])->name('tickets.unarchive');
        });

        // Resourceful controller for tickets
        Route::resource('tickets', TicketController::class);
    });
});

// Public registration routes (no auth)
Route::middleware('guest')->group(function () {
    Route::get('/register/{token}', [UserInvitationController::class, 'show_registration_form'])->name('users.registrations.show');
    Route::post('/register/{token}', [UserInvitationController::class, 'submit_registration'])->name('users.registrations.submit');
});
