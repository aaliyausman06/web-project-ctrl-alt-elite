// author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP)

document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelector(".menu-toggle");
    const closeMenu = document.querySelector(".close-menu");
    const sidebar = document.querySelector(".sidebar-menu");
    const menuItems = document.querySelectorAll(".menu-item .menu-title");

    // Open menu
    menuToggle.addEventListener("click", function () {
        sidebar.classList.add("open");
    });

    // Close menu
    closeMenu.addEventListener("click", function () {
        sidebar.classList.remove("open");
    });

    // Toggle submenu
    menuItems.forEach(item => {
        item.addEventListener("click", function () {
            this.parentElement.classList.toggle("active");
        });
    });
});
window.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const button = document.querySelector('.menu-toggle');
    if (!sidebar.contains(e.target) && !button.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });