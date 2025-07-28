import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "lostTab",
        "foundTab",
        "lostContent",
        "foundContent",
        "viewAllBtn",
        "viewAllText",
    ];
    static values = {
        lostRoute: String,
        foundRoute: String,
    };

    connect() {
        // Initialize with lost pets tab active by default
        this.showLostPets();
    }

    showLostPets() {
        // Update tab styles
        this.lostTabTarget.classList.add("bg-blue-600", "text-white");
        this.lostTabTarget.classList.remove("text-gray-600");
        this.foundTabTarget.classList.remove("bg-blue-600", "text-white");
        this.foundTabTarget.classList.add("text-gray-600");

        // Show/hide content
        this.lostContentTarget.classList.remove("hidden");
        this.foundContentTarget.classList.add("hidden");

        // Update button link and text
        this.viewAllBtnTarget.href = this.lostRouteValue;
        this.viewAllTextTarget.textContent = "Ver todas las perdidas";
    }

    showFoundPets() {
        // Update tab styles
        this.foundTabTarget.classList.add("bg-blue-600", "text-white");
        this.foundTabTarget.classList.remove("text-gray-600");
        this.lostTabTarget.classList.remove("bg-blue-600", "text-white");
        this.lostTabTarget.classList.add("text-gray-600");

        // Show/hide content
        this.foundContentTarget.classList.remove("hidden");
        this.lostContentTarget.classList.add("hidden");

        // Update button link and text
        this.viewAllBtnTarget.href = this.foundRouteValue;
        this.viewAllTextTarget.textContent = "Ver todas las encontradas";
    }
}
