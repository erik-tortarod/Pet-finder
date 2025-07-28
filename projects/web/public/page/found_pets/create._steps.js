// Mobile menu functionality
document.addEventListener("DOMContentLoaded", function () {
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const isHidden = mobileMenu.classList.contains("hidden");

            if (isHidden) {
                mobileMenu.classList.remove("hidden");
                mobileMenuButton.innerHTML =
                    '<i class="fas fa-times text-xl"></i>';
            } else {
                mobileMenu.classList.add("hidden");
                mobileMenuButton.innerHTML =
                    '<i class="fas fa-bars text-xl"></i>';
            }
        });

        // Close mobile menu when clicking on a link
        const mobileMenuLinks = mobileMenu.querySelectorAll("a");
        mobileMenuLinks.forEach((link) => {
            link.addEventListener("click", function () {
                mobileMenu.classList.add("hidden");
                mobileMenuButton.innerHTML =
                    '<i class="fas fa-bars text-xl"></i>';
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener("click", function (e) {
            if (
                !mobileMenuButton.concreate.jstains(e.target) &&
                !mobileMenu.contains(e.target)
            ) {
                mobileMenu.classList.add("hidden");
                mobileMenuButton.innerHTML =
                    '<i class="fas fa-bars text-xl"></i>';
            }
        });

        // Close mobile menu on window resize to desktop
        window.addEventListener("resize", function () {
            if (window.innerWidth >= 768) {
                // md breakpoint
                mobileMenu.classList.add("hidden");
                mobileMenuButton.innerHTML =
                    '<i class="fas fa-bars text-xl"></i>';
            }
        });
    }

    // Initialize map functionality
    initMap();

    // Initialize first step after DOM is loaded
    showStep(1);
});

// Step navigation functionality - make variables global
window.currentStep = 1;
window.totalSteps = 5;
window.stepTitles = [
    "Información Básica",
    "Descripción Física",
    "Ubicación",
    "Foto",
    "Finalizar",
];

// Make functions globally available
window.nextStep = nextStep;
window.previousStep = previousStep;
window.showStep = showStep;

// Map functionality
window.map = null;
window.marker = null;
window.searchTimeout = null;
window.selectedLocation = null;

// Initialize map
window.initMap = function () {
    try {
        // Check if Leaflet is loaded
        if (typeof L === "undefined") {
            console.error("Leaflet is not loaded");
            return;
        }

        // Check if map container exists
        const mapContainer = document.getElementById("map");
        if (!mapContainer) {
            console.error("Map container not found");
            return;
        }

        // Default to Spain coordinates
        window.map = L.map("map").setView([40.4168, -3.7038], 10);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
        }).addTo(window.map);

        // Add click event to map for manual location selection
        window.map.on("click", onMapcreate.jsClick);

        // Initialize search functionality
        initLocationSearch();

        console.log("Map initialized successfully");
    } catch (error) {
        console.error("Error initializing map:", error);
    }
};

function updateStepDisplay() {
    console.log("Updating step display for step:", window.currentStep);

    // Update progress bar
    const progress = (window.currentStep / window.totalSteps) * 100;
    console.log("Progress calculated:", progress + "%");

    const progressBar = document.getElementById("progress-bar");
    if (progressBar) {
        progressBar.style.width = progress + "%";
        console.log("Progress bar updated to:", progressBar.style.width);
    } else {
        console.error("Progress bar element not found!");
    }

    // Update step number and title
    const currentStepElement = document.getElementById("current-step");
    const stepTitleElement = document.getElementById("step-title");

    if (currentStepElement) {
        currentStepElement.textContent = window.currentStep;
    }
    if (stepTitleElement) {
        stepTitleElement.textContent =
            window.stepTitles[window.currentStep - 1];
    }

    // Show/hide navigation buttons
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");
    const submitBtn = document.getElementById("submit-btn");

    if (prevBtn) {
        if (window.currentStep === 1) {
            prevBtn.classList.add("hidden");
        } else {
            prevBtn.classList.remove("hidden");
        }
    }

    if (nextBtn && submitBtn) {
        if (window.currentStep === window.totalSteps) {
            nextBtn.classList.add("hidden");
            submitBtn.classList.remove("hidden");
        } else {
            nextBtn.classList.remove("hidden");
            submitBtn.classList.add("hidden");
        }
    }
}

