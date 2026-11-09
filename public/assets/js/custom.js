"use strict";

/**
 *-------------------------------------------------------------
 * Set up the CSRF token header for all AJAX requests in this app
 *-------------------------------------------------------------
 */
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

/**
 *-------------------------------------------------------------
 * Initialize float thead plugin for tables with class "float-head"
 *-------------------------------------------------------------
 */
if (typeof $.fn.floatThead !== "undefined") {
    $(() => {
        const $floatHeads = $("table.float-head");

        $floatHeads.floatThead();

        const observer = new ResizeObserver(() => {
            $floatHeads.floatThead("reflow");
        });

        $floatHeads.each(function () {
            observer.observe(this);
        });
    });
}

/**
 *-------------------------------------------------------------
 * Block UI method
 *-------------------------------------------------------------
 */
const blockUI = () =>
    $.blockUI({
        message: `<i class="mr-1 m-auto material-symbols-rounded spinner">progress_activity</i> <span class="align-middle">please wait...</span>`,
        css: {
            padding: "3px 3px 8px 3px",
            color: "var(--warning)",
            backgroundColor: "rgba(189, 189, 189, 0.05)",
            border: "none",
        },
    });

/**
 *-------------------------------------------------------------
 * Unblock UI method
 *-------------------------------------------------------------
 */
const unblockUI = () => $.unblockUI();

$(window).on("load", unblockUI);

/**
 *-------------------------------------------------------------
 * Block and Unblock the UI for all ajax requests
 *-------------------------------------------------------------
 */
$(document).ajaxStart(blockUI).ajaxStop(unblockUI);

/**
 *-------------------------------------------------------------
 * Initialize auto size plugin for textarea
 *-------------------------------------------------------------
 */
autosize($("textarea"));

$(document).ready(() => {
    /**
     *-------------------------------------------------------------
     * Form and Date Picker Handlers
     *-------------------------------------------------------------
     */
    $("form.page-block").on("submit", () => blockUI());

    $(".date-pick").datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
    });

    $(".date-pick-full-month-year").datepicker({
        format: "MM, yyyy",
        autoclose: true,
    });

    /**
     *-------------------------------------------------------------
     * Handle user role change on select.
     *-------------------------------------------------------------
     */
    $("select#roles").on("change", function () {
        const $fieldset = $(this).closest("fieldset");
        const $parentData = $fieldset.find("#parent-data");
        const $parentDataInputs = $parentData.find("input");

        const $lastTab = $(".steps ul li.last");
        const $submitBtn = $(".actions ul li:last-child");
        const $navigationBtns = $(".actions ul li").not(":last-child");

        // The line separating the step headers.
        const $stepsLineSeparator = $(".wizard > .steps > ul > li.current");

        // Get the selected options.
        const $selectedOptions = $(this).find("option:selected");

        // Check whether Parent is among the selected roles.
        const hasParentRole = $selectedOptions
            .toArray()
            .some((option) => option.text.trim().toLowerCase() === "parent");

        // Check whether Parent is the only selected role.
        const isParentOnly = hasParentRole && $selectedOptions.length === 1;

        // Show or hide the parent controls.
        if (hasParentRole) {
            $parentData.stop(true, true).show(150);
            $parentDataInputs.prop("disabled", false);
        } else {
            $parentData.stop(true, true).hide(150);
            $parentDataInputs.prop("disabled", true);
        }

        // Adjust wizard navigation.
        if (isParentOnly) {
            // Parent is the only selected role.
            $lastTab.hide();
            $navigationBtns.hide();
            $stepsLineSeparator.addClass("d-li-current-none");
            $submitBtn.show();
        } else {
            // Parent is selected with other roles, or Parent is not selected.
            $lastTab.show();
            $navigationBtns.show();
            $submitBtn.hide();
            $stepsLineSeparator.removeClass("d-li-current-none");

            // Re-enable the Next button if it is disabled.
            const $nextBtn = $(".wizard > .actions > ul > li:nth-child(2)");

            if ($nextBtn.hasClass("disabled")) {
                $nextBtn
                    .removeClass("disabled")
                    .removeAttr("disabled")
                    .children("a")
                    .removeClass("disabled");
            }
        }
    });
});

/**
 *-------------------------------------------------------------
 * Toggle full screen handler
 *-------------------------------------------------------------
 */
$(".full-screen-handle").on("click", function (event) {
    event.preventDefault();
    if (
        (document.fullScreenElement && document.fullScreenElement !== null) ||
        (!document.mozFullScreen && !document.webkitIsFullScreen)
    ) {
        if (document.documentElement.requestFullScreen) {
            document.documentElement.requestFullScreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullScreen) {
            document.documentElement.webkitRequestFullScreen(
                Element.ALLOW_KEYBOARD_INPUT,
            );
        }
        $(this)
            .find("a")
            .attr("title", "Exit Full Screen")
            .find(".material-symbols-rounded")
            .text("fullscreen_exit");
    } else {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        }
        $(this)
            .find("a")
            .attr("title", "Request Full Screen")
            .find(".material-symbols-rounded")
            .text("fullscreen");
    }
});

/**
 *-------------------------------------------------------------
 * Dashboard counter up for numbers (counts)
 *-------------------------------------------------------------
 */
if (window.counterUp) {
    const counterUp = window.counterUp.default;
    const callback = (entries) => {
        entries.forEach((entry) => {
            const el = entry.target;
            if (entry.isIntersecting && !$(el).hasClass("is-visible")) {
                counterUp(el, {
                    duration: 2000,
                    delay: 16,
                });
                $(el).addClass("is-visible");
            }
        });
    };

    const IO = new IntersectionObserver(callback, { threshold: 1 });
    $(".counter").each((_, el) => IO.observe(el));
}

/**
 *-------------------------------------------------------------
 * Initialize popover and tooltip everywhere
 *-------------------------------------------------------------
 */
const initializePopover = () => $('[data-toggle="popover"]').popover();
const initializeTooltip = () => $('[data-toggle="tooltip"]').tooltip();
const hideAllPopover = () => $('[data-toggle="popover"]').popover("hide");

$(document).ready(() => {
    initializePopover();
    initializeTooltip();
});

/**
 *-------------------------------------------------------------
 * Student ID Card print checkboxes
 *-------------------------------------------------------------
 */
$(".st-id-checkbox").on("click", function () {
    const $form = $("form.print-selected");
    const checkedCount = $(":checkbox:checked").length;
    $form
        .find("button[type='submit']")
        .prop("disabled", checkedCount === 0)
        .toggleClass("cursor-not-allowed", checkedCount === 0);
    $form.find("#checked-count").html(checkedCount);
});

const enableElement = (selector) => $(selector).prop("disabled", false);
const disableElement = (selector) => $(selector).prop("disabled", true);
const updateSectionDisableState = () => {
    const values = $("#students-ids").val();
    return values.length
        ? disableElement("#sec_id_add_not_applicable, #section_id")
        : enableElement("#sec_id_add_not_applicable, #section_id");
};

const hide = (selector, duration = 0) => $(selector).hide(duration);
const show = (selector, duration = 0) => $(selector).show(duration);

/**
 *-------------------------------------------------------------
 * Message form (hide & show)
 *-------------------------------------------------------------
 */
const showFormCard = () => {
    show(".card#create-message", 500);
    hide("button#new-message", 50);
};
const hideFormCard = () => {
    hide(".card#create-message", 150);
    show("button#new-message", 250);
};

/**
 *-------------------------------------------------------------
 * Toggle element disabled state method
 *-------------------------------------------------------------
 */
const toggleElementDisabledState = (value, targetEl) => {
    $(targetEl).attr("disabled", value.length > 0);
};

/**
 *-------------------------------------------------------------
 * Validate students selection when creating or sending msgs
 *-------------------------------------------------------------
 */
$(document).on("change", "select#user_roles-ids", function () {
    const activeStudents = "students_active";
    const gradStudents = "students_graduated";
    const allStudents = "students";
    const values = $(this).val();
    let count = 0;

    values.forEach((value) => {
        if ([activeStudents, gradStudents, allStudents].includes(value))
            count++;
    });

    if (count > 1) {
        $("#user_roles-ids").val("").trigger("change");
        pop({
            msg: "Please, select only one option under students user role group.",
            type: "info",
        });
    }
});

/**
 *-------------------------------------------------------------
 * Promotion - Graduates
 *-------------------------------------------------------------
 */
