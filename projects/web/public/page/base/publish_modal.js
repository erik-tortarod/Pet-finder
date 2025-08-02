// Función para inicializar el modal
function initPublishModal() {
    const publishModal = document.querySelector(".nav-publish-btn");
    const publishModalMobile = document.querySelector(
        ".nav-publish-btn-mobile"
    );
    const modal = document.getElementById("publish-modal");
    const closeModal = document.getElementById("close-publish-modal");

    // Remover event listeners previos para evitar duplicados
    if (publishModal) {
        publishModal.removeEventListener("click", openModal);
        publishModal.addEventListener("click", openModal);
    }

    if (publishModalMobile) {
        publishModalMobile.removeEventListener("click", openModal);
        publishModalMobile.addEventListener("click", openModal);
    }

    if (closeModal) {
        closeModal.removeEventListener("click", closeModalFunction);
        closeModal.addEventListener("click", closeModalFunction);
    }

    if (modal) {
        modal.removeEventListener("click", closeOnOutsideClick);
        modal.addEventListener("click", closeOnOutsideClick);
    }

    // Asegurar que el modal esté cerrado al inicializar
    if (modal) {
        modal.classList.add("hidden");
    }
}

// Funciones para abrir y cerrar el modal
function openModal() {
    const modal = document.getElementById("publish-modal");
    if (modal) {
        modal.classList.remove("hidden");
        // Asegurar que el fondo tenga la opacidad correcta
        modal.style.backgroundColor = "rgba(75, 85, 99, 0.5)";
    }
}

function closeModalFunction() {
    const modal = document.getElementById("publish-modal");
    if (modal) {
        modal.classList.add("hidden");
    }
}

function closeOnOutsideClick(e) {
    if (e.target.id === "publish-modal") {
        closeModalFunction();
    }
}

// Event listener para ESC
function handleEscapeKey(e) {
    const modal = document.getElementById("publish-modal");
    if (e.key === "Escape" && modal && !modal.classList.contains("hidden")) {
        closeModalFunction();
    }
}

// Remover event listener previo de ESC y agregar uno nuevo
document.removeEventListener("keydown", handleEscapeKey);
document.addEventListener("keydown", handleEscapeKey);

// Inicializar en carga inicial
document.addEventListener("DOMContentLoaded", initPublishModal);

// Inicializar en navegaciones Turbo
document.addEventListener("turbo:load", initPublishModal);
