// Contact Modal JavaScript for Lost Pets Page
// Variables globales para el modal
let currentPhone = "";
let currentEmail = "";

function openContactModal(petName, phone, email) {
    currentPhone = phone;
    currentEmail = email;

    document.getElementById("modalPetName").textContent = petName;
    document.getElementById("modalPhone").textContent = phone;
    document.getElementById("modalEmail").textContent = email;

    document.getElementById("contactModal").classList.remove("hidden");
}

function closeContactModal() {
    document.getElementById("contactModal").classList.add("hidden");
}

function sendWhatsApp() {
    if (currentPhone && currentPhone !== "No disponible") {
        const message = `Hola, vi tu publicación sobre ${
            document.getElementById("modalPetName").textContent
        } y me gustaría contactarte.`;
        const whatsappUrl = `https://wa.me/${currentPhone.replace(
            /\D/g,
            ""
        )}?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, "_blank");
    } else {
        alert("No hay número de teléfono disponible para WhatsApp");
    }
}

function sendEmail() {
    if (currentEmail && currentEmail !== "No disponible") {
        const subject = `Consulta sobre ${
            document.getElementById("modalPetName").textContent
        }`;
        const body = `Hola,\n\nVi tu publicación sobre ${
            document.getElementById("modalPetName").textContent
        } y me gustaría contactarte.\n\nSaludos.`;
        const mailtoUrl = `mailto:${currentEmail}?subject=${encodeURIComponent(
            subject
        )}&body=${encodeURIComponent(body)}`;
        window.open(mailtoUrl);
    } else {
        alert("No hay email disponible");
    }
}

function copyToClipboard(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard
        .writeText(fullUrl)
        .then(function () {
            alert("Enlace copiado al portapapeles");
        })
        .catch(function (err) {
            console.error("Error al copiar: ", err);
            alert("Error al copiar el enlace");
        });
}

// Cerrar modal al hacer clic fuera de él
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("contactModal");
    if (modal) {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                closeContactModal();
            }
        });
    }
});