$("button.opt-all-as-graduate").on("click", function () {
    const $form = $(this).siblings("form.page-block");
    $form.find('select option[value="G"]').prop("selected", true);
    $form.find(".select2-selection__rendered").text("Graduated");
});

/**
 *-------------------------------------------------------------
 * Do not show this again button handler.
 *-------------------------------------------------------------
 */
const $doNotShowButton = $(
    `<button type="button" class="btn btn-sm btn-info pt-0 pb-0 pr-1 pl-1 position-absolute right-0 font-size-xs do-not-show-again" style="bottom: -10px;" onclick="doNotShowAgain(this)">Do not show again</button>`,
);
const $hasDoNotShowAgainBtnAlert = $(".alert.has-do-not-show-again-button");
$hasDoNotShowAgainBtnAlert.append($doNotShowButton);
if (
    $hasDoNotShowAgainBtnAlert.hasClass("alert-styled-left") ||
    $hasDoNotShowAgainBtnAlert.hasClass("alert-styled-right")
) {
    $doNotShowButton.removeClass("right-0").addClass("right-neg1");
}

const doNotShowAgain = (el) => {
    const $parent = $(el).closest(".has-do-not-show-again-button");
    const id = $parent.attr("id");
    if (!id)
        return console.warn(
            "Missing required unique id attribute for the element.",
        );
    if ($(`[id="${id}"]`).length > 1)
        return console.warn(
            `Multiple ${id} id attributes were found. Id needs to be unique.`,
        );
    let ids = JSON.parse(
        Cookies.get("do_not_show_els_with_these_ids_again") || "[]",
    );
    ids.push(id);
    Cookies.set("do_not_show_els_with_these_ids_again", JSON.stringify(ids));
    updateHiddenIDs(ids, $parent);
};

const updateHiddenIDs = (ids, $el) => {
    const $btn = $el.find("button.do-not-show-again");
    $btn.hide();
    $.ajax({
        global: false,
        url: update_hidden_alerts_url,
        method: "POST",
        data: { hidden_alert_ids: ids },
        success: (resp) => {
            if (resp.ok && resp.msg) {
                removeElement($el);
                flash({
                    msg: "Done. To view this again, go to <strong>My Account</strong> >> <strong>Preferences</strong> to reveal all hidden messages.",
                    type: "info",
                });
                return console.log(resp.msg);
            }
            $btn.show(250);
            flash({
                msg: "Something went wrong, can't hide alert.",
                type: "warning",
            });
            console.warn("Something went wrong.");
        },
        error: (xhr, status, error) => {
            $btn.show(250);
            flash({ msg: "Failed to update hidden alert IDs.", type: "error" });
            console.error("Failed to update hidden alert IDs.", status, error);
        },
    });
};

/**
 *-------------------------------------------------------------
 * Handle alert messages reveal on button click action in settings.
 *-------------------------------------------------------------
 */
$("button#clear-do-not-show-again-alert-msgs").click(function () {
    Modal.fire({
        title: "Are you sure?",
        text: "This will reveal all hidden alert messages.",
        icon: "warning",
        confirmButtonText: "Sure",
        customClass: { confirmButton: "bg-danger" },
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: clear_hidden_alerts_url,
                method: "POST",
                success: (response) => {
                    Cookies.remove("do_not_show_els_with_these_ids_again");
                    flash({
                        msg: "Success. All alert messages are now displayed.",
                        type: "success",
                    });
                },
                error: (xhr, status, error) => {
                    flash({
                        msg: "Something went wrong, cannot clear IDs.",
                        type: "error",
                    });
                    console.error(
                        "Failed to clear hidden alert IDs data.",
                        status,
                        error,
                    );
                },
            });
        }
    });
});

const updatedoNotShowAgainElsState = () => {
    $(document).ready(() => {
        const ids = JSON.parse(
            Cookies.get("do_not_show_els_with_these_ids_again") || "[]",
        );
        ids.forEach((id) => {
            if (id) $(`#${id}`).remove();
        });
    });
};

updatedoNotShowAgainElsState();

/**
 *-------------------------------------------------------------
 * Remove element method - with animation
 *-------------------------------------------------------------
 */
const removeElement = ($el) =>
    $el.fadeOut(500, function () {
        $(this).remove();
    });

/**
 *-------------------------------------------------------------
 * Handle go to bottom btn click event
 *-------------------------------------------------------------
 */
$(function () {
    // Cache the "go to bottom" button element using a more modern variable name
    const $goToBottomBtn = $(".go-to-bottom button");

    $goToBottomBtn.on("click", (e) => {
        e.preventDefault();
        scrollToBottom();
    });

    $(window).on("scroll", () => {
        const scrollBottom = $(window).scrollTop() + $(window).height();
        const scrollingElement = document.scrollingElement || document.body;
        const offsetFromBottom = 300;

        if (scrollingElement.scrollHeight - offsetFromBottom < scrollBottom) {
            $goToBottomBtn.fadeOut();
        } else {
            $goToBottomBtn.fadeIn();
        }
    });
});

/**
 *-------------------------------------------------------------
 * Handle exam number formats displaying on class type change
 *-------------------------------------------------------------
 */
$(document).on("change", "select#class-type", function () {
    const $numberFormatsDiv = $(".number-formats");
    const value = this.value;
    $numberFormatsDiv.find(".text-info").hide();
    $numberFormatsDiv.find(".div").removeClass("d-flex").addClass("d-none");
    $numberFormatsDiv
        .find(`.number-format-${value}`)
        .removeClass("d-none")
        .addClass("d-flex");
});

/**
 *-------------------------------------------------------------
 * Handle Exam CA fields displaying
 *-------------------------------------------------------------
 */
$(document).on("change", "select#has_assessment", function () {
    const value = parseInt(this.value, 10);
    if (typeof value != undefined && value === 1)
        $(".ca-setup")
            .show(150)
            .find("input[type='number']")
            .attr("required", "required");
    else
        $(".ca-setup")
            .hide(150)
            .find("input[type='number']")
            .removeAttr("required");
});

/**
 *-------------------------------------------------------------
 * System Sound Notification
 *-------------------------------------------------------------
 */
const errorSoundElement = document.getElementById("notification-sounds");
const notificationSound = new Audio();

const playNotificationSound = (soundName, condition = false) => {
    if (condition && $(errorSoundElement).data("allow_system_sounds") === 1) {
        notificationSound.src = `${$(errorSoundElement).data(
            "base_url",
        )}/${soundName}`;
        notificationSound.play();
    }
};

$(document).on("input", "table.data-table input", function () {
    const soundName = "error-call-to-attention.mp3";
    const maxVal = parseFloat($(this).attr("max"));
    const value = parseFloat($(this).val());
    const nameAttr = $(this).attr("name");

    if (value > maxVal) {
        playNotificationSound(soundName, true);
        sessionStorage.setItem(nameAttr, "");
        $(this)
            .val("")
            .blur()
            .attr("style", "border: 1px solid rgb(151, 23, 23) !important");
        return;
    } else {
        $(this).removeAttr("style");
    }
    sessionStorage.setItem(nameAttr, value);
});

// Restore values on page reload
const pageReloaded = window.performance
    .getEntriesByType("navigation")
    .some((nav) => nav.type === "reload");
if ($("table.data-table input").length > 0 && pageReloaded) {
    Object.keys(sessionStorage).forEach((key) => {
        if (
            key.startsWith("cw_") ||
            key.startsWith("hw_") ||
            key.startsWith("tt_")
        )
            $(".ca-tbody")
                .find(`input[name="${key}"]`)
                .val(sessionStorage[key]);
        if (key.startsWith("exm_"))
            $(".exam-tbody")
                .find(`input[name="${key}"]`)
                .val(sessionStorage[key]);
    });
}

/**
 *-------------------------------------------------------------
 * Handle auth pages texts and bg preview buttons
 *-------------------------------------------------------------
 */
const $authPagesInput = $("#login-and-related-pgs-bg-input");
const $authPagesPreviewDiv = $("div.auth-pages-preview");

$authPagesInput.on("change", function () {
    if (this.files.length >= 1) {
        $authPagesPreviewDiv.find("select, input").prop("disabled", false);
    } else {
        $authPagesPreviewDiv.find("select, input").prop("disabled", true);
    }
});

/**
 *-------------------------------------------------------------
 * Refresh button click event (target; show on standalone mode)
 *-------------------------------------------------------------
 */
