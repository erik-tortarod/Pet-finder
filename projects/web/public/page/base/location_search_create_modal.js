// Global variables for location search create modal
let locationCreateMap = null;
let locationCreateMarker = null;
let locationCreateSearchTimeout = null;
let selectedCreateLocation = null;

// Function to initialize the location search create modal
function initLocationSearchCreateModal() {
    const locationSearchCreateBtns = document.querySelectorAll(
        "[data-location-search-create-btn]"
    );
    const modal = document.getElementById("location-search-create-modal");
    const closeModal = document.getElementById(
        "close-location-search-create-modal"
    );
    const searchInput = document.getElementById("location-search-create-input");
    const currentLocationBtn = document.getElementById(
        "current-location-create-btn"
    );
    const clearLocationBtn = document.getElementById(
        "clear-location-create-btn"
    );
    const applyLocationBtn = document.getElementById(
        "apply-location-create-btn"
    );
    const suggestions = document.getElementById("location-suggestions-create");

    // Remove previous event listeners to avoid duplicates
    locationSearchCreateBtns.forEach((btn) => {
        btn.removeEventListener("click", openLocationSearchCreateModal);
        btn.addEventListener("click", openLocationSearchCreateModal);
    });

    if (closeModal) {
        closeModal.removeEventListener("click", closeLocationSearchCreateModal);
        closeModal.addEventListener("click", closeLocationSearchCreateModal);
    }

    if (modal) {
        modal.removeEventListener("click", closeOnOutsideClick);
        modal.addEventListener("click", closeOnOutsideClick);
    }

    if (searchInput) {
        searchInput.removeEventListener("input", handleCreateSearchInput);
        searchInput.addEventListener("input", handleCreateSearchInput);
    }

    if (currentLocationBtn) {
        currentLocationBtn.removeEventListener(
            "click",
            getCreateCurrentLocation
        );
        currentLocationBtn.addEventListener("click", getCreateCurrentLocation);
    }

    if (clearLocationBtn) {
        clearLocationBtn.removeEventListener("click", clearCreateLocation);
        clearLocationBtn.addEventListener("click", clearCreateLocation);
    }

    if (applyLocationBtn) {
        applyLocationBtn.removeEventListener("click", applyCreateLocation);
        applyLocationBtn.addEventListener("click", applyCreateLocation);
    }

    // Hide suggestions when clicking outside
    document.addEventListener("click", (e) => {
        if (!e.target.closest(".search-container")) {
            if (suggestions) {
                suggestions.classList.add("hidden");
            }
        }
    });

    // Ensure modal is closed on initialize
    if (modal) {
        modal.classList.add("hidden");
    }
}

// Functions to open and close the modal
function openLocationSearchCreateModal() {
    const modal = document.getElementById("location-search-create-modal");
    if (modal) {
        modal.classList.remove("hidden");
        modal.style.backgroundColor = "rgba(75, 85, 99, 0.5)";

        // Initialize map when modal opens
        setTimeout(() => {
            initLocationCreateMap();
        }, 100);
    }
}

function closeLocationSearchCreateModal() {
    const modal = document.getElementById("location-search-create-modal");
    if (modal) {
        modal.classList.add("hidden");
    }

    // Clean up map when modal closes
    cleanupLocationCreateMap();
}

function closeOnOutsideClick(e) {
    if (e.target.id === "location-search-create-modal") {
        closeLocationSearchCreateModal();
    }
}

// Map functions
function initLocationCreateMap() {
    try {
        const mapContainer = document.getElementById(
            "location-map-create-container"
        );
        if (!mapContainer || !window.L) {
            console.error("Map container or Leaflet not available");
            return;
        }

        // Clean up existing map
        cleanupLocationCreateMap();

        // Clear container
        mapContainer.innerHTML = "";

        // Create new map
        locationCreateMap = L.map(mapContainer).setView([40.4168, -3.7038], 10);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
        }).addTo(locationCreateMap);

        // Add click event to map
        locationCreateMap.on("click", onCreateMapClick);

        console.log("Location create map initialized successfully");
    } catch (error) {
        console.error("Error initializing location create map:", error);
    }
}

