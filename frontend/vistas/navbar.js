document.addEventListener("DOMContentLoaded", () => {
    // Busca el botón con la clase "icon-menu" y le asigna el ID "toggle-menu-btn"
    const toggleBtn = document.querySelector(".icon-menu");
    // Busca el div del menú con la clase "container-menu"
    const sideMenu = document.querySelector(".container-menu");
    // Busca la capa que quieres usar como fondo
    const overlay = document.querySelector(".capa");

    toggleBtn.addEventListener("click", () => {
        // Alterna la clase "open" en el menú lateral
        sideMenu.classList.toggle("open");
        // Alterna la clase "open" en la capa de fondo
        overlay.classList.toggle("open");
    });

    // Cierra el menú al hacer clic en la capa de fondo
    overlay.addEventListener("click", () => {
        sideMenu.classList.remove("open");
        overlay.classList.remove("open");
    });
});