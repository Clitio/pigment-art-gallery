document.addEventListener("DOMContentLoaded", function () {
    const logoutLinks = document.querySelectorAll("[data-confirm-logout]");
    const bookingForm = document.querySelector("[data-booking-form]");
    const passwordInput = document.getElementById("password");
    const showPassword = document.getElementById("showPassword");
    const updateForm = document.querySelector("[data-update-form]");
    const menuToggle = document.querySelector("[data-menu-toggle]");
    const expandableMenu = document.querySelector("[data-expandable-menu]");

    logoutLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            if (!confirm("Are you sure you want to logout?")) {
                event.preventDefault();
            }
        });
    });

    if (bookingForm) {
        bookingForm.addEventListener("submit", function (event) {
            if (!confirm("Do you want to confirm this booking?")) {
                event.preventDefault();
            }
        });
    }

    if (menuToggle && expandableMenu) {
        menuToggle.addEventListener("click", function () {
            expandableMenu.classList.toggle("is-open");
        });
    }

    if (showPassword && passwordInput) {
        showPassword.addEventListener("change", function () {
            passwordInput.type = this.checked ? "text" : "password";
        });
    }

    if (updateForm && passwordInput) {
        updateForm.addEventListener("submit", function (event) {
            if (passwordInput.value !== "" && passwordInput.value.length < 8) {
                event.preventDefault();
                alert("New password must be at least 8 characters.");
                passwordInput.focus();
            }
        });
    }
});