$(document).on("click", ".refresh", function (e) {
    e.preventDefault();
    $(this).html(
        '<i class="material-symbols-rounded mt-auto mb-auto spinner">refresh</i>',
    );
    window.location.reload();
});

/**
 *-------------------------------------------------------------
 * Build and initialize JQuery calendar
 *-------------------------------------------------------------
 */
(function ($) {
    const date = new Date();
    const today = date.getDate();
    const months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];

    $(document).ready(function () {
        // Event listeners
        $(".right-button").on("click", () => nextYear(date));
        $(".left-button").on("click", () => prevYear(date));
        $(".month").on("click", function () {
            monthClick($(this), date);
        });
        $("#add-button").on("click", () => newEvent(date));
        // Set the current month as active
        $(".months-row > td").eq(date.getMonth()).addClass("active-month");
        // Initialize the calendar and show events
        initCalendar(date);
        const events = checkEvents(
            today,
            date.getMonth() + 1,
            date.getFullYear(),
        );
        showEvents(events, months[date.getMonth()], today);
    });

    const initCalendar = (date) => {
        $(".events-container").empty();

        const $tbody = $(".tbody").empty();
        const month = date.getMonth();
        const year = date.getFullYear();
        const dayCount = daysInMonth(month, year);
        const today = date.getDate();

        date.setDate(1);
        const firstDay = date.getDay();

        let $row = $("<tr class='table-row'></tr>");
        for (let i = 0; i < 35 + firstDay; i++) {
            const day = i - firstDay + 1;
            const $currDate = $("<td class='table-date'></td>");
            if (i % 7 === 0) {
                $tbody.append($row);
                $row = $("<tr class='table-row'></tr>");
            }
            if (i < firstDay || day > dayCount) {
                $currDate.addClass("nil");
            } else {
                $currDate.text(day);
                const events = checkEvents(day, month + 1, year);
                if (today === day && $(".active-date").length === 0) {
                    $currDate.addClass("active-date");
                    showEvents(events, months[month], day);
                }
                if (events.length !== 0) $currDate.addClass("event-date");
                $currDate.on("click", (e) =>
                    dateClick(events, months[month], day, e),
                );
            }
            $row.append($currDate);
        }
        $tbody.append($row);
        $(".year").text(year);
    };

    const daysInMonth = (month, year) => new Date(year, month + 1, 0).getDate();

    const dateClick = (events, month, day, e) => {
        $(".events-container").show(250);
        $("#dialog").hide(250);
        $(".active-date").removeClass("active-date");
        $(e.target).addClass("active-date");
        showEvents(events, month, day);
    };

    const monthClick = (monthElement, date) => {
        $(".events-container").show(250);
        $("#dialog").hide(250);
        $(".active-month").removeClass("active-month");
        monthElement.addClass("active-month");
        const newMonth = $(".month").index(monthElement);
        date.setMonth(newMonth);
        initCalendar(date);
    };

    const nextYear = (date) => {
        $("#dialog").hide(250);
        const newYear = date.getFullYear() + 1;
        $(".year").text(newYear);
        date.setFullYear(newYear);
        initCalendar(date);
    };

    const prevYear = (date) => {
        $("#dialog").hide(250);
        const newYear = date.getFullYear() - 1;
        $(".year").text(newYear);
        date.setFullYear(newYear);
        initCalendar(date);
    };

    const newEvent = (date) => {
        if ($(".active-date").length === 0) return;
        // Remove red error input on click
        $("input").on("click", function () {
            $(this).removeClass("error-input");
        });
        // Empty inputs and hide events
        $("#dialog input[type=text], #dialog textarea").val("");
        $(".events-container").hide(250);
        $("#dialog").show(250);

        $("#cancel-button").on("click", () => {
            $("#name, #count").removeClass("error-input");
            $("#dialog").hide(250);
            $(".events-container").show(250);
        });

        $("#ok-button")
            .off()
            .on("click", () => {
                const name = $("#name").val().trim();
                const count = $("#count").val().trim();
                const day = parseInt($(".active-date").text(), 10);
                // Basic form validation
                if (name.length === 0) {
                    $("#name").addClass("error-input");
                } else if (count.length === 0) {
                    $("#count").addClass("error-input");
                } else {
                    $("#dialog").hide(250);
                    newEventJson(name, count, date, day);
                    date.setDate(day);
                    initCalendar(date);
                }
            });
    };

    const newEventJson = (name, count, date, day) => {
        const event = {
            name,
            description: count,
            year: date.getFullYear(),
            month: date.getMonth() + 1,
            day,
        };
        events_data.events.push(event);
        addEventToDb(event);
    };

    const addEventToDb = (event) => {
        $.ajax({
            type: "POST",
            url: event_create_url,
            dataType: "json",
            data: event,
            success: (data) => {
                $(".events-container").html(data.msg).addClass("text-success");
            },
            error: (errorThrown) => {
                console.error(errorThrown);
                $(".events-container")
                    .text("Something went wrong. Event add failed!")
                    .addClass("text-danger");
            },
        });
    };

    // Display all events for a selected date
    const showEvents = (events, month, day) => {
        const $container = $(".events-container").empty().show(250);
        if (events.length === 0) {
            const $card = $("<div class='event-card'></div>");
            const $eventName = $(
                "<div class='event-name'>There are no Events Planned for " +
                    month +
                    " " +
                    day +
                    ".</div>",
            );
            $card
                .css({ "border-left": "10px solid #FF1744" })
                .append($eventName);
            $container.append($card);
        } else {
            events.forEach((event) => {
                const $card = $("<div class='event-card'></div>");
                const $eventName = $(
                    "<div class='event-name'>" + event.name + ":</div>",
                );
                const $eventDescription = $(
                    "<div class='event-description pl-1'>" +
                        event.description +
                        ".</div>",
                );
                let $eventStatus = "";
                switch (event.status) {
                    case "cancelled":
                        $eventStatus = $(
                            "<div class='event-cancelled text-danger pl-1'>(Cancelled)</div>",
                        );
                        break;
                    case "active":
                        $eventStatus = $(
                            "<div class='event-active text-orange pl-1'>(Active now)</div>",
                        );
                        break;
                    case "completed":
                        $eventStatus = $(
                            "<div class='event-completed text-success pl-1'>(Completed)</div>",
                        );
                        break;
                    default:
                        break;
                }
                $card
                    .append($eventName)
                    .append($eventStatus)
                    .append($eventDescription);
                $container.append($card);
            });
        }
    };

    const checkEvents = (day, month, year) => {
        return typeof events_data !== "undefined"
            ? events_data.events.filter(
                  (event) =>
                      event.day === day &&
                      event.month === month &&
                      event.year === year,
              )
            : [];
    };
})(jQuery);

/**
 *-------------------------------------------------------------
 * Reusable swal2 modal and toast
 *-------------------------------------------------------------
 */