function showStep(stepNumber) {
    console.log("Showing step:", stepNumber);

    // Hide all steps
    for (let i = 1; i <= window.totalSteps; i++) {
        const stepElement = document.getElementById(`step-${i}`);
        if (stepElement) {
            stepElement.classList.add("hidden");
        }
    }

    // Show current step
    const currentStepElement = document.getElementById(`step-${stepNumber}`);
    if (currentStepElement) {
        currentStepElement.classList.remove("hidden");
        console.log("Step", stepNumber, "is now visible");
    } else {
        console.error("Step element not found:", `step-${stepNumber}`);
    }

    // Update display
    updateStepDisplay();

    // Initialize map if we're on step 3 (location) and map isn't initialized yet
    if (stepNumber === 3) {
        if (!window.map) {
            console.log("Initializing map for location step");
            window.initMap();
        } else {
            // Refresh map size if it already exists
            setTimeout(() => {
                if (
                    window.map &&
                    typeof window.map.invalidateSize === "function"
                ) {
                    window.map.invalidateSize();
                }
            }, 100);
        }
    }
}

function validateStep(stepNumber) {
    const currentStepElement = document.getElementById(`step-${stepNumber}`);
    if (!currentStepElement) return true;

    const requiredFields = currentStepElement.querySelectorAll("[required]");
    let isValid = true;

    requiredFields.forEach((field) => {
        if (!field.value.trim()) {
            field.classList.add("border-red-500");
            isValid = false;
        } else {
            field.classList.remove("border-red-500");
        }
    });

    // Special validation for step 3 (location)
    if (stepNumber === 3) {
        const locationSearch = document.getElementById("location-search");
        if (locationSearch && !locationSearch.value.trim()) {
            locationSearch.classList.add("border-red-500");
            isValid = false;
        } else if (locationSearch) {
            locationSearch.classList.remove("border-red-500");
        }
    }

    return isValid;
}

function nextStep() {
    console.log("Next step clicked, current step:", window.currentStep);

    if (validateStep(window.currentStep)) {
        if (window.currentStep < window.totalSteps) {
            window.currentStep++;
            console.log("Moving to step:", window.currentStep);
            showStep(window.currentStep);
        }
    } else {
        alert(
            "Por favor, completa todos los campos obligatorios antes de continuar."
        );
    }
}

function previousStep() {
    console.log("Previous step clicked, current step:", window.currentStep);

    if (window.currentStep > 1) {
        window.currentStep--;
        console.log("Moving to step:", window.currentStep);
        showStep(window.currentStep);
    }
}

// Handle map click for manual location selection
async function onMapClick(e) {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;

    // Update map
    if (window.marker) {
        window.map.removeLayer(window.marker);
    }

    window.marker = L.marker([lat, lon]).addTo(window.map);

    // Try to get address for the clicked location
    try {
        const addressData = await reverseGeocodeDetailed(lat, lon);
        const fullAddress = addressData.display_name;

        window.marker.bindPopup(fullAddress).openPopup();
        document.getElementById("location-search").value = fullAddress;

        // Auto-populate address field only
        populateLocationFields(addressData);

        window.selectedLocation = {
            lat,
            lon,
            address: fullAddress,
            details: addressData,
        };
    } catch (error) {
        const fallbackAddress = `Ubicación: ${lat.toFixed(6)}, ${lon.toFixed(
            6
        )}`;
        window.marker.bindPopup(fallbackAddress).openPopup();
        document.getElementById("location-search").value = fallbackAddress;
        window.selectedLocation = { lat, lon, address: fallbackAddress };
    }

    console.log("Manual location selected:", lat, lon);
}

