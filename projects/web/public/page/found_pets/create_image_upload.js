// Initialize photo upload functionality
document.addEventListener("DOMContentLoaded", function () {
    const photoInput = document.getElementById("photo-input");

    if (photoInput) {
        photoInput.addEventListener("change", function (event) {
            const files = Array.from(event.target.files);
            console.log("Files selected:", files.length);
            updatePhotoPreview(files);
        });
    }
});

// Update photo preview
function updatePhotoPreview(files) {
    const container = document.getElementById("photo-preview-container");

    if (files.length === 0) {
        container.classList.add("hidden");
        return;
    }

    container.classList.remove("hidden");
    container.innerHTML = "";

    // Show max 5 files
    const filesToShow = files.slice(0, 5);

    filesToShow.forEach((file, index) => {
        const photoDiv = document.createElement("div");
        photoDiv.className = "relative";

        const img = document.createElement("img");
        img.className = "w-full h-24 object-cover rounded-lg border-2";

        if (index === 0) {
            img.classList.add("border-green-500");
            // Add primary badge
            const badge = document.createElement("div");
            badge.className =
                "absolute top-1 left-1 bg-green-500 text-white text-xs px-2 py-1 rounded";
            badge.textContent = "Principal";
            photoDiv.appendChild(badge);
        } else {
            img.classList.add("border-gray-300");
        }

        // Create image preview
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);

        photoDiv.appendChild(img);
        container.appendChild(photoDiv);
    });

    if (files.length > 5) {
        const infoDiv = document.createElement("div");
        infoDiv.className = "text-sm text-orange-600 mt-2";
        infoDiv.textContent = `Solo se mostrarán las primeras 5 fotos. Total seleccionadas: ${files.length}`;
        container.appendChild(infoDiv);
    }
}