if (typeof Swal !== "undefined") {
    const Modal = Swal.mixin({
        focusConfirm: false,
        returnFocus: false,
        reverseButtons: true,
        showCancelButton: true,
        cancelButtonText: "No, Cancel",
        customClass: {
            cancelButton: "bg-secondary",
        },
    });

    const Toast = Swal.mixin({
        toast: true,
        reverseButtons: true,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    window.Modal = Modal;
    window.Toast = Toast;
}

/**
 *-------------------------------------------------------------
 * Capitalize first letter of a string
 *-------------------------------------------------------------
 */
const capitalize = (string) => string.charAt(0).toUpperCase() + string.slice(1);

/**
 *-------------------------------------------------------------
 * Handle notices
 *-------------------------------------------------------------
 */
const updateNoticeStatus = (el) => {
    const $badge = $(el)
        .closest("div.notices")
        .siblings(".card-header")
        .find(".card-title .badge");
    // Remove class 'unviewed', turn off 'click' event handler, and remove iteration indicator.
    $(`#${el.id}`)
        .removeClass("unviewed")
        .off("click")
        .siblings(".iteration")
        .remove();
    // Update Badge
    $badge.text($badge.text() == 0 ? 0 : $badge.text() - 1);
};

$(document).on("click", ".notices button.unviewed", function () {
    setNoticeAsViwed(this);
});

const setNoticeAsViwed = (el) => {
    $.ajax({
        dataType: "json",
        method: "post",
        data: { id: el.id },
        url: set_notice_as_viewed_url,
        success: (resp) => (resp.ok === true ? updateNoticeStatus(el) : false),
    });
};

let hashChangedProgrammatically = false;

$(document).on("click", ".pagination a", function (event) {
    event.preventDefault();
    $("li").removeClass("active");
    $(this).parent("li").addClass("active");
    hashChangedProgrammatically = true;
    getNoticesData($(this).attr("href"));
});

const getNoticesData = (url) => {
    const status = url.includes("unviewed") ? "unviewed" : "viewed";
    const $notices = $(".notices").find(`#${status}`);
    const paginationMeta = $notices.data("pagination-meta") || "0:0:0";
    const [total, perPage, lastPage] = paginationMeta.split(":").map(Number);
    const pageParam = url.split("=")[1];
    const currentPage = pageParam ? parseInt(pageParam, 10) : 1;
    let to = perPage * currentPage;
    const from = currentPage === 1 ? 0 : to - perPage;
    to = currentPage === lastPage ? total : to;
    const duplicateTimes = to - from;

    // Create a deep clone of the notices for fallback
    const tempNotices = $notices.clone(true, true);

    $notices
        .empty()
        .append(
            `<div class="notices-loading">${noticesLoadingSkin(
                duplicateTimes,
                status,
                lastPage,
            )}</div>`,
        );
    $.ajax({
        global: false,
        url,
        type: "get",
        datatype: "html",
    })
        .done((data) => {
            $notices.replaceWith(data);
            window.location.hash = url;
        })
        .fail(() => {
            $notices.replaceWith(tempNotices);
            flash({ msg: "Sorry, something went wrong.", type: "error" });
        });
};

$(window).on("hashchange", () => {
    if (window.location.hash && !hashChangedProgrammatically) {
        const url = window.location.hash.replace("#", "");
        hashChangedProgrammatically = false;
        getNoticesData(url);
    }
});

function noticesLoadingSkin(duplicateTimes, status, linksCount) {
    const linkTpl = `<li class="page-item disabled">
                       <a class="page-link skeleton"></a>
                     </li>`;

    const links = Array(linksCount).fill(linkTpl).join("");

    const header = `
      <div class="card m-0 border-bottom-0 text-muted">
        <div class="card-header position-relative">
          <span class="float-left pr-10 status-styled">
            ${capitalize(status)}
          </span>
          <i class="text-muted float-right name skeleton"></i>
        </div>
        <div class="card-body p-1">`;

    // Card with iteration indicator
    const iterationTpl = `
      <div class="card mb-1">
        <div class="card-header">
          <h5 class="mb-0 d-flex">
            <span class="text-muted iteration skeleton mr-1"></span>
            <button class="btn btn-link w-100 pl-1 p-0 border-left-1 border-left-info">
              <span class="float-left pr-10 title skeleton"></span>
              <i class="text-muted float-right time skeleton"></i>
            </button>
          </h5>
        </div>
      </div>`;

    // Card without iteration indicator
    const noIterationTpl = `
      <div class="card mb-1">
        <div class="card-header">
          <h5 class="mb-0 d-flex">
            <button class="btn btn-link w-100 pl-1 p-0 border-left-1 border-left-info">
              <span class="float-left pr-10 title skeleton"></span>
              <i class="text-muted float-right time skeleton"></i>
            </button>
          </h5>
        </div>
      </div>`;

    const footer = `
        <div class="position-relative pt-2">
          <span class="float-left showing skeleton mb-1"></span>
          <span class="float-right">
            <nav>
              <ul class="pagination">
                <li class="page-item disabled">
                  <a class="page-link skeleton">‹</a>
                </li>
                ${links}
                <li class="page-item disabled">
                  <a class="page-link skeleton">›</a>
                </li>
              </ul>
            </nav>
          </span>
        </div>
      </div>
    </div>`;

    // Build body by repeating the chosen template
    const bodyTpl = status === "unviewed" ? iterationTpl : noIterationTpl;
    const body = bodyTpl.repeat(duplicateTimes);

    return `${header}${body}${footer}`;
}

/**
 *-------------------------------------------------------------
 * Update elements properties (enable/disable)
 *-------------------------------------------------------------
 */
const toggleElDisableState = (value, selector) =>
    value === "none" ? disableElement(selector) : enableElement(selector);

/**
 *-------------------------------------------------------------
 * Query builder handler events
 *-------------------------------------------------------------
 */
$("#return-to-query").on("click", (e) => {
    e.preventDefault();
    scrollTo("#query-box");
});

$("#secured-query")
    .on("mouseenter", function () {
        $(this).find("span").removeClass("text-secured");
    })
    .on("mouseleave", function () {
        $(this).find("span").addClass("text-secured");
    });

const removeClassCursorNotAllowed = (selector) =>
    $(selector).removeClass("cursor-not-allowed");

/**
 *-------------------------------------------------------------
 * Get table columns by table name
 *-------------------------------------------------------------
 */
function getTableColumns(tableName) {
    const $btn = $("button[type=submit]");
    toggleElDisableState("none", $btn);
    let url = get_table_cols_url.replace(":name", tableName);
    const $select = $("#select");
    const $where = $(".where");
    const $orderby = $(".orderby_column");

    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $where
                .empty()
                .append(
                    '<option disabled selected>where</option><option value="none" selected>none (default)</option>',
                );
            $.each(resp, (i, data) => {
                if (i !== "photo_path")
                    $where.append($("<option>", { value: i, text: data }));
            });
            $orderby
                .empty()
                .append('<option value="id" selected>id (default)</option>');
            $.each(resp, (i, data) => {
                if (i !== "photo_path")
                    $orderby.append($("<option>", { value: i, text: data }));
            });
            $select
                .empty()
                .append(
                    '<option disabled>select</option><option value="*" selected>all (default)</option>',
                );
            $.each(resp, (i, data) => {
                $select.append($("<option>", { value: i, text: data }));
            });
        },
    });
}

const alertInfo = (number) => {
    Toast.fire({
        position: "top-right",
        title: `${number} total records found.`,
        icon: "info",
        timer: 5000,
        showConfirmButton: false,
    });
};

/**
 *-------------------------------------------------------------
 * Get states by nationality id
 *-------------------------------------------------------------
 */
function getState(nalId, destination) {
    let url = get_state_url.replace(":id", nalId);
    const $state = destination ? $(destination) : $("#state_id");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $state.empty();
            $.each(resp, (i, data) => {
                $state.append(
                    $("<option>", { value: data.id, text: data.name }),
                );
            });
        },
    });
}

/**
 *-------------------------------------------------------------
 * Get LGA or cities by state id
 *-------------------------------------------------------------
 */
function getLGA(stateId, destination) {
    let url = get_lga_url.replace(":id", stateId);
    const $lga = destination ? $(destination) : $("#lga_id");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $lga.empty();
            $.each(resp, (i, data) => {
                $lga.append($("<option>", { value: data.id, text: data.name }));
            });
        },
    });
}

/**
 *-------------------------------------------------------------
 * Get students of a class by class id
 *-------------------------------------------------------------
 */
function getClassStudents(classId, destination) {
    let url = get_class_students_url.replace(":id", classId);
    const $section = destination ? $(destination) : $("#students-ids");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $section.empty();
            $.each(resp, (i, data) => {
                $section.append(
                    $("<option>", { value: data.id, text: data.name }),
                );
            });
        },
    });
}

/**
 *-------------------------------------------------------------
 * Get sections of a class by a class id
 *-------------------------------------------------------------
 */
function getClassSections(classId, destination) {
    let url = get_class_sections_url.replace(":id", classId);
    const $section = destination ? $(destination) : $("#section_id");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $section.empty();
            $.each(resp, (i, data) => {
                $section.append(
                    $("<option>", { value: data.id, text: data.name }),
                );
            });
            if (destination) {
                if (destination.includes("add_all"))
                    $section.append(
                        '<option value="all" title="All Sections">All</option>',
                    );
                if (destination.includes("add_not_applicable"))
                    $section.prepend(
                        '<option selected value=" ">Not Applicable</option>',
                    );
            }
        },
        error: () => {
            $section.empty();
        },
    });
}

/**
 *-------------------------------------------------------------
 * Get teachers of a class sections by a class id
 *-------------------------------------------------------------
 */
function getTeacherClassSections(classId, destination) {
    let url = get_teacher_class_sections_url.replace(":id", classId);
    const $section = destination ? $(destination) : $("#section_id");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $section.empty();
            $.each(resp, (i, data) => {
                $section.append(
                    $("<option>", { value: data.id, text: data.name }),
                );
            });
        },
    });
}

