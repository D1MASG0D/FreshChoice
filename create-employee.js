
const employeeForm = document.getElementById("employeeForm");

const passwordInput = document.getElementById("password");
const formMessage = document.getElementById("formMessage");


employeeForm.addEventListener("submit", function(event) {

    // Clear previous messages
    formMessage.textContent = "";
    formMessage.className = "form-message";


    // Get the password
    const password = passwordInput.value;


    // Check password length
    if (password.length < 8) {

        event.preventDefault();

        formMessage.textContent =
            "Password must contain at least 8 characters.";

        formMessage.classList.add("error");

        return;
    }


    // Stop the form from submitting (frontend demonstration only)
    event.preventDefault();

    formMessage.textContent =
        "The form is complete. Account creation is not connected yet.";

    formMessage.classList.add("success");

});
