document
    .getElementById("profile-update-form")
    .addEventListener("submit", function () {
        const button = document.getElementById("save-button");
        const saveText = button.querySelector(".save-text");
        const savingText = button.querySelector(".saving-text");

        // Deshabilitar el botón y mostrar estado de carga
        button.disabled = true;
        saveText.classList.add("hidden");
        savingText.classList.remove("hidden");
    });
