<div class="navbar navbar-expand-lg login">
    <span class="navbar-text d-flex">
        <span class="text-color-custom">&copy; {{ date('Y') }}, &nbsp</span>
        <a href="#" class="text-color-custom">
            <span class="d-none d-md-block">{{ config('app.name') }}.</span>
            <span class="d-block d-md-none">{{ Qs::getStringAbbreviation(config('app.name')) }}</span>
        </a>
    </span>

    <ul class="navbar-nav ml-lg-auto">
        @if (request()->hasCookie('tenant'))
            <li class="nav-item">
                <a
                    href="javascript:;"
                    onclick="document.getElementById('tenant-logout-form').submit()"
                    class="navbar-nav-link text-color-custom"
                    ><i class="material-symbols-rounded mr-2">exit_to_app</i
                    ><span class="d-none d-md-block">{{ 'Back to Central' }} </span></a>
            </li>
            <form method="POST" id="tenant-logout-form" action="{{ route('tenant.leave') }}" class="hidden">
                @csrf
            </form>
        @endif
        <li class="nav-item">
            <a href="{{ route('pages.privacy_policy') }}" class="navbar-nav-link text-color-custom" target="_blank"
                ><i class="material-symbols-rounded mr-2">policy</i
                ><span class="d-none d-md-block">{{ 'Privacy Policy' }} </span></a>
        </li>
        <li class="nav-item">
            <a href="{{ route('pages.terms_of_use') }}" class="navbar-nav-link text-color-custom" target="_blank"
                ><i class="material-symbols-rounded mr-2">gavel</i
                ><span class="d-none d-md-block">{{ 'Terms of Use' }} </span></a>
        </li>
        <li class="nav-item">
            <a
                href="javascript:;"
                class="navbar-nav-link text-color-custom"
                id="contact-link"
                data-toggle="modal"
                data-target="#admin-contact-modal"
                ><i class="material-symbols-rounded mr-2">call</i>
                <span class="d-none d-md-block"> {{ 'Contact Admin' }} </span></a>
        </li>
    </ul>
</div>

<script type="text/javascript">
    $('#exam-announce').marquee({
        direction: 'left',
        pauseOnHover: true,
        speed: 100,
    });
</script>
