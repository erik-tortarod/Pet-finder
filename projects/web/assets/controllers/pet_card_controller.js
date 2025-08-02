import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        url: String,
    };

    connect() {
        console.log("Pet card controller connected");
    }

    navigateToPet(event) {
        // Only navigate if the click was not on a button
        if (
            event.target.tagName === "BUTTON" ||
            event.target.closest("button")
        ) {
            return;
        }

        console.log("Navigating to pet:", this.urlValue);
        window.location.href = this.urlValue;
    }

    openContactModal(event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        const button = event.currentTarget;
        const petName = button.dataset.petName;
        const phone = button.dataset.phone || "No disponible";
        const email = button.dataset.email || "No disponible";

        console.log("Pet name:", petName);
        console.log("Phone:", phone);
        console.log("Email:", email);

        // Find the contact modal controller and open it
        const contactModalController =
            this.application.getControllerForElementAndIdentifier(
                document.querySelector('[data-controller="contact-modal"]'),
                "contact-modal"
            );

        if (contactModalController) {
            // Set the values and open the modal
            contactModalController.currentPhoneValue = phone;
            contactModalController.currentEmailValue = email;

            if (contactModalController.hasPetNameTarget) {
                contactModalController.petNameTarget.textContent = petName;
            }
            if (contactModalController.hasPhoneTarget) {
                contactModalController.phoneTarget.textContent = phone;
            }
            if (contactModalController.hasEmailTarget) {
                contactModalController.emailTarget.textContent = email;
            }

            contactModalController.modalTarget.classList.remove("hidden");
            contactModalController.boundHandleOutsideClick =
                contactModalController.handleOutsideClick.bind(
                    contactModalController
                );
            document.addEventListener(
                "click",
                contactModalController.boundHandleOutsideClick
            );
        } else {
            console.error("Contact modal controller not found");
        }
    }

    copyLink(event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

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