/**
 *-------------------------------------------------------------
 * Get subject section teacher
 *-------------------------------------------------------------
 */
function getSubjectSectionTeacher(subjectId, sectionId, destination) {
    let url = get_subject_section_teacher_url
        .replace(":sub_id", subjectId)
        .replace(":sec_id", sectionId);
    const $section = destination ? $(destination) : $("#teacher_id");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $section.children("option").each(function () {
                if ($(this).val() == resp.id) {
                    $(this).attr("selected", "selected");
                    $("#select2-teacher_id-container")
                        .text(resp.name)
                        .attr("title", resp.name);
                } else {
                    $(this).removeAttr("selected");
                }
            });
        },
    });
}

/**
 *-------------------------------------------------------------
 * Get exams of the particular year
 *-------------------------------------------------------------
 */
function getYearExams(year) {
    let url = get_year_exams_url.replace(":year", year);
    const $exam = $("#exam_id");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $exam.empty();
            $.each(resp, (i, data) => {
                $exam.append(
                    $("<option>", { value: data.id, text: data.name }),
                );
            });
        },
        error: () => {
            $exam.empty();
        },
    });
}

/**
 *-------------------------------------------------------------
 * Get class subjects
 *-------------------------------------------------------------
 */
function getClassSubjects(classId, destination) {
    let url = get_class_subjects_url.replace(":id", classId);
    const $subject = destination ? $(destination) : $("#subject_id");
    $.ajax({
        dataType: "json",
        url,
        success: (resp) => {
            $subject.empty();
            $.each(resp, (i, data) => {
                $subject.append(
                    $("<option>", { value: data.id, text: data.name }),
                );
            });
            if (destination && destination.includes("add_not_applicable")) {
                $subject.prepend(
                    '<option selected value=" ">Not Applicable</option>',
                );
            }
        },
        error: () => {
            $subject.empty();
        },
    });
}

/**
 *-------------------------------------------------------------
 * Handler for class sections visibility
 *-------------------------------------------------------------
 */
function hideShowSection(value, sectionId) {
    return value === "class"
        ? unhideSection(sectionId)
        : hideSection(sectionId);
}

function hideSection(sectionId) {
    const $section = sectionId ? $(sectionId) : $("#section_id");
    return $section.closest("div.form-group").hide(150);
}

function unhideSection(sectionId) {
    const $section = sectionId ? $(sectionId) : $("#section_id");
    return $section.closest("div.form-group").show(150);
}

/**
 *-------------------------------------------------------------
 * Pop-up modals/notifications
 *-------------------------------------------------------------
 */
function pop(data) {
    Toast.fire({
        position: "top-right",
        title: data.title ?? "Oops...",
        text: data.msg,
        icon: data.type,
        timer: data.timer ?? pop_timer, // You can set timer to 0 to prevent autohide (pop_timer defined in custom.blade.php)
        showConfirmButton: false,
    });
}

function popConfirm(data) {
    if (!sessionStorage.getItem(data.msg))
        Toast.fire({
            position: "top-right",
            title: data.title ?? "Oops...",
            text: data.msg,
            icon: data.type,
            showConfirmButton: true,
            confirmButtonText: "Got it",
            customClass: {
                confirmButton: "w-100 swal2-restyled",
                footer: "swal2-footer-class",
            },
            footer: `<button class="btn btn-sm" value="${data.msg}" id="do-not-show-for-this-session">Do not show for this session.</button>`,
        });
}

$(document).on("click", "#do-not-show-for-this-session", function () {
    Swal.close();
    sessionStorage.setItem(this.value, "do-not-show-for-this-session");
    flash({
        msg: "Success. The message will not be shown again for this browser's session.",
        type: "success",
    });
});

function flash(data) {
    toastr.options = {
        closeButton: true,
        closeMethod: "hide",
        closeDuration: 500,
        showEasing: getRandomEasingMethod(),
        hideEasing: getRandomEasingMethod(),
        closeEasing: getRandomEasingMethod(),
        showMethod: getRandomShowMethod(),
        hideMethod: getRandomHideMethod(),
        positionClass: "toast-top-right",
        preventDuplicates: true,
        progressBar: true,
    };
    $.unblockUI();
    displayToast(data.type, data.msg);
}

function displayToast(type, message) {
    if (type === "success") return toastr.success(message);
    else if (type === "info") return toastr.info(message);
    else if (type === "error") {
        toastr.options = {
            timeOut: 0,
            extendedTimeOut: 0,
            preventDuplicates: true,
            closeButton: true,
        };
        return toastr.error(message);
    } else if (type === "warning") return toastr.warning(message);
    else return false;
}

function getRandomShowMethod() {
    const methods = ["show", "slideDown", "fadeIn"];
    return methods[Math.floor(Math.random() * methods.length)];
}

function getRandomHideMethod() {
    const methods = ["hide", "slideUp", "fadeOut"];
    return methods[Math.floor(Math.random() * methods.length)];
}

function getRandomEasingMethod() {
    const methods = ["swing", "linear"];
    return methods[Math.floor(Math.random() * methods.length)];
}

/**
 *-------------------------------------------------------------
 * Confirm operations handlers
 *-------------------------------------------------------------
 */
function confirmOperation(id) {
    Modal.fire({
        title: "Do you want to proceed?",
        icon: "warning",
        confirmButtonText: "Sure, Proceed",
        customClass: { confirmButton: "bg-danger" },
    }).then((result) => {
        if (result.isConfirmed) {
            $(`form#item-confirm-operation-${id}`).submit();
        }
    });
}

function confirmOperationTwice(id) {
    Modal.fire({
        title: "Just in case",
        text: "Do you really wish to proceed with this operation?",
        icon: "warning",
        confirmButtonText: "Yes, I do",
        customClass: { confirmButton: "bg-danger" },
    }).then((result) => {
        if (result.isConfirmed) {
            confirmOperation(id);
        }
    });
}

function confirmDelete(id) {
    Modal.fire({
        title: "Are you sure?",
        text: "This item will be deleted.",
        icon: "warning",
        confirmButtonText: "Sure, Delete",
        customClass: { confirmButton: "bg-danger" },
    }).then((result) => {
        if (result.isConfirmed) {
            $(`form#item-delete-${id}`).submit();
        }
    });
}

function confirmPermanentDelete(id) {
    Modal.fire({
        title: "Are you sure?",
        text: "This item will be permanently deleted.",
        icon: "warning",
        confirmButtonText: "Sure, Delete Permanently",
        customClass: { confirmButton: "bg-danger" },
    }).then((result) => {
        if (result.isConfirmed) {
            $(`form#item-delete-${id}`).submit();
        }
    });
}

function confirmPermanentDeleteTwice(id) {
    Modal.fire({
        title: "Just in case",
        text: "Do you really wish to delete this item?",
        icon: "warning",
        confirmButtonText: "Yes, I do",
        customClass: { confirmButton: "bg-danger" },
    }).then((result) => {
        if (result.isConfirmed) {
            confirmPermanentDelete(id);
        }
    });
}

function confirmForceDelete(id, model) {
    Modal.fire({
        title: "Delete Permanently?",
        text: "Once deleted, you will not be able to recover this item!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sure, Delete Permanently",
        customClass: { confirmButton: "bg-danger" },
    }).then((result) => {
        if (result.isConfirmed) {
            $(`form#${model}-force-delete-${id}`).submit();
        }
    });
}

function confirmPublish(id) {
    Modal.fire({
        title: "Are you sure?",
        text: "This item and its related data will be published.",
        icon: "warning",
        confirmButtonText: "Confirm",
        customClass: { confirmButton: "bg-success" },
    }).then((result) => {
        if (result.isConfirmed) {
            $(`form#item-publish-${id}`).submit();
        }
    });
}

function confirmArchive(id) {
    Modal.fire({
        title: "Are you sure?",
        text: "This item will be archived.",
        icon: "warning",
        confirmButtonText: "Confirm",
        customClass: { confirmButton: "bg-success" },
    }).then((result) => {
        if (result.isConfirmed) {
            $(`form#item-archive-${id}`).submit();
        }
    });
}

/**
 *-------------------------------------------------------------
 * Update exam lock, exam edit, staff data edit, and user blocked states
 *-------------------------------------------------------------
 */
function reverseCheckBoxAction(el) {
    $(el).removeAttr("checked").trigger("click");
}

