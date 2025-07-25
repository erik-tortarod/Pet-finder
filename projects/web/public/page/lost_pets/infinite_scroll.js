let currentPage = 1;
let isLoading = false;
let hasMoreItems = true;

function handleScroll() {
    if (isLoading || !hasMoreItems) return;

    const scrollPosition = window.innerHeight + window.scrollY;
    const threshold = document.body.offsetHeight - 300;

    if (scrollPosition >= threshold) {
        loadMoreAnimals();
    }
}

function loadMoreAnimals() {
    if (isLoading || !hasMoreItems) return;

    isLoading = true;
    currentPage++;

    // Show loading spinner
    const loadingElement = document.getElementById("loading");
    loadingElement.classList.remove("hidden");

    // Get the base URL from a data attribute or construct it
    const baseUrl =
        document.querySelector("[data-lost-pets-url]")?.dataset.lostPetsUrl ||
        "/lost/pets";

    fetch(`${baseUrl}?page=${currentPage}`, {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Error al cargar más mascotas");
            }
            return response.text();
        })
        .then((html) => {
            const container = document.getElementById("lost-animals-container");
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html.trim();

            const newItems = tempDiv.querySelectorAll("article");
            if (newItems.length === 0) {
                hasMoreItems = false;
                window.removeEventListener("scroll", handleScroll);
                console.log("No more items to load");
            } else {
                newItems.forEach((item) => container.appendChild(item));
                console.log(
                    `Loaded ${newItems.length} new items from page ${currentPage}`
                );
            }
        })
        .catch((error) => {
            console.error("Error loading more animals:", error);
            currentPage--; // Revert page increment on error
        })
        .finally(() => {
            isLoading = false;
            loadingElement.classList.add("hidden");
        });
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    // Initialize scroll listener
    window.addEventListener("scroll", handleScroll);

    // Optional: Add intersection observer for better performance
    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && !isLoading && hasMoreItems) {
                        loadMoreAnimals();
                    }
                });
            },
            {
                rootMargin: "100px",
            }
        );

        // Observe a sentinel element at the bottom
        const sentinel = document.createElement("div");
        sentinel.id = "scroll-sentinel";
        sentinel.style.height = "1px";
        const container = document.querySelector(".max-w-7xl");
        if (container) {
            container.appendChild(sentinel);
            observer.observe(sentinel);
        }
    }
});
