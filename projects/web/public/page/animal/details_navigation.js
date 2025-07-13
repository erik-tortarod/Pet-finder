function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll(".tab-content");
    tabContents.forEach((content) => {
        content.classList.remove("active");
    });

    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll(".tab-button");
    tabButtons.forEach((button) => {
        button.classList.remove("active");
    });

    // Show selected tab content
    const selectedTab = document.getElementById("tab-" + tabName);
    if (selectedTab) {
        selectedTab.classList.add("active");
    }

    // Add active class to clicked button
    const clickedButton = event.target.closest(".tab-button");
    if (clickedButton) {
        clickedButton.classList.add("active");
    }
}

function showImageModal(imageSrc) {
    document.getElementById("modalImage").src = imageSrc;
    const modal = document.getElementById("imageModal");
    modal.classList.add("show");

    // Close modal when clicking outside
    modal.addEventListener("click", function (e) {
        if (e.target === modal) {
            closeImageModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            closeImageModal();
        }
    });
}

function closeImageModal() {
    const modal = document.getElementById("imageModal");
    modal.classList.remove("show");
}

function shareAnimal() {
    if (navigator.share) {
        navigator.share({
            title: "{{ animal.name }} - {{ animal.status }}",
            text: "Mira los detalles de {{ animal.name }}, un {{ animal.animalType }} que está {{ animal.status|lower }}",
            url: window.location.href,
        });
    } else {
        // Fallback: copiar URL al portapapeles
        navigator.clipboard.writeText(window.location.href).then(function () {
            alert("Enlace copiado al portapapeles");
        });
    }
}