function removeItemFromSessionStorageAndFlashErrMsg(item) {
    sessionStorage.removeItem(item);
    const msg =
        sessionStorage.getItem("resp.msg") ??
        "Something went wrong. Please try again.";
    flash({ msg, type: "error" });
}

function updateExamLockState(exam_id, el) {
    if (sessionStorage.getItem("update-exam-lock-ok") !== "false") {
        const url = update_exam_lock_state_url;
        const value = el.checked ? 1 : 0;
        $.ajax({
            method: "post",
            data: { id: exam_id, is_locked: value },
            url,
            success: (resp) => {
                if (!resp.ok) {
                    if (resp.msg) sessionStorage.setItem("resp.msg", resp.msg);
                    return rollbackUpdateExamLockAction(el);
                }
            },
            error: () => rollbackUpdateExamLockAction(el),
        });
    } else {
        removeItemFromSessionStorageAndFlashErrMsg("update-exam-lock-ok");
    }
}

function rollbackUpdateExamLockAction(el) {
    sessionStorage.setItem("update-exam-lock-ok", "false");
    reverseCheckBoxAction(el);
}

function updateStaffDataEdtiState(user_id, el) {
    if (sessionStorage.getItem("update-staff-data-edit-ok") !== "false") {
        const url = toggle_staff_data_editing_url;
        const value = el.checked ? 1 : 0;
        $.ajax({
            method: "post",
            data: { id: user_id, allow_staff_data_edit: value },
            url,
            success: (resp) => {
                if (!resp.ok) {
                    if (resp.msg) sessionStorage.setItem("resp.msg", resp.msg);
                    return rollbackUpdateStaffDataEditAction(el);
                }
            },
            error: () => rollbackUpdateStaffDataEditAction(el),
        });
    } else {
        removeItemFromSessionStorageAndFlashErrMsg("update-staff-data-edit-ok");
    }
}

function rollbackUpdateStaffDataEditAction(el) {
    sessionStorage.setItem("update-staff-data-edit-ok", "false");
    reverseCheckBoxAction(el);
}

function updateUserBlockedState(user_id, el) {
    if (sessionStorage.getItem("update-user-blocked-state-ok") !== "false") {
        const url = toggle_user_block_url;
        const value = el.checked ? 1 : 0;
        $.ajax({
            method: "post",
            data: { id: user_id, is_blocked: value },
            url,
            success: (resp) => {
                if (!resp.ok) {
                    if (resp.msg) sessionStorage.setItem("resp.msg", resp.msg);
                    return rollbackUpdateUserBlockedState(el);
                }
            },
            error: () => rollbackUpdateUserBlockedState(el),
        });
    } else {
        removeItemFromSessionStorageAndFlashErrMsg(
            "update-user-blocked-state-ok",
        );
    }
}

function rollbackUpdateUserBlockedState(el) {
    sessionStorage.setItem("update-user-blocked-state-ok", "false");
    reverseCheckBoxAction(el);
}

/**
 *-------------------------------------------------------------
 * Confirm reset operations
 *-------------------------------------------------------------
 */
function confirmReset(id, href = null) {
    Modal.fire({
        title: "Are you sure?",
        text: "This will reset this item to default state",
        icon: "warning",
        confirmButtonText: "Sure, Reset",
        customClass: { confirmButton: "bg-primary" },
    }).then((result) => {
        if (result.isConfirmed) {
            if (id === null && href !== null) return (window.location = href);
            $(`form#item-reset-${id}`).submit();
        }
    });
}

function confirmResetTwice(id, href = null) {
    Modal.fire({
        title: "Just in case",
        text: "Do you really want to reset this item?",
        icon: "warning",
        confirmButtonText: "Sure, Reset",
        customClass: { confirmButton: "bg-primary" },
    }).then((result) => {
        if (result.isConfirmed) {
            confirmReset(id, href);
        }
    });
}

function confirmResetPassword(href, default_pass) {
    Modal.fire({
        title: "Are you sure?",
        text: `This will reset password to ${default_pass ?? "user"}`,
        icon: "warning",
        confirmButtonText: "Sure, Reset",
        customClass: { confirmButton: "bg-primary" },
    }).then((result) => {
        if (result.isConfirmed) {
            if (href) return (window.location = href);
            flash({ msg: "Oops! Invalid URL", type: "info" });
        }
    });
}

$("a.needs-reset-pass-confirmation").on("click", function (ev) {
    ev.preventDefault();
    const href = $(this).data("href");
    const default_pass = $(this).data("default_pass");
    confirmResetPassword(href, default_pass);
});

/**
 *-------------------------------------------------------------
 * Payment pay handler on form submit
 *-------------------------------------------------------------
 */
$("form.ajax-pay").on("submit", function (ev) {
    ev.preventDefault();
    submitForm($(this), "store", true);
    // Retrieve IDS
    const formId = $(this).attr("id");
    const $tdAmt = $(`td#amt-${formId}`);
    const $tdAmountPaid = $(`td#amount_paid-${formId}`);
    const $tdBal = $(`td#bal-${formId}`);
    const $input = $(`#val-${formId}`);
    // Get Values
    let amt = parseInt($tdAmt.data("amount"));
    let amtPaid = parseInt($tdAmountPaid.data("amount"));
    const amtInput = parseInt($input.val());
    // Update Values
    amtPaid += amtInput;
    const bal = amt - amtPaid;
    $tdBal.text(`${bal}`);
    $tdAmountPaid.text(`${amtPaid}`).data("amount", `${amtPaid}`);
    $input.attr("max", bal);
    if (bal < 1) $(`#${formId}`).fadeOut("slow").remove();
});

/**
 *-------------------------------------------------------------
 * Ajax store, update, and register form handler
 *-------------------------------------------------------------
 */
$(document).on(
    "submit",
    "form.ajax-store, form.ajax-update, form#ajax-reg",
    function (ev) {
        ev.preventDefault();
        const formType =
            $(this).hasClass("ajax-store") || $(this).attr("id") === "ajax-reg"
                ? "store"
                : "";
        const $isNotGlobal = $(this).hasClass("ajax-not-global");
        submitForm($(this), formType, $isNotGlobal);
        const reloadDiv = $(this).data("reload");
        if (reloadDiv) reloadDivFn(reloadDiv);
    },
);

$(document).on("click", ".download-receipt", function (ev) {
    ev.preventDefault();
    $.get($(this).attr("href"));
    flash({ msg: "Download in Progress", type: "info" });
});

function reloadDivFn(div, url = window.location.href) {
    $(div).parent().load(`${url} ${div}`);
}

/**
 *-------------------------------------------------------------
 * Submit form (base method for submitting forms)
 *-------------------------------------------------------------
 */
function submitForm($form, formType, $isNotGlobal = false) {
    // Normal form Submit button
    let $btn = $form.find("button[type=submit]");
    // Wizard form - Steps validation Submit button
    if (!$btn.length) $btn = $form.find(".actions li:last-child a");
    const btnHtml = $btn.html();
    if ($btn.hasClass("needs-time-counter")) setTimeCounter($btn);
    disableBtn($btn);
    const ajaxOptions = {
        global: !$isNotGlobal,
        url: $form.attr("action"),
        type: "POST",
        cache: false,
        processData: false,
        dataType: "json",
        contentType: false,
        data: new FormData($form[0]),
    };
    $.ajax(ajaxOptions)
        .done((resp) => {
            closeConfirmMessageDeleteModal();
            if ($btn.hasClass("needs-time-counter")) stopTimeCounter();
            hideAjaxAlert();
            enableBtn($btn, btnHtml);
            if (resp.hasAjaxErrors) displayAjaxErr(resp.errors);
            if (resp.ok && resp.msg) {
                resp.pop
                    ? pop({
                          msg: resp.msg,
                          type: "success",
                          timer: resp.pop_timer,
                      })
                    : flash({ msg: resp.msg, type: "success" });
            } else {
                resp.pop
                    ? pop({
                          msg: resp.msg,
                          type: "error",
                          timer: resp.pop_timer,
                      })
                    : flash({ msg: resp.msg, type: "error" });
            }
            if (formType === "store") clearForm($form);
            $btn.html(btnHtml);
            if (resp.scrollToBtn) return scrollTo($btn);
            if (resp.complete) return resp;
            scrollTo("body");
        })
        .fail((e) => {
            closeConfirmMessageDeleteModal();
            unblockUI();
            if ($btn.hasClass("needs-time-counter")) stopTimeCounter();
            if (e.status === 422) displayAjaxErr(e.responseJSON.errors);
            if (e.status === 500)
                displayAjaxErr([
                    `${e.status} ${e.statusText} Please Check for Duplicate entry or Contact Administrator`,
                ]);
            if (e.status === 404)
                displayAjaxErr([
                    `${e.status} ${e.statusText} - Requested Resource Not Found`,
                ]);
            enableBtn($btn, btnHtml);
            return e.status;
        });
}

