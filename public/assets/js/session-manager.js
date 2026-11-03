/**
 * Multi-tenant PWA session manager.
 *
 * Behaviour:
 *  - The server tells us when the session expires (meta tag).
 *  - We ping the server on user activity (throttled by PING_INTERVAL).
 *  - A Bootstrap modal warns ~60 s before expiry.
 *  - If the user does nothing → the server is asked to force-logout and we
 *    redirect to /login. No local token is ever stored.
 *  - Remember-me users skip the countdown entirely.
 *  - Offline: handled entirely by the offline-preference feature (Workbox +
 *    the user's own choice). This script does not gate cached pages.
 */
(function ($) {
    "use strict";

    // ------------------------------------------------------------------
    // Meta tag reads
    // ------------------------------------------------------------------
    const meta = (name) =>
        document.querySelector(`meta[name="${name}"]`)?.content ?? null;

    const SESSION_LIFETIME =
        Number(meta("session-lifetime")) * 1000 || 30 * 60 * 1000;
    const SERVER_EXPIRES_AT = Number(meta("session-expires-at")) * 1000 || 0;
    const SERVER_REMEMBER = meta("session-remember") === "1";
    const PING_URL = meta("session-ping-url");
    const STATUS_URL = meta("session-status-url");
    const EXPIRED_URL = meta("session-expired-url") || "/session/expired";
    const USER_ID = Number(meta("auth-user-id")) || null;
    const AUTH_TOKEN = meta("auth-token");
    const RETURN_KEY = "session_return_url";
    const IDENTITY_KEY = "session_reauth_identity";

    if (!PING_URL || !USER_ID) return; // Not on an authenticated page

    // ------------------------------------------------------------------
    // Configuration
    // ------------------------------------------------------------------
    const WARNING_BEFORE = 60 * 1000; // show modal 60 s before expiry
    const PING_INTERVAL = Math.min(
        Math.max(SESSION_LIFETIME / 2, 30_000),
        120_000,
    ); // 30 s … 2 min
    const ACTIVITY_DEBOUNCE = 2_000;
    const COUNTDOWN_TICK = 2_000;

    const STORAGE_EXPIRES = "session_expires_at";
    const STORAGE_REMEMBER = "session_remember";

    const isOffline = () => !navigator.onLine;

    // ------------------------------------------------------------------
    // State
    // ------------------------------------------------------------------
    let sessionExpiresAt = isOffline()
        ? readLocalExpiry()
        : SERVER_EXPIRES_AT ||
          readLocalExpiry() ||
          Date.now() + SESSION_LIFETIME;

    let remember;

    if (isOffline()) {
        remember = localStorage.getItem(STORAGE_REMEMBER) === "1";
    } else {
        remember = SERVER_REMEMBER;
        localStorage.setItem(STORAGE_REMEMBER, remember ? "1" : "0");
    }

    let warningShown = false;
    let lastPingTime = 0;
    let countdownTimer = null;
    let activityTimer = null;
    let redirecting = false;

    // ------------------------------------------------------------------
    // Seed the reauth identity (sessionStorage, per-tab)
    // ------------------------------------------------------------------
    if (AUTH_TOKEN && USER_ID) {
        try {
            sessionStorage.setItem(
                IDENTITY_KEY,
                JSON.stringify({
                    user_id: USER_ID,
                    token: AUTH_TOKEN,
                }),
            );
        } catch (_) {
            /* storage disabled, ignore */
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    function readLocalExpiry() {
        const stored = Number(localStorage.getItem(STORAGE_EXPIRES));
        return stored > 0 ? stored : 0;
    }

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || "";
    }

    function remainingSeconds() {
        return Math.max(0, Math.floor((sessionExpiresAt - Date.now()) / 1000));
    }

    function persistExpiry() {
        localStorage.setItem(STORAGE_EXPIRES, String(sessionExpiresAt));
    }

    // ------------------------------------------------------------------
    // Countdown & modal
    // ------------------------------------------------------------------
    function checkSessionTime() {
        if (redirecting) return;

        const remainingSec = remainingSeconds();

        if (remainingSec <= 0) {
            redirectToLogin();
            return;
        }

        if (
            !remember &&
            !warningShown &&
            remainingSec * 1000 <= WARNING_BEFORE
        ) {
            warningShown = true;
            showExpiryModal(remainingSec);
        }

        if (warningShown) updateModalCountdown(remainingSec);
    }

    function showExpiryModal(initialSecondsLeft) {
        updateModalCountdown(initialSecondsLeft);
        $("#session-expiry-modal").modal("show");
    }

    function updateModalCountdown(remainingSec) {
        $("#session-countdown-text").text(`${remainingSec} seconds remaining`);
        const percent = Math.min(
            100,
            ((remainingSec * 1000) / WARNING_BEFORE) * 100,
        );
        $("#session-countdown-bar").css("width", `${percent}%`);
    }

    function closeExpiryModal() {
        try {
            const $modal = $("#session-expiry-modal");
            if ($modal.length && $modal.hasClass("show")) $modal.modal("hide");
        } catch (_) {
            /* never let a UI error block the redirect */
        }
    }

    // ------------------------------------------------------------------
    // Extend
    // ------------------------------------------------------------------
    function resetSessionWarning() {
        closeExpiryModal();
        sessionExpiresAt = Date.now() + SESSION_LIFETIME;
        persistExpiry();
        warningShown = false;
    }

    // ------------------------------------------------------------------
    // Ping
    // ------------------------------------------------------------------
    function sendSessionPing(force) {
        if (isOffline()) return;

        const now = Date.now();
        if (!force && now - lastPingTime < PING_INTERVAL) return;
        lastPingTime = now;

        $.ajax({
            global: false,
            method: "POST",
            url: PING_URL,
            headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
            dataType: "json",
        })
            .done((data) => {
                if (data && data.expires_at) {
                    sessionExpiresAt = data.expires_at * 1000;
                    remember = !!data.remember;
                    persistExpiry();
                    localStorage.setItem(
                        STORAGE_REMEMBER,
                        remember ? "1" : "0",
                    );
                }
            })
            .fail((xhr) => {
                if (xhr.status === 401) redirectToLogin();
            });
    }

    // ------------------------------------------------------------------
    // Activity
    // ------------------------------------------------------------------
    function onUserActivity() {
        if (redirecting) return;
        if (warningShown) return;
        sendSessionPing(false);
    }

    function debouncedActivity() {
        clearTimeout(activityTimer);
        activityTimer = setTimeout(onUserActivity, ACTIVITY_DEBOUNCE);
    }

    // ------------------------------------------------------------------
    // Redirect — force a server-side logout to avoid guest-middleware bounce
    // ------------------------------------------------------------------
    function redirectToLogin() {
        const hostname = window.location.hostname;
        const isLocalHost = ["localhost", "127.0.0.1", "::1"].includes(
            hostname,
        );

        if (redirecting || isLocalHost) return;
        redirecting = true;

        try {
            clearInterval(countdownTimer);
        } catch (_) {}
        closeExpiryModal();

        // Throttle double redirects
        const last = Number(sessionStorage.getItem("session_redirect_at") || 0);
        if (Date.now() - last < 5_000) return;
        sessionStorage.setItem("session_redirect_at", String(Date.now()));

        // Remember the current URL so we can return after reauth
        try {
            sessionStorage.setItem(RETURN_KEY, window.location.href);
        } catch (_) {}

        // Send it to the server too — the server needs it after logout
        const url = new URL(EXPIRED_URL, window.location.origin);
        url.searchParams.set("return", window.location.href);
        window.location.replace(url.toString());
    }

    // ------------------------------------------------------------------
    // Bootstrap from server
    // ------------------------------------------------------------------
    function syncFromServer(cb) {
        if (isOffline() || !STATUS_URL) {
            cb && cb();
            return;
        }
        $.ajax({
            global: false,
            url: STATUS_URL,
            method: "GET",
            dataType: "json",
            headers: { Accept: "application/json" },
        })
            .done((data) => {
                if (data && data.expires_at) {
                    sessionExpiresAt = data.expires_at * 1000;
                    remember = !!data.remember;
                    persistExpiry();
                    localStorage.setItem(
                        STORAGE_REMEMBER,
                        remember ? "1" : "0",
                    );
                }
            })
            .fail((xhr) => {
                if (xhr.status === 401) redirectToLogin();
            })
            .always(() => cb && cb());
    }

    // ------------------------------------------------------------------
    // Event bindings
    // ------------------------------------------------------------------
    $(document).on(
        "mousemove keydown click scroll touchstart",
        debouncedActivity,
    );

    document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "visible") {
            syncFromServer(checkSessionTime);
        }
    });

    window.addEventListener("focus", checkSessionTime);

    window.addEventListener("online", () => {
        syncFromServer(() => sendSessionPing(true));
    });

    window.addEventListener("offline", () => {
        const local = readLocalExpiry();
        if (local > 0) sessionExpiresAt = local;
    });

    // Extend session button
    $("#extend-session-btn").on("click", function () {
        const $btn = $(this);
        $btn.prop("disabled", true).text("Extending…");

        $.post({
            global: false,
            url: PING_URL,
            headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
        })
            .done((data) => {
                if (data && data.expires_at) {
                    sessionExpiresAt = data.expires_at * 1000;
                    remember = !!data.remember;
                    persistExpiry();
                }
                resetSessionWarning();
                if (window.toastrMessage) {
                    toastrMessage(null, {
                        type: "success",
                        msg: "Your session has been extended.",
                    });
                }
            })
            .fail(() => {
                if (window.toastrMessage) {
                    toastrMessage(null, {
                        type: "error",
                        msg: "Failed to extend session. Redirecting…",
                    });
                }
                setTimeout(() => redirectToLogin(), 1500);
            })
            .always(() => {
                $btn.prop("disabled", false).text("Continue Session");
            });
    });

    // Logout now
    $("#logout-now-btn").on("click", function () {
        if (window.handleLogout) return window.handleLogout();
        window.location.href = "/logout";
    });

    // Global AJAX: any 401 → redirect
    $(document).ajaxError((event, xhr) => {
        if (xhr.status === 401) redirectToLogin();
    });

    // ------------------------------------------------------------------
    // Start
    // ------------------------------------------------------------------
    syncFromServer(() => {
        if (!remember)
            countdownTimer = setInterval(checkSessionTime, COUNTDOWN_TICK);
        checkSessionTime();
    });

    // ------------------------------------------------------------------
    // Logout
    // ------------------------------------------------------------------
    window.handleLogout = async function () {
        localStorage.removeItem(STORAGE_EXPIRES);
        localStorage.removeItem(STORAGE_REMEMBER);
        sessionStorage.clear();

        if ("caches" in window) {
            try {
                const keys = await caches.keys();
                await Promise.all(keys.map((k) => caches.delete(k)));
            } catch (_) {
                /* ignore */
            }
        }

        if (navigator.serviceWorker?.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "CLEAR_CACHES",
            });
        }

        const token =
            document.querySelector('meta[name="csrf-token"]')?.content || "";
        const logoutUrl =
            document.querySelector('meta[name="logout-url"]')?.content ||
            "/logout";

        try {
            await fetch(logoutUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": token,
                    Accept: "text/html",
                },
                credentials: "same-origin",
                redirect: "manual",
            });
        } catch (_) {
            /* even if it fails, still navigate away */
        }

        window.location.replace("/login");
    };
})(jQuery);
