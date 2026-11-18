
document.addEventListener("DOMContentLoaded", function () {

    const accountButton = document.getElementById("accountButton");
    const accountDropdown = document.getElementById("accountDropdown");

    const mobileMenuButton = document.getElementById("mobileMenuButton");
    const mobileMenu = document.getElementById("mobileMenu");

    if (accountButton && accountDropdown) {

        accountButton.setAttribute("aria-expanded", "false");

        accountButton.addEventListener("click", function (event) {
            event.stopPropagation();

            const isOpen = accountDropdown.classList.toggle("show");

            accountButton.classList.toggle("open", isOpen);
            accountButton.setAttribute("aria-expanded", String(isOpen));
        });

    }

    if (mobileMenuButton && mobileMenu) {

        mobileMenuButton.setAttribute("aria-expanded", "false");

        mobileMenuButton.addEventListener("click", function () {
            const isOpen = mobileMenu.classList.toggle("open");

            mobileMenuButton.classList.toggle("open", isOpen);
            mobileMenuButton.setAttribute("aria-expanded", String(isOpen));
        });

        mobileMenu.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                mobileMenu.classList.remove("open");
                mobileMenuButton.classList.remove("open");
                mobileMenuButton.setAttribute("aria-expanded", "false");
            });
        });

    }

    document.addEventListener("click", function (event) {

        if (
            accountDropdown &&
            accountButton &&
            !accountDropdown.contains(event.target) &&
            !accountButton.contains(event.target)
        ) {
            accountDropdown.classList.remove("show");
            accountButton.classList.remove("open");
            accountButton.setAttribute("aria-expanded", "false");
        }

        if (
            mobileMenu &&
            mobileMenuButton &&
            !mobileMenu.contains(event.target) &&
            !mobileMenuButton.contains(event.target)
        ) {
            mobileMenu.classList.remove("open");
            mobileMenuButton.classList.remove("open");
            mobileMenuButton.setAttribute("aria-expanded", "false");
        }

    });

    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            if (accountDropdown && accountButton) {
                accountDropdown.classList.remove("show");
                accountButton.classList.remove("open");
                accountButton.setAttribute("aria-expanded", "false");
            }

            if (mobileMenu && mobileMenuButton) {
                mobileMenu.classList.remove("open");
                mobileMenuButton.classList.remove("open");
                mobileMenuButton.setAttribute("aria-expanded", "false");
            }

        }

    });

});
