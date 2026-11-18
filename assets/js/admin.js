const sidebarToggle = document.getElementById("sidebarToggle");
const adminSidebar = document.getElementById("adminSidebar");

if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener("click", function () {
        adminSidebar.classList.toggle("open");
    });
}

document.addEventListener("click", function (event) {
    if (!adminSidebar || !sidebarToggle) {
        return;
    }

    if (
        adminSidebar.classList.contains("open") &&
        !adminSidebar.contains(event.target) &&
        !sidebarToggle.contains(event.target)
    ) {
        adminSidebar.classList.remove("open");
    }
});