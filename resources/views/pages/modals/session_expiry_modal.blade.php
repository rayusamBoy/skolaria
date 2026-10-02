<div
    class="modal fade"
    id="session-expiry-modal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="session-expiry-title"
    aria-hidden="true"
    data-backdrop="static"
    data-keyboard="false"
>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="session-expiry-title">
                    <i class="material-symbols-rounded mr-1 align-middle">timer</i>
                    Session about to expire
                </h5>
            </div>
            <div class="modal-body text-center">
                <p class="mb-2">
                    You’ve been idle for a while. To keep working in this tab, please extend your session.
                </p>
                <p class="text-muted mb-3" id="session-countdown-text">60 seconds remaining</p>
                <div class="progress" style="height: 6px">
                    <div
                        class="progress-bar bg-warning"
                        id="session-countdown-bar"
                        role="progressbar"
                        style="width: 100%"
                    ></div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="logout-now-btn">
                    <i class="material-symbols-rounded mr-1 align-middle">logout</i>
                    Log out now
                </button>
                <button type="button" class="btn btn-primary" id="extend-session-btn">
                    <i class="material-symbols-rounded mr-1 align-middle">autorenew</i>
                    Continue Session
                </button>
            </div>
        </div>
    </div>
</div>
