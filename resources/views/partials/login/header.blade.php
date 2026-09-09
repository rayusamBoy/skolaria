<div class="navbar login">
    <div class="mt-2 mr-5">
        <a href="{{ route('login') }}" class="d-inline-block">
            <h4 class="text-bold d-none d-md-block text-color-custom">
                {{ $settings->firstWhere('key', 'system_name')->value }}
            </h4>
            <h4 class="text-bold d-block d-md-none text-color-custom">
                {{ $settings->firstWhere('key', 'system_title')?->value ?? Qs::getStringAbbreviation() }}
            </h4>
        </a>
    </div>

    <a
        href="{{ $is_tenant_context ? route('home') : route('central_home') }}"
        class="navbar-nav-link d-flex text-color-custom"
    ><span class="mr-1">{{ "Home" }}</span><i class="material-symbols-rounded">home</i></a>
</div>