function setTimeCounter($btn) {
    $(
        '<span id="time-counter" class="status-styled d-inline-block"></span>',
    ).insertAfter($btn);
    startTimeCounter();
}

let sec = 0,
    time_interval;
const pad = (val) => (val > 9 ? val : `0${val}`);

function updateTimer() {
    $("#time-counter").html(
        `${pad(parseInt(sec / 60, 10))}:${pad(++sec % 60)} elapsed.`,
    );
}

function startTimeCounter() {
    time_interval = setInterval(updateTimer, 1000);
}

function stopTimeCounter() {
    clearInterval(time_interval);
}

function closeConfirmMessageDeleteModal() {
    return $("#confirm-message-delete .close").click();
}

function disableBtn($btn) {
    const btnText = $btn.data("text")
        ? $btn.data("text")
        : $btn.data("text") == " "
          ? ""
          : "Submitting...";
    $btn.prop("disabled", true).html(
        `<i class="material-symbols-rounded mr-2 spinner">progress_activity</i>${btnText}`,
    );
}

function enableBtn($btn, btnHtml) {
    $btn.prop("disabled", false).html(btnHtml);
}

function displayAjaxErr(errors) {
    $("#ajax-alert")
        .show()
        .html(
            '<div class="alert alert-danger border-0 alert-dismissible" id="ajax-msg"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>',
        );
    $.each(errors, (k, v) => {
        $("#ajax-msg").append(
            `<span><i class="material-symbols-rounded pb-2px">hdr_strong</i> ${v}</span><br/>`,
        );
    });
    scrollTo("body");
}

function scrollTo(el) {
    $("html, body").animate({ scrollTop: $(el).offset().top }, 2000);
}

function scrollToBottom() {
    const scrollingElement = document.scrollingElement || document.body;
    $("html, body").animate(
        {
            scrollTop: scrollingElement.scrollHeight,
        },
        2000,
    );
}

function hideAjaxAlert() {
    $("#ajax-alert").hide();
}

function clearForm($form) {
    $form.find(".checked").removeClass("checked");
    $form
        .find(".select, .select-search")
        .val([])
        .select2({ placeholder: "Select..." });
    $form[0].reset();
}

$(".print-this-btn").on("click", function () {
    const $parent = $(this).closest(".print-this");
    if (getResolvedTheme(getPreferredTheme()) === colorThemes[2]) {
        Modal.fire({
            text: "Please, print while in light color mode for best visual quality.",
            icon: "warning",
            showDenyButton: true,
            denyButtonText: "Let me switch",
            confirmButtonText: "Just Print",
            customClass: { confirmButton: "bg-warning", denyButton: "bg-info" },
        }).then((result) => {
            if (result.isConfirmed) {
                printElement($parent);
            } else if (result.isDenied) {
                scrollTo("body");
            }
        });
    } else {
        printElement($parent);
    }
});

function printElement($element) {
    $element.printThis({
        base: "https://jasonday.github.io/printThis/",
        beforePrint: function () {
            blockUI();
        },
        afterPrint: function () {
            unblockUI();
        },
    });
}

function resizeChartInstances() {
    for (let id in Chart.instances) {
        Chart.instances[id].resize();
    }
}

/**
 *-------------------------------------------------------------
 * Handle Offline Access
 *-------------------------------------------------------------
 */
const offlineDiv = document.getElementById("offline-alert");
const $submitBtns = $("button[type=submit]");

const updateOfflineStatus = () => {
    const hostname = window.location.hostname;
    const isLocalHost = ["localhost", "127.0.0.1", "::1"].includes(hostname);

    if (!isLocalHost) {
        if (navigator.onLine) {
            offlineDiv.style.display = "none";
            $submitBtns
                .prop("disabled", false)
                .removeClass("cursor-not-allowed");
        } else {
            offlineDiv.style.display = "inline-flex";
            $submitBtns.prop("disabled", true).addClass("cursor-not-allowed");
        }
    }
};

window.addEventListener("offline", updateOfflineStatus);
window.addEventListener("online", updateOfflineStatus);

updateOfflineStatus();

/**
 *-------------------------------------------------------------
 * Dashboard Charts Initialization
 * Uses Chart.js for rendering various charts and sparklines.
 *-------------------------------------------------------------
 */
