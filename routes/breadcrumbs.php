<?php

declare(strict_types=1);

use App\Helpers\Usr;
use App\Repositories\StudentRepo;
use App\Repositories\TimeTableRepo;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// ----------------------------------------------------------------------
// Common Home / Dashboard
// ----------------------------------------------------------------------

Breadcrumbs::for(Usr::tenancyInitialized() ? 'index' : 'central_index', function (BreadcrumbTrail $trail): void {
    $trail->push('Home', route(Usr::tenancyInitialized() ? 'index' : 'central_index'));
});

Breadcrumbs::for(Usr::tenancyInitialized() ? 'home' : 'central_home', function (BreadcrumbTrail $trail): void {
    $trail->push('Home', route(Usr::tenancyInitialized() ? 'home' : 'central_home'));
});

Breadcrumbs::for(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard', function (BreadcrumbTrail $trail): void {
    $trail->push('Dashboard', route(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard'));
});

// ----------------------------------------------------------------------
// Account & Security
// ----------------------------------------------------------------------

Breadcrumbs::for('account_security.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Account Security', route('account_security.index'));
});

Breadcrumbs::for('my_account.edit', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('My Account', route('my_account.edit'));
});

Breadcrumbs::for('my_account.update', function (BreadcrumbTrail $trail): void {
    $trail->parent('my_account.edit');
    $trail->push('Update', route('my_account.update'));
});

Breadcrumbs::for('my_account.change_pass', function (BreadcrumbTrail $trail): void {
    $trail->parent('my_account.edit');
    $trail->push('Change Password', route('my_account.change_pass'));
});

// ----------------------------------------------------------------------
// Students
// ----------------------------------------------------------------------

Breadcrumbs::for('students.create', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Admit Student', route('students.create'));
});

Breadcrumbs::for('students.list_by_class', function (BreadcrumbTrail $trail, $class_id): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Students List', route('students.list_by_class', $class_id));
});

Breadcrumbs::for('students.password.reset', function (BreadcrumbTrail $trail, $st_id): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Reset Student Password', route('students.password.reset', $st_id));
});

Breadcrumbs::for('students.graduated', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Graduated Students', route('students.graduated'));
});

Breadcrumbs::for('students.id_cards', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Students ID Cards', route('students.id_cards'));
});

Breadcrumbs::for('students.id_cards.manage', function (BreadcrumbTrail $trail): void {
    $trail->parent('students.id_cards');
    $trail->push('Manage Students ID Cards', route('students.id_cards.manage'));
});

Breadcrumbs::for('students.show_profile', function (BreadcrumbTrail $trail, $student): void {
    $trail->parent(
        auth()->user()->hasExactRoles([config('roles.default_roles.tenant.parent')])
        ? 'parent.my_children'
        : 'dashboard'
    );
    $trail->push('My Profile', route('students.show_profile', $student));
});

Breadcrumbs::for('students.show', function (BreadcrumbTrail $trail, $student): void {
    $class_id = StudentRepo::getStudentRecordColumn(['id' => $student], 'my_class_id');
    $trail->parent('students.list_by_class', $class_id);
    $trail->push('View Student Profile', route('students.show', $student));
});

Breadcrumbs::for('students.edit', function (BreadcrumbTrail $trail, $student): void {
    $class_id = StudentRepo::getStudentRecordColumn(['id' => $student], 'my_class_id');
    $trail->parent('students.list_by_class', $class_id);
    $trail->push('Edit Student Profile', route('students.edit', $student));
});

Breadcrumbs::for('students.promotion', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Promote Student', route('students.promotion'));
});

Breadcrumbs::for('students.promotion.manage', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Promotions', route('students.promotion.manage'));
});

// ----------------------------------------------------------------------
// Users
// ----------------------------------------------------------------------

Breadcrumbs::for('users.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Users', route('users.index'));
});

Breadcrumbs::for('users.edit', function (BreadcrumbTrail $trail, $user): void {
    $trail->parent('users.index');
    $trail->push('Edit User Details', route('users.edit', $user));
});

