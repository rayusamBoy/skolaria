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
        'show' => 'view',
        'list' => 'view',
        'create' => 'create',
        'store' => 'store',
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
        'Controllers\Concerns',
        'Controllers\Auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission names
    |-------------------------------------------------------------------------- 
    */
    'names' => [
        'tenant' =>
            [
                'users' => [
                    'destroy' => 'users.delete',
                    'edit' => 'users.update',
                    'index' => 'users.view',
                    'show' => 'users.view',
                    'store' => 'users.store',
                    'update' => 'users.update',
                    'update_staff_data_edit_state' => 'users.update.staff_data_edit_state',
                    'update_user_blocked_state' => 'users.update.user_blocked_state',
                ],
                'roles' => [
                    'destroy' => 'roles.delete',
                    'edit' => 'roles.update',
                    'index' => 'roles.view',
                    'store' => 'roles.store',
                    'update' => 'roles.update',
                ],
                'analytics' => [
                    'fetch_data' => 'analytics.fetch_data',
                    'google_setup' => 'analytics.google_setup',
                    'index' => 'analytics.view',
                ],
                'assessments' => [
                    'bulk' => 'assessments.bulk',
                    'bulk_select' => 'assessments.bulk_select',
                    'comment_update' => 'assessments.comment_update',
                    'index' => 'assessments.view',
                    'list' => 'assessments.view',
                    'manage' => 'assessments.manage',
                    'print_assessments' => 'assessments.print_assessments',
                    'print_cover' => 'assessments.print_cover',
                    'print_detailed' => 'assessments.print_detailed',
                    'print_minimal' => 'assessments.print_minimal',
                    'print_progressive' => 'assessments.print_progressive',
                    'progressive' => 'assessments.progressive',
                    'progressive_select' => 'assessments.progressive_select',
                    'selector' => 'assessments.selector',
                    'show' => 'assessments.view',
                    'skills_update' => 'assessments.skills_update',
                    'update' => 'assessments.update',
                    'year_selected' => 'assessments.year_selected',
                    'year_selector' => 'assessments.year_selector',
                ],
                'book_requests' => [
                    'destroy' => 'book_requests.delete',
                    'edit' => 'book_requests.update',
                    'index' => 'book_requests.view',
                    'store' => 'book_requests.store',
                    'update' => 'book_requests.update',
                ],
                'books' => [
                    'destroy' => 'books.delete',
                    'edit' => 'books.update',
                    'index' => 'books.view',
                    'store' => 'books.store',
                    'update' => 'books.update',
                ],
                'divisions' => [
                    'destroy' => 'divisions.delete',
                    'edit' => 'divisions.update',
                    'index' => 'divisions.view',
                    'store' => 'divisions.store',
                    'update' => 'divisions.update',
                ],
                'dorms' => [
                    'destroy' => 'dorms.delete',
                    'edit' => 'dorms.update',
                    'index' => 'dorms.view',
                    'store' => 'dorms.store',
                    'update' => 'dorms.update',
                ],
                'exams' => [
                    'announce' => 'exams.announce',
                    'destroy' => 'exams.delete',
                    'edit' => 'exams.update',
                    'index' => 'exams.view',
                    'publish' => 'exams.publish',
                    'store' => 'exams.store',
                    'update' => 'exams.update',
                    'update_edit_state' => 'exams.update.edit_state',
                    'update_lock_state' => 'exams.update.lock_state',
                ],
                'grades' => [
                    'destroy' => 'grades.delete',
                    'edit' => 'grades.update',
                    'index' => 'grades.view',
                    'store' => 'grades.store',
                    'update' => 'grades.update',
                ],
                'marks' => [
                    'batch' => 'marks.batch',
                    'batch_delete' => 'marks.batch_delete',
                    'batch_template' => 'marks.batch_template',
                    'batch_update' => 'marks.batch_update',
                    'batch_upload' => 'marks.batch_upload',
                    'bulk' => 'marks.bulk',
                    'bulk_select' => 'marks.bulk_select',
                    'comment_update' => 'marks.comment_update',
                    'convert' => 'marks.convert',
                    'getExams' => 'marks.get_exams',
                    'index' => 'marks.view',
                    'manage' => 'marks.manage',
                    'print_tabulation' => 'marks.print_tabulation',
                    'print_view' => 'marks.print_view',
                    'select_year' => 'marks.select_year',
                    'selector' => 'marks.selector',
                    'show' => 'marks.view',
                    'skills_update' => 'marks.skills_update',
                    'tabulation' => 'marks.tabulation',
                    'tabulation_select' => 'marks.tabulation_select',
                    'update' => 'marks.update',
                    'year_selected' => 'marks.year_selected',
                    'year_selector' => 'marks.year_selector',
                ],
                'messages' => [
                    'activate_all_participants' => 'messages.activate_all_participants',
                    'create' => 'messages.create',
                    'destroy' => 'messages.delete',
                    'fetch_previous' => 'messages.fetch_previous',
                    'get_users' => 'messages.get_users',
                    'index' => 'messages.view',
                    'remove_participant' => 'messages.remove_participant',
                    'show' => 'messages.view',
                    'store' => 'messages.store',
                    'update' => 'messages.update',
                    'user_delete' => 'messages.user_delete',
                ],
                'mies' => [
                    'children' => 'mies.children',
                ],
                'my_accounts' => [
                    'change_pass' => 'my_accounts.change_pass',
                    'clear_hidden_alerts' => 'my_accounts.clear_hidden_alerts',
                    'edit_profile' => 'my_accounts.update.profile',
                    'other' => 'my_accounts.other',
                    'update_hidden_alerts' => 'my_accounts.update.hidden_alerts',
                    'update_profile' => 'my_accounts.update.profile',
                ],
                'my_classes' => [
                    'destroy' => 'my_classes.delete',
                    'edit' => 'my_classes.update',
                    'index' => 'my_classes.view',
                    'store' => 'my_classes.store',
                    'update' => 'my_classes.update',
                ],
                'notices' => [
                    'destroy' => 'notices.delete',
                    'edit' => 'notices.update',
                    'index' => 'notices.view',
                    'set_as_viewed' => 'notices.set_as_viewed',
                    'store' => 'notices.store',
                    'update_record' => 'notices.update.record',
                ],
                'notifications' => [
                    'delete_device_token' => 'notifications.delete.device_token',
                    'destroy' => 'notifications.delete',
                    'index' => 'notifications.view',
                    'mark_all_read' => 'notifications.mark_all_read',
                    'mark_as_read' => 'notifications.mark_as_read',
                    'update_firebase_device_token' => 'notifications.update.firebase_device_token',
                    'update_is_notifiable' => 'notifications.update.is_notifiable',
                ],
                'payments' => [
                    'create' => 'payments.create',
                    'destroy' => 'payments.delete',
                    'edit' => 'payments.update',
                    'index' => 'payments.view',
                    'invoice' => 'payments.invoice',
                    'manage' => 'payments.manage',
                    'pay_now' => 'payments.pay_now',
                    'pdf_receipts' => 'payments.pdf_receipts',
                    'receipts' => 'payments.receipts',
                    'reset_record' => 'payments.reset_record',
                    'select_class' => 'payments.select_class',
                    'select_year' => 'payments.select_year',
                    'show' => 'payments.view',
                    'status' => 'payments.status',
                    'store' => 'payments.store',
                    'update' => 'payments.update',
                ],
                'pins' => [
                    'create' => 'pins.create',
                    'destroy' => 'pins.delete',
                    'enter_pin' => 'pins.enter_pin',
                    'index' => 'pins.view',
                    'store' => 'pins.store',
                    'verify' => 'pins.verify',
                ],
                'promotions' => [
                    'manage' => 'promotions.manage',
                    'promote' => 'promotions.promote',
                    'promotion' => 'promotions.promotion',
                    'remarks' => 'promotions.remarks',
                    'reset' => 'promotions.reset',
                    'reset_all' => 'promotions.reset_all',
                    'selector' => 'promotions.selector',
                ],
                'query_builders' => [
                    'index' => 'query_builders.view',
                    'print_staff_data' => 'query_builders.print_staff_data',
                    'select' => 'query_builders.select',
                ],
                'schedules' => [
                    'create_event' => 'schedules.create.event',
                    'delete_event' => 'schedules.delete.event',
                    'edit_event' => 'schedules.update.event',
                    'index' => 'schedules.view',
                    'update_event' => 'schedules.update.event',
                ],
                'sections' => [
                    'destroy' => 'sections.delete',
                    'edit' => 'sections.update',
                    'index' => 'sections.view',
                    'store' => 'sections.store',
                    'update' => 'sections.update',
                ],
                'settings' => [
                    'disable_analytics' => 'settings.disable_analytics',
                    'enable_analytics' => 'settings.enable_analytics',
                    'index' => 'settings.view',
                    'preview_login_form' => 'settings.preview_login_form',
                    'update' => 'settings.update',
                ],
                'soft_deletes' => [
                    'bin' => 'soft_deletes.bin',
                    'empty_soft_deleted_model' => 'soft_deletes.empty_soft_deleted_model',
                    'exam_force_delete' => 'soft_deletes.exam_force_delete',
                    'exam_restore' => 'soft_deletes.exam_restore',
                    'my_class_force_delete' => 'soft_deletes.my_class_force_delete',
                    'my_class_restore' => 'soft_deletes.my_class_restore',
                    'payment_force_delete' => 'soft_deletes.payment_force_delete',
                    'payment_restore' => 'soft_deletes.payment_restore',
                    'thread_force_delete' => 'soft_deletes.thread_force_delete',
                    'thread_restore' => 'soft_deletes.thread_restore',
                    'user_force_delete' => 'soft_deletes.user_force_delete',
                    'user_restore' => 'soft_deletes.user_restore',
                ],
                'statistics' => [
                    'index' => 'statistics.view',
                ],
                'student_records' => [
                    'batch_store' => 'student_records.batch_store',
                    'batch_template' => 'student_records.batch_template',
                    'block_all_class' => 'student_records.block_all_class',
                    'block_all_graduated' => 'student_records.block_all_graduated',
                    'create' => 'student_records.create',
                    'destroy' => 'student_records.delete',
                    'edit' => 'student_records.update',
                    'graduated' => 'student_records.graduated',
                    'id_cards' => 'student_records.id_cards',
                    'id_cards_manage' => 'student_records.id_cards_manage',
                    'list_by_class' => 'student_records.view.by_class',
                    'not_graduated' => 'student_records.not_graduated',
                    'print_class_id_cards' => 'student_records.print_class_id_cards',
                    'print_selected_id_cards' => 'student_records.print_selected_id_cards',
                    'reset_pass' => 'student_records.reset_pass',
                    'show' => 'student_records.view',
                    'store' => 'student_records.store',
                    'unblock_all_class' => 'student_records.unblock_all_class',
                    'unblock_all_graduated' => 'student_records.unblock_all_graduated',
                    'update' => 'student_records.update'
                ],
                'subjects' => [
                    'delete_record' => 'subjects.delete.record',
                    'edit_record' => 'subjects.update.record',
                    'index' => 'subjects.view',
                    'store' => 'subjects.store',
                    'update_record' => 'subjects.update.record',
                ],
                'tickets' => [
                    'answer' => 'tickets.answer',
                    'archive_ticket' => 'tickets.archive_ticket',
                    'close_ticket' => 'tickets.close_ticket',
                    'delete' => 'tickets.delete',
                    'index' => 'tickets.view',
                    'index_central' => 'tickets.view.central',
                    'lock_ticket' => 'tickets.lock_ticket',
                    'reply' => 'tickets.reply',
                    'store' => 'tickets.store',
                    'store_message' => 'tickets.store.message',
                    'store_operator_message' => 'tickets.store.operator_message',
                    'unarchive_ticket' => 'tickets.unarchive_ticket',
                    'unlock_ticket' => 'tickets.unlock_ticket',
                    'update_assignee' => 'tickets.update.assignee',
                    'update_properties' => 'tickets.update.properties',
                ],
                'time_tables' => [
                    'delete' => 'time_tables.delete',
                    'delete_record' => 'time_tables.delete.record',
                    'delete_time_slot' => 'time_tables.delete.time_slot',
                    'edit_record' => 'time_tables.update.record',
                    'edit_time_slot' => 'time_tables.update.time_slot',
                    'index' => 'time_tables.view',
                    'manage' => 'time_tables.manage',
                    'print_record' => 'time_tables.print_record',
                    'show_record' => 'time_tables.view.record',
                    'store' => 'time_tables.store',
                    'store_record' => 'time_tables.store.record',
                    'store_time_slot' => 'time_tables.store.time_slot',
                    'update' => 'time_tables.update',
                    'update_record' => 'time_tables.update.record',
                    'update_time_slot' => 'time_tables.update.time_slot',
                    'use_time_slot' => 'time_tables.use_time_slot',
                ],
                'logs' => [
                    'clean_activity_log' => 'logs.clean_activity_log',
                    'delete_activity' => 'logs.delete.activity',
                    'index' => 'logs.view',
                    'reset_login_hist' => 'logs.reset_login_hist',
                    'reset_login_histories' => 'logs.reset_login_histories',
                ],
            ],

        'central' => [
            'central_users' => [
                'destroy' => 'central_users.delete',
                'edit' => 'central_users.update',
                'index' => 'central_users.view',
                'reset_pass' => 'central_users.reset_pass',
                'show' => 'central_users.view',
                'store' => 'central_users.store',
                'update' => 'central_users.update',
                'update_staff_data_edit_state' => 'central_users.update.staff_data_edit_state',
                'update_user_blocked_state' => 'central_users.update.user_blocked_state',
            ],
            'roles' => [
                'destroy' => 'roles.delete',
                'edit' => 'roles.update',
                'index' => 'roles.view',
                'store' => 'roles.store',
                'update' => 'roles.update',
            ],
            'tickets' => [
                'answer' => 'tickets.answer',
                'archive_ticket' => 'tickets.archive_ticket',
                'close_ticket' => 'tickets.close_ticket',
                'delete' => 'tickets.delete',
                'index' => 'tickets.view',
                'index_central' => 'tickets.view.central',
                'lock_ticket' => 'tickets.lock_ticket',
                'reply' => 'tickets.reply',
                'store' => 'tickets.store',
                'store_message' => 'tickets.store.message',
                'store_operator_message' => 'tickets.store.operator_message',
                'unarchive_ticket' => 'tickets.unarchive_ticket',
                'unlock_ticket' => 'tickets.unlock_ticket',
                'update_assignee' => 'tickets.update.assignee',
                'update_properties' => 'tickets.update.properties',
            ],
            'settings_non_tenancy' => [
                'disable_analytics' => 'settings_non_tenancy.disable_analytics',
                'enable_analytics' => 'settings_non_tenancy.enable_analytics',
                'index' => 'settings_non_tenancy.view',
                'preview_login_form' => 'settings_non_tenancy.preview_login_form',
                'update' => 'settings_non_tenancy.update',
            ],
            'artisan_commands' => [
                'clean_activity_logs' => 'artisan_commands.clean_activity_logs',
                'config_cache' => 'artisan_commands.config_cache',
                'config_clear' => 'artisan_commands.config_clear',
                'event_cache' => 'artisan_commands.event_cache',
                'event_clear' => 'artisan_commands.event_clear',
                'index' => 'artisan_commands.view',
                'optimize' => 'artisan_commands.optimize',
                'optimize_clear' => 'artisan_commands.optimize_clear',
                'publish_log_viewer' => 'artisan_commands.publish_log_viewer',
                'route_cache' => 'artisan_commands.route_cache',
                'route_clear' => 'artisan_commands.route_clear',
                'run_command' => 'artisan_commands.run_command',
                'storage_link' => 'artisan_commands.storage_link',
                'storage_unlink' => 'artisan_commands.storage_unlink',
                'view_cache' => 'artisan_commands.view_cache',
                'view_clear' => 'artisan_commands.view_clear',
            ],
            'central_logs' => [
                'clean_activity_log' => 'central_logs.clean_activity_log',
                'delete_activity' => 'central_logs.delete.activity',
                'index' => 'central_logs.view',
                'reset_login_hist' => 'central_logs.reset_login_hist',
                'reset_login_histories' => 'central_logs.reset_login_histories',
            ],
            'tenants' => [
                'destroy' => 'tenants.delete',
                'edit' => 'tenants.update',
                'index' => 'tenants.view',
                'show' => 'tenants.view',
                'store' => 'tenants.store',
                'update' => 'tenants.update',
            ],
        ]
    ]
];