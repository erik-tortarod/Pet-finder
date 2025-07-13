import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["input", "preview", "filename"];
    static values = { maxSize: Number };

    connect() {
        this.maxSize = this.maxSizeValue || 2 * 1024 * 1024; // 2MB por defecto
    }

    selectFile(event) {
        const file = event.target.files[0];

        if (!file) {
            return;
        }

        // Validar tipo de archivo
        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/gif",
            "image/webp",
            "image/jpg",
            "image/avif",
        ];
        if (!allowedTypes.includes(file.type)) {
            this.showError(
                "Por favor selecciona una imagen válida (JPG, PNG, GIF, WebP, JPG, AVIF)"
            );
            this.clearFile();
            return;
        }

        // Validar tamaño
        if (file.size > this.maxSize) {
            this.showError(
                "La imagen es demasiado grande. El tamaño máximo es 2MB"
            );
            this.clearFile();
            return;
        }

        // Mostrar nombre del archivo
        if (this.hasFilenameTarget) {
            this.filenameTarget.textContent = file.name;
        }

        // Mostrar preview
        this.showPreview(file);
    }

    showPreview(file) {
        if (!this.hasPreviewTarget) {
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            this.previewTarget.innerHTML = `
                <img src="${
                    e.target.result
                }" class="img-fluid rounded" style="max-height: 200px;" alt="Preview">
                <div class="mt-2">
                    <small class="text-muted">
                        ${file.name} (${this.formatFileSize(file.size)})
                    </small>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }

    clearFile() {
        if (this.hasInputTarget) {
            this.inputTarget.value = "";
        }
        if (this.hasPreviewTarget) {
            this.previewTarget.innerHTML = "";
        }
        if (this.hasFilenameTarget) {
            this.filenameTarget.textContent = "";
        }
    }

    showError(message) {
        // Crear alerta de error
        const alertDiv = document.createElement("div");
        alertDiv.className =
            "alert alert-danger alert-dismissible fade show mt-2";
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        this.element.appendChild(alertDiv);

        // Remover alerta después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }
}
