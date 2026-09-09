<!-- Admin Modal -->
<div
    class="modal fade"
    id="admin-contact-modal"
    tabindex="-1"
    aria-labelledby="admin-contacts-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog admin modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h3 class="modal-title" id="admin-contacts-modal-title">
                    <strong class="text-warning">{{ "Admin Contacts" }}</strong>
                </h3>
            </div>

            <div class="modal-body pt-0 pb-0">
                <div class="card contact m-0 border-0 bg-transparent">
                    <div class="card-body p-0">
                        <div class="card-text">
                            <h4><strong>{{ "Welcome!" }}</strong></h4>
                            <p>{{ "Need assistance? Reach out through any of the channels below." }}</p>

                            <h5 class="mt-3">{{ "Contact options" }}</h5>
                            <ul class="list-unstyled">
                                @php
                                    $whatsapp = $settings->firstWhere('key', 'admin_whatsapp_link')->value ?? null;
                                    $email = $settings->firstWhere('key', 'admin_email')->value ?? null;
                                    $fb = $settings->firstWhere('key', 'admin_facebook_link')->value ?? null;
                                    $x = $settings->firstWhere('key', 'admin_x_link')->value ?? null;
                                    $github = $settings->firstWhere('key', 'admin_github_link')->value ?? null;
                                @endphp

                                @if ($whatsapp)
                                    <li class="pb-2">
                                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer">
                                            <i class="material-symbols-rounded me-1 align-middle">chat</i>
                                            <span class="align-middle">WhatsApp</span>
                                        </a>
                                    </li>
                                @endif

                                @if ($email)
                                    <li class="pb-2">
                                        <a href="mailto:{{ $email }}">
                                            <i class="material-symbols-rounded me-1 align-middle">mail</i>
                                            <span class="align-middle">{{ $email }}</span>
                                        </a>
                                    </li>
                                @endif

                                @if ($fb || $x || $github)
                                    <h5 class="text-lightslategray mt-2">{{ "Social media" }}</h5>
                                    @if ($fb)
                                        <li class="pb-2">
                                            <a href="{{ $fb }}" target="_blank" rel="noopener noreferrer">
                                                <i class="material-symbols-rounded me-1 align-middle">share</i>
                                                <span class="align-middle">Facebook</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($x)
                                        <li class="pb-2">
                                            <a href="{{ $x }}" target="_blank" rel="noopener noreferrer">
                                                <i class="material-symbols-rounded me-1 align-middle">work</i>
                                                <span class="align-middle">X</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($github)
                                        <li class="pb-2">
                                            <a href="{{ $github }}" target="_blank" rel="noopener noreferrer">
                                                <i class="material-symbols-rounded me-1 align-middle">code</i>
                                                <span class="align-middle">GitHub</span>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-center">
                <button type="button text-inherit" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>
