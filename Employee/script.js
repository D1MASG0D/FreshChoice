
// Finds an HTML element by its id
function get(id) {
    return document.getElementById(id);
}


// HAMBURGER MENU

function openMenu() {
    get("sideMenu").classList.add("open");
    get("overlay").classList.add("open");
}

function closeMenu() {
    get("sideMenu").classList.remove("open");
    get("overlay").classList.remove("open");
}

function logout() {
    alert("Logout is not connected yet.");
}


// SHIFT REQUEST STATUS

// localStorage remembers information in the browser
function getResponse() {
    return localStorage.getItem("shiftResponse");
}

// Checks whether the sample request needs a response
function hasNewRequest() {
    return !getResponse();
}


// NOTIFICATION BELL

function showBell() {

    // Shows the red dot when there is a pending request
    get("bellDot").hidden = !hasNewRequest();

}


// WEEKLY SCHEDULE

let week = 0;

function previousWeek() {
    if (week > -1) {
        week = week - 1;
    }

    showWeek();
}

function nextWeek() {
    if (week < 1) {
        week = week + 1;
    }

    showWeek();
}

function showWeek() {

    if (week === -1) {
        get("weekDate").innerText = "30 Nov – 6 Dec 2026";
    } else if (week === 1) {
        get("weekDate").innerText = "14–20 December 2026";
    } else {
        get("weekDate").innerText = "7–13 December 2026";
    }

    get("weeklyShifts").hidden = week !== 0;
    get("emptyWeek").hidden = week === 0;

    get("previousButton").disabled = week === -1;
    get("nextButton").disabled = week === 1;

    if (week === 0) {
        get("totalHours").innerText = "24 hours";
    } else {
        get("totalHours").innerText = "0 hours";
    }

    // Updates the schedule when a request is accepted
    if (getResponse() === "accepted") {

        get("shiftDay").innerText = "Saturday";
        get("shiftTime").innerText = "12:00–20:00";

    } else {

        get("shiftDay").innerText = "Friday";
        get("shiftTime").innerText = "09:00–17:00";

    }
}


// ACCEPT AND DECLINE

function acceptRequest() {
    saveResponse("accepted");
}

function declineRequest() {
    saveResponse("declined");
}

function saveResponse(response) {

    // Prevents changing an already answered request
    if (hasNewRequest()) {
        localStorage.setItem("shiftResponse", response);
    }

    // Returns to Shift Alerts
    window.location.href = "shift-alerts.html";

}


// NOTIFICATIONS AND PREVIOUS REQUESTS

function showAlerts() {

    let response = getResponse();

    // Shows the current request only when unanswered
    get("changeNotification").hidden = !hasNewRequest();

    // Shows a message when there are no new requests
    get("noRequests").hidden = hasNewRequest();

    // Displays the answered request in Previous Requests
    get("latestRequest").hidden = hasNewRequest();

    if (response === "accepted") {

        get("requestStatus").innerText = "Accepted";
        get("requestStatus").className = "accepted";

        get("requestMessage").innerText = "Shift changed to Saturday.";
        get("reminderTime").innerText = "Saturday · 12:00–20:00";

    } else if (response === "declined") {

        get("requestStatus").innerText = "Declined";
        get("requestStatus").className = "declined";

        get("requestMessage").innerText = "Original shift unchanged.";
        get("reminderTime").innerText = "Friday · 09:00–17:00";

    }

    if (localStorage.getItem("notificationsRead") === "yes") {
        markAsRead();
    }

}


// MARK ALL AS READ

function markAsRead() {

    get("notification1").classList.remove("unread");
    get("notification2").classList.remove("unread");

    get("dot1").hidden = true;
    get("dot2").hidden = true;

    localStorage.setItem("notificationsRead", "yes");

}


// RUNS WHEN A PAGE OPENS

function loadPage() {

    if (get("bellDot")) {
        showBell();
    }

    if (get("weeklyShifts")) {
        showWeek();
    }

    if (get("changeNotification")) {
        showAlerts();
    }

    if (get("changeRequest")) {

        get("acceptButton").disabled = !hasNewRequest();
        get("declineButton").disabled = !hasNewRequest();
        get("completedMessage").hidden = hasNewRequest();

    }

}

// Updates the page when opening it or returning to it
window.addEventListener("pageshow", loadPage);

// Updates information if another tab changes it
window.addEventListener("storage", loadPage);