// Enhanced reverse geocoding to get detailed address information
async function reverseGeocodeDetailed(lat, lon) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1&accept-language=es`
        );
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Error reverse geocoding:", error);
        throw error;
    }
}

// Function to populate address field automatically (zone remains manual)
function populateLocationFields(addressData) {
    console.log("Address data received:", addressData);

    // Build complete address with all available information
    const fullAddress = addressData.display_name || "";

    // Update only the address field - zone field remains manual
    const addressInput =
        document.getElementById("form_foundAddress") ||
        document.querySelector('input[name*="foundAddress"]');

    if (addressInput) {
        // Set the complete detected address
        addressInput.value = fullAddress;

        // Visual feedback that the field was auto-populated
        addressInput.style.backgroundColor = "#ecfdf5";
        addressInput.style.borderColor = "#10b981";
        setTimeout(() => {
            addressInput.style.backgroundColor = "#f9fafb";
            addressInput.style.borderColor = "#d1d5db";
        }, 2000);

        console.log("Address auto-populated with:", fullAddress);
    }

    console.log("Auto-populated fields:", {
        address: fullAddress,
        note: "Zone field remains manual for user input",
    });
}

// Function to toggle manual edit mode for address field only
function toggleManualEdit() {
    // Only the address field has auto-detection, zone field is always manual
    const addressInput =
        document.getElementById("form_foundAddress") ||
        document.querySelector('input[name*="foundAddress"]');
    const editButtonText = document.getElementById("edit-button-text");
    const editButton = document.getElementById("toggle-manual-edit");

    if (addressInput) {
        const isReadonly =
            addressInput.hasAttribute("readonly") || addressInput.readOnly;

        if (isReadonly) {
            // Enable manual editing for address
            addressInput.removeAttribute("readonly");
            addressInput.readOnly = false;
            addressInput.classList.remove("bg-gray-50", "text-gray-600");
            addressInput.classList.add("bg-white", "text-gray-900");

            if (editButtonText) {
                editButtonText.textContent = "Usar detección automática";
            }
            editButton.classList.remove("border-gray-300", "text-gray-700");
            editButton.classList.add(
                "border-green-300",
                "text-green-700",
                "bg-green-50"
            );

            console.log("Manual edit mode enabled for address");
        } else {
            // Disable manual editing and use auto-detection for address
            addressInput.setAttribute("readonly", true);
            addressInput.readOnly = true;
            addressInput.classList.remove("bg-white", "text-gray-900");
            addressInput.classList.add("bg-gray-50", "text-gray-600");

            if (editButtonText) {
                editButtonText.textContent = "Editar dirección manualmente";
            }
            editButton.classList.remove(
                "border-green-300",
                "text-green-700",
                "bg-green-50"
            );
            editButton.classList.add("border-gray-300", "text-gray-700");

            // Re-populate address with current location if available
            if (window.selectedLocation && window.selectedLocation.details) {
                populateLocationFields(window.selectedLocation.details);
            }

            console.log("Auto-detection mode enabled for address");
        }
    }
}

// Free geocoding service (Nominatim)
async function searchPlaces(query) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
                query
            )}&limit=5&addressdetails=1&accept-language=es`
        );
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Error searching places:", error);
        return [];
    }
}

// Display suggestions
function showSuggestions(places) {
    const suggestionsDiv = document.getElementById("location-suggestions");
    suggestionsDiv.innerHTML = "";

    if (places.length === 0) {
        suggestionsDiv.classList.add("hidden");
        return;
    }

    places.forEach((place) => {
        const div = document.createElement("div");
        div.className =
            "px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-200 last:border-b-0";
        div.textContent = place.display_name;
        div.onclick = () => selectPlace(place);
        suggestionsDiv.appendChild(div);
    });

    suggestionsDiv.classList.remove("hidden");
}

