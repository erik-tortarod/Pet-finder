import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "searchInput",
        "animalTypeSelect",
        "zoneSelect",
        "container",
        "loading",
        "quickFilter",
    ];

    static values = {
        url: String,
        currentPage: Number,
    };

    connect() {
        console.log("Filters controller connected");
        console.log("URL value:", this.urlValue);

        this.currentPageValue = 1;
        this.filters = {
            search: "",
            animalType: "",
            zone: "",
            tags: [],
        };

        // Initialize quick filters
        this.initializeQuickFilters();
    }

    initializeQuickFilters() {
        console.log(
            "Initializing quick filters, found:",
            this.quickFilterTargets.length
        );
        this.quickFilterTargets.forEach((button) => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                console.log("Quick filter clicked:", button.textContent.trim());
                this.toggleQuickFilter(button);
            });
        });
    }

    // Search input filter
    search(event) {
        console.log("Search triggered:", event.target.value);
        this.filters.search = event.target.value;
        this.debounce(() => this.applyFilters(), 300);
    }

    // Animal type filter
    filterByAnimalType(event) {
        console.log("Animal type filter:", event.target.value);
        this.filters.animalType = event.target.value;
        this.applyFilters();
    }

    // Zone filter
    filterByZone(event) {
        console.log("Zone filter:", event.target.value);
        this.filters.zone = event.target.value;
        this.applyFilters();
    }

    // Quick filters (tags)
    toggleQuickFilter(button) {
        const tag = button.textContent.trim();
        const isActive = button.classList.contains("tag-active");

        console.log("Toggle quick filter:", tag, "is active:", isActive);

        if (isActive) {
            // Remove from active filters
            button.classList.remove("tag-active");
            button.classList.add("tag-innactive");
            this.filters.tags = this.filters.tags.filter((t) => t !== tag);
        } else {
            // Add to active filters
            button.classList.remove("tag-innactive");
            button.classList.add("tag-active");
            this.filters.tags.push(tag);
        }

        console.log("Current tags:", this.filters.tags);
        this.applyFilters();
    }

    // Apply all filters
    async applyFilters() {
        console.log("Applying filters:", this.filters);
        this.currentPageValue = 1;
        this.showLoading();

        try {
            const params = new URLSearchParams({
                page: this.currentPageValue,
                search: this.filters.search,
                animalType: this.filters.animalType,
                zone: this.filters.zone,
                tags: this.filters.tags.join(","),
            });

            const url = `${this.urlValue}?${params.toString()}`;
            console.log("Fetching URL:", url);

            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "text/html",
                },
            });

            console.log("Response status:", response.status);

            if (response.ok) {
                const html = await response.text();
                console.log("Response HTML length:", html.length);
                this.updateResults(html);
            } else {
                console.error(
                    "Response not OK:",
                    response.status,
                    response.statusText
                );
            }
        } catch (error) {
            console.error("Error applying filters:", error);
        } finally {
            this.hideLoading();
        }
    }

    // Update results container
    updateResults(html) {
        console.log("Updating results");

        // Create a temporary element to parse the response
        const temp = document.createElement("div");
        temp.innerHTML = html;

        // Find the results container in the response
        const newResults = temp.querySelector(
            '[data-filters-target="container"]'
        );

        console.log("New results found:", !!newResults);
        console.log("Has container target:", this.hasContainerTarget);

        if (newResults && this.hasContainerTarget) {
            this.containerTarget.innerHTML = newResults.innerHTML;
            console.log("Results updated successfully");
        } else {
            console.error(
                "Could not update results - container not found or missing target"
            );
        }
    }

    // Show loading spinner
    showLoading() {
        console.log("Show loading, has target:", this.hasLoadingTarget);
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.remove("hidden");
        }
    }

    // Hide loading spinner
    hideLoading() {
        console.log("Hide loading, has target:", this.hasLoadingTarget);
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.add("hidden");
        }
    }

    // Debounce function for search input
    debounce(func, wait) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, wait);
    }

    // Reset all filters
    resetFilters() {
        console.log("Resetting filters");

        // Reset form inputs
        if (this.hasSearchInputTarget) this.searchInputTarget.value = "";
        if (this.hasAnimalTypeSelectTarget)
            this.animalTypeSelectTarget.value = "";
        if (this.hasZoneSelectTarget) this.zoneSelectTarget.value = "";

        // Reset quick filters
        this.quickFilterTargets.forEach((button) => {
            button.classList.remove("tag-active");
            button.classList.add("tag-innactive");
        });

        // Reset filter state
        this.filters = {
            search: "",
            animalType: "",
            zone: "",
            tags: [],
        };

        this.applyFilters();
    }
}
