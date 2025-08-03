// Confirm Modal Global Handler
window.ConfirmModalHandler = {
    async confirm(options = {}) {
        return new Promise((resolve) => {
            const modal = document.createElement("div");
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
                        <div class="p-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">${
                                    options.title || "Confirmar acción"
                                }</h3>
                            </div>
                            <p class="text-gray-600 mb-6">${
                                options.message ||
                                "¿Estás seguro de que quieres continuar?"
                            }</p>
                            <div class="flex justify-end space-x-3">
                                <button class="cancel-btn px-4 py-2 text-white font-medium rounded-lg transition-colors duration-200 bg-red-600 hover:bg-red-700">
                                    ${options.cancelText || "Rechazar"}
                                </button>
                                <button class="confirm-btn px-4 py-2 text-white font-medium rounded-lg transition-colors duration-200 bg-green-600 hover:bg-green-700">
                                    ${options.confirmText || "Aceptar"}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const confirmBtn = modal.querySelector(".confirm-btn");
            const cancelBtn = modal.querySelector(".cancel-btn");

            const cleanup = () => {
                document.body.removeChild(modal);
            };

            confirmBtn.addEventListener("click", () => {
                console.log("✅ User confirmed");
                cleanup();
                resolve(true);
            });

            cancelBtn.addEventListener("click", () => {
                console.log("❌ User cancelled");
                cleanup();
                resolve(false);
            });

            // Cerrar con Escape
            const handleEscape = (event) => {
                if (event.key === "Escape") {
                    console.log("❌ User pressed escape");
                    cleanup();
                    resolve(false);
                }
            };
            document.addEventListener("keydown", handleEscape);

            // Cerrar con clic fuera
            modal.addEventListener("click", (event) => {
                if (event.target === modal) {
                    console.log("❌ User clicked outside");
                    cleanup();
                    resolve(false);
                }
            });

            document.body.appendChild(modal);
            cancelBtn.focus();
        });
    },

    async confirmToast(options = {}) {
        return new Promise((resolve) => {
            const position = options.position || "top-right";
            const duration = options.duration || 5000;

            const toast = document.createElement("div");
            toast.className = `fixed z-50 transform transition-all duration-300 opacity-0 ${this.getPositionClasses(
                position
            )}`;
            toast.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl border-l-4 border-blue-500 max-w-sm w-full mx-4 mb-4">
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900">${
                                        options.title || "Confirmar acción"
                                    }</h4>
                                    <p class="text-sm text-gray-600 mt-1">${
                                        options.message ||
                                        "¿Estás seguro de que quieres continuar?"
                                    }</p>
                                </div>
                            </div>
                            <button type="button" class="toast-close text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex justify-end space-x-2 mt-4">
                            <button type="button" class="toast-cancel px-3 py-1.5 text-sm text-white font-medium rounded transition-colors duration-200 bg-red-600 hover:bg-red-700">
                                ${options.cancelText || "Rechazar"}
                            </button>
                            <button type="button" class="toast-confirm px-3 py-1.5 text-sm text-white font-medium rounded transition-colors duration-200 bg-green-600 hover:bg-green-700">
                                ${options.confirmText || "Aceptar"}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(toast);

            // Animar entrada
            setTimeout(() => {
                toast.classList.add("translate-x-0", "opacity-100");
            }, 10);

            const confirmBtn = toast.querySelector(".toast-confirm");
            const cancelBtn = toast.querySelector(".toast-cancel");
            const closeBtn = toast.querySelector(".toast-close");

            const cleanup = () => {
                toast.classList.remove("translate-x-0", "opacity-100");
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            };

            confirmBtn.addEventListener("click", () => {
                console.log("✅ Toast - user confirmed");
                cleanup();
                resolve(true);
            });

            cancelBtn.addEventListener("click", () => {
                console.log("❌ Toast - user cancelled");
                cleanup();
                resolve(false);
            });

            closeBtn.addEventListener("click", () => {
                console.log("❌ Toast - user closed");
                cleanup();
                resolve(false);
            });

            // Auto-cerrar después de la duración especificada
            if (duration > 0) {
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        console.log("⏰ Toast - auto closing");
                        cleanup();
                        resolve(false);
                    }
                }, duration);
            }
        });
    },

    getPositionClasses(position) {
        switch (position) {
            case "top-left":
                return "top-4 left-4 -translate-x-full";
            case "top-right":
                return "top-4 right-4 translate-x-full";
            case "bottom-left":
                return "bottom-4 left-4 -translate-x-full";
            case "bottom-right":
                return "bottom-4 right-4 translate-x-full";
            default:
                return "top-4 right-4 translate-x-full";
        }
    },
};

// Función global para uso fácil
window.confirmAction = async function (options) {
    return await window.ConfirmModalHandler.confirm(options);
};

console.log("🔧 ConfirmModalHandler loaded");