Breadcrumbs::for('users.show', function (BreadcrumbTrail $trail, $user): void {
    $trail->parent('users.index');
    $trail->push('View User Profile', route('users.show', $user));
});

Breadcrumbs::for('users_profile.show', function (BreadcrumbTrail $trail, $user): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('My Profile', route('users_profile.show', $user));
});

// Central users
Breadcrumbs::for('central_users.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('central_dashboard');
    $trail->push('Manage Users', route('central_users.index'));
});

Breadcrumbs::for('central_users.edit', function (BreadcrumbTrail $trail, $user): void {
    $trail->parent('central_users.index');
    $trail->push('Edit User Details', route('central_users.edit', $user));
});

Breadcrumbs::for('central_users.show', function (BreadcrumbTrail $trail, $user): void {
    $trail->parent('central_users.index');
    $trail->push('View User Profile', route('central_users.show', $user));
});

Breadcrumbs::for('central_users_profile.show', function (BreadcrumbTrail $trail, $user): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('My Profile', route('central_users_profile.show', $user));
});

// Invitations
Breadcrumbs::for('users.invitations.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('dashboard');
    $trail->push('Manage Invitations', route('users.invitations.index'));
});

// ----------------------------------------------------------------------
// Classes / Dorms / Sections / Class Types
// ----------------------------------------------------------------------

Breadcrumbs::for('classes.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Classes', route('classes.index'));
});

Breadcrumbs::for('classes.edit', function (BreadcrumbTrail $trail, $class): void {
    $trail->parent('classes.index');
    $trail->push('Edit Class', route('classes.edit', $class));
});

Breadcrumbs::for('dorms.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Dorms', route('dorms.index'));
});

Breadcrumbs::for('dorms.edit', function (BreadcrumbTrail $trail, $dorm): void {
    $trail->parent('dorms.index');
    $trail->push('Edit Dorm', route('dorms.edit', $dorm));
});

Breadcrumbs::for('sections.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Class Sections', route('sections.index'));
});

Breadcrumbs::for('sections.edit', function (BreadcrumbTrail $trail, $section): void {
    $trail->parent('sections.index');
    $trail->push('Edit Section', route('sections.edit', $section));
});

Breadcrumbs::for('class_types.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Class Types', route('class_types.index'));
});

Breadcrumbs::for('class_types.edit', function (BreadcrumbTrail $trail, $class_type): void {
    $trail->parent('class_types.index');
    $trail->push('Edit Class Type', route('class_types.edit', $class_type));
});

// ----------------------------------------------------------------------
// Subjects
// ----------------------------------------------------------------------

Breadcrumbs::for('subjects.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Subjects', route('subjects.index'));
});

Breadcrumbs::for('subject_records.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Subject Records', route('subject_records.index'));
});

Breadcrumbs::for('subjects.edit', function (BreadcrumbTrail $trail, $subject): void {
    $trail->parent('subjects.index');
    $trail->push('Edit Subject', route('subjects.edit', $subject));
});

Breadcrumbs::for('subject_records.edit', function (BreadcrumbTrail $trail, $subject): void {
    $trail->parent('subject_records.index');
    $trail->push('Edit Subject Record', route('subject_records.edit', $subject));
});

// ----------------------------------------------------------------------
// Exams / Grades / Divisions
// ----------------------------------------------------------------------

Breadcrumbs::for('exams.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Exams', route('exams.index'));
});

Breadcrumbs::for('exams.edit', function (BreadcrumbTrail $trail, $exam): void {
    $trail->parent('exams.index');
    $trail->push('Edit Exam', route('exams.edit', $exam));
});

Breadcrumbs::for('grades.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Grades', route('grades.index'));
});

Breadcrumbs::for('grades.edit', function (BreadcrumbTrail $trail, $grade): void {
    $trail->parent('grades.index');
    $trail->push('Edit Grade', route('grades.edit', $grade));
});

Breadcrumbs::for('divisions.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Divisions', route('divisions.index'));
});

Breadcrumbs::for('divisions.edit', function (BreadcrumbTrail $trail, $division): void {
    $trail->parent('divisions.index');
    $trail->push('Edit Division', route('divisions.edit', $division));
});

