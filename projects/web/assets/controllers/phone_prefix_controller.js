import { Controller } from "@hotwired/stimulus";
import { phones } from "./phones.js";

export default class extends Controller {
    static targets = [
        "select",
        "input",
        "display",
        "dropdown",
        "search",
        "options",
        "button",
    ];
    static values = {
        selectedPrefix: String,
        phoneNumber: String,
        isOpen: Boolean,
    };

    connect() {
        console.log("Phone prefix controller connected");
        this.initializeSelect();
        this.setDefaultCountry();
        this.isOpenValue = false;
        this.bindGlobalEvents();
    }

    disconnect() {
        console.log("Phone prefix controller disconnected");
        this.removeGlobalEvents();
    }

    // Method to reinitialize the controller if needed
    reinitialize() {
        console.log("Reinitializing phone prefix controller");
        this.initializeSelect();
        this.setDefaultCountry();
        this.isOpenValue = false;
    }

    bindGlobalEvents() {
        this.handleClickOutside = this.handleClickOutside.bind(this);
        document.addEventListener("click", this.handleClickOutside);
    }

    removeGlobalEvents() {
        document.removeEventListener("click", this.handleClickOutside);
    }

    handleClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.closeDropdown();
        }
    }

    initializeSelect() {
        if (this.hasSelectTarget) {
            // Limpiar opciones existentes
            this.selectTarget.innerHTML = "";

            // Agregar opción por defecto
            const defaultOption = document.createElement("option");
            defaultOption.value = "";
            defaultOption.textContent = "Seleccionar país";
            defaultOption.disabled = true;
            defaultOption.selected = true;
            this.selectTarget.appendChild(defaultOption);

            // Agregar países ordenados alfabéticamente
            phones
                .sort((a, b) => a.pais.localeCompare(b.pais))
                .forEach((country) => {
                    const option = document.createElement("option");
                    option.value = country.prefijo;
                    option.textContent = `${country.bandera} ${country.pais} (${country.prefijo})`;
                    option.dataset.country = country.pais;
                    option.dataset.flag = country.bandera;
                    this.selectTarget.appendChild(option);
                });
        }

        // Inicializar dropdown personalizado
        this.renderDropdownOptions();
    }

    renderDropdownOptions() {
        if (this.hasOptionsTarget) {
            this.optionsTarget.innerHTML = "";

            phones
                .sort((a, b) => a.pais.localeCompare(b.pais))
                .forEach((country) => {
                    const option = document.createElement("div");
                    option.className =
                        "px-4 py-3 hover:bg-blue-50 cursor-pointer flex items-center space-x-3 transition-colors duration-150 border-b border-gray-100 last:border-b-0";
                    option.dataset.prefix = country.prefijo;
                    option.dataset.country = country.pais;
                    option.dataset.flag = country.bandera;
                    option.dataset.nativeName = country.nombre_nativo;
                    option.innerHTML = `
                        <span class="text-xl flex-shrink-0">${country.bandera}</span>
                        <div class="flex-1 min-w-0">
                            <div class="text-gray-900 font-medium">${country.nombre_nativo}</div>
                            <div class="text-gray-500 text-sm">${country.prefijo}</div>
                        </div>
                    `;

                    option.addEventListener("click", () =>
                        this.selectCountryFromDropdown(country)
                    );
                    this.optionsTarget.appendChild(option);
                });
        }
    }

    setDefaultCountry() {
        // Intentar detectar el país del usuario basado en el idioma del navegador
        const userLanguage = navigator.language || navigator.userLanguage;
        let defaultCountry = "España"; // País por defecto

        if (userLanguage.includes("es")) {
            defaultCountry = "España";
        } else if (userLanguage.includes("en")) {
            defaultCountry = "Estados Unidos";
        } else if (userLanguage.includes("fr")) {
            defaultCountry = "Francia";
        } else if (userLanguage.includes("de")) {
            defaultCountry = "Alemania";
        }

        // Buscar el país en la lista
        const countryData = phones.find(
            (country) => country.pais === defaultCountry
        );
        if (countryData) {
            this.selectCountryFromDropdown(countryData);
        }
    }

    toggleDropdown() {
        if (this.isOpenValue) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        this.isOpenValue = true;
        if (this.hasDropdownTarget) {
            this.dropdownTarget.classList.remove("hidden");
            this.dropdownTarget.classList.add("block");
        }
        if (this.hasSearchTarget) {
            this.searchTarget.focus();
        }
        // Animar el icono
        this.animateIcon(true);
    }

    closeDropdown() {
        this.isOpenValue = false;
        if (this.hasDropdownTarget) {
            this.dropdownTarget.classList.add("hidden");
            this.dropdownTarget.classList.remove("block");
        }
        if (this.hasSearchTarget) {
            this.searchTarget.value = "";
            this.filterCountries("");
        }
        // Animar el icono
        this.animateIcon(false);
    }

    animateIcon(isOpen) {
        const icon = this.element.querySelector(
            '[data-phone-prefix-target="icon"]'
        );
        if (icon) {
            if (isOpen) {
                icon.style.transform = "rotate(180deg)";
            } else {
                icon.style.transform = "rotate(0deg)";
            }
        }
    }

    selectCountryFromDropdown(country) {
        this.selectedPrefixValue = country.prefijo;
        this.updateDisplay(country);
        this.closeDropdown();

        // Enfocar el campo de teléfono
        if (this.hasInputTarget) {
            this.inputTarget.focus();
        }
    }

    selectCountry(event) {
        const selectedOption = event.target.options[event.target.selectedIndex];
        if (selectedOption.value) {
            this.selectedPrefixValue = selectedOption.value;
            this.updateDisplay();

            // Enfocar el campo de teléfono
            if (this.hasInputTarget) {
                this.inputTarget.focus();
            }
        }
    }

    updateDisplay(country = null) {
        if (this.hasDisplayTarget) {
            if (country) {
                this.displayTarget.innerHTML = `
                    <span class="text-lg">${country.bandera}</span>
                    <span class="text-gray-500 ml-1">${country.prefijo}</span>
                `;
            } else {
                const selectedOption =
                    this.selectTarget.options[this.selectTarget.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    this.displayTarget.innerHTML = `
                        <span class="text-lg">${selectedOption.dataset.flag}</span>
                        <span class="text-gray-500 ml-1">${this.selectedPrefixValue}</span>
                    `;
                } else {
                    this.displayTarget.innerHTML = `
                        <span class="text-lg">🌍</span>
                        <span class="text-gray-500 ml-1">+</span>
                    `;
                }
            }
        }
    }

    searchCountries(event) {
        const searchTerm = event.target.value.toLowerCase();
        this.filterCountries(searchTerm);
    }

    filterCountries(searchTerm) {
        if (this.hasOptionsTarget) {
            const options =
                this.optionsTarget.querySelectorAll("div[data-prefix]");

            options.forEach((option) => {
                const countryName = option.dataset.country.toLowerCase();
                const nativeName = option.dataset.nativeName.toLowerCase();
                const prefix = option.dataset.prefix.toLowerCase();

                if (
                    countryName.includes(searchTerm) ||
                    nativeName.includes(searchTerm) ||
                    prefix.includes(searchTerm)
                ) {
                    option.style.display = "flex";
                } else {
                    option.style.display = "none";
                }
            });
        }
    }

    formatPhoneNumber(event) {
        let value = event.target.value.replace(/\D/g, ""); // Solo números

        // Limitar a 15 dígitos
        if (value.length > 15) {
            value = value.substring(0, 15);
        }

        // Formatear el número
        let formattedValue = "";
        if (value.length > 0) {
            formattedValue = value.replace(/(\d{3})(\d{3})(\d{3})/, "$1 $2 $3");
        }

        event.target.value = formattedValue;
        this.phoneNumberValue = formattedValue;
    }

    getFullPhoneNumber() {
        if (this.selectedPrefixValue && this.phoneNumberValue) {
            return `${this.selectedPrefixValue} ${this.phoneNumberValue}`;
        }
        return this.phoneNumberValue || "";
    }

    prepareForSubmit() {
        if (
            this.hasInputTarget &&
            this.selectedPrefixValue &&
            this.inputTarget.value
        ) {
            // Combine prefix with phone number
            const fullPhone = `${this.selectedPrefixValue} ${this.inputTarget.value}`;
            this.inputTarget.value = fullPhone;
            return fullPhone;
        }
        return this.inputTarget ? this.inputTarget.value : "";
    }

    // Method to get the full phone number for form submission
    getFullPhoneForSubmission() {
        if (
            this.selectedPrefixValue &&
            this.hasInputTarget &&
            this.inputTarget.value
        ) {
            return `${this.selectedPrefixValue} ${this.inputTarget.value}`;
        }
        return this.hasInputTarget ? this.inputTarget.value : "";
    }
}
