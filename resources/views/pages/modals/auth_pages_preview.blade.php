<!-- Modal - Login and related pages preview for texts and background -->
<div class="modal fade" id="auth-pages-preview" tabindex="-1" aria-label="Preview login and related pages - texts and background" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content position-absolute bottom-0 h-100 w-100">
            <div class="modal-header">
                <strong>Showing Preview</strong>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">Close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body p-0 h-100">
                    @php
                    $preview_route = Usr::tenancyInitialized() ? route('settings.preview_login_form') : route('settings_non_tenancy.preview_login_form');
                    @endphp
                    <iframe class="user-select-none pointer-events-none" src="{{ $preview_route }}" allowfullscreen width="100%" height="100%"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