// ----------------------------------------------------------------------
// Marks
// ----------------------------------------------------------------------

Breadcrumbs::for('marks.tabulation', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Tabulation Sheet', route('marks.tabulation'));
});

Breadcrumbs::for('marks.batch', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Batch Marks', route('marks.batch'));
});

Breadcrumbs::for('marks.manage', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Exam Marks', route('marks.manage'));
});

Breadcrumbs::for('marks.sheets', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Student Marksheet', route('marks.sheets'));
});

Breadcrumbs::for('marks.year_selector', function (BreadcrumbTrail $trail, $id): void {
    $trail->parent(
        auth()->user()->hasExactRoles([config('roles.default_roles.tenant.parent')])
        ? 'parent.my_children'
        : 'dashboard'
    );
    $trail->push('Select Year', route('marks.year_selector', $id));
});

Breadcrumbs::for('marks.show', function (BreadcrumbTrail $trail, $id, $year): void {
    $trail->parent('marks.year_selector', Usr::hash($id));
    $trail->push('Student Marksheet', route('marks.show', [$id, $year]));
});

// ----------------------------------------------------------------------
// Assessments (Continuous Assessments)
// ----------------------------------------------------------------------

Breadcrumbs::for('assessments.list', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Assessments List', route('assessments.list'));
});

Breadcrumbs::for('assessments.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Assessments', route('assessments.index'));
});

Breadcrumbs::for('assessments.manage', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Assessments Management', route('assessments.manage'));
});

Breadcrumbs::for('assessments.progressive', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Progressive Sheet', route('assessments.progressive'));
});

Breadcrumbs::for('assessments.sheets', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Assessment Sheet', route('assessments.sheets'));
});

Breadcrumbs::for('assessments.year_selector', function (BreadcrumbTrail $trail, $id): void {
    $trail->parent(
        auth()->user()->hasExactRoles([config('roles.default_roles.tenant.parent')])
        ? 'parent.my_children'
        : 'dashboard'
    );
    $trail->push('Select Year', route('assessments.year_selector', $id));
});

Breadcrumbs::for('assessments.show', function (BreadcrumbTrail $trail, $id, $year): void {
    $trail->parent('assessments.year_selector', params: Usr::hash($id));
    $trail->push('Assessment Sheet', route('assessments.show', [$id, $year]));
});

// ----------------------------------------------------------------------
// Settings
// ----------------------------------------------------------------------

Breadcrumbs::for('settings.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('System Settings', route('settings.index'));
});

Breadcrumbs::for('settings_non_tenancy.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('central_dashboard');
    $trail->push('System Settings', route('settings_non_tenancy.index'));
});

// ----------------------------------------------------------------------
// Timetables & Time Slots
// ----------------------------------------------------------------------

Breadcrumbs::for('timetables.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Manage Timetables', route('timetables.index'));
});

Breadcrumbs::for('ttr.show', function (BreadcrumbTrail $trail, $ttr): void {
    $trail->parent('timetables.index');
    $trail->push('View Timetable', route('ttr.show', $ttr));
});

Breadcrumbs::for('ttr.edit', function (BreadcrumbTrail $trail, $ttr): void {
    $trail->parent('timetables.index');
    $trail->push('Edit Timetable', route('ttr.edit', $ttr));
});

Breadcrumbs::for('ttr.manage', function (BreadcrumbTrail $trail, $ttr): void {
    $trail->parent('timetables.index');
    $trail->push('Manage Timetable Record', route('ttr.manage', $ttr));
});

Breadcrumbs::for('ts.edit', function (BreadcrumbTrail $trail, $ts_id): void {
    $timeTableRepo = new TimeTableRepo;
    $ttr_id = $timeTableRepo->findTimeSlot($ts_id)->ttr_id;
    $trail->parent('ttr.manage', $ttr_id);
    $trail->push('Edit Time Slot', route('ts.edit', $ts_id));
});

// ----------------------------------------------------------------------
// Payments
// ----------------------------------------------------------------------

Breadcrumbs::for('payments.create', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Create Payment', route('payments.create'));
});

