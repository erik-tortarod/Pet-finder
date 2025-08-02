// Global variables for location search
let locationMap = null;
let locationMarker = null;
let locationSearchTimeout = null;
let selectedLocation = null;

// Function to initialize the location search modal
function initLocationSearchModal() {
    const locationSearchBtns = document.querySelectorAll(
        "[data-location-search-btn]"
    );
    const modal = document.getElementById("location-search-modal");
    const closeModal = document.getElementById("close-location-search-modal");
    const searchInput = document.getElementById("location-search-input");
    const currentLocationBtn = document.getElementById("current-location-btn");
    const clearLocationBtn = document.getElementById("clear-location-btn");
    const applyLocationBtn = document.getElementById("apply-location-btn");
    const suggestions = document.getElementById("location-suggestions");

    // Remove previous event listeners to avoid duplicates
    locationSearchBtns.forEach((btn) => {
        btn.removeEventListener("click", openLocationSearchModal);
        btn.addEventListener("click", openLocationSearchModal);
    });

    if (closeModal) {
        closeModal.removeEventListener("click", closeLocationSearchModal);
        closeModal.addEventListener("click", closeLocationSearchModal);
    }

    if (modal) {
        modal.removeEventListener("click", closeOnOutsideClick);
        modal.addEventListener("click", closeOnOutsideClick);
    }

    if (searchInput) {
        searchInput.removeEventListener("input", handleSearchInput);
        searchInput.addEventListener("input", handleSearchInput);
    }

    if (currentLocationBtn) {
        currentLocationBtn.removeEventListener("click", getCurrentLocation);
        currentLocationBtn.addEventListener("click", getCurrentLocation);
    }

    if (clearLocationBtn) {
        clearLocationBtn.removeEventListener("click", clearLocation);
        clearLocationBtn.addEventListener("click", clearLocation);
    }

    if (applyLocationBtn) {
        applyLocationBtn.removeEventListener("click", applyLocation);
        applyLocationBtn.addEventListener("click", applyLocation);
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
function openLocationSearchModal() {
    const modal = document.getElementById("location-search-modal");
    if (modal) {
        modal.classList.remove("hidden");
        modal.style.backgroundColor = "rgba(75, 85, 99, 0.5)";

        // Initialize map when modal opens
        setTimeout(() => {
            initLocationMap();
        }, 100);
    }
}

function closeLocationSearchModal() {
    const modal = document.getElementById("location-search-modal");
    if (modal) {
        modal.classList.add("hidden");
    }

    // Clean up map when modal closes
    cleanupLocationMap();
}

function closeOnOutsideClick(e) {
    if (e.target.id === "location-search-modal") {
        closeLocationSearchModal();
    }
}

// Map functions
function initLocationMap() {
    try {
        const mapContainer = document.getElementById("location-map-container");
        if (!mapContainer || !window.L) {
            console.error("Map container or Leaflet not available");
            return;
        }

        // Clean up existing map
        cleanupLocationMap();

        // Clear container
        mapContainer.innerHTML = "";

        // Create new map
        locationMap = L.map(mapContainer).setView([40.4168, -3.7038], 10);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
        }).addTo(locationMap);

        // Add click event to map
        locationMap.on("click", onMapClick);

        console.log("Location map initialized successfully");
    } catch (error) {
        console.error("Error initializing location map:", error);
    }
}

function cleanupLocationMap() {
    if (locationMap) {
        try {
            locationMap.remove();
        } catch (error) {
            console.warn("Error cleaning up map:", error);
        }
        locationMap = null;
        locationMarker = null;
    }
}

function onMapClick(e) {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;

    // Update map marker
    if (locationMarker) {
        locationMap.removeLayer(locationMarker);
    }

    locationMarker = L.marker([lat, lon]).addTo(locationMap);

    // Get address for clicked location
    reverseGeocode(lat, lon)
        .then((address) => {
            locationMarker.bindPopup(address).openPopup();

            const searchInput = document.getElementById(
                "location-search-input"
            );
            if (searchInput) {
                searchInput.value = address;
            }

            selectedLocation = { lat, lon, address };
            updateHiddenInputs(lat, lon);
        })
        .catch((error) => {
            const fallbackAddress = `Ubicación: ${lat.toFixed(
                6
            )}, ${lon.toFixed(6)}`;
            locationMarker.bindPopup(fallbackAddress).openPopup();

            const searchInput = document.getElementById(
                "location-search-input"
            );
            if (searchInput) {
                searchInput.value = fallbackAddress;
            }

            selectedLocation = { lat, lon, address: fallbackAddress };
            updateHiddenInputs(lat, lon);
        });
}

