import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["modal", "petName", "phone", "email"];
    static values = {
        currentPhone: String,
        currentEmail: String,
    };

    connect() {
        this.boundHandleOutsideClick = this.handleOutsideClick.bind(this);
    }

    disconnect() {
        document.removeEventListener("click", this.boundHandleOutsideClick);
    }

    open(event) {
        const button = event.currentTarget;
        const petName = button.dataset.petName;
        const phone = button.dataset.phone || "No disponible";
        const email = button.dataset.email || "No disponible";

        this.currentPhoneValue = phone;
        this.currentEmailValue = email;

        if (this.hasPetNameTarget) this.petNameTarget.textContent = petName;
        if (this.hasPhoneTarget) this.phoneTarget.textContent = phone;
        if (this.hasEmailTarget) this.emailTarget.textContent = email;

        this.modalTarget.classList.remove("hidden");
        document.addEventListener("click", this.boundHandleOutsideClick);
    }

    close() {
        this.modalTarget.classList.add("hidden");
        document.removeEventListener("click", this.boundHandleOutsideClick);
    }

    handleOutsideClick(event) {
        if (event.target === this.modalTarget) {
            this.close();
        }
    }

    sendWhatsApp() {
        if (
            this.currentPhoneValue &&
            this.currentPhoneValue !== "No disponible"
        ) {
            const petName = this.hasPetNameTarget
                ? this.petNameTarget.textContent
                : "esta mascota";
            const message = `Hola, vi tu publicación sobre ${petName} y me gustaría contactarte.`;
            const whatsappUrl = `https://wa.me/${this.currentPhoneValue.replace(
                /\D/g,
                ""
            )}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, "_blank");
        } else {
            alert("No hay número de teléfono disponible para WhatsApp");
        }
    }

    sendEmail() {
        if (
            this.currentEmailValue &&
            this.currentEmailValue !== "No disponible"
        ) {
            const petName = this.hasPetNameTarget
                ? this.petNameTarget.textContent
                : "esta mascota";
            const subject = `Consulta sobre ${petName}`;
            const body = `Hola,\n\nVi tu publicación sobre ${petName} y me gustaría contactarte.\n\nSaludos.`;
            const mailtoUrl = `mailto:${
                this.currentEmailValue
            }?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(
                body
            )}`;
            window.open(mailtoUrl);
        } else {
            alert("No hay email disponible");
        }
    }

    copyLink(event) {
        const url = event.currentTarget.dataset.url;
        const fullUrl = window.location.origin + url;

        navigator.clipboard
            .writeText(fullUrl)
            .then(() => {
                alert("Enlace copiado al portapapeles");
            })
            .catch((err) => {
                console.error("Error al copiar: ", err);
                alert("Error al copiar el enlace");
            });
    }
}