Breadcrumbs::for('payments.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Payments', route('payments.index'));
});

Breadcrumbs::for('payments.show', function (BreadcrumbTrail $trail, $payment): void {
    $trail->parent('payments.index');
    $trail->push('Payments Details', route('payments.show', $payment));
});

Breadcrumbs::for('payments.edit', function (BreadcrumbTrail $trail, $payment): void {
    $trail->parent('payments.index');
    $trail->push('Edit Payment', route('payments.edit', $payment));
});

Breadcrumbs::for('payments.manage', function (BreadcrumbTrail $trail, $class_id = null): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Student Payments', route('payments.manage', $class_id));
});

Breadcrumbs::for('payments.invoice', function (BreadcrumbTrail $trail, $student_id, $year): void {
    $trail->parent('payments.manage');
    $trail->push('Student Invoice', route('payments.invoice', [$student_id, $year]));
});

Breadcrumbs::for('payments.status', function (BreadcrumbTrail $trail, $student_id, $year): void {
    $trail->parent('parent.my_children');
    $trail->push('Payment Status', route('payments.status', [$student_id, $year]));
});

// ----------------------------------------------------------------------
// Pins
// ----------------------------------------------------------------------

Breadcrumbs::for('pins.create', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    $trail->push('Generate Pins', route('pins.create'));
});

Breadcrumbs::for('pins.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('pins.create');
    $trail->push('View Pins', route('pins.index'));
});

Breadcrumbs::for('pins.enter', function (BreadcrumbTrail $trail, $sid): void {
    $trail->parent('marks.year_selector', Usr::hash($sid));
    $trail->push('Enter Pin', route('pins.enter', $sid));
});

// ----------------------------------------------------------------------
// Children / Parent
// ----------------------------------------------------------------------

Breadcrumbs::for('parent.my_children', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('My Children', route('parent.my_children'));
});

// ----------------------------------------------------------------------
// Bin / Statistics / Analytics
// ----------------------------------------------------------------------

Breadcrumbs::for('bin', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Recycle Bin', route('bin'));
});

Breadcrumbs::for('central_bin', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Recycle Bin', route('central_bin'));
});

Breadcrumbs::for('statistics.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Statistics', route('statistics.index'));
});

Breadcrumbs::for('analytics.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Analytics', route('analytics.index'));
});

Breadcrumbs::for('analytics.fetch_data', function (BreadcrumbTrail $trail): void {
    $trail->parent('analytics.index');
    $trail->push('Analytics Data', route('analytics.fetch_data'));
});

// ----------------------------------------------------------------------
// Schedule / Events
// ----------------------------------------------------------------------

Breadcrumbs::for('schedule.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Schedule', route('schedule.index'));
});

Breadcrumbs::for('events.edit', function (BreadcrumbTrail $trail, $event_id): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Edit Event', route('schedule.events.edit', $event_id));
});

// ----------------------------------------------------------------------
// Artisan Commands / Query Builder
// ----------------------------------------------------------------------

Breadcrumbs::for('artisan_commands.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Artisan Commands', route('artisan_commands.index'));
});

Breadcrumbs::for('query_builder.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Query Builder', route('query_builder.index'));
});

Breadcrumbs::for('query_builder.select', function (BreadcrumbTrail $trail): void {
    $trail->parent('query_builder.index');
    $trail->push('Query Results', route('query_builder.select'));
});

// ----------------------------------------------------------------------
// Notices
// ----------------------------------------------------------------------

Breadcrumbs::for('notices.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Notices', route('notices.index'));
});

Breadcrumbs::for('notices.edit', function (BreadcrumbTrail $trail, $notice_id): void {
    $trail->parent('notices.index');
    $trail->push('Edit Notice', route('notices.edit', $notice_id));
});

// ----------------------------------------------------------------------
// Messages
// ----------------------------------------------------------------------

Breadcrumbs::for('message_threads.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Threads', route('message_threads.index'));
});

Breadcrumbs::for('message_threads.show', function (BreadcrumbTrail $trail, $message_id): void {
    $trail->parent('message_threads.index');
    $trail->push('Thread Messages', route('message_threads.show', $message_id));
});