// Search functions
function handleSearchInput(e) {
    const query = e.target.value.trim();
    const suggestions = document.getElementById("location-suggestions");

    if (query.length < 3) {
        if (suggestions) {
            suggestions.classList.add("hidden");
        }
        return;
    }

    // Debounce search
    clearTimeout(locationSearchTimeout);
    locationSearchTimeout = setTimeout(async () => {
        const places = await searchPlaces(query);
        showSuggestions(places);
    }, 300);
}

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

function showSuggestions(places) {
    const suggestions = document.getElementById("location-suggestions");
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
        div.onclick = () => selectPlace(place);
        suggestions.appendChild(div);
    });

    suggestions.classList.remove("hidden");
}

function selectPlace(place) {
    const searchInput = document.getElementById("location-search-input");
    const suggestions = document.getElementById("location-suggestions");

    if (searchInput) {
        searchInput.value = place.display_name;
    }

    if (suggestions) {
        suggestions.classList.add("hidden");
    }

    // Update map
    const lat = parseFloat(place.lat);
    const lon = parseFloat(place.lon);

    if (locationMap) {
        locationMap.setView([lat, lon], 15);

        if (locationMarker) {
            locationMap.removeLayer(locationMarker);
        }

        locationMarker = L.marker([lat, lon]).addTo(locationMap);
        locationMarker.bindPopup(place.display_name).openPopup();
    }

    selectedLocation = {
        lat,
        lon,
        address: place.display_name,
        details: place,
    };

    updateHiddenInputs(lat, lon);
}

async function reverseGeocode(lat, lon) {
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
        (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            // Update map
            if (locationMap) {
                locationMap.setView([lat, lon], 15);

                if (locationMarker) {
                    locationMap.removeLayer(locationMarker);
                }

                locationMarker = L.marker([lat, lon]).addTo(locationMap);
                locationMarker.bindPopup("Tu ubicación actual").openPopup();
            }

            // Get address
            reverseGeocode(lat, lon)
                .then((address) => {
                    const searchInput = document.getElementById(
                        "location-search-input"
                    );
                    if (searchInput) {
                        searchInput.value = "Mi ubicación actual";
                    }

                    selectedLocation = {
                        lat,
                        lon,
                        address: "Mi ubicación actual",
                        details: { display_name: address },
                    };

                    updateHiddenInputs(lat, lon);
                })
                .catch((error) => {
                    const searchInput = document.getElementById(
                        "location-search-input"
                    );
                    if (searchInput) {
                        searchInput.value = "Mi ubicación actual";
                    }

                    selectedLocation = {
                        lat,
                        lon,
                        address: "Mi ubicación actual",
                    };

                    updateHiddenInputs(lat, lon);
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

function clearLocation() {
    selectedLocation = null;

    const searchInput = document.getElementById("location-search-input");
    if (searchInput) {
        searchInput.value = "";
    }

    const suggestions = document.getElementById("location-suggestions");
    if (suggestions) {
        suggestions.classList.add("hidden");
    }

    if (locationMarker && locationMap) {
        locationMap.removeLayer(locationMarker);
        locationMarker = null;
    }

    updateHiddenInputs("", "");
}

function applyLocation() {
    if (selectedLocation) {
        // Update any forms on the page with location data
        const latitudeInputs = document.querySelectorAll(
            'input[name="latitude"]'
        );
        const longitudeInputs = document.querySelectorAll(
            'input[name="longitude"]'
        );

        latitudeInputs.forEach((input) => {
            input.value = selectedLocation.lat;
        });

        longitudeInputs.forEach((input) => {
            input.value = selectedLocation.lon;
        });

        // Update location text if it exists
        const locationTexts = document.querySelectorAll("[data-location-text]");
        locationTexts.forEach((text) => {
            text.textContent = "Ubicación seleccionada";
        });

        // Find and submit the form automatically
        const forms = document.querySelectorAll("form");
        forms.forEach((form) => {
            // Check if this form has location inputs
            const hasLocationInputs =
                form.querySelector('input[name="latitude"]') ||
                form.querySelector('input[name="longitude"]');
            if (hasLocationInputs) {
                console.log("Submitting form with location data");
                form.submit();
            }
        });
    }

    closeLocationSearchModal();
}

function updateHiddenInputs(lat, lon) {
    const latInput = document.getElementById("location-latitude");
    const lonInput = document.getElementById("location-longitude");

    if (latInput) latInput.value = lat;
    if (lonInput) lonInput.value = lon;
}

// Event listener for ESC key
function handleEscapeKey(e) {
    const modal = document.getElementById("location-search-modal");
    if (e.key === "Escape" && modal && !modal.classList.contains("hidden")) {
        closeLocationSearchModal();
    }
}

// Remove previous event listener and add new one
document.removeEventListener("keydown", handleEscapeKey);
document.addEventListener("keydown", handleEscapeKey);

// Initialize on initial load
document.addEventListener("DOMContentLoaded", initLocationSearchModal);

// Initialize on Turbo navigations
document.addEventListener("turbo:load", initLocationSearchModal);