function cleanupLocationCreateMap() {
    if (locationCreateMap) {
        try {
            locationCreateMap.remove();
        } catch (error) {
            console.warn("Error cleaning up create map:", error);
        }
        locationCreateMap = null;
        locationCreateMarker = null;
    }
}

function onCreateMapClick(e) {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;

    // Update map marker
    if (locationCreateMarker) {
        locationCreateMap.removeLayer(locationCreateMarker);
    }

    locationCreateMarker = L.marker([lat, lon]).addTo(locationCreateMap);

    // Get address for clicked location
    reverseCreateGeocode(lat, lon)
        .then((address) => {
            locationCreateMarker.bindPopup(address).openPopup();

            const searchInput = document.getElementById(
                "location-search-create-input"
            );
            if (searchInput) {
                searchInput.value = address;
            }

            selectedCreateLocation = { lat, lon, address };
            updateCreateHiddenInputs(lat, lon);
        })
        .catch((error) => {
            const fallbackAddress = `Ubicación: ${lat.toFixed(
                6
            )}, ${lon.toFixed(6)}`;
            locationCreateMarker.bindPopup(fallbackAddress).openPopup();

            const searchInput = document.getElementById(
                "location-search-create-input"
            );
            if (searchInput) {
                searchInput.value = fallbackAddress;
            }

            selectedCreateLocation = { lat, lon, address: fallbackAddress };
            updateCreateHiddenInputs(lat, lon);
        });
}

// Search functions
function handleCreateSearchInput(e) {
    const query = e.target.value.trim();
    const suggestions = document.getElementById("location-suggestions-create");

    if (query.length < 3) {
        if (suggestions) {
            suggestions.classList.add("hidden");
        }
        return;
    }

    // Debounce search
    clearTimeout(locationCreateSearchTimeout);
    locationCreateSearchTimeout = setTimeout(async () => {
        const places = await searchCreatePlaces(query);
        showCreateSuggestions(places);
    }, 300);
}

