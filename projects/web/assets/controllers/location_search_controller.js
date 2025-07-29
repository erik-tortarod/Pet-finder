import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "toggleButton",
        "locationText",
        "latitudeInput",
        "longitudeInput",
        "searchSection",
        "searchInput",
        "currentLocationBtn",
        "mapContainer",
        "suggestions",
        "form",
        "quickFilters",
        "tagsInput",
    ];

    static values = {
        latitude: String,
        longitude: String,
    };

    connect() {
        console.log("LocationSearch controller connected");
        console.log("Controller element:", this.element);
        console.log("Available targets:", this.targets);

        this.map = null;
        this.marker = null;
        this.searchTimeout = null;
        this.selectedLocation = null;
        this.mapInitialized = false;

        this.initializeLocationIndicator();
        this.initializeQuickFilters();
        this.initializeLocationSearch();
        this.initializeToggleButton();
    }

    disconnect() {
        console.log("LocationSearch controller disconnected");
        if (this.map) {
            this.map.remove();
            this.map = null;
            this.mapInitialized = false;
        }
    }

    initializeLocationIndicator() {
        // Check if we have location values from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const urlLatitude = urlParams.get("latitude");
        const urlLongitude = urlParams.get("longitude");

        // If we have URL parameters, use them
        if (urlLatitude && urlLongitude) {
            this.latitudeInputTarget.value = urlLatitude;
            this.longitudeInputTarget.value = urlLongitude;
            this.locationTextTarget.textContent = "Ubicación seleccionada";
            console.log(
                "Location initialized from URL:",
                urlLatitude,
                urlLongitude
            );
        }
        // Otherwise check if we have values in the inputs
        else if (
            this.latitudeInputTarget.value &&
            this.longitudeInputTarget.value
        ) {
            this.locationTextTarget.textContent = "Ubicación seleccionada";
            console.log(
                "Location initialized from inputs:",
                this.latitudeInputTarget.value,
                this.longitudeInputTarget.value
            );
        }
    }

    initializeQuickFilters() {
        if (this.hasQuickFiltersTarget && this.hasTagsInputTarget) {
            this.quickFiltersTarget.addEventListener("click", (e) => {
                if (e.target.hasAttribute("data-tag")) {
                    e.preventDefault();

                    const button = e.target;
                    const tag = button.getAttribute("data-tag");
                    const isActive = button.classList.contains("tag-active");

                    // Get current tags
                    let currentTags = this.tagsInputTarget.value
                        ? this.tagsInputTarget.value.split(",")
                        : [];

                    if (isActive) {
                        // Remove tag
                        button.classList.remove("tag-active");
                        button.classList.add("tag-innactive");
                        currentTags = currentTags.filter((t) => t !== tag);
                    } else {
                        // Add tag
                        button.classList.remove("tag-innactive");
                        button.classList.add("tag-active");
                        currentTags.push(tag);
                    }

                    // Update hidden input
                    this.tagsInputTarget.value = currentTags.join(",");

                    // Trigger filters update instead of form submit
                    this.triggerFiltersUpdate();
                }
            });
        }
    }

    initializeLocationSearch() {
        if (this.hasSearchInputTarget && this.hasCurrentLocationBtnTarget) {
            // Handle input changes
            this.searchInputTarget.addEventListener("input", (e) => {
                const query = e.target.value.trim();

                if (query.length < 3) {
                    this.suggestionsTarget.classList.add("hidden");
                    return;
                }

                // Debounce search
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(async () => {
                    const places = await this.searchPlaces(query);
                    this.showSuggestions(places);
                }, 300);
            });

            // Hide suggestions when clicking outside
            document.addEventListener("click", (e) => {
                if (!e.target.closest(".search-container")) {
                    this.suggestionsTarget.classList.add("hidden");
                }
            });

            // Get current location
            this.currentLocationBtnTarget.addEventListener("click", () => {
                this.getCurrentLocation();
            });
        }
    }

    initializeToggleButton() {
        if (this.hasToggleButtonTarget) {
            this.toggleButtonTarget.addEventListener("click", () => {
                this.toggleLocationSearch();
            });
        }
    }

    toggleLocationSearch() {
        if (this.searchSectionTarget.style.display === "none") {
            this.searchSectionTarget.style.display = "block";
            this.mapContainerTarget.style.display = "block";

            // Initialize map if not already done
            if (!this.mapInitialized) {
                this.initMap();
            } else if (this.map) {
                // Refresh map size
                setTimeout(() => {
                    if (
                        this.map &&
                        typeof this.map.invalidateSize === "function"
                    ) {
                        this.map.invalidateSize();
                    }
                }, 100);
            }
        } else {
            this.searchSectionTarget.style.display = "none";
            this.mapContainerTarget.style.display = "none";
        }
    }

    initMap() {
        try {
            // Check if Leaflet is loaded
            if (typeof L === "undefined") {
                console.error("Leaflet is not loaded");
                return;
            }

            // Check if map container exists
            if (!this.hasMapContainerTarget) {
                console.error("Map container not found");
                return;
            }

            // Check if map is already initialized
            if (this.mapInitialized) {
                console.log("Map already initialized, skipping...");
                return;
            }

            // Clear the container first to avoid conflicts
            this.mapContainerTarget.innerHTML = "";

            // Default to Spain coordinates
            this.map = L.map(this.mapContainerTarget).setView(
                [40.4168, -3.7038],
                10
            );

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap contributors",
            }).addTo(this.map);

            // Add click event to map for manual location selection
            this.map.on("click", (e) => this.onMapClick(e));

            this.mapInitialized = true;
            console.log("Map initialized successfully");
        } catch (error) {
            console.error("Error initializing map:", error);
            this.mapInitialized = false;
        }
    }

    async onMapClick(e) {
        const lat = e.latlng.lat;
        const lon = e.latlng.lng;

        // Update map
        if (this.marker) {
            this.map.removeLayer(this.marker);
        }

        this.marker = L.marker([lat, lon]).addTo(this.map);

        // Try to get address for the clicked location
        try {
            const addressData = await this.reverseGeocodeDetailed(lat, lon);
            const fullAddress = addressData.display_name;

            this.marker.bindPopup(fullAddress).openPopup();
            this.searchInputTarget.value = fullAddress;

            this.selectedLocation = {
                lat,
                lon,
                address: fullAddress,
                details: addressData,
            };

            // Update hidden inputs
            this.latitudeInputTarget.value = lat;
            this.longitudeInputTarget.value = lon;
            this.locationTextTarget.textContent = "Ubicación seleccionada";

            // Trigger filters update instead of form submit
            this.triggerFiltersUpdate();
        } catch (error) {
            const fallbackAddress = `Ubicación: ${lat.toFixed(
                6
            )}, ${lon.toFixed(6)}`;
            this.marker.bindPopup(fallbackAddress).openPopup();
            this.searchInputTarget.value = fallbackAddress;
            this.selectedLocation = { lat, lon, address: fallbackAddress };

            // Update hidden inputs
            this.latitudeInputTarget.value = lat;
            this.longitudeInputTarget.value = lon;
            this.locationTextTarget.textContent = "Ubicación seleccionada";

            // Trigger filters update instead of form submit
            this.triggerFiltersUpdate();
        }

        console.log("Manual location selected:", lat, lon);
    }

    async reverseGeocodeDetailed(lat, lon) {
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

    async searchPlaces(query) {
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

    showSuggestions(places) {
        this.suggestionsTarget.innerHTML = "";

        if (places.length === 0) {
            this.suggestionsTarget.classList.add("hidden");
            return;
        }

        places.forEach((place) => {
            const div = document.createElement("div");
            div.className =
                "px-3 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-200 last:border-b-0";
            div.textContent = place.display_name;
            div.onclick = () => this.selectPlace(place);
            this.suggestionsTarget.appendChild(div);
        });

        this.suggestionsTarget.classList.remove("hidden");
    }

    selectPlace(place) {
        console.log("selectPlace called with:", place);

        if (!this.map || !this.mapInitialized) {
            console.error("Map not initialized");
            return;
        }

        if (this.hasSearchInputTarget) {
            this.searchInputTarget.value = place.display_name;
        }

        this.suggestionsTarget.classList.add("hidden");

        // Update map
        const lat = parseFloat(place.lat);
        const lon = parseFloat(place.lon);

        try {
            this.map.setView([lat, lon], 15);

            if (this.marker) {
                this.map.removeLayer(this.marker);
            }

            this.marker = L.marker([lat, lon]).addTo(this.map);
            this.marker.bindPopup(place.display_name).openPopup();

            this.selectedLocation = {
                lat,
                lon,
                address: place.display_name,
                details: place,
            };

            // Update hidden inputs
            this.latitudeInputTarget.value = lat;
            this.longitudeInputTarget.value = lon;
            this.locationTextTarget.textContent = "Ubicación seleccionada";

            // Trigger filters update instead of form submit
            this.triggerFiltersUpdate();

            console.log("Selected place:", place);
            console.log("Coordinates:", lat, lon);
        } catch (error) {
            console.error("Error in selectPlace:", error);
        }
    }

    getCurrentLocation() {
        const button = this.currentLocationBtnTarget;
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
                if (this.map && this.mapInitialized) {
                    this.map.setView([lat, lon], 15);

                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                    }

                    this.marker = L.marker([lat, lon]).addTo(this.map);
                    this.marker.bindPopup("Tu ubicación actual").openPopup();
                }

                // Get detailed address information for current location
                this.reverseGeocodeDetailed(lat, lon)
                    .then((addressData) => {
                        this.selectedLocation = {
                            lat,
                            lon,
                            address: "Mi ubicación actual",
                            details: addressData,
                        };

                        // Update hidden inputs
                        this.latitudeInputTarget.value = lat;
                        this.longitudeInputTarget.value = lon;
                        this.locationTextTarget.textContent =
                            "Ubicación actual";

                        // Trigger filters update instead of form submit
                        this.triggerFiltersUpdate();
                    })
                    .catch((error) => {
                        console.error(
                            "Error getting address details for current location:",
                            error
                        );
                        this.selectedLocation = {
                            lat,
                            lon,
                            address: "Mi ubicación actual",
                        };

                        // Update hidden inputs
                        this.latitudeInputTarget.value = lat;
                        this.longitudeInputTarget.value = lon;
                        this.locationTextTarget.textContent =
                            "Ubicación actual";

                        // Trigger filters update instead of form submit
                        this.triggerFiltersUpdate();
                    });

                // Update search input
                if (this.hasSearchInputTarget) {
                    this.searchInputTarget.value = "Mi ubicación actual";
                }

                console.log("Current location:", lat, lon);

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
                        errorMessage +=
                            "Información de ubicación no disponible.";
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

    // Trigger filters update instead of form submit
    triggerFiltersUpdate() {
        // Get the filters controller from the same element
        const filtersController =
            this.application.getControllerForElementAndIdentifier(
                this.element,
                "filters"
            );

        if (filtersController) {
            // Update location filters in the filters controller
            filtersController.updateLocationFilters(
                this.latitudeInputTarget.value,
                this.longitudeInputTarget.value
            );
        } else {
            // Fallback to form submit if filters controller is not available
            console.warn(
                "Filters controller not found, falling back to form submit"
            );
            setTimeout(() => {
                this.formTarget.submit();
            }, 500);
        }
    }

    clearLocation() {
        // Clear location inputs
        this.latitudeInputTarget.value = "";
        this.longitudeInputTarget.value = "";
        this.locationTextTarget.textContent = "Buscar por ubicación";

        // Clear search input
        if (this.hasSearchInputTarget) {
            this.searchInputTarget.value = "";
        }

        // Clear map marker
        if (this.marker && this.map) {
            this.map.removeLayer(this.marker);
            this.marker = null;
        }

        // Trigger filters update
        this.triggerFiltersUpdate();
    }
}
