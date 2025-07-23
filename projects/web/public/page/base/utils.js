// Utility functions for the application

// Function to copy URL to clipboard and show flash message
function copyToClipboard(url) {
    // Create the full URL if it's relative
    const fullUrl = url.startsWith("http") ? url : window.location.origin + url;

    // Copy to clipboard
    navigator.clipboard
        .writeText(fullUrl)
        .then(function () {
            // Show success flash message
            createFlashMessage("success", "¡URL copiada al portapapeles!");
        })
        .catch(function (err) {
            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = fullUrl;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand("copy");
                createFlashMessage("success", "¡URL copiada al portapapeles!");
            } catch (err) {
                createFlashMessage("error", "Error al copiar la URL");
            }
            document.body.removeChild(textArea);
        });
}
