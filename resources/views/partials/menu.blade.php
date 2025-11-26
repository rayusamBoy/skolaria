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
                            <img src="{{ Usr::getTenantAwarePhoto(auth()->user()->photo) }}" width="38" height="38" class="rounded-circle" alt="photo">
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

                {{-- Tenant Menu --}}
                @if (Usr::tenancyInitilized())

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard', 'home') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">
                            {{ request()->routeIs('dashboard') ? 'dashboard' : 'home' }}
                        </i>
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

                {{-- Academics --}}
                @canany($tenant_permissions['time_tables'])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('timetables.*', 'ttr.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">school</i>
                        <span>Academics</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Academics">
                        @can('view_timetables')
                        <li class="nav-item">
                            <a href="{{ route('timetables.index') }}" class="nav-link {{ request()->routeIs('timetables.*', 'ttr.*') ? 'active' : '' }}">
                                Timetables
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Students --}}
                @canany($tenant_permissions['student_records'])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('students.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">groups</i>
                        <span>Students</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Students">
                        @can('admit_student')
                        <li class="nav-item">
                            <a href="{{ route('students.create') }}" class="nav-link {{ request()->routeIs('students.create') ? 'active' : '' }}">
                                Admit Student
                            </a>
                        </li>
                        @endcan

                        @can('view_student_information')
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('students.list','students.edit','students.show') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('students.list','students.edit','students.show') ? 'active' : '' }}">
                                Student Information
                            </a>
                            <ul class="nav nav-group-sub">
                                @foreach(Usr::getAllClassesOrdered() as $c)
                                <li class="nav-item">
                                    <a href="{{ route('students.list_by_class', $c->id) }}" class="nav-link">
                                        {{ $c->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @endcan

                        @can('promote_students')
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('students.promotion*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('students.promotion*') ? 'active' : '' }}">
                                Student Promotion
                            </a>
                            <ul class="nav nav-group-sub">
                                <li class="nav-item">
                                    <a href="{{ route('students.promotion') }}" class="nav-link {{ request()->routeIs('students.promotion') ? 'active' : '' }}">
                                        Promote Students
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('students.promotion.manage') }}" class="nav-link {{ request()->routeIs('students.promotion_manage') ? 'active' : '' }}">
                                        Manage Promotions
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endcan

                        @can('view_graduated_students')
                        <li class="nav-item">
                            <a href="{{ route('students.graduated') }}" class="nav-link {{ request()->routeIs('students.graduated') ? 'active' : '' }}">
                                Students Graduated
                            </a>
                        </li>
                        @endcan

                        @can('manage_id_cards')
                        <li class="nav-item">
                            <a href="{{ route('students.id_cards') }}" class="nav-link {{ request()->routeIs('students.id_cards','students.id_cards.manage') ? 'active' : '' }}">
                                Students ID Cards
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Classes --}}
                @canany($tenant_permissions['my_classes'])
                <li class="nav-item">
                    <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">domain</i>
                        <span>Classes</span>
                    </a>
                </li>
                @endcanany

                {{-- Sections --}}
                @canany($tenant_permissions['sections'])
                <li class="nav-item">
                    <a href="{{ route('sections.index') }}" class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">stacks</i>
                        <span>Sections</span>
                    </a>
                </li>
                @endcanany

                {{-- Subjects --}}
                @canany($tenant_permissions['subjects'])
                <li class="nav-item">
                    <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">subject</i>
                        <span>Subjects</span>
                    </a>
                </li>
                @endcanany

                {{-- Exams --}}
                @canany($tenant_permissions['exams'])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('exams.*','grades.*','marks.*','divisions.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">article</i>
                        <span>Exams</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Exams">
                        @can('view_exam_list')
                        <li class="nav-item">
                            <a href="{{ route('exams.index') }}" class="nav-link {{ request()->routeIs('exams.index') ? 'active' : '' }}">
                                Exam List
                            </a>
                        </li>
                        @endcan

                        @can('manage_grades')
                        <li class="nav-item">
                            <a href="{{ route('grades.index') }}" class="nav-link {{ request()->routeIs('grades.*') ? 'active' : '' }}">
                                Grades
                            </a>
                        </li>
                        @endcan

                        @can('manage_divisions')
                        <li class="nav-item">
                            <a href="{{ route('divisions.index') }}" class="nav-link {{ request()->routeIs('divisions.*') ? 'active' : '' }}">
                                Divisions
                            </a>
                        </li>
                        @endcan

                        @can('view_tabulation')
                        <li class="nav-item">
                            <a href="{{ route('marks.tabulation') }}" class="nav-link {{ request()->routeIs('marks.tabulation') ? 'active' : '' }}">
                                Tabulation Sheet
                            </a>
                        </li>
                        @endcan

                        @can('manage_marks')
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('marks.index','marks.convert','marks.batch','marks.manage') ? 'nav-item-expanded nav-item-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('marks.index','marks.convert','marks.batch') ? 'active' : '' }}">
                                Marks
                            </a>
                            <ul class="nav nav-group-sub">
                                @can('batch_marks')
                                <li class="nav-item">
                                    <a href="{{ route('marks.batch') }}" class="nav-link {{ request()->routeIs('marks.batch') ? 'active' : '' }}">
                                        Batch
                                    </a>
                                </li>
                                @endcan
                                <li class="nav-item">
                                    <a href="{{ route('marks.index') }}" class="nav-link {{ request()->routeIs('marks.index','marks.manage') ? 'active' : '' }}">
                                        Manage
                                    </a>
                                </li>
                                @can('convert_marks')
                                <li class="nav-item">
                                    <a href="{{ route('marks.convert') }}" class="nav-link {{ request()->routeIs('marks.convert') ? 'active' : '' }}">
                                        Convert
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcan

                        @can('view_bulk_marks')
                        <li class="nav-item">
                            <a href="{{ route('marks.bulk') }}" class="nav-link {{ request()->routeIs('marks.bulk','marks.show') ? 'active' : '' }}">
                                Bulk Marks
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Assessments --}}
                @canany($tenant_permissions['assessments'])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('assessments.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">folder</i>
                        <span>Assessments</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Assessments">
                        @can('view_ca_list')
                        <li class="nav-item">
                            <a href="{{ route('assessments.list') }}" class="nav-link {{ request()->routeIs('assessments.list') ? 'active' : '' }}">
                                CA List
                            </a>
                        </li>
                        @endcan

                        @can('view_progressive_sheet')
                        <li class="nav-item">
                            <a href="{{ route('assessments.progressive') }}" class="nav-link {{ request()->routeIs('assessments.progressive') ? 'active' : '' }}">
                                Progressive Sheet
                            </a>
                        </li>
                        @endcan

                        @can('manage_assessments')
                        <li class="nav-item">
                            <a href="{{ route('assessments.index') }}" class="nav-link {{ request()->routeIs('assessments.index','assessments.manage') ? 'active' : '' }}">
                                Assessments
                            </a>
                        </li>
                        @endcan

                        @can('view_assessment_sheet')
                        <li class="nav-item">
                            <a href="{{ route('assessments.bulk') }}" class="nav-link {{ request()->routeIs('assessments.bulk','assessments.show','assessments.year_selector') ? 'active' : '' }}">
                                Assessment Sheet
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Results --}}
                @canany(array_merge($tenant_permissions['assessments'], $tenant_permissions['marks']))
                <li class="nav-item nav-item-submenu {{ request()->routeIs('assessments.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">grading</i>
                        <span>Results</span>
                    </a>

                    <ul class="nav nav-group-sub" data-submenu-title="Results">
                        @can('view_marksheet')
                        <li class="nav-item">
                            <a href="{{ route('marks.year_selector', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ request()->routeIs('marks.*', 'pins.enter') ? 'active' : '' }}">
                                Marksheet
                            </a>
                        </li>
                        @endcan

                        @can('view_assessment_sheet')
                        <li class="nav-item">
                            <a href="{{ route('assessments.year_selector', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ request()->routeIs('assessments.*') ? 'active' : '' }}">
                                Assessment Sheet
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Administrative --}}
                @canany($tenant_permissions['payments'])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('payments.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">apartment</i>
                        <span>Administrative</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Administrative">
                        @can('manage_payments')
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('payments.*') ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                Payments
                            </a>
                            <ul class="nav nav-group-sub">
                                @can('create_payment')
                                <li class="nav-item">
                                    <a href="{{ route('payments.create') }}" class="nav-link {{ request()->routeIs('payments.create') ? 'active' : '' }}">
                                        Create Payment
                                    </a>
                                </li>
                                @endcan
                                @can('view_payments')
                                <li class="nav-item">
                                    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.index','payments.edit','payments.show') ? 'active' : '' }}">
                                        Manage Payments
                                    </a>
                                </li>
                                @endcan
                                @can('view_student_payments')
                                <li class="nav-item">
                                    <a href="{{ route('payments.manage') }}" class="nav-link {{ request()->routeIs('payments.manage','payments.invoice','payments.receipts') ? 'active' : '' }}">
                                        Student Payments
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Books --}}
                @canany(array_merge($tenant_permissions['books'], $tenant_permissions['book_requests']))
                <li class="nav-item nav-item-submenu {{ request()->routeIs('books.*','book_requests.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">library_books</i>
                        <span>Books</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Books">
                        @can('manage_books')
                        <li class="nav-item">
                            <a href="{{ route('books.index') }}" class="nav-link {{ request()->routeIs('books.index','books.edit') ? 'active' : '' }}">
                                Manage
                            </a>
                        </li>
                        @endcan
                        @can('manage_book_requests')
                        <li class="nav-item">
                            <a href="{{ route('book_requests.index') }}" class="nav-link {{ request()->routeIs('book_requests.*') ? 'active' : '' }}">
                                Manage Requests
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Dormitories --}}
                @canany($tenant_permissions['dorms'])
                <li class="nav-item">
                    <a href="{{ route('dorms.index') }}" class="nav-link {{ request()->routeIs('dorms.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">home_work</i>
                        <span>Dormitories</span>
                    </a>
                </li>
                @endcanany

                {{-- Pins --}}
                @canany($tenant_permissions['pins'])
                <li class="nav-item nav-item-submenu {{ request()->routeIs('pins.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">password</i>
                        <span>Pins</span>
                    </a>

                    <ul class="nav nav-group-sub" data-submenu-title="Manage Pins">
                        @can('generate_pins')
                        <li class="nav-item">
                            <a href="{{ route('pins.create') }}" class="nav-link {{ request()->routeIs('pins.create') ? 'active' : '' }}">
                                Generate Pins
                            </a>
                        </li>
                        @endcan

                        @can('view_pins')
                        <li class="nav-item">
                            <a href="{{ route('pins.index') }}" class="nav-link {{ request()->routeIs('pins.index') ? 'active' : '' }}">
                                View Pins
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Users --}}
                @canany($tenant_permissions['users'])
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">group</i>
                        <span>Users</span>
                    </a>
                </li>
                @endcan

                {{-- Query Builder --}}
                @canany($tenant_permissions['query_builders'])
                <li class="nav-item">
                    <a href="{{ route('query_builder.index') }}" class="nav-link {{ request()->routeIs('query_builder.*') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">join</i>
                        <span>Query Builder</span>
                    </a>
                </li>
                @endcanany

                {{-- Settings --}}
                @canany(array_merge($tenant_permissions['settings'], $tenant_permissions['roles']))
                <li class="nav-item nav-item-submenu {{ request()->routeIs('settings.*', 'permissions.*', 'roles.*') ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="material-symbols-rounded">settings</i>
                        <span>Settings</span>
                    </a>
                    <ul class="nav nav-group-sub" data-submenu-title="Settings">
                        @can('view_settings')
                        <li class="nav-item">
                            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                                App
                            </a>
                        </li>
                        @endcan
                        @can('manage_roles')
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                Roles
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- My Children --}}
                @canany($tenant_permissions['mies'])
                <li class="nav-item">
                    <a href="{{ route('parent.my_children') }}" class="nav-link {{ request()->routeIs('parent.my_children', 'marks.year_selector', 'assessments.year_selector', 'payments.status', 'students.show', 'marks.show', 'assessments.show') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">account_child_invert</i>
                        <span>My Children</span>
                    </a>
                </li>
                @endcanany

                @else

                {{-- Central Menu --}}
                @include('pages.central.menu')
                @endif
            </ul>
        </div>
    </div>
</div>