async function searchCreatePlaces(query) {
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

function showCreateSuggestions(places) {
    const suggestions = document.getElementById("location-suggestions-create");
    if (!suggestions) return;

    suggestions.innerHTML = "";

    if (places.length === 0) {
        suggestions.classList.add("hidden");
        return;
    }

    places.forEach((place) => {
        const div = document.createElement("div");
        div.className =
            "px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-200 last:border-b-0";
        div.textContent = place.display_name;
        div.onclick = () => selectCreatePlace(place);
        suggestions.appendChild(div);
    });

    suggestions.classList.remove("hidden");
}

function selectCreatePlace(place) {
    const searchInput = document.getElementById("location-search-create-input");
    const suggestions = document.getElementById("location-suggestions-create");

    if (searchInput) {
        searchInput.value = place.display_name;
    }

    if (suggestions) {
        suggestions.classList.add("hidden");
    }

    // Update map
    const lat = parseFloat(place.lat);
    const lon = parseFloat(place.lon);

    if (locationCreateMap) {
        locationCreateMap.setView([lat, lon], 15);

        if (locationCreateMarker) {
            locationCreateMap.removeLayer(locationCreateMarker);
        }

        locationCreateMarker = L.marker([lat, lon]).addTo(locationCreateMap);
        locationCreateMarker.bindPopup(place.display_name).openPopup();
    }

    selectedCreateLocation = {
        lat,
        lon,
        address: place.display_name,
        details: place,
    };

    updateCreateHiddenInputs(lat, lon);
}

async function reverseCreateGeocode(lat, lon) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1&accept-language=es`
        );
        const data = await response.json();
        return data.display_name;
    } catch (error) {
        console.error("Error reverse geocoding:", error);
        throw error;
    }
}

function getCreateCurrentLocation() {
    const button = document.getElementById("current-location-create-btn");
    const originalText = button.innerHTML;

    if (!navigator.geolocation) {
        alert("La geolocalización no es compatible con este navegador.");
        return;
    }

    button.disabled = true;
    button.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-1"></i>Obteniendo ubicación...';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            // Update map
            if (locationCreateMap) {
                locationCreateMap.setView([lat, lon], 15);

                if (locationCreateMarker) {
                    locationCreateMap.removeLayer(locationCreateMarker);
                }

                locationCreateMarker = L.marker([lat, lon]).addTo(
                    locationCreateMap
                );
                locationCreateMarker
                    .bindPopup("Tu ubicación actual")
                    .openPopup();
            }

            // Get address
            reverseCreateGeocode(lat, lon)
                .then((address) => {
                    const searchInput = document.getElementById(
                        "location-search-create-input"
                    );
                    if (searchInput) {
                        searchInput.value = address; // Show actual address instead of generic text
                    }

                    selectedCreateLocation = {
                        lat,
                        lon,
                        address: address, // Use actual address
                        details: { display_name: address },
                    };

                    updateCreateHiddenInputs(lat, lon);
                })
                .catch((error) => {
                    const searchInput = document.getElementById(
                        "location-search-create-input"
                    );
                    if (searchInput) {
                        searchInput.value = `Ubicación: ${lat.toFixed(
                            6
                        )}, ${lon.toFixed(6)}`; // Fallback with coordinates
                    }

                    selectedCreateLocation = {
                        lat,
                        lon,
                        address: `Ubicación: ${lat.toFixed(6)}, ${lon.toFixed(
                            6
                        )}`,
                    };

                    updateCreateHiddenInputs(lat, lon);
                });

            button.disabled = false;
            button.innerHTML = originalText;
        },
        (error) => {
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

function clearCreateLocation() {
    selectedCreateLocation = null;

    const searchInput = document.getElementById("location-search-create-input");
    if (searchInput) {
        searchInput.value = "";
    }

    const suggestions = document.getElementById("location-suggestions-create");
    if (suggestions) {
        suggestions.classList.add("hidden");
    }

    if (locationCreateMarker && locationCreateMap) {
        locationCreateMap.removeLayer(locationCreateMarker);
        locationCreateMarker = null;
    }

    updateCreateHiddenInputs("", "");

    // Update the location display text to clear it
    if (window.updateLocationDisplayText) {
        window.updateLocationDisplayText(null);
    }
}

function applyCreateLocation() {
    if (selectedCreateLocation) {
        // Update form fields with location data
        const addressInput =
            document.getElementById("form_lostAddress") ||
            document.querySelector('input[name*="lostAddress"]') ||
            document.querySelector('input[name*="foundAddress"]');

        if (addressInput) {
            addressInput.value = selectedCreateLocation.address;
            // Enable manual editing
            addressInput.removeAttribute("readonly");
            addressInput.classList.remove("bg-gray-50", "text-gray-600");
            addressInput.classList.add("bg-white", "text-gray-900");
        }

        // Store coordinates globally for form submission
        window.coordinates = {
            latitude: selectedCreateLocation.lat,
            longitude: selectedCreateLocation.lon,
        };

        // Update the location display text
        if (window.updateLocationDisplayText) {
            window.updateLocationDisplayText(selectedCreateLocation.address);
        }

        console.log("Location applied:", selectedCreateLocation);
    }

    closeLocationSearchCreateModal();
}

function updateCreateHiddenInputs(lat, lon) {
    const latInput = document.getElementById("location-create-latitude");
    const lonInput = document.getElementById("location-create-longitude");

    if (latInput) latInput.value = lat;
    if (lonInput) lonInput.value = lon;
}

// Event listener for ESC key
function handleCreateEscapeKey(e) {
    const modal = document.getElementById("location-search-create-modal");
    if (e.key === "Escape" && modal && !modal.classList.contains("hidden")) {
        closeLocationSearchCreateModal();
    }
}

// Remove previous event listener and add new one
document.removeEventListener("keydown", handleCreateEscapeKey);
document.addEventListener("keydown", handleCreateEscapeKey);

// Initialize on initial load
document.addEventListener("DOMContentLoaded", initLocationSearchCreateModal);

// Initialize on Turbo navigations
document.addEventListener("turbo:load", initLocationSearchCreateModal);
