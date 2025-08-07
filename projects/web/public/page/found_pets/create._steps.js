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
                !mobileMenuButton.contains(e.target) &&
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

    // Initialize animal type other field functionality
    setTimeout(() => {
        initAnimalTypeOtherField();
    }, 100);

    // Don't initialize map here since we're using the modal
    // window.initMap();

    // Initialize first step after DOM is loaded
    showStep(1);

    // Add form submit listener to include coordinates
    const form = document.getElementById("pet-form");
    if (form) {
        form.addEventListener("submit", function (e) {
            window.addCoordinatesToForm();
        });
    }
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

// Coordinates for form submission
window.coordinates = { latitude: null, longitude: null };

// Function to add coordinates to form before submission
window.addCoordinatesToForm = function () {
    console.log("Adding coordinates to form");
    console.log("Current coordinates:", window.coordinates);

    const form = document.getElementById("pet-form");
    if (form && window.coordinates.latitude && window.coordinates.longitude) {
        // Remove existing coordinate inputs if any
        const existingLat = form.querySelector(
            'input[name="coordinates_latitude"]'
        );
        const existingLon = form.querySelector(
            'input[name="coordinates_longitude"]'
        );
        if (existingLat) existingLat.remove();
        if (existingLon) existingLon.remove();

        // Create hidden inputs for coordinates
        const latInput = document.createElement("input");
        latInput.type = "hidden";
        latInput.name = "coordinates_latitude";
        latInput.value = window.coordinates.latitude;

        const lonInput = document.createElement("input");
        lonInput.type = "hidden";
        lonInput.name = "coordinates_longitude";
        lonInput.value = window.coordinates.longitude;

        form.appendChild(latInput);
        form.appendChild(lonInput);

        console.log("Coordinates added to form:", window.coordinates);
    } else {
        console.error(
            "Cannot add coordinates to form. Form:",
            !!form,
            "Coordinates:",
            window.coordinates
        );
    }
};

// Function to update location display text
window.updateLocationDisplayText = function (address) {
    const displayText = document.getElementById("location-display-text");
    const locationPreview = document.getElementById("location-preview");
    const locationPreviewText = document.getElementById(
        "location-preview-text"
    );

    if (displayText) {
        displayText.textContent =
            address || "Haz clic para seleccionar ubicación";
    }

    // Update the button styling
    const locationBtn = document.querySelector(
        "[data-location-search-create-btn]"
    );
    if (locationBtn) {
        if (address) {
            locationBtn.classList.remove(
                "border-gray-300",
                "text-gray-600",
                "bg-gray-50"
            );
            locationBtn.classList.add(
                "border-green-400",
                "text-green-600",
                "bg-green-50"
            );

            // Show location preview
            if (locationPreview) {
                locationPreview.classList.remove("hidden");
            }
            if (locationPreviewText) {
                locationPreviewText.textContent = address;
            }
        } else {
            locationBtn.classList.remove(
                "border-green-400",
                "text-green-600",
                "bg-green-50"
            );
            locationBtn.classList.add(
                "border-gray-300",
                "text-gray-600",
                "bg-gray-50"
            );

            // Hide location preview
            if (locationPreview) {
                locationPreview.classList.add("hidden");
            }
        }
    }
};

// Function to clear selected location
window.clearSelectedLocation = function () {
    // Clear coordinates
    window.coordinates = { latitude: null, longitude: null };

    // Clear address input
    const addressInput =
        document.getElementById("form_foundAddress") ||
        document.querySelector('input[name*="foundAddress"]') ||
        document.querySelector('input[name*="lostAddress"]');
    if (addressInput) {
        addressInput.value = "";
        // Make it readonly again
        addressInput.setAttribute("readonly", true);
        addressInput.classList.remove("bg-white", "text-gray-900");
        addressInput.classList.add("bg-gray-50", "text-gray-600");
    }

    // Update display
    window.updateLocationDisplayText(null);
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
        // Check if location has been selected
        if (!window.coordinates.latitude || !window.coordinates.longitude) {
            alert("Por favor, selecciona una ubicación antes de continuar.");
            isValid = false;
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

// Function to handle animal type other field visibility
function initAnimalTypeOtherField() {
    console.log("Initializing animal type other field functionality");

    // Try different selectors to find the animal type select
    const animalTypeSelect =
        document.querySelector('select[name*="animalType"]') ||
        document.querySelector("#form_animalType") ||
        document.querySelector('select[name="form[animalType]"]');

    const animalTypeOtherContainer = document.getElementById(
        "animal-type-other-container"
    );
    const animalTypeOtherInput =
        document.querySelector('input[name*="animalTypeOther"]') ||
        document.querySelector("#form_animalTypeOther") ||
        document.querySelector('input[name="form[animalTypeOther]"]');

    console.log("Animal type select found:", !!animalTypeSelect);
    console.log(
        "Animal type other container found:",
        !!animalTypeOtherContainer
    );
    console.log("Animal type other input found:", !!animalTypeOtherInput);

    if (animalTypeSelect && animalTypeOtherContainer && animalTypeOtherInput) {
        // Check initial value
        console.log("Initial animal type value:", animalTypeSelect.value);
        toggleAnimalTypeOtherField(animalTypeSelect.value);

        // Add change event listener
        animalTypeSelect.addEventListener("change", function () {
            console.log("Animal type changed to:", this.value);
            toggleAnimalTypeOtherField(this.value);
        });

        console.log(
            "Animal type other field functionality initialized successfully"
        );
    } else {
        console.error(
            "Could not find required elements for animal type other field"
        );
        console.log("animalTypeSelect:", animalTypeSelect);
        console.log("animalTypeOtherContainer:", animalTypeOtherContainer);
        console.log("animalTypeOtherInput:", animalTypeOtherInput);
    }
}

// Function to toggle animal type other field visibility
function toggleAnimalTypeOtherField(selectedValue) {
    console.log("Toggling animal type other field for value:", selectedValue);

    const animalTypeOtherContainer = document.getElementById(
        "animal-type-other-container"
    );
    const animalTypeOtherInput =
        document.querySelector('input[name*="animalTypeOther"]') ||
        document.querySelector("#form_animalTypeOther") ||
        document.querySelector('input[name="form[animalTypeOther]"]');

    if (animalTypeOtherContainer && animalTypeOtherInput) {
        if (selectedValue === "otro") {
            animalTypeOtherContainer.style.display = "block";
            animalTypeOtherInput.setAttribute("required", "required");
            console.log("Animal type other field shown and made required");
        } else {
            animalTypeOtherContainer.style.display = "none";
            animalTypeOtherInput.removeAttribute("required");
            animalTypeOtherInput.value = ""; // Clear the field when hiding
            console.log("Animal type other field hidden and cleared");
        }
    } else {
        console.error("Could not find animal type other elements for toggling");
    }
}