// Select a place
function selectPlace(place) {
    console.log("selectPlace called with:", place);

    if (!window.map) {
        console.error("Map not initialized");
        return;
    }

    const searchInput = document.getElementById("location-search");
    if (searchInput) {
        searchInput.value = place.display_name;
    }

    document.getElementById("location-suggestions").classList.add("hidden");

    // Update map
    const lat = parseFloat(place.lat);
    const lon = parseFloat(place.lon);

    try {
        window.map.setView([lat, lon], 15);

        if (window.marker) {
            window.map.removeLayer(window.marker);
        }

        window.marker = L.marker([lat, lon]).addTo(window.map);
        window.marker.bindPopup(place.display_name).openPopup();

        // Auto-populate address field using the place data
        populateLocationFields(place);

        window.selectedLocation = {
            lat,
            lon,
            address: place.display_name,
            details: place,
        };

        console.log("Selected place:", place);
        console.log("Coordinates:", lat, lon);
    } catch (error) {
        console.error("Error in selectPlace:", error);
    }
}

// Initialize location search functionality
function initLocationSearch() {
    const searchInput = document.getElementById("location-search");
    const currentLocationBtn = document.getElementById("current-location-btn");

    if (!searchInput || !currentLocationBtn) return;

    // Handle input changes
    searchInput.addEventListener("input", function (e) {
        const query = e.target.value.trim();

        if (query.length < 3) {
            document
                .getElementById("location-suggestions")
                .classList.add("hidden");
            return;
        }

        // Debounce search
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(async () => {
            const places = await searchPlaces(query);
            showSuggestions(places);
        }, 300);
    });

    // Hide suggestions when clicking outside
    document.addEventListener("click", function (e) {
        if (!e.target.closest(".search-container")) {
            document
                .getElementById("location-suggestions")
                .classList.add("hidden");
        }
    });

    // Get current location
    currentLocationBtn.addEventListener("click", getCurrentLocation);
}

// Get current location
function getCurrentLocation() {
    const button = document.getElementById("current-location-btn");
    const originalText = button.innerHTML;

    if (!navigator.geolocation) {
        alert("La geolocalización no es compatible con este navegador.");
        return;
    }

    button.disabled = true;
    button.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-1"></i>Obteniendo ubicación...';

    navigator.geolocation.getCurrentPosition(
        function (position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            // Update map
            window.map.setView([lat, lon], 15);

            if (window.marker) {
                window.map.removeLayer(window.marker);
            }

            window.marker = L.marker([lat, lon]).addTo(window.map);
            window.marker.bindPopup("Tu ubicación actual").openPopup();

            // Get detailed address information for current location and auto-populate fields
            reverseGeocodeDetailed(lat, lon)
                .then((addressData) => {
                    populateLocationFields(addressData);
                    window.selectedLocation = {
                        lat,
                        lon,
                        address: "Mi ubicación actual",
                        details: addressData,
                    };
                })
                .catch((error) => {
                    console.error(
                        "Error getting address details for current location:",
                        error
                    );
                    window.selectedLocation = {
                        lat,
                        lon,
                        address: "Mi ubicación actual",
                    };
                });

            // Update search input
            document.getElementById("location-search").value =
                "Mi ubicación actual";

            console.log("Current location:", lat, lon);

            button.disabled = false;
            button.innerHTML = originalText;
        },
        function (error) {
            let errorMessage = "Error al obtener la ubicación: ";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage +=
                        "Permiso denegado. Por favor, permite el acceso a la ubicación.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += "Información de ubicación no disponible.";
                    break;
                case error.TIMEOUT:
                    errorMessage +=
                        "Solicitud de ubicación agotó el tiempo de espera.";
                    break;
                default:
                    errorMessage += "Ocurrió un error desconocido.";
                    break;
            }
            alert(errorMessage);

            button.disabled = false;
            button.innerHTML = originalText;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000,
        }
    );
}
