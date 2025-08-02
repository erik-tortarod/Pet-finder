import { Controller } from "@hotwired/stimulus";

// Global map registry to prevent conflicts
if (!window.mapRegistry) {
    window.mapRegistry = new Map();
}

// Global function to disconnect all location search controllers
window.disconnectAllLocationSearchControllers = function () {
    console.log("Disconnecting all location search controllers");
    if (window.mapRegistry) {
        window.mapRegistry.forEach((controller, id) => {
            console.log(`Disconnecting controller ${id}`);
            if (controller.disconnect) {
                controller.disconnect();
            }
        });
        window.mapRegistry.clear();
    }
};

// Global function to clean up all Leaflet maps in the DOM
window.cleanupAllLeafletMaps = function () {
    console.log("Cleaning up all Leaflet maps in DOM");

    // Remove all leaflet containers
    const leafletContainers = document.querySelectorAll(".leaflet-container");
    leafletContainers.forEach((container) => {
        console.log("Removing leaflet container:", container);
        try {
            container.remove();
        } catch (error) {
            console.warn("Error removing leaflet container:", error);
        }
    });

    // Remove all map containers with our pattern
    const mapContainers = document.querySelectorAll('[id^="map-container-"]');
    mapContainers.forEach((container) => {
        console.log("Removing map container:", container.id);
        try {
            container.remove();
        } catch (error) {
            console.warn("Error removing map container:", error);
        }
    });

    // Clear any map containers that might have content
    const allMapContainers = document.querySelectorAll(
        '[data-location-search-target="mapContainer"]'
    );
    allMapContainers.forEach((container) => {
        console.log("Clearing map container content");
        try {
            container.innerHTML = "";
        } catch (error) {
            console.warn("Error clearing map container:", error);
        }
    });
};

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
        // Clean up all existing Leaflet maps first
        window.cleanupAllLeafletMaps();

        // Disconnect all existing controllers first
        window.disconnectAllLocationSearchControllers();

        // Generate unique ID for this controller instance
        this.controllerId = Math.random().toString(36).substr(2, 9);

        console.log(`LocationSearch controller ${this.controllerId} connected`);
        console.log("Controller element:", this.element);
        console.log("Available targets:", this.targets);

        this.map = null;
        this.marker = null;
        this.searchTimeout = null;
        this.toggleTimeout = null;
        this.selectedLocation = null;
        this.mapInitialized = false;

        // Register this controller
        window.mapRegistry.set(this.controllerId, this);

        // Listen for Turbo navigation events
        this.handleTurboBeforeRender = this.handleTurboBeforeRender.bind(this);
        this.handleTurboBeforeCache = this.handleTurboBeforeCache.bind(this);
        this.handleTurboLoad = this.handleTurboLoad.bind(this);

        document.addEventListener(
            "turbo:before-render",
            this.handleTurboBeforeRender
        );
        document.addEventListener(
            "turbo:before-cache",
            this.handleTurboBeforeCache
        );
        document.addEventListener("turbo:load", this.handleTurboLoad);

        // Initialize immediately
        this.initializeController();
    }

    disconnect() {
        console.log(
            `LocationSearch controller ${this.controllerId} disconnected`
        );

        // Remove from registry
        window.mapRegistry.delete(this.controllerId);

        // Remove Turbo event listeners
        document.removeEventListener(
            "turbo:before-render",
            this.handleTurboBeforeRender
        );
        document.removeEventListener(
            "turbo:before-cache",
            this.handleTurboBeforeCache
        );
        document.removeEventListener("turbo:load", this.handleTurboLoad);

        // Clean up timeouts
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        if (this.toggleTimeout) {
            clearTimeout(this.toggleTimeout);
        }

        // Clean up event listeners
        this.cleanupEventListeners();

        // Properly clean up the map
        this.cleanupMap();

        // Clear the map container
        if (this.hasMapContainerTarget) {
            this.mapContainerTarget.innerHTML = "";
        }
    }

    handleTurboLoad() {
        console.log(
            `Controller ${this.controllerId}: Turbo load event - reinitializing controller`
        );
        // Reinitialize the controller after Turbo navigation
        this.initializeController();
    }

    handleTurboBeforeRender() {
        console.log(
            `Turbo before-render - cleaning up location search controller ${this.controllerId}`
        );
        this.cleanupMap();
    }

    handleTurboBeforeCache() {
        console.log(
            `Turbo before-cache - cleaning up location search controller ${this.controllerId}`
        );
        this.cleanupMap();
    }

    // Static method to clean up all maps globally
    static cleanupAllMaps() {
        console.log("Cleaning up all maps globally");
        if (window.mapRegistry) {
            window.mapRegistry.forEach((controller, id) => {
                console.log(`Cleaning up map from controller ${id}`);
                controller.cleanupMap();
            });
        }
    }

    cleanupMap() {
        if (this.map) {
            console.log(
                `Controller ${this.controllerId}: Cleaning up existing map`
            );
            try {
                // Remove all layers first
                this.map.eachLayer((layer) => {
                    this.map.removeLayer(layer);
                });
                // Remove the map instance
                this.map.remove();
                console.log(
                    `Controller ${this.controllerId}: Map cleaned up successfully`
                );
            } catch (error) {
                console.warn(
                    `Controller ${this.controllerId}: Error cleaning up map:`,
                    error
                );
            }
            this.map = null;
            this.mapInitialized = false;
        } else {
            console.log(`Controller ${this.controllerId}: No map to clean up`);
        }

        // Aggressive cleanup of the container
        if (this.hasMapContainerTarget && this.mapContainerTarget) {
            console.log(
                `Controller ${this.controllerId}: Aggressively cleaning map container`
            );

            try {
                // Remove all child elements
                while (this.mapContainerTarget.firstChild) {
                    this.mapContainerTarget.removeChild(
                        this.mapContainerTarget.firstChild
                    );
                }

                // Remove any Leaflet-specific classes and attributes
                this.mapContainerTarget.className =
                    this.mapContainerTarget.className.replace(
                        /\bleaflet\S*/g,
                        ""
                    );
                this.mapContainerTarget.removeAttribute("style");

                // Remove unique identifiers
                this.mapContainerTarget.removeAttribute("data-controller-id");
                this.mapContainerTarget.removeAttribute("id");

                // Force a reflow to ensure DOM is clean
                this.mapContainerTarget.offsetHeight;
            } catch (error) {
                console.warn(
                    `Controller ${this.controllerId}: Error cleaning container:`,
                    error
                );
            }
        }
    }

    cleanupEventListeners() {
        // Remove any global event listeners that might persist
        if (this.searchInputTarget) {
            this.searchInputTarget.removeEventListener(
                "input",
                this.handleSearchInput
            );
        }
        if (this.currentLocationBtnTarget) {
            this.currentLocationBtnTarget.removeEventListener(
                "click",
                this.handleCurrentLocation
            );
        }
        if (this.toggleButtonTarget) {
            this.toggleButtonTarget.removeEventListener(
                "click",
                this.handleToggle
            );
        }
        if (this.quickFiltersTarget) {
            this.quickFiltersTarget.removeEventListener(
                "click",
                this.handleQuickFilters
            );
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
            // Store reference to handler for cleanup
            this.handleQuickFilters = (e) => {
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
            };

            this.quickFiltersTarget.addEventListener(
                "click",
                this.handleQuickFilters
            );
        }
    }

    initializeLocationSearch() {
        if (this.hasSearchInputTarget && this.hasCurrentLocationBtnTarget) {
            // Store reference to handlers for cleanup
            this.handleSearchInput = (e) => {
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
            };

            this.handleCurrentLocation = () => {
                this.getCurrentLocation();
            };

            // Handle input changes
            this.searchInputTarget.addEventListener(
                "input",
                this.handleSearchInput
            );

            // Hide suggestions when clicking outside
            document.addEventListener("click", (e) => {
                if (!e.target.closest(".search-container")) {
                    this.suggestionsTarget.classList.add("hidden");
                }
            });

            // Get current location
            this.currentLocationBtnTarget.addEventListener(
                "click",
                this.handleCurrentLocation
            );
        }
    }

    initializeToggleButton() {
        if (this.hasToggleButtonTarget) {
            // Store reference to handler for cleanup
            this.handleToggle = () => {
                this.toggleLocationSearch();
            };

            this.toggleButtonTarget.addEventListener(
                "click",
                this.handleToggle
            );
        }
    }

    initializeController() {
        console.log(`Controller ${this.controllerId}: Initializing controller`);

        // Reset state
        this.mapInitialized = false;
        this.selectedLocation = null;

        // Clean up any existing maps
        this.cleanupMap();
        this.cleanupOtherMaps();

        // Initialize components
        this.initializeLocationIndicator();
        this.initializeQuickFilters();
        this.initializeLocationSearch();
        this.initializeToggleButton();

        console.log(
            `Controller ${this.controllerId}: Controller initialized successfully`
        );
    }

    toggleLocationSearch() {
        // Check if controller is in valid state
        if (!this.isValidState()) {
            console.warn(
                `Controller ${this.controllerId}: Controller not in valid state, forcing reset`
            );
            this.forceReset();
            return;
        }

        // Prevent rapid clicking
        if (this.toggleTimeout) {
            clearTimeout(this.toggleTimeout);
        }

        this.toggleTimeout = setTimeout(() => {
            try {
                if (
                    this.hasSearchSectionTarget &&
                    this.searchSectionTarget.style.display === "none"
                ) {
                    console.log(
                        `Controller ${this.controllerId}: Showing location search section`
                    );
                    this.searchSectionTarget.style.display = "block";

                    if (this.hasMapContainerTarget) {
                        this.mapContainerTarget.style.display = "block";
                    }

                    // Initialize map when showing the section
                    this.mapInitialized = false;
                    this.initMap();
                } else if (this.hasSearchSectionTarget) {
                    console.log(
                        `Controller ${this.controllerId}: Hiding location search section`
                    );
                    this.searchSectionTarget.style.display = "none";

                    if (this.hasMapContainerTarget) {
                        this.mapContainerTarget.style.display = "none";
                    }
                }
            } catch (error) {
                console.error(
                    `Controller ${this.controllerId}: Error in toggleLocationSearch:`,
                    error
                );
                // Try to reset the controller state
                this.forceReset();
            }
        }, 100);
    }

    // Method to reinitialize the controller (useful for debugging)
    reinitialize() {
        console.log(
            `Controller ${this.controllerId}: Reinitializing location search controller`
        );
        this.cleanupMap();
        this.mapInitialized = false;

        // Reinitialize components
        this.initializeLocationIndicator();
        this.initializeQuickFilters();
        this.initializeLocationSearch();
        this.initializeToggleButton();
    }

    // Method to check if controller is in valid state
    isValidState() {
        return (
            this.element &&
            this.element.isConnected &&
            this.hasMapContainerTarget
        );
    }

    // Method to check for container conflicts
    hasContainerConflict() {
        if (!this.hasMapContainerTarget) return false;

        // Check if the container has any Leaflet-specific elements
        const hasLeafletElements =
            this.mapContainerTarget.querySelector(".leaflet-container") !==
            null;
        const hasLeafletClasses =
            this.mapContainerTarget.className.includes("leaflet");

        // Check if there are any other map containers in the DOM
        const otherMapContainers = document.querySelectorAll(
            '[id^="map-container-"]'
        );
        const hasOtherContainers = otherMapContainers.length > 0;

        // Check if the container has any child elements (potential map elements)
        const hasChildElements = this.mapContainerTarget.children.length > 0;

        return (
            hasLeafletElements ||
            hasLeafletClasses ||
            hasOtherContainers ||
            hasChildElements
        );
    }

    // Method to force reset the controller state
    forceReset() {
        console.log(
            `Controller ${this.controllerId}: Force resetting controller state`
        );
        this.cleanupMap();
        this.mapInitialized = false;
        this.selectedLocation = null;

        try {
            if (this.hasMapContainerTarget && this.mapContainerTarget) {
                this.mapContainerTarget.innerHTML = "";
            }

            if (this.hasSearchSectionTarget && this.searchSectionTarget) {
                this.searchSectionTarget.style.display = "none";
            }

            if (this.hasLocationTextTarget && this.locationTextTarget) {
                this.locationTextTarget.textContent = "Buscar por ubicación";
            }
        } catch (error) {
            console.warn(
                `Controller ${this.controllerId}: Error in forceReset:`,
                error
            );
        }
    }

    initMap() {
        console.log(
            `Controller ${this.controllerId}: Starting map initialization`
        );

        try {
            // Check if Leaflet is loaded
            if (typeof L === "undefined") {
                console.error(
                    `Controller ${this.controllerId}: Leaflet is not loaded`
                );
                return;
            }

            // Check if map container exists
            if (!this.hasMapContainerTarget) {
                console.error(
                    `Controller ${this.controllerId}: Map container not found`
                );
                return;
            }

            // Check if map is already initialized
            if (this.mapInitialized && this.map) {
                console.log(
                    `Controller ${this.controllerId}: Map already initialized, skipping...`
                );
                return;
            }

            // Clean up any existing maps
            this.cleanupMap();

            // Clear the container completely
            this.mapContainerTarget.innerHTML = "";

            // Create a new map container with a unique ID
            const newMapContainer = document.createElement("div");
            newMapContainer.id = `map-container-${
                this.controllerId
            }-${Date.now()}`;
            newMapContainer.className =
                "w-full h-64 rounded-lg border border-gray-300";
            newMapContainer.style.cssText = "width: 100%; height: 256px;";
            newMapContainer.setAttribute(
                "data-controller-id",
                this.controllerId
            );

            // Add the new container
            this.mapContainerTarget.appendChild(newMapContainer);

            console.log(
                `Controller ${this.controllerId}: Creating Leaflet map instance in container: ${newMapContainer.id}`
            );

            // Create the map
            this.map = L.map(newMapContainer).setView([40.4168, -3.7038], 10);

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap contributors",
            }).addTo(this.map);

            // Add click event to map for manual location selection
            this.map.on("click", (e) => this.onMapClick(e));

            this.mapInitialized = true;
            console.log(
                `Controller ${this.controllerId}: Map initialized successfully`
            );
        } catch (error) {
            console.error(
                `Controller ${this.controllerId}: Error initializing map:`,
                error
            );
            this.mapInitialized = false;
            // Clear the container on error
            if (this.hasMapContainerTarget) {
                this.mapContainerTarget.innerHTML = "";
            }
        }
    }

    cleanupOtherMaps() {
        console.log(`Controller ${this.controllerId}: Cleaning up other maps`);

        // Clean up maps from other controllers
        window.mapRegistry.forEach((controller, id) => {
            if (id !== this.controllerId && controller.map) {
                console.log(
                    `Controller ${this.controllerId}: Cleaning up map from controller ${id}`
                );
                controller.cleanupMap();
            }
        });

        // Also clean up any existing Leaflet maps in the DOM that might be orphaned
        const existingMaps = document.querySelectorAll(".leaflet-container");
        existingMaps.forEach((mapElement) => {
            console.log(
                `Controller ${this.controllerId}: Removing orphaned map element`
            );
            try {
                mapElement.remove();
            } catch (error) {
                console.warn(
                    `Controller ${this.controllerId}: Error removing orphaned map:`,
                    error
                );
            }
        });

        // Clean up any map containers with our specific ID pattern
        const mapContainers = document.querySelectorAll(
            '[id^="map-container-"]'
        );
        mapContainers.forEach((container) => {
            if (container.id !== `map-container-${this.controllerId}`) {
                console.log(
                    `Controller ${this.controllerId}: Removing other map container: ${container.id}`
                );
                try {
                    container.remove();
                } catch (error) {
                    console.warn(
                        `Controller ${this.controllerId}: Error removing other map container:`,
                        error
                    );
                }
            }
        });
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
