import { initializeApp } from "firebase/app";
import {
    deleteToken,
    getMessaging,
    getToken,
    onMessage,
} from "firebase/messaging";
import "../../public/assets/js/firebase-config";

// ----------------------------------------------------------------------
// Initialize Firebase
// ----------------------------------------------------------------------
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);
const vapidKey = import.meta.env.VITE_VAPID_KEY;

// ----------------------------------------------------------------------
// DOM Elements
// ----------------------------------------------------------------------
const tokenDiv = "token-div";
const permissionDiv = "permission-div";
const requestPermissionButton = document.getElementById(
    "request-permission-button",
);
const deleteTokenButton = document.getElementById("delete-token-button");
const tokenInfo = document.getElementById("token-info");
const unreadNotsList = document.querySelectorAll(".unread-notifications-list");

// ----------------------------------------------------------------------
// Helper: show / hide by ID
// ----------------------------------------------------------------------
const showHideDiv = (divId, show) => {
    const div = document.querySelector(`#${divId}`);
    if (div) {
        div.style.display = show ? "block" : "none";
    }
};

// ----------------------------------------------------------------------
// Helper: update token info message and alert style
// ----------------------------------------------------------------------
const showTokenInfo = (info) => {
    if (!tokenInfo) return;
    tokenInfo.textContent = info;

    // Ensure the token info container is visible
    const container = tokenInfo.parentElement?.parentElement;
    if (container) container.style.display = "block";

    const alertBox = tokenInfo.parentElement;
    if (alertBox) {
        alertBox.classList.remove(
            "alert-warning",
            "alert-success",
            "alert-info",
        );
        const alertType = info.includes("Error")
            ? "warning"
            : info.includes("Success")
              ? "success"
              : "info";
        alertBox.classList.add(`alert-${alertType}`);
    }
};

// ----------------------------------------------------------------------
// Server communication (async)
// ----------------------------------------------------------------------
const sendTokenToServer = async (currentToken, method = "POST") => {
    console.log("Sending token to server...", currentToken);
    return await updateTokenToServer(currentToken, method);
};

const updateTokenToServer = async (currentToken, method) => {
    try {
        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;
        const response = await fetch(firebase_url, {
            headers: {
                "X-CSRF-Token": csrfToken,
                "Content-Type": "application/json",
            },
            credentials: "same-origin",
            method,
            body: JSON.stringify({ token: currentToken }),
        });
        return response.ok;
    } catch (error) {
        console.error("Server communication error:", error);
        return false;
    }
};

// ----------------------------------------------------------------------
// UI state helpers
// ----------------------------------------------------------------------
const updateUIForPushEnabled = () => {
    showHideDiv(tokenDiv, true);
    showHideDiv(permissionDiv, false);
    showTokenInfo(
        "You’ve granted permission for web push notifications. To disable them, adjust the settings manually in your device.",
    );
};

const updateUIForPushPermissionRequired = () => {
    showHideDiv(tokenDiv, false);
    showHideDiv(permissionDiv, true);
};

// ----------------------------------------------------------------------
// Request permission (async)
// ----------------------------------------------------------------------
const requestPermission = async () => {
    console.log("Requesting permission...");
    showTokenInfo("Requesting permission...");
    showHideDiv(tokenDiv, false);

    try {
        const permission = await Notification.requestPermission();
        if (permission === "granted") {
            console.log("Notification permission granted.");
            showTokenInfo(
                "Success; notification permission granted.",
                "success",
            );
            resetUI(); // re-read token state
        } else {
            console.info(
                `Permission ${permission} received; notifications disabled.`,
            );
            showTokenInfo(
                `Error; unable to get permission to notify, permission ${permission}. Please enable notifications manually.`,
                "warning",
            );
            showHideDiv("delete-token-button", false);
        }
    } catch (error) {
        console.error("Permission request error:", error);
        showTokenInfo("Error requesting permission.", "error");
    }
};

// ----------------------------------------------------------------------
// Delete token (async)
// ----------------------------------------------------------------------
const deleteTokenFromFirebase = async () => {
    showTokenInfo("Deleting...");
    try {
        const currentToken = await getToken(messaging, { vapidKey });
        await deleteToken(messaging);
        await sendTokenToServer(currentToken, "DELETE");
        resetUI("Token deleted. Requesting a new one...");
    } catch (error) {
        console.error("Error deleting token:", error);
        showTokenInfo("Error unable to delete token.");
    }
};

// ----------------------------------------------------------------------
// Reset / initial UI load (async)
// ----------------------------------------------------------------------
const resetUI = async (message = "Loading...") => {
    showTokenInfo(message);
    try {
        const currentToken = await getToken(messaging, { vapidKey });
        if (currentToken) {
            // Token exists – push is already enabled
            await sendTokenToServer(currentToken, "POST");
            updateUIForPushEnabled();
        } else {
            console.log(
                "No registration token available. Request permission to generate one.",
            );
            showTokenInfo(
                "No registration token available. Request permission to generate one.",
            );
            updateUIForPushPermissionRequired();
        }
    } catch (err) {
        console.error("Error retrieving token:", err);
        showTokenInfo("Error while retrieving registration token.");
        showHideDiv("delete-token-button", false);
        showHideDiv(permissionDiv, false);
    }
};

// ----------------------------------------------------------------------
// Incoming message handler
// ----------------------------------------------------------------------
onMessage(messaging, (payload) => {
    console.log("Message received. ", payload);
    updateUI(payload);
});

// ----------------------------------------------------------------------
// Notification count and list updates
// ----------------------------------------------------------------------
const updateNotificationsCount = (el, operation) => {
    const currentCount = parseInt(el.innerText || el.textContent, 10);
    el.innerHTML = operation === "add" ? currentCount + 1 : currentCount - 1;
};

const updateUI = (payload) => {
    const unreadCountEls = document.querySelectorAll(
        `.unread-${payload.data.type}-count`,
    );
    if (unreadCountEls.length > 0) {
        unreadCountEls.forEach((el) => updateNotificationsCount(el, "add"));

        unreadNotsList.forEach((list) => {
            if (payload.data.type === "notifications") {
                const li = document.createElement("li");
                const strong = document.createElement("strong");
                strong.textContent = payload.data.title;

                const a = document.createElement("a");
                if (payload.data.url) a.href = payload.data.url;
                a.target = "_blank";
                a.textContent = payload.data.url_title;
                a.appendChild(strong);

                const small = document.createElement("small");
                small.className = "float-right";
                small.textContent = payload.data.created_at;

                li.appendChild(a);
                li.appendChild(small);
                list.appendChild(li);
            }
        });
    }

    // Handle notification marked as read
    if (payload.data.type === "notification_marked_as_read") {
        document
            .querySelectorAll(`.unread-${payload.data.id}`)
            .forEach((li) => li.remove());
        document
            .querySelectorAll(".unread-notifications-count")
            .forEach((countEl) => updateNotificationsCount(countEl, "sub"));
    }

    // Tenancy-specific message
    if (payload.data.type === "tenancy") {
        pop({
            title: payload.data.title,
            msg: payload.data.body,
            type: "info",
            timer: 0,
        });
    }
};

// ----------------------------------------------------------------------
// Event listeners
// ----------------------------------------------------------------------
if (requestPermissionButton) {
    requestPermissionButton.addEventListener("click", requestPermission);
}
if (deleteTokenButton) {
    deleteTokenButton.addEventListener("click", deleteTokenFromFirebase);
}

// ----------------------------------------------------------------------
// Start the UI
// ----------------------------------------------------------------------
resetUI();
