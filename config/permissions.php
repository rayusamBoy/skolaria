<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Method mapping
    |--------------------------------------------------------------------------
    | Map common controller verbs (lowercase method prefixes) to permission
    | verbs. You can tweak or add values here to change the generated names.
    */
    'method_map' => [
        'index' => 'view',
        'show' => 'show',
        'list' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'destroy' => 'delete',
        'delete' => 'delete',
    ],

    /*
    |--------------------------------------------------------------------------
    | Underscore handling
    |--------------------------------------------------------------------------
    | If true, underscores in method names become dots in permission suffixes
    | (e.g. update_record => update.record). If false, underscores are kept.
    */
    'use_dots' => false,

    /*
    |--------------------------------------------------------------------------
    | Permission naming overrides
    |--------------------------------------------------------------------------
    | Optional: explicit per-controller (short name) overrides. Keys are
    | controller class short names (e.g. "NoticeController") or resource names
    | (e.g. ("notices"). This lets you fix special cases without attributes.
    |
    | Example:
    | 'overrides' => [
    |    'NoticeController' => [
    |         'publish' => 'notices.publish',
    |    ],
    |    'notices' => [
    |         'approve' => 'notices.approve',
    |    ],
    | ],
    */
    'overrides' => [],

    /*
    |--------------------------------------------------------------------------
    | Ignore Controllers
    |--------------------------------------------------------------------------
    | List of controller class short names or full class names to be ignored
    | during permission generation.
    */
    'ignore_controllers' => [
        'HomeController',
        'CookieController',
        'AjaxController',
        'TestController',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Paths
    |--------------------------------------------------------------------------
    | List of controller paths to be ignored during permission generation.
    */
    'ignore_paths' => [
        'Controllers\Auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission names
    |--------------------------------------------------------------------------
    | Nested array of permission names. The first level keys are controller short names
    | or resource names, and the second level keys are method names.
    | Each entry should have a 'permission' key for the permission name,
    | and optionally 'label' and 'description' for human-readable information.
    |
    | You can use the GeneratePermissions artisan command to print out the scanned permission names,
    | then copy-paste and edit them here to add labels and descriptions.
    */
    'names' => [
        'tenant' => [
            'users' => [
                'destroy' => [
                    'permission' => 'users.delete',
                    'label' => 'Delete Users (including Students)',
                    'description' => 'Permanently remove user accounts.',
                ],
                'edit' => [
                    'permission' => 'users.update',
                    'label' => 'Edit Users',
                    'description' => 'Modify user details and settings.',
                ],
                'index' => [
                    'permission' => 'users.view',
                    'label' => 'View Users List',
                    'description' => 'Display a list of all users.',
                ],
                'show' => [
                    'permission' => 'users.show',
                    'label' => 'View User Details / Profile',
                    'description' => 'View detailed information about a specific user.',
                ],
                'store' => [
                    'permission' => 'users.create',
                    'label' => 'Create Users',
                    'description' => 'Add new user accounts.',
                ],
                'update' => [
                    'permission' => 'users.update',
                    'label' => 'Update Users',
                    'description' => 'Save changes to user accounts.',
                ],
                'reset_password' => [
                    'permission' => 'users.reset_password',
                    'label' => 'Reset User Password',
                    'description' => 'Reset a user\'s password.',
                ],
                'toggle_staff_data_editing' => [
                    'permission' => 'users.toggle.staff_data_editing',
                    'label' => 'Toggle Staff Data Editing',
                    'description' => 'Allow or prevent staff from editing certain data.',
                ],
                'toggle_user_block' => [
                    'permission' => 'users.update.user_blocked_state',
                    'label' => 'Block/Unblock Users',
                    'description' => 'Block or unblock user accounts.',
                ],
                'invite_user' => [
                    'permission' => 'users.invite',
                    'label' => 'Invite Users',
                    'description' => 'Generate registration invitation links.',
                ],
                'view_registrations' => [
                    'permission' => 'users.registrations',
                    'label' => 'View Registration Requests',
                    'description' => 'Review user registrations.',
                ],
                'approve' => [
                    'permission' => 'users.approve',
                    'label' => 'Approve User Registration Request',
                    'description' => 'Store the user with pending registration request.',
                ],
                'reject' => [
                    'permission' => 'users.reject',
                    'label' => 'Reject User Registration Request',
                    'description' => 'Reject the user with pending registration request.',
                ],
                'cancel' => [
                    'permission' => 'users.cancel',
                    'label' => 'Cancel User Registration Request',
                    'description' => 'Cancel the user with pending registration request.',
                ],
            ],

            'roles' => [
                'destroy' => [
                    'permission' => 'roles.delete',
                    'label' => 'Delete Roles',
                    'description' => 'Permanently remove roles.',
                ],
                'edit' => [
                    'permission' => 'roles.update',
                    'label' => 'Edit Roles',
                    'description' => 'Modify role details and permissions.',
                ],
                'index' => [
                    'permission' => 'roles.view',
                    'label' => 'View Roles List',
                    'description' => 'Display a list of all roles.',
                ],
                'store' => [
                    'permission' => 'roles.create',
                    'label' => 'Create Roles',
                    'description' => 'Add new roles.',
                ],
                'update' => [
                    'permission' => 'roles.update',
                    'label' => 'Update Roles',
                    'description' => 'Save changes to roles.',
                ],
            ],

            'analytics' => [
                'fetch_data' => [
                    'permission' => 'analytics.fetch_data',
                    'label' => 'Fetch Analytics Data',
                    'description' => 'Retrieve analytics data from external sources.',
                ],
                'google_setup' => [
                    'permission' => 'analytics.google_setup',
                    'label' => 'Configure Google Analytics',
                    'description' => 'Set up Google Analytics integration.',
                ],
                'index' => [
                    'permission' => 'analytics.view',
                    'label' => 'View Analytics',
                    'description' => 'View analytics reports.',
                ],
            ],

            'assessments' => [
                'sheets' => [
                    'permission' => 'assessments.sheets',
                    'label' => 'Manage Assessmentsheets',
                    'description' => 'Manage assessments sheets.',
                ],
                'sheets_select' => [
                    'permission' => 'assessments.sheets_select',
                    'label' => 'Select Assessmentsheets',
                    'description' => 'Select assessment sheets.',
                ],
                'comment_update' => [
                    'permission' => 'assessments.comment_update',
                    'label' => 'Update Assessment Comments',
                    'description' => 'Add or edit comments on assessments.',
                ],
                'index' => [
                    'permission' => 'assessments.view',
                    'label' => 'View Assessments List',
                    'description' => 'List all assessments.',
                ],
                'list' => [
                    'permission' => 'assessments.view',
                    'label' => 'View Assessments (Alternative)',
                    'description' => 'Alternate view of assessments list.',
                ],
                'manage' => [
                    'permission' => 'assessments.manage',
                    'label' => 'Manage Assessments',
                    'description' => 'Full management of assessments.',
                ],
                'print' => [
                    'permission' => 'assessments.print',
                    'label' => 'Print Assessments',
                    'description' => 'Print assessment reports.',
                ],
                'print_cover' => [
                    'permission' => 'assessments.print_cover',
                    'label' => 'Print Cover Sheet',
                    'description' => 'Print cover sheets for assessments.',
                ],
                'print_detailed' => [
                    'permission' => 'assessments.print_detailed',
                    'label' => 'Print Detailed Assessments',
                    'description' => 'Print detailed assessment reports.',
                ],
                'print_minimal' => [
                    'permission' => 'assessments.print_minimal',
                    'label' => 'Print Minimal Assessments',
                    'description' => 'Print minimal assessment summaries.',
                ],
                'print_progressive' => [
                    'permission' => 'assessments.print_progressive',
                    'label' => 'Print Progressive Assessments',
                    'description' => 'Print progressive assessment reports.',
                ],
                'progressive' => [
                    'permission' => 'assessments.progressive',
                    'label' => 'Progressive Assessments',
                    'description' => 'Access progressive assessment features.',
                ],
                'progressive_select' => [
                    'permission' => 'assessments.progressive_select',
                    'label' => 'Select Progressive Assessments',
                    'description' => 'Select progressive assessments for processing.',
                ],
                'selector' => [
                    'permission' => 'assessments.selector',
                    'label' => 'Assessment Selector',
                    'description' => 'Use the assessment selection tool.',
                ],
                'show' => [
                    'permission' => 'assessments.show',
                    'label' => 'View Assessment Details',
                    'description' => 'View detailed information of a single assessment.',
                ],
                'skills_update' => [
                    'permission' => 'assessments.skills_update',
                    'label' => 'Update Assessment Skills',
                    'description' => 'Modify skills associated with assessments.',
                ],
                'update' => [
                    'permission' => 'assessments.update',
                    'label' => 'Update Assessments',
                    'description' => 'Save changes to assessments.',
                ],
                'year_selector' => [
                    'permission' => 'assessments.year_selector',
                    'label' => 'View Year Selector',
                    'description' => 'Access the year selection form for assessments.',
                ],
            ],

            'book_requests' => [
                'destroy' => [
                    'permission' => 'book_requests.delete',
                    'label' => 'Delete Book Requests',
                    'description' => 'Remove book requests.',
                ],
                'edit' => [
                    'permission' => 'book_requests.update',
                    'label' => 'Edit Book Requests',
                    'description' => 'Modify book request details.',
                ],
                'index' => [
                    'permission' => 'book_requests.view',
                    'label' => 'View Book Requests',
                    'description' => 'List all book requests.',
                ],
                'store' => [
                    'permission' => 'book_requests.create',
                    'label' => 'Create Book Request',
                    'description' => 'Submit a new book request.',
                ],
                'update' => [
                    'permission' => 'book_requests.update',
                    'label' => 'Update Book Requests',
                    'description' => 'Save changes to book requests.',
                ],
            ],

            'books' => [
                'destroy' => [
                    'permission' => 'books.delete',
                    'label' => 'Delete Books',
                    'description' => 'Remove books from the system.',
                ],
                'edit' => [
                    'permission' => 'books.update',
                    'label' => 'Edit Books',
                    'description' => 'Modify book details.',
                ],
                'index' => [
                    'permission' => 'books.view',
                    'label' => 'View Books',
                    'description' => 'List all books.',
                ],
                'store' => [
                    'permission' => 'books.create',
                    'label' => 'Create Book',
                    'description' => 'Add a new book.',
                ],
                'update' => [
                    'permission' => 'books.update',
                    'label' => 'Update Books',
                    'description' => 'Save changes to books.',
                ],
            ],

            'divisions' => [
                'destroy' => [
                    'permission' => 'divisions.delete',
                    'label' => 'Delete Divisions',
                    'description' => 'Remove divisions.',
                ],
                'edit' => [
                    'permission' => 'divisions.update',
                    'label' => 'Edit Divisions',
                    'description' => 'Modify division details.',
                ],
                'index' => [
                    'permission' => 'divisions.view',
                    'label' => 'View Divisions',
                    'description' => 'List all divisions.',
                ],
                'store' => [
                    'permission' => 'divisions.create',
                    'label' => 'Create Division',
                    'description' => 'Add a new division.',
                ],
                'update' => [
                    'permission' => 'divisions.update',
                    'label' => 'Update Divisions',
                    'description' => 'Save changes to divisions.',
                ],
            ],

            'dorms' => [
                'destroy' => [
                    'permission' => 'dorms.delete',
                    'label' => 'Delete Dorms',
                    'description' => 'Remove dormitories.',
                ],
                'edit' => [
                    'permission' => 'dorms.update',
                    'label' => 'Edit Dorms',
                    'description' => 'Modify dormitory details.',
                ],
                'index' => [
                    'permission' => 'dorms.view',
                    'label' => 'View Dorms',
                    'description' => 'List all dormitories.',
                ],
                'store' => [
                    'permission' => 'dorms.create',
                    'label' => 'Create Dorm',
                    'description' => 'Add a new dormitory.',
                ],
                'update' => [
                    'permission' => 'dorms.update',
                    'label' => 'Update Dorms',
                    'description' => 'Save changes to dormitories.',
                ],
            ],

            'exams' => [
                'announce' => [
                    'permission' => 'exams.announce',
                    'label' => 'Announce Exams',
                    'description' => 'Publish exam announcements.',
                ],
                'destroy' => [
                    'permission' => 'exams.delete',
                    'label' => 'Delete Exams',
                    'description' => 'Remove exams.',
                ],
                'edit' => [
                    'permission' => 'exams.update',
                    'label' => 'Edit Exams',
                    'description' => 'Modify exam details.',
                ],
                'index' => [
                    'permission' => 'exams.view',
                    'label' => 'View Exams List',
                    'description' => 'List all exams.',
                ],
                'publish' => [
                    'permission' => 'exams.publish',
                    'label' => 'Publish Exams',
                    'description' => 'Make exams publicly available.',
                ],
                'store' => [
                    'permission' => 'exams.create',
                    'label' => 'Create Exam',
                    'description' => 'Add a new exam.',
                ],
                'update' => [
                    'permission' => 'exams.update',
                    'label' => 'Update Exams',
                    'description' => 'Save changes to exams.',
                ],
                'update_lock_state' => [
                    'permission' => 'exams.update.lock_state',
                    'label' => 'Lock/Unlock Exams',
                    'description' => 'Lock or unlock exams for changes.',
                ],
            ],

            'grades' => [
                'destroy' => [
                    'permission' => 'grades.delete',
                    'label' => 'Delete Grades',
                    'description' => 'Remove grade records.',
                ],
                'edit' => [
                    'permission' => 'grades.update',
                    'label' => 'Edit Grades',
                    'description' => 'Modify grade details.',
                ],
                'index' => [
                    'permission' => 'grades.view',
                    'label' => 'View Grades',
                    'description' => 'List all grades.',
                ],
                'store' => [
                    'permission' => 'grades.create',
                    'label' => 'Create Grade',
                    'description' => 'Add a new grade.',
                ],
                'update' => [
                    'permission' => 'grades.update',
                    'label' => 'Update Grades',
                    'description' => 'Save changes to grades.',
                ],
            ],

            'marks' => [
                'batch' => [
                    'permission' => 'marks.batch',
                    'label' => 'Batch Marks Operations',
                    'description' => 'Perform batch operations on marks.',
                ],
                'batch_delete' => [
                    'permission' => 'marks.batch_delete',
                    'label' => 'Batch Delete Marks',
                    'description' => 'Delete marks in bulk.',
                ],
                'batch_template' => [
                    'permission' => 'marks.batch_template',
                    'label' => 'Batch Template for Marks',
                    'description' => 'Download or upload batch templates for marks.',
                ],
                'batch_update' => [
                    'permission' => 'marks.batch_update',
                    'label' => 'Batch Update Marks',
                    'description' => 'Update marks in bulk.',
                ],
                'batch_upload' => [
                    'permission' => 'marks.batch_upload',
                    'label' => 'Batch Upload Marks',
                    'description' => 'Upload marks via batch file.',
                ],
                'sheets' => [
                    'permission' => 'marks.sheets',
                    'label' => 'Select Student Marksheets',
                    'description' => 'View student marks sheets.',
                ],
                'sheets_select' => [
                    'permission' => 'marks.sheets_select',
                    'label' => 'Select Marks Sheets',
                    'description' => 'Select student marksheets.',
                ],
                'comment_update' => [
                    'permission' => 'marks.comment_update',
                    'label' => 'Update Marks Comments',
                    'description' => 'Add or edit comments on marks.',
                ],
                'manage' => [
                    'permission' => 'marks.manage',
                    'label' => 'Manage Marks',
                    'description' => 'Full management of marks.',
                ],
                'print_tabulation' => [
                    'permission' => 'marks.print_tabulation',
                    'label' => 'Print Tabulation Sheets',
                    'description' => 'Print tabulation sheets for marks.',
                ],
                'print_view' => [
                    'permission' => 'marks.print_view',
                    'label' => 'Print Marks View',
                    'description' => 'Print a formatted view of marks.',
                ],
                'selector' => [
                    'permission' => 'marks.selector',
                    'label' => 'Marksheets Selector',
                    'description' => 'Fill students marksheets selection form.',
                ],
                'show' => [
                    'permission' => 'marks.show',
                    'label' => 'View Marksheet(s)',
                    'description' => 'View detailed information of a marksheet(s).',
                ],
                'skills_update' => [
                    'permission' => 'marks.skills_update',
                    'label' => 'Update Marks Skills',
                    'description' => 'Modify skills associated with marks.',
                ],
                'tabulation' => [
                    'permission' => 'marks.tabulation',
                    'label' => 'Tabulation',
                    'description' => 'Access tabulation features for marks.',
                ],
                'tabulation_select' => [
                    'permission' => 'marks.tabulation_select',
                    'label' => 'Select Tabulation',
                    'description' => 'Select marks for tabulation.',
                ],
                'update' => [
                    'permission' => 'marks.update',
                    'label' => 'Update Marks',
                    'description' => 'Save changes to marks.',
                ],
                'year_selector' => [
                    'permission' => 'marks.year_selector',
                    'label' => 'Marks Exam Year Selector',
                    'description' => 'Access the year selection form for marks.',
                ],
            ],

            'message_threads' => [
                'activate_all_participants' => [
                    'permission' => 'message_threads.activate_all_participants',
                    'label' => 'Activate All Participants',
                    'description' => 'Activate all participants in a message thread.',
                ],
                'create' => [
                    'permission' => 'message_threads.create',
                    'label' => 'Create New Messages Thread',
                    'description' => 'Store a newly created message thread.',
                ],
                'destroy' => [
                    'permission' => 'message_threads.delete',
                    'label' => 'Delete Messages Thread',
                    'description' => 'Remove message threads.',
                ],
                'index' => [
                    'permission' => 'message_threads.view',
                    'label' => 'View Messages Threads',
                    'description' => 'View all message threads.',
                ],
                'remove_participant' => [
                    'permission' => 'message_threads.remove_participant',
                    'label' => 'Remove Participant',
                    'description' => 'Remove a participant from a conversation.',
                ],
                'show' => [
                    'permission' => 'message_threads.show',
                    'label' => 'View Message Thread',
                    'description' => 'View a specific message thread.',
                ],
                'store' => [
                    'permission' => 'message_threads.create',
                    'label' => 'Create New Messages Thread',
                    'description' => 'Store a newly created message thread.',
                ],
                'add_message' => [
                    'permission' => 'message_threads.add_message',
                    'label' => 'Add New Message',
                    'description' => 'Add a new message to an existing thread.',
                ],
            ],

            'my_accounts' => [
                'change_pass' => [
                    'permission' => 'my_accounts.change_pass',
                    'label' => 'Change Password',
                    'description' => 'Change own account password.',
                ],
                'clear_hidden_alerts' => [
                    'permission' => 'my_accounts.clear_hidden_alerts',
                    'label' => 'Clear Hidden Alerts',
                    'description' => 'Clear alerts that have been hidden.',
                ],
                'edit_profile' => [
                    'permission' => 'my_accounts.update.profile',
                    'label' => 'Edit Profile',
                    'description' => 'Edit own profile information.',
                ],
                'update_preferences' => [
                    'permission' => 'my_accounts.update.preferences',
                    'label' => 'Update Preferences',
                    'description' => 'Change account preferences.',
                ],
                'update_profile' => [
                    'permission' => 'my_accounts.update.profile',
                    'label' => 'Update Profile',
                    'description' => 'Save profile changes.',
                ],
            ],

            'my_classes' => [
                'destroy' => [
                    'permission' => 'my_classes.delete',
                    'label' => 'Delete My Classes',
                    'description' => 'Remove classes from personal list.',
                ],
                'edit' => [
                    'permission' => 'my_classes.update',
                    'label' => 'Edit My Classes',
                    'description' => 'Modify personal class details.',
                ],
                'index' => [
                    'permission' => 'my_classes.view',
                    'label' => 'View My Classes',
                    'description' => 'List personal classes.',
                ],
                'store' => [
                    'permission' => 'my_classes.create',
                    'label' => 'Add My Classes',
                    'description' => 'Add classes to personal list.',
                ],
                'update' => [
                    'permission' => 'my_classes.update',
                    'label' => 'Update My Classes',
                    'description' => 'Save changes to personal classes.',
                ],
            ],

            'notices' => [
                'destroy' => [
                    'permission' => 'notices.delete',
                    'label' => 'Delete Notices',
                    'description' => 'Remove notices.',
                ],
                'edit' => [
                    'permission' => 'notices.update',
                    'label' => 'Edit Notices',
                    'description' => 'Modify notice content.',
                ],
                'index' => [
                    'permission' => 'notices.view',
                    'label' => 'Manage Notices',
                    'description' => 'Manage own/all notices.',
                ],
                'store' => [
                    'permission' => 'notices.create',
                    'label' => 'Create Notice',
                    'description' => 'Add a new notice.',
                ],
                'update_record' => [
                    'permission' => 'notices.update.record',
                    'label' => 'Update Notice Record',
                    'description' => 'Update specific notice record.',
                ],
            ],

            'notifications' => [
                'destroy' => [
                    'permission' => 'notifications.delete',
                    'label' => 'Delete Notifications',
                    'description' => 'Remove notifications.',
                ],
                'index' => [
                    'permission' => 'notifications.view',
                    'label' => 'View Notifications',
                    'description' => 'List all notifications.',
                ],
                'mark_all_read' => [
                    'permission' => 'notifications.mark_all_read',
                    'label' => 'Mark All Read',
                    'description' => 'Mark all notifications as read.',
                ],
                'mark_as_read' => [
                    'permission' => 'notifications.mark_as_read',
                    'label' => 'Mark Notification Read',
                    'description' => 'Mark a single notification as read.',
                ],
                'update_notification_preferences' => [
                    'permission' => 'notifications.update.notification_preferences',
                    'label' => 'Update Notifiable Status',
                    'description' => 'Change whether user receives notifications.',
                ],
            ],

            'payments' => [
                'create' => [
                    'permission' => 'payments.create',
                    'label' => 'Create Payments',
                    'description' => 'Create new payment records.',
                ],
                'destroy' => [
                    'permission' => 'payments.delete',
                    'label' => 'Delete Payments',
                    'description' => 'Remove payment records.',
                ],
                'edit' => [
                    'permission' => 'payments.update',
                    'label' => 'Edit Payments',
                    'description' => 'Modify payment details.',
                ],
                'index' => [
                    'permission' => 'payments.view',
                    'label' => 'View Payments',
                    'description' => 'List all payments.',
                ],
                'invoice' => [
                    'permission' => 'payments.invoice',
                    'label' => 'View Invoices',
                    'description' => 'View payment invoices.',
                ],
                'manage' => [
                    'permission' => 'payments.manage',
                    'label' => 'Manage Payments',
                    'description' => 'Full management of payments.',
                ],
                'pay_now' => [
                    'permission' => 'payments.pay_now',
                    'label' => 'Pay Now',
                    'description' => 'Process immediate payment.',
                ],
                'receipts' => [
                    'permission' => 'payments.receipts',
                    'label' => 'View Receipts',
                    'description' => 'View payment receipts.',
                ],
                'reset_record' => [
                    'permission' => 'payments.reset_record',
                    'label' => 'Reset Payment Record',
                    'description' => 'Reset a payment record.',
                ],
                'select_class' => [
                    'permission' => 'payments.select_class',
                    'label' => 'Select Class for Payment',
                    'description' => 'Choose a class for payment operations.',
                ],
                'select_year' => [
                    'permission' => 'payments.select_year',
                    'label' => 'Select Year for Payment',
                    'description' => 'Choose an academic year for payments.',
                ],
                'show' => [
                    'permission' => 'payments.show',
                    'label' => 'View Payment Details',
                    'description' => 'View detailed payment information.',
                ],
                'status' => [
                    'permission' => 'payments.status',
                    'label' => 'View Payment Status',
                    'description' => 'Check payment status.',
                ],
                'store' => [
                    'permission' => 'payments.create',
                    'label' => 'Store Payment',
                    'description' => 'Save payment record.',
                ],
                'update' => [
                    'permission' => 'payments.update',
                    'label' => 'Update Payments',
                    'description' => 'Save changes to payments.',
                ],
            ],

            'pins' => [
                'create' => [
                    'permission' => 'pins.create',
                    'label' => 'Create Pins',
                    'description' => 'Generate new pins.',
                ],
                'destroy' => [
                    'permission' => 'pins.delete',
                    'label' => 'Delete Pins',
                    'description' => 'Remove pins.',
                ],
                'enter_pin' => [
                    'permission' => 'pins.enter_pin',
                    'label' => 'Enter PIN for Results View',
                    'description' => 'Submit a PIN for verification to view results.',
                ],
                'index' => [
                    'permission' => 'pins.view',
                    'label' => 'View Pins',
                    'description' => 'List all pins.',
                ],
                'store' => [
                    'permission' => 'pins.create',
                    'label' => 'Store Pins',
                    'description' => 'Save new pins.',
                ],
            ],

            'promotions' => [
                'manage' => [
                    'permission' => 'promotions.manage',
                    'label' => 'Manage Promotions',
                    'description' => 'Full management of student promotions.',
                ],
                'promote' => [
                    'permission' => 'promotions.promote',
                    'label' => 'Promote Students',
                    'description' => 'Promote students to next level.',
                ],
                'promotion' => [
                    'permission' => 'promotions.promotion',
                    'label' => 'Promotion Process',
                    'description' => 'Access promotion features.',
                ],
                'remarks' => [
                    'permission' => 'promotions.remarks',
                    'label' => 'Add Promotion Remarks',
                    'description' => 'Add remarks during promotion.',
                ],
                'reset' => [
                    'permission' => 'promotions.reset',
                    'label' => 'Reset Promotion',
                    'description' => 'Reset promotion status.',
                ],
                'reset_all' => [
                    'permission' => 'promotions.reset_all',
                    'label' => 'Reset All Promotions',
                    'description' => 'Reset all promotion records.',
                ],
                'selector' => [
                    'permission' => 'promotions.selector',
                    'label' => 'Promotion Selector',
                    'description' => 'Select students for promotion.',
                ],
            ],

            'query_builders' => [
                'index' => [
                    'permission' => 'query_builders.view',
                    'label' => 'View Query Builders',
                    'description' => 'List query builder tools.',
                ],
                'print_users_data' => [
                    'permission' => 'query_builders.print_users_data',
                    'label' => 'Print Users Data',
                    'description' => 'Print user data reports.',
                ],
                'select' => [
                    'permission' => 'query_builders.select',
                    'label' => 'Select Query',
                    'description' => 'Use query builder selection.',
                ],
            ],

            'schedules' => [
                'create_event' => [
                    'permission' => 'schedules.create.event',
                    'label' => 'Create Event',
                    'description' => 'Add new schedule events.',
                ],
                'delete_event' => [
                    'permission' => 'schedules.delete.event',
                    'label' => 'Delete Event',
                    'description' => 'Remove schedule events.',
                ],
                'edit_event' => [
                    'permission' => 'schedules.update.event',
                    'label' => 'Edit Event',
                    'description' => 'Modify event details.',
                ],
                'index' => [
                    'permission' => 'schedules.view',
                    'label' => 'View Schedules',
                    'description' => 'List all schedules.',
                ],
                'update_event' => [
                    'permission' => 'schedules.update.event',
                    'label' => 'Update Event',
                    'description' => 'Save changes to events.',
                ],
            ],

            'sections' => [
                'destroy' => [
                    'permission' => 'sections.delete',
                    'label' => 'Delete Sections',
                    'description' => 'Remove sections.',
                ],
                'edit' => [
                    'permission' => 'sections.update',
                    'label' => 'Edit Sections',
                    'description' => 'Modify section details.',
                ],
                'index' => [
                    'permission' => 'sections.view',
                    'label' => 'View Sections',
                    'description' => 'List all sections.',
                ],
                'store' => [
                    'permission' => 'sections.create',
                    'label' => 'Create Section',
                    'description' => 'Add a new section.',
                ],
                'update' => [
                    'permission' => 'sections.update',
                    'label' => 'Update Sections',
                    'description' => 'Save changes to sections.',
                ],
            ],

            'class_types' => [
                'destroy' => [
                    'permission' => 'class_types.delete',
                    'label' => 'Delete Class Types',
                    'description' => 'Remove class types.',
                ],
                'edit' => [
                    'permission' => 'class_types.update',
                    'label' => 'Edit Class Types',
                    'description' => 'Modify class type details.',
                ],
                'index' => [
                    'permission' => 'class_types.view',
                    'label' => 'View Class Types',
                    'description' => 'List all class types.',
                ],
                'store' => [
                    'permission' => 'class_types.create',
                    'label' => 'Create Class Type',
                    'description' => 'Add a new class type.',
                ],
                'update' => [
                    'permission' => 'class_types.update',
                    'label' => 'Update Class Types',
                    'description' => 'Save changes to class types.',
                ],
            ],

            'settings' => [
                'disable_analytics' => [
                    'permission' => 'settings.disable_analytics',
                    'label' => 'Disable Analytics',
                    'description' => 'Turn off analytics tracking.',
                ],
                'enable_analytics' => [
                    'permission' => 'settings.enable_analytics',
                    'label' => 'Enable Analytics',
                    'description' => 'Turn on analytics tracking.',
                ],
                'index' => [
                    'permission' => 'settings.view',
                    'label' => 'View Settings',
                    'description' => 'View system settings.',
                ],
                'update' => [
                    'permission' => 'settings.update',
                    'label' => 'Update Settings',
                    'description' => 'Save changes to settings.',
                ],
            ],

            'soft_deletes' => [
                'bin' => [
                    'permission' => 'soft_deletes.bin',
                    'label' => 'View Soft Delete Bin',
                    'description' => 'View deleted items.',
                ],
                'empty_soft_deleted_model' => [
                    'permission' => 'soft_deletes.empty_soft_deleted_model',
                    'label' => 'Empty Soft Delete Bin',
                    'description' => 'Permanently remove all soft-deleted items.',
                ],
                'exam_force_delete' => [
                    'permission' => 'soft_deletes.exam_force_delete',
                    'label' => 'Force Delete Exam',
                    'description' => 'Permanently delete a soft-deleted exam.',
                ],
                'exam_restore' => [
                    'permission' => 'soft_deletes.exam_restore',
                    'label' => 'Restore Exam',
                    'description' => 'Restore a soft-deleted exam.',
                ],
                'my_class_force_delete' => [
                    'permission' => 'soft_deletes.my_class_force_delete',
                    'label' => 'Force Delete My Class',
                    'description' => 'Permanently delete a soft-deleted class.',
                ],
                'my_class_restore' => [
                    'permission' => 'soft_deletes.my_class_restore',
                    'label' => 'Restore My Class',
                    'description' => 'Restore a soft-deleted class.',
                ],
                'payment_force_delete' => [
                    'permission' => 'soft_deletes.payment_force_delete',
                    'label' => 'Force Delete Payment',
                    'description' => 'Permanently delete a soft-deleted payment.',
                ],
                'payment_restore' => [
                    'permission' => 'soft_deletes.payment_restore',
                    'label' => 'Restore Payment',
                    'description' => 'Restore a soft-deleted payment.',
                ],
                'thread_force_delete' => [
                    'permission' => 'soft_deletes.thread_force_delete',
                    'label' => 'Force Delete Thread',
                    'description' => 'Permanently delete a soft-deleted thread.',
                ],
                'thread_restore' => [
                    'permission' => 'soft_deletes.thread_restore',
                    'label' => 'Restore Thread',
                    'description' => 'Restore a soft-deleted thread.',
                ],
                'user_force_delete' => [
                    'permission' => 'soft_deletes.user_force_delete',
                    'label' => 'Force Delete User',
                    'description' => 'Permanently delete a soft-deleted user.',
                ],
                'user_restore' => [
                    'permission' => 'soft_deletes.user_restore',
                    'label' => 'Restore User',
                    'description' => 'Restore a soft-deleted user.',
                ],
            ],

            'statistics' => [
                'index' => [
                    'permission' => 'statistics.view',
                    'label' => 'View Statistics',
                    'description' => 'View statistical reports.',
                ],
            ],

            'student_records' => [
                'batch_store' => [
                    'permission' => 'student_records.batch_store',
                    'label' => 'Batch Store Students',
                    'description' => 'Add multiple student records at once.',
                ],
                'batch_template' => [
                    'permission' => 'student_records.batch_template',
                    'label' => 'Batch Template',
                    'description' => 'Download or upload batch templates for students.',
                ],
                'block_all_class' => [
                    'permission' => 'student_records.block_all_class',
                    'label' => 'Block All Students in Class',
                    'description' => 'Block all students in a specific class.',
                ],
                'block_all_graduated' => [
                    'permission' => 'student_records.block_all_graduated',
                    'label' => 'Block All Graduated Students',
                    'description' => 'Block all graduated students.',
                ],
                'create' => [
                    'permission' => 'student_records.create',
                    'label' => 'Create Student Record',
                    'description' => 'Add a new student record.',
                ],
                'edit' => [
                    'permission' => 'student_records.update',
                    'label' => 'Edit Student Record',
                    'description' => 'Modify student details.',
                ],
                'graduated' => [
                    'permission' => 'student_records.graduated',
                    'label' => 'Mark as Graduated',
                    'description' => 'Mark students as graduated.',
                ],
                'mark_not_graduated' => [
                    'permission' => 'student_records.mark_not_graduated',
                    'label' => 'Mark as Not Graduated',
                    'description' => 'Remove graduation status.',
                ],
                'list_by_class' => [
                    'permission' => 'student_records.view.by_class',
                    'label' => 'View Students by Class',
                    'description' => 'List students filtered by class.',
                ],
                'reset_password' => [
                    'permission' => 'student_records.reset_password',
                    'label' => 'Reset Student Password',
                    'description' => 'Reset a student\'s password.',
                ],
                'show' => [
                    'permission' => 'student_records.show',
                    'label' => 'View Student Details',
                    'description' => 'View detailed student information.',
                ],
                'store' => [
                    'permission' => 'student_records.create',
                    'label' => 'Store Student Record',
                    'description' => 'Save new student record.',
                ],
                'unblock_all_class' => [
                    'permission' => 'student_records.unblock_all_class',
                    'label' => 'Unblock All Students in Class',
                    'description' => 'Unblock all students in a class.',
                ],
                'unblock_all_graduated' => [
                    'permission' => 'student_records.unblock_all_graduated',
                    'label' => 'Unblock All Graduated Students',
                    'description' => 'Unblock all graduated students.',
                ],
                'update' => [
                    'permission' => 'student_records.update',
                    'label' => 'Update Student Record',
                    'description' => 'Save changes to student records.',
                ],
            ],

            'student_id_cards' => [
                'index' => [
                    'permission' => 'student_id_cards.view',
                    'label' => 'View ID Cards',
                    'description' => 'List student ID cards.',
                ],
                'manage' => [
                    'permission' => 'student_id_cards.manage',
                    'label' => 'Manage ID Cards',
                    'description' => 'Full management of student ID cards.',
                ],
                'print_class' => [
                    'permission' => 'student_id_cards.print_class',
                    'label' => 'Print Class ID Cards',
                    'description' => 'Print ID cards for a whole class.',
                ],
                'print_selected' => [
                    'permission' => 'student_id_cards.print_selected',
                    'label' => 'Print Selected ID Cards',
                    'description' => 'Print ID cards for selected students.',
                ],
            ],

            'subjects' => [
                'destroy' => [
                    'permission' => 'subjects.delete',
                    'label' => 'Delete Subjects',
                    'description' => 'Remove subjects.',
                ],
                'delete_record' => [
                    'permission' => 'subjects.delete.record',
                    'label' => 'Delete Subject Record',
                    'description' => 'Delete a specific subject record.',
                ],
                'edit' => [
                    'permission' => 'subjects.update',
                    'label' => 'Edit Subjects',
                    'description' => 'Modify subject details.',
                ],
                'edit_record' => [
                    'permission' => 'subjects.update.record',
                    'label' => 'Edit Subject Record',
                    'description' => 'Edit a specific subject record.',
                ],
                'index' => [
                    'permission' => 'subjects.view',
                    'label' => 'View Subjects',
                    'description' => 'List all subjects.',
                ],
                'index_record' => [
                    'permission' => 'subjects.view.record',
                    'label' => 'View Subject Records',
                    'description' => 'List subject records.',
                ],
                'store' => [
                    'permission' => 'subjects.create',
                    'label' => 'Create Subject',
                    'description' => 'Add a new subject.',
                ],
                'store_record' => [
                    'permission' => 'subjects.create.record',
                    'label' => 'Create Subject Record',
                    'description' => 'Add a new subject record.',
                ],
                'update' => [
                    'permission' => 'subjects.update',
                    'label' => 'Update Subjects',
                    'description' => 'Save changes to subjects.',
                ],
                'update_record' => [
                    'permission' => 'subjects.update.record',
                    'label' => 'Update Subject Record',
                    'description' => 'Save changes to a subject record.',
                ],
            ],

            'tickets' => [
                'answer' => [
                    'permission' => 'tickets.answer',
                    'label' => 'Answer Tickets',
                    'description' => 'Provide answers to tickets.',
                ],
                'archive_ticket' => [
                    'permission' => 'tickets.archive_ticket',
                    'label' => 'Archive Ticket',
                    'description' => 'Archive a ticket.',
                ],
                'close_ticket' => [
                    'permission' => 'tickets.close_ticket',
                    'label' => 'Close Ticket',
                    'description' => 'Mark a ticket as closed.',
                ],
                'index' => [
                    'permission' => 'tickets.view',
                    'label' => 'View Tickets',
                    'description' => 'List all tickets.',
                ],
                'reply' => [
                    'permission' => 'tickets.reply',
                    'label' => 'Reply to Ticket',
                    'description' => 'Reply to a ticket.',
                ],
                'store' => [
                    'permission' => 'tickets.create',
                    'label' => 'Create Ticket',
                    'description' => 'Submit a new ticket.',
                ],
                'store_message' => [
                    'permission' => 'tickets.create.message',
                    'label' => 'Store Ticket Message',
                    'description' => 'Add a message to a ticket.',
                ],
                'unarchive_ticket' => [
                    'permission' => 'tickets.unarchive_ticket',
                    'label' => 'Unarchive Ticket',
                    'description' => 'Restore an archived ticket.',
                ],
            ],

            'time_tables' => [
                'delete' => [
                    'permission' => 'time_tables.delete',
                    'label' => 'Delete Timetable',
                    'description' => 'Remove a timetable.',
                ],
                'delete_record' => [
                    'permission' => 'time_tables.delete.record',
                    'label' => 'Delete Timetable Record',
                    'description' => 'Delete a specific timetable record.',
                ],
                'delete_time_slot' => [
                    'permission' => 'time_tables.delete.time_slot',
                    'label' => 'Delete Time Slot',
                    'description' => 'Remove a time slot from timetable.',
                ],
                'edit_record' => [
                    'permission' => 'time_tables.update.record',
                    'label' => 'Edit Timetable Record',
                    'description' => 'Modify a timetable record.',
                ],
                'edit_time_slot' => [
                    'permission' => 'time_tables.update.time_slot',
                    'label' => 'Edit Time Slot',
                    'description' => 'Modify a time slot.',
                ],
                'index' => [
                    'permission' => 'time_tables.view',
                    'label' => 'View Timetables',
                    'description' => 'List all timetables.',
                ],
                'manage_record' => [
                    'permission' => 'time_tables.manage_record',
                    'label' => 'Manage Timetable Record',
                    'description' => 'Full management of timetable records.',
                ],
                'print_record' => [
                    'permission' => 'time_tables.print_record',
                    'label' => 'Print Timetable Record',
                    'description' => 'Print a timetable record.',
                ],
                'show_record' => [
                    'permission' => 'time_tables.view.record',
                    'label' => 'View Timetable Record',
                    'description' => 'View a specific timetable record.',
                ],
                'store' => [
                    'permission' => 'time_tables.create',
                    'label' => 'Create Timetable',
                    'description' => 'Add a new timetable.',
                ],
                'store_record' => [
                    'permission' => 'time_tables.create.record',
                    'label' => 'Create Timetable Record',
                    'description' => 'Add a new timetable record.',
                ],
                'store_time_slot' => [
                    'permission' => 'time_tables.create.time_slot',
                    'label' => 'Create Time Slot',
                    'description' => 'Add a new time slot.',
                ],
                'update' => [
                    'permission' => 'time_tables.update',
                    'label' => 'Update Timetable',
                    'description' => 'Save changes to a timetable.',
                ],
                'update_record' => [
                    'permission' => 'time_tables.update.record',
                    'label' => 'Update Timetable Record',
                    'description' => 'Save changes to a timetable record.',
                ],
                'update_time_slot' => [
                    'permission' => 'time_tables.update.time_slot',
                    'label' => 'Update Time Slot',
                    'description' => 'Save changes to a time slot.',
                ],
                'use_time_slot' => [
                    'permission' => 'time_tables.use_time_slot',
                    'label' => 'Use Time Slot',
                    'description' => 'Assign a time slot to a timetable.',
                ],
            ],

            'logs' => [
                'clean_activity_log' => [
                    'permission' => 'logs.clean_activity_log',
                    'label' => 'Clean Activity Log',
                    'description' => 'Remove old activity log entries.',
                ],
                'delete_activity' => [
                    'permission' => 'logs.delete.activity',
                    'label' => 'Delete Activity Log Entry',
                    'description' => 'Delete a specific activity log entry.',
                ],
                'index' => [
                    'permission' => 'logs.view',
                    'label' => 'View Logs',
                    'description' => 'List all logs.',
                ],
            ],
            'dashboard_statistics' => [
                'active_students' => [
                    'permission' => 'dashboard.stats.active_students',
                    'label' => 'View Active Students statistic',
                    'description' => 'Shows the Active Students card with male/female breakdown.',
                ],
                'graduated_students' => [
                    'permission' => 'dashboard.stats.graduated_students',
                    'label' => 'View Graduated Students statistic',
                    'description' => 'Shows the Graduated Students card.',
                ],
                'overall_students' => [
                    'permission' => 'dashboard.stats.overall_students',
                    'label' => 'View Overall Students statistic',
                    'description' => 'Shows the Overall Students card with sparkline and gender counts.',
                ],
                'administrators' => [
                    'permission' => 'dashboard.stats.administrators',
                    'label' => 'View Administrators statistic',
                    'description' => 'Shows the Total Administrators card.',
                ],
                'teachers' => [
                    'permission' => 'dashboard.stats.teachers',
                    'label' => 'View Teachers statistic',
                    'description' => 'Shows the Total Teachers card.',
                ],
                'accountants_librarians' => [
                    'permission' => 'dashboard.stats.accountants_librarians',
                    'label' => 'View Accountants & Librarians statistic',
                    'description' => 'Shows the combined Accountants and Librarians card.',
                ],
                'assessments_declared' => [
                    'permission' => 'dashboard.stats.assessments_declared',
                    'label' => 'View Assessments Declared statistic',
                    'description' => 'Shows the number of declared assessments.',
                ],
                'parents' => [
                    'permission' => 'dashboard.stats.parents',
                    'label' => 'View Parents statistic',
                    'description' => 'Shows the Total Parents card.',
                ],
                'subjects' => [
                    'permission' => 'dashboard.stats.subjects',
                    'label' => 'View Subjects statistic',
                    'description' => 'Shows the Total Subjects card.',
                ],
                'classes' => [
                    'permission' => 'dashboard.stats.classes',
                    'label' => 'View Classes statistic',
                    'description' => 'Shows the Total Classes card.',
                ],
                'results_declared' => [
                    'permission' => 'dashboard.stats.results_declared',
                    'label' => 'View Results Declared statistic',
                    'description' => 'Shows the number of published exam results.',
                ],
                'payments_done' => [
                    'permission' => 'dashboard.stats.payments_done',
                    'label' => 'View Payments Done statistic',
                    'description' => 'Shows the Total Payments Done card.',
                ],
                'books' => [
                    'permission' => 'dashboard.stats.books',
                    'label' => 'View Books statistic',
                    'description' => 'Shows the Total Books card.',
                ],
                'book_requests' => [
                    'permission' => 'dashboard.stats.book_requests',
                    'label' => 'View Book Requests statistic',
                    'description' => 'Shows the Book Requests card with returned/out breakdown.',
                ],
                'gender_chart' => [
                    'permission' => 'dashboard.charts.gender',
                    'label' => 'View Gender Distribution chart',
                    'description' => 'Access to the Gender Distribution chart.',
                ],
                'payments_chart' => [
                    'permission' => 'dashboard.charts.payments',
                    'label' => 'View Payments (12 months) chart',
                    'description' => 'Access to the Payments trend chart.',
                ],
                'students_per_class_chart' => [
                    'permission' => 'dashboard.charts.students_per_class',
                    'label' => 'View Students per Class chart',
                    'description' => 'Access to the Students per Class chart.',
                ],
                'student_breakdown_modal' => [
                    'permission' => 'dashboard.modal.students_breakdown',
                    'label' => 'View Student number breakdown modal',
                    'description' => 'Allows opening the detailed student breakdown modal.',
                ],
            ],
            'system' => [
                'view_warnings' => [
                    'permission' => 'system.view_warnings',
                    'label' => 'View system warnings',
                    'description' => 'Shows session/year mismatch warnings.',
                ],
            ],
        ],

        // ----------------------------------------------------------------------
        // Central permissions
        // ----------------------------------------------------------------------
        'central' => [
            'central_users' => [
                'destroy' => [
                    'permission' => 'central_users.delete',
                    'label' => 'Delete Central Users',
                    'description' => 'Remove central user accounts.',
                ],
                'edit' => [
                    'permission' => 'central_users.update',
                    'label' => 'Edit Central Users',
                    'description' => 'Modify central user details.',
                ],
                'index' => [
                    'permission' => 'central_users.view',
                    'label' => 'View Central Users',
                    'description' => 'List all central users.',
                ],
                'reset_password' => [
                    'permission' => 'central_users.reset_password',
                    'label' => 'Reset Central User Password',
                    'description' => 'Reset password for a central user.',
                ],
                'show' => [
                    'permission' => 'central_users.show',
                    'label' => 'View Central User Details',
                    'description' => 'View detailed central user information.',
                ],
                'store' => [
                    'permission' => 'central_users.create',
                    'label' => 'Create Central User',
                    'description' => 'Add a new central user.',
                ],
                'update' => [
                    'permission' => 'central_users.update',
                    'label' => 'Update Central Users',
                    'description' => 'Save changes to central users.',
                ],
                'toggle_staff_data_editing' => [
                    'permission' => 'central_users.update.staff_data_edit_state',
                    'label' => 'Toggle Central Staff Data Editing',
                    'description' => 'Allow or prevent central staff from editing certain data.',
                ],
                'toggle_user_block' => [
                    'permission' => 'central_users.update.user_blocked_state',
                    'label' => 'Block/Unblock Central Users',
                    'description' => 'Block or unblock central user accounts.',
                ],
            ],

            'roles' => [
                'destroy' => [
                    'permission' => 'roles.delete',
                    'label' => 'Delete Roles (Central)',
                    'description' => 'Remove central roles.',
                ],
                'edit' => [
                    'permission' => 'roles.update',
                    'label' => 'Edit Roles (Central)',
                    'description' => 'Modify central role details.',
                ],
                'index' => [
                    'permission' => 'roles.view',
                    'label' => 'View Roles (Central)',
                    'description' => 'List all central roles.',
                ],
                'store' => [
                    'permission' => 'roles.create',
                    'label' => 'Create Role (Central)',
                    'description' => 'Add a new central role.',
                ],
                'update' => [
                    'permission' => 'roles.update',
                    'label' => 'Update Roles (Central)',
                    'description' => 'Save changes to central roles.',
                ],
            ],

            'tickets' => [
                'answer' => [
                    'permission' => 'tickets.answer',
                    'label' => 'Answer Central Tickets',
                    'description' => 'Provide answers to central tickets.',
                ],
                'delete' => [
                    'permission' => 'tickets.delete',
                    'label' => 'Delete Central Ticket',
                    'description' => 'Remove a central ticket.',
                ],
                'index_central' => [
                    'permission' => 'tickets.view.central',
                    'label' => 'View Central Tickets',
                    'description' => 'List all central tickets.',
                ],
                'lock_ticket' => [
                    'permission' => 'tickets.lock_ticket',
                    'label' => 'Lock Central Ticket',
                    'description' => 'Lock a central ticket to prevent changes.',
                ],
                'reply' => [
                    'permission' => 'tickets.reply',
                    'label' => 'Reply to Central Ticket',
                    'description' => 'Reply to a central ticket.',
                ],
                'store' => [
                    'permission' => 'tickets.create',
                    'label' => 'Create Central Ticket',
                    'description' => 'Submit a new central ticket.',
                ],
                'store_operator_message' => [
                    'permission' => 'tickets.create.operator_message',
                    'label' => 'Store Central Operator Message',
                    'description' => 'Add an operator message to a central ticket.',
                ],
                'unlock_ticket' => [
                    'permission' => 'tickets.unlock_ticket',
                    'label' => 'Unlock Central Ticket',
                    'description' => 'Unlock a locked central ticket.',
                ],
                'update_assignee' => [
                    'permission' => 'tickets.update.assignee',
                    'label' => 'Update Central Ticket Assignee',
                    'description' => 'Change the assigned user for a central ticket.',
                ],
                'update_properties' => [
                    'permission' => 'tickets.update.properties',
                    'label' => 'Update Central Ticket Properties',
                    'description' => 'Modify central ticket attributes.',
                ],
            ],

            'settings_non_tenancy' => [
                'disable_analytics' => [
                    'permission' => 'settings_non_tenancy.disable_analytics',
                    'label' => 'Disable Central Analytics',
                    'description' => 'Turn off central analytics tracking.',
                ],
                'enable_analytics' => [
                    'permission' => 'settings_non_tenancy.enable_analytics',
                    'label' => 'Enable Central Analytics',
                    'description' => 'Turn on central analytics tracking.',
                ],
                'index' => [
                    'permission' => 'settings_non_tenancy.view',
                    'label' => 'View Central Settings',
                    'description' => 'View central system settings.',
                ],
                'update' => [
                    'permission' => 'settings_non_tenancy.update',
                    'label' => 'Update Central Settings',
                    'description' => 'Save changes to central settings.',
                ],
            ],

            'central_soft_deletes' => [
                'bin' => [
                    'permission' => 'central_soft_deletes.bin',
                    'label' => 'View Central Recycle Bin',
                    'description' => 'View Central Recycle Bin',
                ],
                'user_restore' => [
                    'permission' => 'central_soft_deletes.user_restore',
                    'label' => 'Restore Central User',
                    'description' => 'Restore Soft-deleted Central User',
                ],
                'user_force_delete' => [
                    'permission' => 'central_soft_deletes.user_force_delete',
                    'label' => 'Permanently Delete Central User',
                    'description' => 'Permanently Removing Soft-deleted Central User',
                ],
                'tenant_restore' => [
                    'permission' => 'central_soft_deletes.tenant_restore',
                    'label' => 'Restore Tenant',
                    'description' => 'Restore Soft-deleted Tenant',
                ],
                'tenant_force_delete' => [
                    'permission' => 'central_soft_deletes.tenant_force_delete',
                    'label' => 'Permanently Delete Tenant',
                    'description' => 'Permanently Remove Soft-deleted Tenant',
                ],
                'empty_soft_deleted_model' => [
                    'permission' => 'central_soft_deletes.empty',
                    'label' => 'Empty Recycle Bin',
                    'description' => 'Empty Recycle Bin',
                ],
            ],

            'artisan_commands' => [
                'clean_activity_logs' => [
                    'permission' => 'artisan_commands.clean_activity_logs',
                    'label' => 'Clean Activity Logs',
                    'description' => 'Run artisan command to clean activity logs.',
                ],
                'config_cache' => [
                    'permission' => 'artisan_commands.config_cache',
                    'label' => 'Cache Config',
                    'description' => 'Run artisan config:cache.',
                ],
                'config_clear' => [
                    'permission' => 'artisan_commands.config_clear',
                    'label' => 'Clear Config Cache',
                    'description' => 'Run artisan config:clear.',
                ],
                'event_cache' => [
                    'permission' => 'artisan_commands.event_cache',
                    'label' => 'Cache Events',
                    'description' => 'Run artisan event:cache.',
                ],
                'event_clear' => [
                    'permission' => 'artisan_commands.event_clear',
                    'label' => 'Clear Event Cache',
                    'description' => 'Run artisan event:clear.',
                ],
                'index' => [
                    'permission' => 'artisan_commands.view',
                    'label' => 'View Artisan Commands',
                    'description' => 'List available artisan commands.',
                ],
                'optimize' => [
                    'permission' => 'artisan_commands.optimize',
                    'label' => 'Optimize Application',
                    'description' => 'Run artisan optimize.',
                ],
                'optimize_clear' => [
                    'permission' => 'artisan_commands.optimize_clear',
                    'label' => 'Clear Optimization Cache',
                    'description' => 'Run artisan optimize:clear.',
                ],
                'publish_log_viewer' => [
                    'permission' => 'artisan_commands.publish_log_viewer',
                    'label' => 'Publish Log Viewer',
                    'description' => 'Publish log viewer assets.',
                ],
                'route_cache' => [
                    'permission' => 'artisan_commands.route_cache',
                    'label' => 'Cache Routes',
                    'description' => 'Run artisan route:cache.',
                ],
                'route_clear' => [
                    'permission' => 'artisan_commands.route_clear',
                    'label' => 'Clear Route Cache',
                    'description' => 'Run artisan route:clear.',
                ],
                'run_command' => [
                    'permission' => 'artisan_commands.run_command',
                    'label' => 'Run Artisan Command',
                    'description' => 'Execute an artisan command via UI.',
                ],
                'storage_link' => [
                    'permission' => 'artisan_commands.storage_link',
                    'label' => 'Create Storage Link',
                    'description' => 'Run artisan storage:link.',
                ],
                'storage_unlink' => [
                    'permission' => 'artisan_commands.storage_unlink',
                    'label' => 'Remove Storage Link',
                    'description' => 'Remove the storage symbolic link.',
                ],
                'view_cache' => [
                    'permission' => 'artisan_commands.view_cache',
                    'label' => 'Cache Views',
                    'description' => 'Run artisan view:cache.',
                ],
                'view_clear' => [
                    'permission' => 'artisan_commands.view_clear',
                    'label' => 'Clear View Cache',
                    'description' => 'Run artisan view:clear.',
                ],
            ],

            'central_logs' => [
                'clean_activity_log' => [
                    'permission' => 'central_logs.clean_activity_log',
                    'label' => 'Clean Central Activity Log',
                    'description' => 'Remove old central activity log entries.',
                ],
                'delete_activity' => [
                    'permission' => 'central_logs.delete.activity',
                    'label' => 'Delete Central Activity Log Entry',
                    'description' => 'Delete a specific central activity log entry.',
                ],
                'index' => [
                    'permission' => 'central_logs.view',
                    'label' => 'View Central Logs',
                    'description' => 'List all central logs.',
                ],
            ],

            'tenants' => [
                'destroy' => [
                    'permission' => 'tenants.delete',
                    'label' => 'Delete Tenant',
                    'description' => 'Remove a tenant from the system.',
                ],
                'edit' => [
                    'permission' => 'tenants.update',
                    'label' => 'Edit Tenant',
                    'description' => 'Modify tenant details.',
                ],
                'index' => [
                    'permission' => 'tenants.view',
                    'label' => 'View Tenants List',
                    'description' => 'List all tenants.',
                ],
                'show' => [
                    'permission' => 'tenants.show',
                    'label' => 'View Tenant Details',
                    'description' => 'View detailed tenant information.',
                ],
                'store' => [
                    'permission' => 'tenants.create',
                    'label' => 'Create Tenant',
                    'description' => 'Add a new tenant.',
                ],
                'update' => [
                    'permission' => 'tenants.update',
                    'label' => 'Update Tenant',
                    'description' => 'Save changes to tenants.',
                ],
                'run_command' => [
                    'permission' => 'tenants.run_command',
                    'label' => 'Run Tenant Command',
                    'description' => 'Execute commands for a specific tenant.',
                ],
            ],
            'master_data_syncs' => [
                'sync' => [
                    'permission' => 'master_data_syncs.sync',
                    'label' => 'Sync Master Data',
                    'description' => 'Sync master data between template tenant and selected tenant(s).',
                ],
            ],
        ],
    ],
];
