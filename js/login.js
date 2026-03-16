const emailField = document.querySelector('[name="email"]');
const passField  = document.querySelector('[name="password"]');

function showError(input, message) {
    input.style.border = "2px solid red";
    input.nextElementSibling.textContent = message;
}

function showSuccess(input) {
    input.style.border = "2px solid green";
    input.nextElementSibling.textContent = "";
}

/* REAL-TIME EMAIL VALIDATION */
emailField.addEventListener('blur', () => {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
        showError(emailField, "Invalid email format");
    } else {
        showSuccess(emailField);
    }
});

/* FINAL SUBMIT */
function validateLogin() {

    let valid = true;

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
        showError(emailField, "Valid email required");
        valid = false;
    }else {
        showSuccess(emailField);
    }
    passField.style.border = ""; // ⭐ reset
    /* OPTIONAL: show warning if admin email & empty password
    if (emailField.value.includes("admin") && passField.value.trim() === "") {
    showError(passField, "Admin password required");
    return false;
    }*/
    if (!valid) {
        document.querySelector('.error:not(:empty)')
            ?.previousElementSibling.focus();
    }

    return valid;
}