document.addEventListener("DOMContentLoaded", function () {
    // Lightweight chart factory (sparklines)
    function createSparkline(canvasId, data, opts = {}) {
        const el = document.getElementById(canvasId);
        if (!el) return null;
        return new Chart(el.getContext("2d"), {
            type: "line",
            data: {
                labels: data.map((_, i) => i + 1),
                datasets: [
                    {
                        data: data,
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.3,
                    },
                ],
            },
            options: Object.assign(
                {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false },
                    },
                    scales: { x: { display: false }, y: { display: false } },
                },
                opts,
            ),
        });
    }

    // Big chart creators
    function createDoughnut(canvasId, labels, data) {
        const el = document.getElementById(canvasId);
        if (!el) return null;
        return new Chart(el.getContext("2d"), {
            type: "doughnut",
            data: { labels: labels, datasets: [{ data: data }] },
            options: {
                animation: false,
                responsive: true,
                plugins: {
                    legend: { position: "bottom" },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${ctx.parsed}`,
                        },
                    },
                },
            },
        });
    }

    function createLine(canvasId, labels, data, label) {
        const el = document.getElementById(canvasId);
        if (!el) return null;
        return new Chart(el.getContext("2d"), {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: label || "",
                        data: data,
                        tension: 0.25,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                animation: false,
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: "index", intersect: false },
                },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }

    function createBar(canvasId, labels, data, opts = {}) {
        const el = document.getElementById(canvasId);
        if (!el) return null;
        return new Chart(el.getContext("2d"), {
            type: "bar",
            data: { labels: labels, datasets: [{ data: data }] },
            options: Object.assign(
                {
                    animation: false,
                    responsive: true,
                    plugins: { legend: { display: false }, tooltip: {} },
                    scales: { y: { beginAtZero: true } },
                },
                opts,
            ),
        });
    }

    // Lazy init using IntersectionObserver to reduce CPU on page load
    const observers = [];
    function lazyInit(selector, factory) {
        const el = document.querySelector(selector);
        if (!el) return;
        if ("IntersectionObserver" in window) {
            const obs = new IntersectionObserver(
                function (entries, o) {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            factory();
                            o.unobserve(entry.target);
                        }
                    });
                },
                { rootMargin: "200px" },
            );
            obs.observe(el);
            observers.push(obs);
        } else {
            // fallback: init immediately
            factory();
        }
    }

    // Sparklines (cards)
    lazyInit("#spark-overall-students", () =>
        createSparkline("spark-overall-students", studentsMonthly),
    );

    // Big charts
    lazyInit("#chart-gender", () =>
        createDoughnut(
            "chart-gender",
            ["Male", "Female"],
            [genderCounts.male || 0, genderCounts.female || 0],
        ),
    );
    lazyInit("#chart-payments", () =>
        createLine("chart-payments", months, paymentsMonthly, "Payments"),
    );
    lazyInit("#chart-students-per-class", () =>
        createBar(
            "chart-students-per-class",
            studentsPerClassLabels,
            studentsPerClassCounts,
        ),
    );
});

/**
 *-------------------------------------------------------------
 * Handle user optional additional permissions selection
 * Applies to both central and tenant users - index and edit pages
 *-------------------------------------------------------------
 */
$(function () {
    const $roles = $("#roles");
    const $perms = $("#permissions");
    const usingSelect2 = $perms.hasClass("select");

    let originalPermOptions = [];

    // Capture the initial options from the DOM
    $perms.find("option").each(function () {
        originalPermOptions.push($(this).clone()[0]);
    });

    // Role data maps
    const rolePermMap = {};
    const roleAllMap = {};
    $roles.find("option").each(function () {
        const $opt = $(this);
        const name = $opt.val();
        try {
            rolePermMap[name] = JSON.parse(
                $opt.attr("data-permissions") || "[]",
            );
        } catch (e) {
            rolePermMap[name] = [];
        }
        roleAllMap[name] =
            $opt.attr("data-all") === "1" || $opt.attr("data-all") === "true";
    });

    // Track manually selected permissions (names)
    let manualSelected = new Set();
    let manualBackup = null;
    let hadRootBefore = false;

    // Compute permissions forced by selected roles
    function getRoleGrantedPermissions() {
        const selectedRoles = $roles.val() || [];
        const granted = new Set();
        let grantsAll = false;

        selectedRoles.forEach((role) => {
            if (roleAllMap[role]) {
                grantsAll = true;
            } else {
                (rolePermMap[role] || []).forEach((p) => granted.add(p));
            }
        });

        if (grantsAll) {
            // If any root role is selected, all permissions are granted
            originalPermOptions.forEach((opt) => granted.add(opt.value));
        }
        return { granted, grantsAll };
    }

    // Rebuild the select options: show only permissions NOT granted by roles
    function rebuildSelectOptions() {
        const { granted } = getRoleGrantedPermissions();

        // Filter original options to those whose value is NOT in granted set
        const availableOptions = originalPermOptions.filter(
            (opt) => !granted.has(opt.value),
        );

        // Clear current select
        $perms.empty();

        if (availableOptions.length === 0) {
            // No optional permissions left
            $perms.prop("disabled", true);
            if (usingSelect2) $perms.trigger("change.select2");
            return;
        }

        $perms.prop("disabled", false);

        // Append the filtered options
        availableOptions.forEach((opt) => {
            const $opt = $(opt).clone();
            // Preserve manual selection state
            if (manualSelected.has(opt.value)) {
                $opt.prop("selected", true);
            }
            $perms.append($opt);
        });

        // Refresh Select2
        if (usingSelect2) {
            $perms.trigger("change.select2");
        }
    }

    // Capture manual changes (user selects/deselects permissions)
    $perms.on("change", function () {
        const currentlySelected = new Set($perms.val() || []);
        const { granted } = getRoleGrantedPermissions();

        manualSelected.clear();
        currentlySelected.forEach((p) => {
            if (!granted.has(p)) manualSelected.add(p);
        });
        // No rebuild needed here – the user changed selections manually
    });

    // When roles change, update the permission dropdown
    $roles.on("change", function () {
        const { granted, grantsAll } = getRoleGrantedPermissions();

        // Handle root role activation/deactivation
        if (grantsAll && !hadRootBefore) {
            manualBackup = new Set(manualSelected);
            manualSelected.clear();
        } else if (!grantsAll && hadRootBefore) {
            if (manualBackup) {
                manualSelected = new Set(manualBackup);
                manualBackup = null;
            }
        }

        // Remove any manual selections that are now granted by roles
        [...manualSelected].forEach((p) => {
            if (granted.has(p)) manualSelected.delete(p);
        });

        hadRootBefore = grantsAll;

        // Rebuild the select (only non-granted permissions)
        rebuildSelectOptions();

        // Restore manual selections in the new options
        $perms.val([...manualSelected]);
        if (usingSelect2) $perms.trigger("change.select2");
    });

    // Initial setup: capture initial selected values
    const initialSelected = $perms.val() || [];
    const { granted } = getRoleGrantedPermissions();
    manualSelected = new Set(initialSelected.filter((p) => !granted.has(p)));
    rebuildSelectOptions();
    $perms.val([...manualSelected]);
    if (usingSelect2) $perms.trigger("change.select2");
});

/**
 *-------------------------------------------------------------
 * Initialize overType markdown editors
 *-------------------------------------------------------------
 */
const overtypeEditors = document.getElementsByClassName("overtype-editor");
if (overtypeEditors.length > 0) {
    OverType.initFromData(".overtype-editor", {
        placeholder: "Your message...",
        value: "",
        autoResize: true,
        textareaProps: {
            required: true,
            name: "message",
        },
        theme: getOvertypeTheme(),
        fontSize: "1rem",
    });
}

// Select all / Deselect all per permission group
$(".select-all-group").click(function () {
    var group = $(this).data("group");
    $('input[data-group="' + group + '"]').prop("checked", true);
});
$(".deselect-all-group").click(function () {
    var group = $(this).data("group");
    $('input[data-group="' + group + '"]').prop("checked", false);
});

/**
 *-------------------------------------------------------------
 * Handle students block/unblock
 *-------------------------------------------------------------
 */
$(document).on(
    "click",
    "#students-block-all-active, #students-block-all-graduated",
    function () {
        confirmRequest($(this), "block");
    },
);
$(document).on(
    "click",
    "#students-unblock-all-active, #students-unblock-all-graduated",
    function () {
        confirmRequest($(this), "unblock");
    },
);

function confirmRequest($this, type) {
    const text =
        type === "block"
            ? "This will block students from accessing the system"
            : "This will allow students to access the system";
    Swal.fire({
        title: "Are you sure?",
        text,
        showCancelButton: true,
        reverseButtons: true,
        cancelButtonText: "No, cancel",
    }).then((res) => {
        if (res.isConfirmed) ajaxProcessRequest($this, type);
    });
}

function ajaxProcessRequest($this, type) {
    let url = $this.attr("url");
    let class_id = $this.val();

    $.post(
        url,
        {
            class_id,
        },
        (resp) => {
            if (type === "block") {
                $("label.form-switch input").attr("checked", "checked");
            } else {
                $("label.form-switch input").removeAttr("checked");
            }
            $this.prop("disabled", true);
            $this.siblings("button").prop("disabled", false);

            flash({
                msg: resp.msg,
                type: "success",
            });
        },
    );
}

/**
 *-------------------------------------------------------------
 * Handle select change validation for Query-builder
 *-------------------------------------------------------------
 */
$("select#select").on("change", function () {
    let vals = $(this).val() || [];
    let btn = $(this).closest("form").find("button[type=submit]");
    if (vals.includes("*") && vals.length > 1) {
        $(this)
            .siblings(".select2")
            .find(".select2-selection")
            .addClass("border-danger");
        btn.prop("disabled", true);
        pop({
            msg: "Cannot combine default (*) with specific columns.",
            type: "warning",
        });
        return;
    }
    $(this)
        .siblings(".select2")
        .find(".select2-selection")
        .removeClass("border-danger");
    btn.prop("disabled", false);
});

$(document).on("click", ".item-copy", function (e) {
    e.preventDefault();
    const $outputEls = $(this).siblings(".item-output");
    let combinedText = "";
    $outputEls.each((_, el) => {
        combinedText += $(el).text() + "\n";
    });
    const text = combinedText.trim();
    copyToClipboard(text, this);
});

// Function to copy text to clipboard using the Clipboard API
const copyToClipboard = (text, element) => {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(
            () => showTooltipTitle(element),
            (err) => {
                showTooltipTitle(element, "Failed! Try copying manually");
                console.error("Failed to copy text:", err);
            },
        );
    } else {
        const $temp = $("<textarea>").val(text).appendTo("body").select();
        try {
            document.execCommand("copy");
            showTooltipTitle(element);
        } catch (err) {
            showTooltipTitle(element, "Failed! Try copying manually");
            console.error("Failed to copy text:", err);
        }
        $temp.remove();
    }

    function showTooltipTitle(el, title = "Copied") {
        title == "Copied" ? $(el).text("check") : $(el).text("error");
        $(el).attr("data-original-title", title).tooltip("show");
    }

    setTimeout(() => {
        const title = $(element).data("title") ?? "Copy";
        $(element).text("content_copy");
        $(element).attr("data-original-title", title).tooltip("hide");
    }, 3000);
};

/**
 *-------------------------------------------------------------
 *  Feature Module toggle functionality (Settings)
 *-------------------------------------------------------------
 */
$(document).on("change", ".module-toggle", function () {
    const timeout = 1000; // 1 second delay before reload
    const $el = $(this);
    const module = $el.data("module");
    const url = $el.data("url");

    $.ajax({
        url,
        method: "PUT",
        data: {
            enabled: $el.is(":checked") ? 1 : 0,
            module: module,
        },
        success: () => {
            // Reload so the sidebar, roles UI, and other Blade conditionals
            // pick up the new module state immediately.
            pop({
                msg: "Module state changed successfully. Reloading now...",
                type: "success",
            });
            setTimeout(() => {
                window.location.reload();
            }, timeout);
        },
        error: () => {
            $el.prop("checked", !$el.is(":checked"));
            pop({ msg: "Could not change module state.", type: "error" });
        },
    });
});
