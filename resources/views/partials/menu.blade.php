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
                            <img src="{{ Usr::getTenancyAwarePhotoUrl(auth()->user()->photo_path) }}" width="38" height="38" class="rounded-circle" alt="photo">
                        </a>
                    </div>
                    <div class="media-body">
                        <div class="media-title font-weight-semibold">{{ auth()->user()->name }}</div>
                        <div class="font-size-sm opacity-50 d-flex">
                            <i class="material-symbols-rounded">person</i>
                            &nbsp;{{ auth()->user()->roles->pluck('name')->join(', ') }}
                        </div>
                    </div>
                    <div class="ml-3 align-self-center">
                        <a href="{{ route('my_account.edit') }}"><i class="material-symbols-rounded">settings</i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->

        <!-- Main navigation -->
        <div class="card card-sidebar-mobile border-0">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                @if (Usr::tenancyInitialized())

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard', 'home') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">{{ request()->routeIs('dashboard') ? 'dashboard' : 'home' }}</i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- My Account --}}
                <li class="nav-item">
                    <a href="{{ route('my_account.edit') }}" class="nav-link {{ request()->routeIs('my_account.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">account_circle</i>
                        <span>My Account</span>
                    </a>
                </li>

                {{-- Academic Setup --}}
                @canany([
                ...array_column($tenant_permissions['time_tables'], 'permission'),
                ...array_column($tenant_permissions['my_classes'], 'permission'),
                ...array_column($tenant_permissions['sections'], 'permission'),
                ...array_column($tenant_permissions['class_types'], 'permission'),
                ...array_column($tenant_permissions['subjects'], 'permission')
                ])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('timetables.*','ttr.*','classes.*','sections.*','class_types.*','subjects.*','subject_records.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">school</i>
                        <span>Academic Setup</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Academic Setup">
                        {{-- Timetables --}}
                        @canany(array_column($tenant_permissions['time_tables'], 'permission'))
                        <li class="nav-item">
                            <a href="{{ route('timetables.index') }}" class="nav-link {{ request()->routeIs('timetables.*','ttr.*') ? 'active' : '' }}">
                                Timetables
                            </a>
                        </li>
                        @endcanany

                        {{-- Classes --}}
                        @canany(array_column($tenant_permissions['my_classes'], 'permission'))
                        <li class="nav-item">
                            <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
                                Classes
                            </a>
                        </li>
                        @endcanany

                        {{-- Sections --}}
                        @canany(array_column($tenant_permissions['sections'], 'permission'))
                        <li class="nav-item">
                            <a href="{{ route('sections.index') }}" class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}">
                                Sections
                            </a>
                        </li>
                        @endcanany

                        {{-- Class Types --}}
                        @canany(array_column($tenant_permissions['class_types'], 'permission'))
                        <li class="nav-item">
                            <a href="{{ route('class_types.index') }}" class="nav-link {{ request()->routeIs('class_types.*') ? 'active' : '' }}">
                                Class Types
                            </a>
                        </li>
                        @endcanany

                        {{-- Subjects --}}
                        @canany(array_column($tenant_permissions['subjects'], 'permission'))
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('subjects.*','subject_records.*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('subjects.*','subject_records.*') ? 'active' : '' }}">
                                Subjects
                            </a>
                            <ul class="nav nav-group-sub">
                                @can($tenant_permissions['subjects']['index']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                                        Manage
                                    </a>
                                </li>
                                @endcan
                                @can($tenant_permissions['subjects']['index_record']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('subject_records.index') }}" class="nav-link {{ request()->routeIs('subject_records.*') ? 'active' : '' }}">
                                        Manage Records
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                    </ul>
                </li>
                @endcanany

                {{-- Students --}}
                @canany([
                ...array_column($tenant_permissions['student_records'], 'permission'),
                ...array_column($tenant_permissions['promotions'], 'permission'),
                ...array_column($tenant_permissions['student_id_cards'], 'permission')
                ])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('students.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">groups</i>
                        <span>Students</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Students">
                        @can($tenant_permissions['student_records']['create']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('students.create') }}" class="nav-link {{ request()->routeIs('students.create') ? 'active' : '' }}">
                                Admit Student
                            </a>
                        </li>
                        @endcan

                        @can($tenant_permissions['student_records']['list_by_class']['permission'])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('students.list','students.edit','students.show','students.list_by_class') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('students.list','students.edit','students.show','students.list_by_class') ? 'active' : '' }}">
                                Student Information
                            </a>
                            <ul class="nav nav-group-sub">
                                @foreach($my_classes as $c)
                                <li class="nav-item">
                                    <a href="{{ route('students.list_by_class', $c->id) }}" class="nav-link">
                                        {{ $c->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @endcan

                        @canany(array_column($tenant_permissions['promotions'], 'permission'))
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('students.promotion*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('students.promotion*') ? 'active' : '' }}">
                                Student Promotion
                            </a>
                            <ul class="nav nav-group-sub">
                                @can($tenant_permissions['promotions']['promotion']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('students.promotion') }}" class="nav-link {{ request()->routeIs('students.promotion') ? 'active' : '' }}">
                                        Promote Students
                                    </a>
                                </li>
                                @endcan
                                @can($tenant_permissions['promotions']['manage']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('students.promotion.manage') }}" class="nav-link {{ request()->routeIs('students.promotion.manage') ? 'active' : '' }}">
                                        Manage Promotions
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany

                        @can($tenant_permissions['student_records']['graduated']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('students.graduated') }}" class="nav-link {{ request()->routeIs('students.graduated') ? 'active' : '' }}">
                                Students Graduated
                            </a>
                        </li>
                        @endcan

                        @canany([$tenant_permissions['student_id_cards']['index']['permission'], $tenant_permissions['student_id_cards']['manage']['permission']])
                        <li class="nav-item">
                            <a href="{{ route('students.id_cards') }}" class="nav-link {{ request()->routeIs('students.id_cards', 'students.id_cards.manage') ? 'active' : '' }}">
                                Students ID Cards
                            </a>
                        </li>
                        @endcanany
                    </ul>
                </li>
                @endcanany

                {{-- Examinations --}}
                @canany([
                ...array_column($tenant_permissions['exams'], 'permission'),
                ...array_column($tenant_permissions['grades'], 'permission'),
                ...array_column($tenant_permissions['divisions'], 'permission'),
                ...array_column($tenant_permissions['marks'], 'permission'),
                ...array_column($tenant_permissions['assessments'], 'permission')
                ])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('exams.*','grades.*','divisions.*','marks.*','assessments.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">article</i>
                        <span>Examinations</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Examinations">

                        {{-- Exams List --}}
                        @can($tenant_permissions['exams']['index']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('exams.index') }}" class="nav-link {{ request()->routeIs('exams.index') ? 'active' : '' }}">
                                Exams List
                            </a>
                        </li>
                        @endcan

                        {{-- Grades --}}
                        @can($tenant_permissions['grades']['index']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('grades.index') }}" class="nav-link {{ request()->routeIs('grades.*') ? 'active' : '' }}">
                                Grades
                            </a>
                        </li>
                        @endcan

                        {{-- Divisions --}}
                        @can($tenant_permissions['divisions']['index']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('divisions.index') }}" class="nav-link {{ request()->routeIs('divisions.*') ? 'active' : '' }}">
                                Divisions
                            </a>
                        </li>
                        @endcan

                        {{-- Marks (sub‑group) --}}
                        @canany([$tenant_permissions['marks']['batch']['permission'], $tenant_permissions['marks']['manage']['permission'], $tenant_permissions['marks']['convert']['permission']])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('marks.*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('marks.*') ? 'active' : '' }}">
                                Marks
                            </a>
                            <ul class="nav nav-group-sub">
                                @can($tenant_permissions['marks']['batch']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('marks.batch') }}" class="nav-link {{ request()->routeIs('marks.batch') ? 'active' : '' }}">Batch</a>
                                </li>
                                @endcan
                                @can($tenant_permissions['marks']['manage']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('marks.manage') }}" class="nav-link {{ request()->routeIs('marks.selector','marks.manage') ? 'active' : '' }}">Manage</a>
                                </li>
                                @endcan
                                @can($tenant_permissions['marks']['convert']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('marks.convert') }}" class="nav-link {{ request()->routeIs('marks.convert') ? 'active' : '' }}">Convert</a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany

                        {{-- Marksheets --}}
                        @canany([$tenant_permissions['marks']['sheets']['permission'], $tenant_permissions['marks']['sheets_select']['permission']])
                        <li class="nav-item">
                            <a href="{{ route('marks.sheets') }}" class="nav-link {{ request()->routeIs('marks.sheets','marks.show') ? 'active' : '' }}">
                                Marksheets
                            </a>
                        </li>
                        @endcanany

                        {{-- Tabulation Sheet --}}
                        @canany([$tenant_permissions['marks']['tabulation']['permission'], $tenant_permissions['marks']['tabulation_select']['permission']])
                        <li class="nav-item">
                            <a href="{{ route('marks.tabulation') }}" class="nav-link {{ request()->routeIs('marks.tabulation') ? 'active' : '' }}">
                                Tabulation Sheet
                            </a>
                        </li>
                        @endcanany

                        {{-- Assessments (sub‑group) --}}
                        @canany([$tenant_permissions['assessments']['list']['permission'], $tenant_permissions['assessments']['index']['permission'], $tenant_permissions['assessments']['progressive']['permission'], $tenant_permissions['assessments']['sheets']['permission']])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('assessments.*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('assessments.*') ? 'active' : '' }}">
                                Assessments
                            </a>
                            <ul class="nav nav-group-sub">
                                @can($tenant_permissions['assessments']['list']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('assessments.list') }}" class="nav-link {{ request()->routeIs('assessments.list') ? 'active' : '' }}">List</a>
                                </li>
                                @endcan
                                @can($tenant_permissions['assessments']['index']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('assessments.index') }}" class="nav-link {{ request()->routeIs('assessments.index','assessments.manage') ? 'active' : '' }}">Manage</a>
                                </li>
                                @endcan
                                @can([$tenant_permissions['assessments']['progressive']['permission'], $tenant_permissions['assessments']['progressive_select']['permission']])
                                <li class="nav-item">
                                    <a href="{{ route('assessments.progressive') }}" class="nav-link {{ request()->routeIs('assessments.progressive') ? 'active' : '' }}">
                                        Progressive Sheet
                                    </a>
                                </li>
                                @endcan
                                @can([$tenant_permissions['assessments']['sheets']['permission'], $tenant_permissions['assessments']['sheets_select']['permission']])
                                <li class="nav-item">
                                    <a href="{{ route('assessments.sheets') }}" class="nav-link {{ request()->routeIs('assessments.sheets', 'assessments.sheets_select') ? 'active' : '' }}">
                                        Sheets
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany

                        {{-- Results (student/parent only) --}}
                        @if(auth()->user()->hasAnyRole([config('roles.default_roles.tenant.student'), config('roles.default_roles.tenant.parent')]))
                        @canany([$tenant_permissions['marks']['year_selector']['permission'], $tenant_permissions['assessments']['year_selector']['permission']])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('marks.year_selector', 'assessments.year_selector') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('marks.year_selector', 'assessments.year_selector') ? 'active' : '' }}">
                                Results
                            </a>
                            <ul class="nav nav-group-sub">
                                @can($tenant_permissions['marks']['year_selector']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('marks.year_selector', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ request()->routeIs('marks.year_selector') ? 'active' : '' }}">
                                        My Marksheet
                                    </a>
                                </li>
                                @endcan
                                @can($tenant_permissions['assessments']['year_selector']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('assessments.year_selector', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ request()->routeIs('assessments.year_selector') ? 'active' : '' }}">
                                        My Assessment Sheet
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
                @canany(array_column($tenant_permissions['payments'], 'permission'))
                <li class="nav-item nav-item-submenu {{ request()->routeIs('payments.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">payments</i>
                        <span>Finances</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Finances">
                        @can($tenant_permissions['payments']['create']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('payments.create') }}" class="nav-link {{ request()->routeIs('payments.create') ? 'active' : '' }}">
                                Create Payment
                            </a>
                        </li>
                        @endcan
                        @can($tenant_permissions['payments']['index']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.index','payments.edit','payments.show') ? 'active' : '' }}">
                                Manage Payments
                            </a>
                        </li>
                        @endcan
                        @can($tenant_permissions['payments']['manage']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('payments.manage') }}" class="nav-link {{ request()->routeIs('payments.manage','payments.invoice','payments.receipts') ? 'active' : '' }}">
                                Student Payments
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Resources --}}
                @canany([
                ...array_column($tenant_permissions['books'], 'permission'),
                ...array_column($tenant_permissions['book_requests'], 'permission'),
                ...array_column($tenant_permissions['dorms'], 'permission')
                ])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('books.*','book_requests.*','dorms.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">library_books</i>
                        <span>Resources</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Resources">
                        {{-- Books --}}
                        @canany([...array_column($tenant_permissions['books'], 'permission'), ...array_column($tenant_permissions['book_requests'], 'permission')])
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('books.*','book_requests.*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('books.*','book_requests.*') ? 'active' : '' }}">
                                Books
                            </a>
                            <ul class="nav nav-group-sub">
                                @can($tenant_permissions['books']['index']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('books.index') }}" class="nav-link {{ request()->routeIs('books.index','books.edit') ? 'active' : '' }}">
                                        Manage
                                    </a>
                                </li>
                                @endcan
                                @can($tenant_permissions['book_requests']['index']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('book_requests.index') }}" class="nav-link {{ request()->routeIs('book_requests.*') ? 'active' : '' }}">
                                        Book Requests
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany

                        {{-- Dormitories --}}
                        @canany(array_column($tenant_permissions['dorms'], 'permission'))
                        <li class="nav-item">
                            <a href="{{ route('dorms.index') }}" class="nav-link {{ request()->routeIs('dorms.*') ? 'active' : '' }}">
                                Dormitories
                            </a>
                        </li>
                        @endcanany
                    </ul>
                </li>
                @endcanany

                {{-- Users & Roles --}}
                @canany([
                ...array_column($tenant_permissions['users'], 'permission'),
                ...array_column($tenant_permissions['roles'], 'permission')
                ])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('users.*','roles.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">group</i>
                        <span>Users & Roles</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Users & Roles">
                        @canany(array_column($tenant_permissions['users'], 'permission'))
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                Users
                            </a>
                        </li>
                        @endcanany
                        @can($tenant_permissions['roles']['index']['permission'])
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                Roles
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Tools --}}
                @canany([
                ...array_column($tenant_permissions['pins'], 'permission'),
                ...array_column($tenant_permissions['query_builders'], 'permission')
                ])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('pins.*','query_builder.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">construction</i>
                        <span>Tools</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Tools">
                        @canany(array_column($tenant_permissions['pins'], 'permission'))
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('pins.*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('pins.*') ? 'active' : '' }}">
                                Pins
                            </a>
                            <ul class="nav nav-group-sub">
                                @can($tenant_permissions['pins']['create']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('pins.create') }}" class="nav-link {{ request()->routeIs('pins.create') ? 'active' : '' }}">
                                        Generate Pins
                                    </a>
                                </li>
                                @endcan
                                @can($tenant_permissions['pins']['index']['permission'])
                                <li class="nav-item">
                                    <a href="{{ route('pins.index') }}" class="nav-link {{ request()->routeIs('pins.index') ? 'active' : '' }}">
                                        View Pins
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany

                        @canany(array_column($tenant_permissions['query_builders'], 'permission'))
                        <li class="nav-item">
                            <a href="{{ route('query_builder.index') }}" class="nav-link {{ request()->routeIs('query_builder.*') ? 'active' : '' }}">
                                Query Builder
                            </a>
                        </li>
                        @endcanany
                    </ul>
                </li>
                @endcanany

                {{-- Settings --}}
                @canany(array_column($tenant_permissions['settings'], 'permission'))
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">settings</i>
                        <span>Settings</span>
                    </a>
                </li>
                @endcanany

                {{-- My Children --}}
                @if(auth()->user()->hasRole(config('roles.default_roles.tenant.parent')))
                @canany(array_column($tenant_permissions['childrens'], 'permission'))
                <li class="nav-item">
                    <a href="{{ route('parent.my_children') }}" class="nav-link {{ request()->routeIs('parent.my_children', 'marks.year_selector', 'assessments.year_selector', 'payments.status', 'students.show', 'marks.show', 'assessments.show') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">account_child_invert</i>
                        <span>My Children</span>
                    </a>
                </li>
                @endcanany
                @endif

                @else
                @include('pages.central.menu')
                @endif
            </ul>
        </div>
    </div>
</div>
