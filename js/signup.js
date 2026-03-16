document.addEventListener("DOMContentLoaded", function () {

    const nameField  = document.querySelector('[name="fname"]');
    const phoneField = document.querySelector('[name="phone"]');
    const emailField = document.querySelector('[name="email"]');

    function showError(input, message) {
        input.style.border = "2px solid red";
        input.nextElementSibling.textContent = message;
    }

    function showSuccess(input) {
        input.style.border = "2px solid green";
        input.nextElementSibling.textContent = "";
    }

    /* ========== REAL-TIME VALIDATION ========== */

    nameField.addEventListener('blur', () => {
        if (!/^[A-Za-z ]{3,50}$/.test(nameField.value.trim())) {
            showError(nameField, "Only letters & spaces (min 3 chars)");
        } else {
            showSuccess(nameField);
        }
    });

    phoneField.addEventListener('blur', () => {
        if (!/^[0-9]{10}$/.test(phoneField.value.trim())) {
            showError(phoneField, "Phone must be exactly 10 digits");
        } else {
            showSuccess(phoneField);
        }
    });

    emailField.addEventListener('blur', () => {
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
            showError(emailField, "Invalid email format");
        } else {
            showSuccess(emailField);
        }
    });

    /* ========== FINAL SUBMIT CHECK ========== */
    window.validateSignup = function () {

        let valid = true;

        if (!/^[A-Za-z ]{3,50}$/.test(nameField.value.trim())) {
            showError(nameField, "Only letters & spaces (min 3 chars)");
            valid = false;
        }

        if (!/^[0-9]{10}$/.test(phoneField.value.trim())) {
            showError(phoneField, "Phone must be 10 digits");
            valid = false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
            showError(emailField, "Invalid email format");
            valid = false;
        }

        if (!valid) {
            document.querySelector('.error:not(:empty)')
                ?.previousElementSibling.focus();
            return false;
        }

        // ✅ Disable button AFTER validation
        document.querySelector('.signup-btn').disabled = true;

        return true; // 🚀 FORM SUBMITS
    };

});