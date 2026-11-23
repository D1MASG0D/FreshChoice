
const loginForm = document.getElementById("loginForm");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const errorMessage = document.getElementById("errorMessage");

loginForm.addEventListener("submit", function (event) {
    errorMessage.textContent = "";

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    if (email === "" || password.trim() === "") {
        event.preventDefault();
        errorMessage.textContent = "Please enter your email address and password.";
        return;
    }

    if (!emailInput.checkValidity()) {
        event.preventDefault();
        errorMessage.textContent = "Please enter a valid email address.";
        return;
    }

    // If the information is valid, the form is submitted
    // to login.php for server-side authentication.

event.preventDefault();
window.location.href = "../Manager Overview/overview.htm";

});
