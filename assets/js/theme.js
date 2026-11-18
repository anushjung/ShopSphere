
(function () {
    const savedTheme = localStorage.getItem("shopsphere-theme") || "light";

    document.documentElement.setAttribute("data-theme", savedTheme);

    document.addEventListener("DOMContentLoaded", function () {

        const themeButtons = document.querySelectorAll("[data-theme-choice]");

        function updateTheme(theme) {
            document.documentElement.setAttribute("data-theme", theme);
            localStorage.setItem("shopsphere-theme", theme);

            themeButtons.forEach(function (button) {
                button.classList.toggle(
                    "active",
                    button.dataset.themeChoice === theme
                );
            });
        }

        themeButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                updateTheme(button.dataset.themeChoice);
            });
        });

        updateTheme(savedTheme);
    });
})();
