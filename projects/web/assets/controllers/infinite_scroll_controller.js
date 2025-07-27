import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        url: String,
        page: { type: Number, default: 1 },
        hasMore: { type: Boolean, default: true },
    };
    static targets = ["container", "loading"];

    connect() {
        this.isLoading = false;
        this.boundHandleScroll = this.handleScroll.bind(this);
        this.addScrollListener();
        this.setupIntersectionObserver();
    }

    disconnect() {
        this.removeScrollListener();
        if (this.observer) {
            this.observer.disconnect();
        }
    }

    addScrollListener() {
        window.addEventListener("scroll", this.boundHandleScroll);
    }

    removeScrollListener() {
        window.removeEventListener("scroll", this.boundHandleScroll);
    }

    handleScroll() {
        if (this.isLoading || !this.hasMoreValue) return;

        const scrollPosition = window.innerHeight + window.scrollY;
        const threshold = document.body.offsetHeight - 300;

        if (scrollPosition >= threshold) {
            this.loadMore();
        }
    }

    setupIntersectionObserver() {
        if (!("IntersectionObserver" in window)) return;

        const sentinel = document.createElement("div");
        sentinel.style.height = "1px";
        sentinel.dataset.infiniteScrollSentinel = "";

        if (this.hasContainerTarget) {
            this.containerTarget.parentElement.appendChild(sentinel);

            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (
                            entry.isIntersecting &&
                            !this.isLoading &&
                            this.hasMoreValue
                        ) {
                            this.loadMore();
                        }
                    });
                },
                { rootMargin: "100px" }
            );

            this.observer.observe(sentinel);
        }
    }

    async loadMore() {
        if (this.isLoading || !this.hasMoreValue || !this.hasContainerTarget)
            return;

        this.isLoading = true;
        this.pageValue += 1;

        // Show loading
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.remove("hidden");
        }

        try {
            const response = await fetch(
                `${this.urlValue}?page=${this.pageValue}`,
                {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const html = await response.text();

            // Create temporary container
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html.trim();

            const newItems = tempDiv.querySelectorAll("article");

            if (newItems.length === 0) {
                this.hasMoreValue = false;
            } else {
                // Append new items
                newItems.forEach((item) => {
                    this.containerTarget.appendChild(item);
                });
            }
        } catch (error) {
            console.error("Error loading more items:", error);
            this.pageValue -= 1; // Revert page increment on error
        } finally {
            this.isLoading = false;
            if (this.hasLoadingTarget) {
                this.loadingTarget.classList.add("hidden");
            }
        }
    }
}
