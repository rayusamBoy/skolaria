<div class="sidebar sidebar-main sidebar-expand-md">
    <!-- Sidebar mobile toggler -->
    <div class="sidebar-mobile-toggler text-center">
        <a href="javascript:;" class="sidebar-mobile-main-toggle">
            <i class="material-symbols-rounded">arrow_back</i>
        </a>
        Navigation
        <a href="javascript:;" class="sidebar-mobile-expand">
            <i class="material-symbols-rounded">format_indent_increase</i>
            <i class="material-symbols-rounded">format_indent_decrease</i>
        </a>
    </div>
    <!-- /sidebar mobile toggler -->

    <!-- Sidebar content -->
    <div class="sidebar-content">
        <!-- User menu -->
        <div class="sidebar-user">
            <div class="card-body">
                <div class="media">
                    <div class="mr-3">
                        <a href="{{ route('my_account.edit') }}">
                            <img
                                src="{{ Usr::getTenancyAwarePhotoUrl(auth()->user()->photo_path) }}"
                                width="38"
                                height="38"
                                class="rounded-circle"
                                alt="photo"
                            />
                        </a>
                    </div>
                    <div class="media-body">
                        <div class="media-title font-weight-semibold">{{ auth()->user()->name }}</div>
                        <div class="font-size-sm d-flex opacity-50">
                            <i class="material-symbols-rounded">person</i>
                            &nbsp;{{ auth()->user()->roles->pluck('name')->join(', ') }}
                        </div>
                    </div>
                    <div class="align-self-center ml-3">
                        <a href="{{ route('my_account.edit') }}"><i class="material-symbols-rounded">settings</i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->

        <!-- Main navigation -->
        <div class="card card-sidebar-mobile border-0">
            <ul class="nav nav-sidebar" data-nav-type="accordion">
                @if ($is_tenant_context)
                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a
                            href="{{ route('dashboard') }}"
                            class="nav-link {{ request()->routeIs('dashboard', 'home') ? 'active' : '' }}"
                        >
                            <i class="material-symbols-rounded">{{ request()->routeIs('dashboard') ? 'dashboard' : 'home' }}</i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    {{-- My Account --}}
                    <li class="nav-item">
                        <a
                            href="{{ route('my_account.edit') }}"
                            class="nav-link {{ request()->routeIs('my_account.*') ? 'active' : '' }}"
                        >
                            <i class="material-symbols-rounded">account_circle</i>
                            <span>My Account</span>
                        </a>
                    </li>

                    {{-- Academic Setup --}}
                    @canany([
                        'time_tables.view',
                        'my_classes.view',
                        'sections.view',
                        'class_types.view',
                        'subjects.view',
                    ])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('timetables.*','ttr.*','classes.*','sections.*','class_types.*','subjects.*','subject_records.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="material-symbols-rounded">school</i>
                                <span>Academic Setup</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Academic Setup">
                                {{-- Timetables --}}
                                @can('time_tables.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('timetables.index') }}"
                                            class="nav-link {{ request()->routeIs('timetables.*','ttr.*') ? 'active' : '' }}"
                                        >
                                            Timetables
                                        </a>
                                    </li>
                                @endcan

                                {{-- Classes --}}
                                @can('my_classes.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('classes.index') }}"
                                            class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}"
                                        >
                                            Classes
                                        </a>
                                    </li>
                                @endcan

                                {{-- Sections --}}
                                @can('sections.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('sections.index') }}"
                                            class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}"
                                        >
                                            Sections
                                        </a>
                                    </li>
                                @endcan

                                {{-- Class Types --}}
                                @can('class_types.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('class_types.index') }}"
                                            class="nav-link {{ request()->routeIs('class_types.*') ? 'active' : '' }}"
                                        >
                                            Class Types
                                        </a>
                                    </li>
                                @endcan

                                {{-- Subjects --}}
                                @can('subjects.view')
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('subjects.*','subject_records.*') ? 'nav-item-expanded' : '' }}">
                                        <a
                                            href="#"
                                            class="nav-link {{ request()->routeIs('subjects.*','subject_records.*') ? 'active' : '' }}"
                                        >
                                            Subjects
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('subjects.index')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('subjects.index') }}"
                                                        class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}"
                                                    >
                                                        Manage
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('subjects.index_record')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('subject_records.index') }}"
                                                        class="nav-link {{ request()->routeIs('subject_records.*') ? 'active' : '' }}"
                                                    >
                                                        Manage Records
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    {{-- Students --}}
                    @canany([
                        'students.create',
                        'student_records.list_by_class',
                        'promotions.manage',
                        'student_id_cards.view',
                    ])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('students.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="material-symbols-rounded">groups</i>
                                <span>Students</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Manage Students">
                                @can('student_records.create')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('students.create') }}"
                                            class="nav-link {{ request()->routeIs('students.create') ? 'active' : '' }}"
                                        >
                                            Admit
                                        </a>
                                    </li>
                                @endcan

                                @can('student_records.list_by_class')
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('students.list','students.edit','students.show','students.list_by_class') ? 'nav-item-expanded' : '' }}">
                                        <a
                                            href="#"
                                            class="nav-link {{ request()->routeIs('students.list','students.edit','students.show','students.list_by_class') ? 'active' : '' }}"
                                        >
                                            Information
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @foreach ($my_classes as $c)
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('students.list_by_class', $c->id) }}"
                                                        class="nav-link"
                                                    >
                                                        {{ $c->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endcan

                                @can('promotions.manage')
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('students.promotion*') ? 'nav-item-expanded' : '' }}">
                                        <a
                                            href="#"
                                            class="nav-link {{ request()->routeIs('students.promotion*') ? 'active' : '' }}"
                                        >
                                            Promotion
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('promotions.promotion')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('students.promotion') }}"
                                                        class="nav-link {{ request()->routeIs('students.promotion') ? 'active' : '' }}"
                                                    >
                                                        Promote
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('promotions.manage')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('students.promotion.manage') }}"
                                                        class="nav-link {{ request()->routeIs('students.promotion.manage') ? 'active' : '' }}"
                                                    >
                                                        Manage Promotions
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan

                                @can('student_records.graduated')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('students.graduated') }}"
                                            class="nav-link {{ request()->routeIs('students.graduated') ? 'active' : '' }}"
                                        >
                                            Graduated
                                        </a>
                                    </li>
                                @endcan

                                @can('student_id_cards.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('students.id_cards') }}"
                                            class="nav-link {{ request()->routeIs('students.id_cards', 'students.id_cards.manage') ? 'active' : '' }}"
                                        >
                                            ID Cards
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    {{-- Examinations --}}
                    @php
                        // Permissions that only allow a user to view their own (or their child's)
                        // results. Stripping these only matters for users who have a dedicated
                        // view to fall back on — currently just parents ("My Children").
                        $readOnlyResultPermissions = array_values(array_filter([
                            'marks.year_selector' ?? null,
                            'marks.year_selected' ?? null,
                            'marks.show' ?? null,
                            'assessments.year_selector' ?? null,
                            'assessments.year_selected' ?? null,
                            'assessments.show' ?? null,
                        ]));

                        // Only parents need the read-only result permissions excluded — they
                        // reach results through "My Children". Students need the menu visible
                        // for its Results submenu; staff/teachers/admins hold manage
                        // permissions so the menu renders regardless.
                        $isParent = auth()->user()->hasRole(config('roles.default_roles.tenant.parent'));

                        $excludeFromExaminationsMenu = $isParent ? $readOnlyResultPermissions : [];

                        $examinationsManagePermissions = array_values(array_unique(array_diff([
                            'exams.view',
                            'grades.view',
                            'divisions.view',
                            'marks.manage',
                            'assessments.manage',
                        ], $excludeFromExaminationsMenu)));
                    @endphp

                    @canany($examinationsManagePermissions)
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('exams.*','grades.*','divisions.*','marks.*','assessments.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="material-symbols-rounded">article</i>
                                <span>Examinations</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Manage Examinations">
                                {{-- Exams List --}}
                                @can('exams.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('exams.index') }}"
                                            class="nav-link {{ request()->routeIs('exams.index') ? 'active' : '' }}"
                                        >
                                            List
                                        </a>
                                    </li>
                                @endcan

                                {{-- Grades --}}
                                @can('grades.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('grades.index') }}"
                                            class="nav-link {{ request()->routeIs('grades.*') ? 'active' : '' }}"
                                        >
                                            Grades
                                        </a>
                                    </li>
                                @endcan

                                {{-- Divisions --}}
                                @can('divisions.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('divisions.index') }}"
                                            class="nav-link {{ request()->routeIs('divisions.*') ? 'active' : '' }}"
                                        >
                                            Divisions
                                        </a>
                                    </li>
                                @endcan

                                {{-- Marks (sub‑group) --}}
                                @canany(['marks.batch', 'marks.manage'])
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('marks.batch', 'marks.manage') ? 'nav-item-expanded' : '' }}">
                                        <a
                                            href="#"
                                            class="nav-link {{ request()->routeIs('marks.batch', 'marks.manage') ? 'active' : '' }}"
                                        >
                                            Marks
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('marks.batch')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('marks.batch') }}"
                                                        class="nav-link {{ request()->routeIs('marks.batch') ? 'active' : '' }}"
                                                    >Batch</a>
                                                </li>
                                            @endcan
                                            @can('marks.manage')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('marks.manage') }}"
                                                        class="nav-link {{ request()->routeIs('marks.selector','marks.manage') ? 'active' : '' }}"
                                                    >Manage</a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcanany

                                {{-- Marksheets --}}
                                @canany(['marks.sheets', 'marks.sheets_select'])
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('marks.sheets') }}"
                                            class="nav-link {{ request()->routeIs('marks.sheets','marks.show') ? 'active' : '' }}"
                                        >
                                            Marksheets
                                        </a>
                                    </li>
                                @endcanany

                                {{-- Tabulation Sheet --}}
                                @canany(['marks.tabulation', 'marks.tabulation_select'])
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('marks.tabulation') }}"
                                            class="nav-link {{ request()->routeIs('marks.tabulation') ? 'active' : '' }}"
                                        >
                                            Tabulation Sheet
                                        </a>
                                    </li>
                                @endcanany

                                @can('exams.assign_view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('exams.exam_numbers.assign') }}"
                                            class="nav-link {{ request()->routeIs('exams.exam_numbers.assign') ? 'active' : '' }}"
                                        >
                                            Assign Numbers
                                        </a>
                                    </li>
                                @endcan

                                {{-- Assessments (sub‑group) --}}
                                @canany(['assessments.list', 'assessments.view', 'assessments.progressive', 'assessments.sheets'])
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('assessments.*') ? 'nav-item-expanded' : '' }}">
                                        <a
                                            href="#"
                                            class="nav-link {{ request()->routeIs('assessments.*') ? 'active' : '' }}"
                                        >
                                            Assessments
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('assessments.list')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('assessments.list') }}"
                                                        class="nav-link {{ request()->routeIs('assessments.list') ? 'active' : '' }}"
                                                    >List</a>
                                                </li>
                                            @endcan
                                            @can('assessments.view')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('assessments.index') }}"
                                                        class="nav-link {{ request()->routeIs('assessments.index','assessments.manage') ? 'active' : '' }}"
                                                    >Manage</a>
                                                </li>
                                            @endcan
                                            @canany(['assessments.progressive', 'assessments.progressive_select'])
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('assessments.progressive') }}"
                                                        class="nav-link {{ request()->routeIs('assessments.progressive') ? 'active' : '' }}"
                                                    >
                                                        Progressive Sheet
                                                    </a>
                                                </li>
                                            @endcanany
                                            @canany(['assessments.sheets', 'assessments.sheets_select'])
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('assessments.sheets') }}"
                                                        class="nav-link {{ request()->routeIs('assessments.sheets', 'assessments.sheets_select') ? 'active' : '' }}"
                                                    >
                                                        Sheets
                                                    </a>
                                                </li>
                                            @endcanany
                                        </ul>
                                    </li>
                                @endcanany

                                {{-- Results (student/parent only) --}}
                                @if (auth()->user()->hasAnyRole([config('roles.default_roles.tenant.student'), config('roles.default_roles.tenant.parent')]))
                                    @canany(['marks.year_selector', 'assessments.year_selector'])
                                        <li class="nav-item nav-item-submenu {{ request()->routeIs('marks.year_selector', 'assessments.year_selector') ? 'nav-item-expanded' : '' }}">
                                            <a
                                                href="#"
                                                class="nav-link {{ request()->routeIs('marks.year_selector', 'assessments.year_selector') ? 'active' : '' }}"
                                            >
                                                Results
                                            </a>
                                            <ul class="nav nav-group-sub">
                                                @can('marks.year_selector')
                                                    <li class="nav-item">
                                                        <a
                                                            href="{{ route('marks.year_selector', Qs::hash(Auth::user()->id)) }}"
                                                            class="nav-link {{ request()->routeIs('marks.year_selector') ? 'active' : '' }}"
                                                        >
                                                            Marksheet
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('assessments.year_selector')
                                                    <li class="nav-item">
                                                        <a
                                                            href="{{ route('assessments.year_selector', Qs::hash(Auth::user()->id)) }}"
                                                            class="nav-link {{ request()->routeIs('assessments.year_selector') ? 'active' : '' }}"
                                                        >
                                                            Assessment Sheet
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </li>
                                    @endcanany
                                @endif
                            </ul>
                        </li>
                    @endcanany

                    {{-- Finances --}}
                    @can('payments.view')
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('payments.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="material-symbols-rounded">payments</i>
                                <span>Finances</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Finances">
                                @can('payments.create')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('payments.create') }}"
                                            class="nav-link {{ request()->routeIs('payments.create') ? 'active' : '' }}"
                                        >
                                            Create Payment
                                        </a>
                                    </li>
                                @endcan
                                @can('payments.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('payments.index') }}"
                                            class="nav-link {{ request()->routeIs('payments.index','payments.edit','payments.show') ? 'active' : '' }}"
                                        >
                                            Manage Payments
                                        </a>
                                    </li>
                                @endcan
                                @can('payments.manage')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('payments.manage') }}"
                                            class="nav-link {{ request()->routeIs('payments.manage','payments.invoice','payments.receipts') ? 'active' : '' }}"
                                        >
                                            Student Payments
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcan

                    {{-- Resources --}}
                    @canany([
                        'books.view',
                        'book_requests.view',
                        'dorms.view',
                    ])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('books.*','book_requests.*','dorms.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="material-symbols-rounded">library_books</i>
                                <span>Resources</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Resources">
                                {{-- Books --}}
                                @canany(['books.view', 'book_requests.view', 'dorms.view'])
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('books.*','book_requests.*') ? 'nav-item-expanded' : '' }}">
                                        <a
                                            href="#"
                                            class="nav-link {{ request()->routeIs('books.*','book_requests.*') ? 'active' : '' }}"
                                        >
                                            Books
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('books.view')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('books.index') }}"
                                                        class="nav-link {{ request()->routeIs('books.index','books.edit','books.show') ? 'active' : '' }}"
                                                    >
                                                        Manage
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('book_requests.view')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('book_requests.index') }}"
                                                        class="nav-link {{ request()->routeIs('book_requests.*') ? 'active' : '' }}"
                                                    >
                                                        Book Requests
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcanany

                                {{-- Dormitories --}}
                                @can('dorms.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('dorms.index') }}"
                                            class="nav-link {{ request()->routeIs('dorms.*') ? 'active' : '' }}"
                                        >
                                            Dormitories
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    {{-- Users & Roles --}}
                    @canany([
                        'users.view',
                        'roles.view',
                    ])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('users.*','roles.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="material-symbols-rounded">group</i>
                                <span>Users & Roles</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Users & Roles">
                                @can('users.view')
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('users.*') ? 'nav-item-expanded' : '' }}">
                                        <a
                                            href="#"
                                            class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                        >
                                            Users
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('users.view')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('users.index') }}"
                                                        class="nav-link {{ request()->routeIs('users.index','users.edit','users.show') ? 'active' : '' }}"
                                                    >
                                                        Manage
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('users.view_registrations')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('users.invitations.index') }}"
                                                        class="nav-link {{ request()->routeIs('users.invitations.*') ? 'active' : '' }}"
                                                    >
                                                        Invitations
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan
                                @can('roles.view')
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('roles.index') }}"
                                            class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                                        >
                                            Roles
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    {{-- Tools --}}
                    @canany(array_diff([
                        'pins.view',
                        'query_builders.view',
                    ], ['pins.enter_pin']))
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('pins.*','query_builder.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link">
                                <i class="material-symbols-rounded">construction</i>
                                <span>Tools</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Tools">
                                @canany(array_diff(['pins.view', 'query_builders.view'], ['pins.enter_pin']))
                                    <li class="nav-item nav-item-submenu {{ request()->routeIs('pins.*') ? 'nav-item-expanded' : '' }}">
                                        <a href="#" class="nav-link {{ request()->routeIs('pins.*') ? 'active' : '' }}">
                                            Pins
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('pins.create')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('pins.create') }}"
                                                        class="nav-link {{ request()->routeIs('pins.create') ? 'active' : '' }}"
                                                    >
                                                        Generate Pins
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('pins.view')
                                                <li class="nav-item">
                                                    <a
                                                        href="{{ route('pins.index') }}"
                                                        class="nav-link {{ request()->routeIs('pins.index') ? 'active' : '' }}"
                                                    >
                                                        View Pins
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcanany

                                @canany(['query_builders.view'])
                                    <li class="nav-item">
                                        <a
                                            href="{{ route('query_builder.index') }}"
                                            class="nav-link {{ request()->routeIs('query_builder.*') ? 'active' : '' }}"
                                        >
                                            Query Builder
                                        </a>
                                    </li>
                                @endcanany
                            </ul>
                        </li>
                    @endcanany

                    {{-- Settings --}}
                    @can('settings.view')
                        <li class="nav-item">
                            <a
                                href="{{ route('settings.index') }}"
                                class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                            >
                                <i class="material-symbols-rounded">settings</i>
                                <span>Settings</span>
                            </a>
                        </li>
                    @endcan

                    {{-- My Children --}}
                    @if (auth()->user()->hasRole(config('roles.default_roles.tenant.parent')))
                        <li class="nav-item">
                            <a
                                href="{{ route('parent.my_children') }}"
                                class="nav-link {{ request()->routeIs('parent.my_children', 'marks.year_selector', 'assessments.year_selector', 'payments.status', 'students.show', 'marks.show', 'assessments.show') ? 'active' : '' }}"
                            >
                                <i class="material-symbols-rounded">account_child_invert</i>
                                <span>My Children</span>
                            </a>
                        </li>
                    @endif

                @else
                    @include('pages.central.menu')
                @endif

                @can('user_impersonations.view')
                    @if ((session('impersonated_from_central') && $is_tenant_context) || ! $is_tenant_context && config('tenancy.allow_user_impersonation') === true)
                        <li class="nav-item">
                            <a
                                href="{{ route('tenant.impersonation') }}"
                                class="nav-link {{ request()->routeIs('tenant.impersonation') ? 'active' : '' }}"
                            >
                                <i class="material-symbols-rounded">swap_horiz</i>
                                <span>Impersonation</span>
                            </a>
                        </li>
                    @endif
                @endcan
            </ul>
        </div>
    </div>
</div>