Breadcrumbs::for('message_threads.create', function (BreadcrumbTrail $trail): void {
    $trail->parent('message_threads.index');
    $trail->push('New Message', route('message_threads.create'));
});

// ----------------------------------------------------------------------
// Logs (tenant & central)
// ----------------------------------------------------------------------

Breadcrumbs::for('logs.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Manage Logs', route('logs.index'));
});

Breadcrumbs::for('central_logs.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('central_dashboard');
    $trail->push('Manage Logs', route('central_logs.index'));
});

// ----------------------------------------------------------------------
// Books
// ----------------------------------------------------------------------

Breadcrumbs::for('books.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Manage Books', route('books.index'));
});

Breadcrumbs::for('books.edit', function (BreadcrumbTrail $trail, $book_id): void {
    $trail->parent('books.index');
    $trail->push('Edit Book', route('books.edit', $book_id));
});

Breadcrumbs::for('books.show', function (BreadcrumbTrail $trail, $book_id): void {
    $trail->parent('books.index');
    $trail->push('Book Details', route('books.show', $book_id));
});

Breadcrumbs::for('book_requests.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Manage Book Requests', route('book_requests.index'));
});

Breadcrumbs::for('book_requests.edit', function (BreadcrumbTrail $trail, $book_req_id): void {
    $trail->parent('book_requests.index');
    $trail->push('Edit Request', route('book_requests.edit', $book_req_id));
});

// ----------------------------------------------------------------------
// Tenants
// ----------------------------------------------------------------------

Breadcrumbs::for('tenants.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Manage Tenants', route('tenants.index'));
});

Breadcrumbs::for('tenants.edit', function (BreadcrumbTrail $trail, $tenant_id): void {
    $trail->parent('tenants.index');
    $trail->push('Edit Tenant', route('tenants.edit', $tenant_id));
});

Breadcrumbs::for('tenants.show', function (BreadcrumbTrail $trail, $tenant_id): void {
    $trail->parent('tenants.index');
    $trail->push('View Tenant', route('tenants.show', $tenant_id));
});

Breadcrumbs::for('master_data_sync.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('tenants.index');
    $trail->push('Sync Master Data', route('master_data_sync.index'));
});

Breadcrumbs::for('tenant.impersonation', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Impersonate Tenant', route('tenant.impersonation'));
});

// ----------------------------------------------------------------------
// Notifications
// ----------------------------------------------------------------------

Breadcrumbs::for('notifications.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Manage Notifications', route('notifications.index'));
});

// ----------------------------------------------------------------------
// Tickets (tenant & central)
// ----------------------------------------------------------------------

Breadcrumbs::for('tickets.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Support Tickets', route('tickets.index'));
});

Breadcrumbs::for('tickets.index_central', function (BreadcrumbTrail $trail): void {
    $trail->parent('central_dashboard');
    $trail->push('Manage Tickets', route('tickets.index_central'));
});

Breadcrumbs::for('tickets.reply', function (BreadcrumbTrail $trail, $ticket_id): void {
    $trail->parent('tickets.index');
    $trail->push('Ticket Reply', route('tickets.reply', $ticket_id));
});

Breadcrumbs::for('tickets.answer', function (BreadcrumbTrail $trail, $ticket_id): void {
    $trail->parent('tickets.index_central');
    $trail->push('Ticket Answer', route('tickets.answer', $ticket_id));
});

// ----------------------------------------------------------------------
// Roles
// ----------------------------------------------------------------------

Breadcrumbs::for('roles.index', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Manage Roles', route('roles.index'));
});

Breadcrumbs::for('roles.edit', function (BreadcrumbTrail $trail, $role_id): void {
    $trail->parent('roles.index');
    $trail->push('Edit Role', route('roles.edit', $role_id));
});

// ----------------------------------------------------------------------
// Test (debugging)
// ----------------------------------------------------------------------

Breadcrumbs::for('test', function (BreadcrumbTrail $trail): void {
    $trail->parent(Usr::tenancyInitialized() ? 'home' : 'central_home');
    $trail->push('Test', route('test'));
});
