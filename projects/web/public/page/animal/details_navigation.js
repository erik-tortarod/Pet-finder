// Función simple para mostrar una pestaña específica
function showTab(tabName) {
    console.log("Switching to tab:", tabName);

    // Ocultar todas las pestañas
    const allTabs = document.querySelectorAll(".tab-content");
    allTabs.forEach((tab) => {
        tab.style.display = "none";
        tab.classList.remove("active");
    });

    // Quitar clase activa de todos los botones y aplicar estilos
    const allButtons = document.querySelectorAll(".tab-button");
    allButtons.forEach((button) => {
        button.classList.remove("active");
        // Aplicar estilos de botón inactivo
        button.style.backgroundColor = "transparent";
        button.style.color = "#6b7280";
        button.style.borderBottomColor = "transparent";
        button.style.borderColor = "transparent";
    });

    // Mostrar la pestaña seleccionada
    const targetTab = document.getElementById("tab-" + tabName);
    if (targetTab) {
        targetTab.style.display = "block";
        targetTab.classList.add("active");
    }

    // Marcar el botón como activo y aplicar estilos
    const activeButton = event.target.closest(".tab-button");
    if (activeButton) {
        activeButton.classList.add("active");
        // Aplicar estilos de botón activo
        activeButton.style.backgroundColor = "#3b82f6";
        activeButton.style.color = "white";
        activeButton.style.borderBottomColor = "#3b82f6";
        activeButton.style.borderColor = "#3b82f6";
    }
}

// Función para inicializar las pestañas
function initializeTabs() {
    console.log("Initializing tabs...");

    // Ocultar todas las pestañas
    const allTabs = document.querySelectorAll(".tab-content");
    allTabs.forEach((tab) => {
        tab.style.display = "none";
        tab.classList.remove("active");
    });

    // Mostrar solo la primera pestaña
    if (allTabs.length > 0) {
        allTabs[0].style.display = "block";
        allTabs[0].classList.add("active");
    }

    // Marcar el primer botón como activo y aplicar estilos
    const allButtons = document.querySelectorAll(".tab-button");
    if (allButtons.length > 0) {
        allButtons[0].classList.add("active");
        // Aplicar estilos de botón activo al primero
        allButtons[0].style.backgroundColor = "#3b82f6";
        allButtons[0].style.color = "white";
        allButtons[0].style.borderBottomColor = "#3b82f6";
        allButtons[0].style.borderColor = "#3b82f6";

        // Aplicar estilos de botón inactivo a los demás
        for (let i = 1; i < allButtons.length; i++) {
            allButtons[i].style.backgroundColor = "transparent";
            allButtons[i].style.color = "#6b7280";
            allButtons[i].style.borderBottomColor = "transparent";
            allButtons[i].style.borderColor = "transparent";
        }
    }

    console.log("Tabs initialized successfully");
}

// Función para mostrar modal de imagen
function showImageModal(imageSrc) {
    const modal = document.getElementById("imageModal");
    const modalImage = document.getElementById("modalImage");

    if (modal && modalImage) {
        modalImage.src = imageSrc;
        modal.classList.add("show");
    }
}

// Función para cerrar modal de imagen
function closeImageModal() {
    const modal = document.getElementById("imageModal");
    if (modal) {
        modal.classList.remove("show");
    }
}

// Función para compartir animal
function shareAnimal() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: "Mira los detalles de este animal",
            url: window.location.href,
        });
    } else {
        // Copiar URL al portapapeles
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert("Enlace copiado al portapapeles");
        });
    }
}

// Función de inicialización principal
function initializePage() {
    console.log("Page initialization started");

    // Verificar si las funciones están disponibles
    if (typeof initializeTabs === "function") {
        console.log("Calling initializeTabs");
        initializeTabs();
    } else {
        console.log("initializeTabs not found, doing manual initialization");

        // Inicialización manual
        const tabs = document.querySelectorAll(".tab-content");
        const buttons = document.querySelectorAll(".tab-button");

        console.log(
            "Found",
            tabs.length,
            "tab contents and",
            buttons.length,
            "tab buttons"
        );

        // Ocultar todas las pestañas
        tabs.forEach((tab) => {
            tab.style.display = "none";
            tab.classList.remove("active");
        });

        // Mostrar primera pestaña
        if (tabs.length > 0) {
            tabs[0].style.display = "block";
            tabs[0].classList.add("active");
            console.log("Made first tab active");
        }

        // Activar primer botón
        if (buttons.length > 0) {
            buttons[0].classList.add("active");
            console.log("Made first button active");
        }
    }

    console.log("Page initialization completed");
}

// Agregar event listeners para hover
function addHoverListeners() {
    const buttons = document.querySelectorAll(".tab-button");

    buttons.forEach((button) => {
        button.addEventListener("mouseenter", function () {
            if (!this.classList.contains("active")) {
                this.style.backgroundColor = "#f9fafb";
                this.style.color = "#374151";
                this.style.borderBottomColor = "#d1d5db";
                this.style.borderColor = "#d1d5db";
            } else {
                this.style.backgroundColor = "#2563eb";
                this.style.color = "white";
                this.style.borderBottomColor = "#2563eb";
                this.style.borderColor = "#2563eb";
            }
        });

        button.addEventListener("mouseleave", function () {
            if (!this.classList.contains("active")) {
                this.style.backgroundColor = "transparent";
                this.style.color = "#6b7280";
                this.style.borderBottomColor = "transparent";
                this.style.borderColor = "transparent";
            } else {
                this.style.backgroundColor = "#3b82f6";
                this.style.color = "white";
                this.style.borderBottomColor = "#3b82f6";
                this.style.borderColor = "#3b82f6";
            }
        });
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, initializing page...");
    initializePage();
    addHoverListeners();
});

// También inicializar inmediatamente si el DOM ya está listo
if (document.readyState !== "loading") {
    console.log("DOM already ready, initializing page...");
    initializePage();
    addHoverListeners();
}
