document.addEventListener("DOMContentLoaded", function () {
    // Confirm before logout
    const logoutLinks = document.querySelectorAll("[data-confirm-logout]");
    logoutLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            if (!confirm("Are you sure you want to logout?")) {
                event.preventDefault();
            }
        });
    });

    // Confirm before submitting an event form (small UX safety)
    const eventForms = document.querySelectorAll(".event-form");
    eventForms.forEach(function (form) {
        form.addEventListener("submit", function (event) {
            // Only require confirm on edit (presence of event_ID hidden input means we're updating)
            const isUpdate = form.querySelector('input[name="event_ID"]') !== null;
            if (isUpdate && !confirm("Save changes to this event?")) {
                event.preventDefault();
            }
        });
    });
});
