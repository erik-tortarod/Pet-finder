import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "modal",
        "title",
        "message",
        "confirmButton",
        "cancelButton",
    ];
    static values = {
        title: { type: String, default: "Confirmar acción" },
        message: {
            type: String,
            default: "¿Estás seguro de que quieres continuar?",
        },
        confirmText: { type: String, default: "Aceptar" },
        cancelText: { type: String, default: "Rechazar" },
        confirmClass: {
            type: String,
            default: "bg-green-600 hover:bg-green-700",
        },
        cancelClass: { type: String, default: "bg-red-600 hover:bg-red-700" },
        type: { type: String, default: "modal" },
        position: { type: String, default: "top-right" },
        duration: { type: Number, default: 5000 },
    };

    connect() {
        console.log("🔗 ConfirmModal controller connected");
        this.boundHandleOutsideClick = this.handleOutsideClick.bind(this);
        this.boundHandleEscape = this.handleEscape.bind(this);
    }

    disconnect() {
        console.log("🔌 ConfirmModal controller disconnected");
        document.removeEventListener("click", this.boundHandleOutsideClick);
        document.removeEventListener("keydown", this.boundHandleEscape);
    }

    // Método principal para mostrar la confirmación
    show(event) {
        console.log("🎯 Show method called", event);
        event.preventDefault();
        event.stopPropagation();

        // Obtener datos del botón que disparó el evento
        const button = event.currentTarget;
        const title = button.dataset.confirmTitle || this.titleValue;
        const message = button.dataset.confirmMessage || this.messageValue;
        const confirmText = button.dataset.confirmText || this.confirmTextValue;
        const cancelText = button.dataset.cancelText || this.cancelTextValue;
        const confirmClass =
            button.dataset.confirmClass || this.confirmClassValue;
        const cancelClass = button.dataset.cancelClass || this.cancelClassValue;
        const type = button.dataset.confirmType || this.typeValue;
        const position = button.dataset.confirmPosition || this.positionValue;
        const duration =
            parseInt(button.dataset.confirmDuration) || this.durationValue;

        console.log("📋 Config:", { title, message, type, position, duration });

        if (type === "toast") {
            this.showToast(
                title,
                message,
                confirmText,
                cancelText,
                confirmClass,
                cancelClass,
                position,
                duration
            );
        } else {
            this.showModal(
                title,
                message,
                confirmText,
                cancelText,
                confirmClass,
                cancelClass
            );
        }
    }

    // Mostrar modal de confirmación
    showModal(
        title,
        message,
        confirmText,
        cancelText,
        confirmClass,
        cancelClass
    ) {
        console.log("🎭 Showing modal");
        // Configurar el modal
        this.titleTarget.textContent = title;
        this.messageTarget.textContent = message;
        this.confirmButtonTarget.textContent = confirmText;
        this.cancelButtonTarget.textContent = cancelText;

        // Aplicar clases CSS
        this.confirmButtonTarget.className = `px-4 py-2 text-white font-medium rounded-lg transition-colors duration-200 ${confirmClass}`;
        this.cancelButtonTarget.className = `px-4 py-2 text-white font-medium rounded-lg transition-colors duration-200 ${cancelClass}`;

        // Mostrar el modal
        this.modalTarget.classList.remove("hidden");
        this.modalTarget.classList.add("flex");

        // Agregar listeners
        document.addEventListener("click", this.boundHandleOutsideClick);
        document.addEventListener("keydown", this.boundHandleEscape);

        // Focus en el botón de cancelar por defecto
        this.cancelButtonTarget.focus();
    }

    // Mostrar toast de confirmación
    showToast(
        title,
        message,
        confirmText,
        cancelText,
        confirmClass,
        cancelClass,
        position,
        duration
    ) {
        console.log("🍞 Showing toast");
        // Crear el toast dinámicamente
        const toast = document.createElement("div");
        toast.className = `fixed z-50 transform transition-all duration-300 ${this.getPositionClasses(
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
                                <h4 class="text-sm font-semibold text-gray-900">${title}</h4>
                                <p class="text-sm text-gray-600 mt-1">${message}</p>
                            </div>
                        </div>
                        <button type="button" class="toast-close text-gray-400 hover:text-gray-600 transition-colors duration-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" class="toast-cancel px-3 py-1.5 text-sm text-white font-medium rounded transition-colors duration-200 ${cancelClass}">
                            ${cancelText}
                        </button>
                        <button type="button" class="toast-confirm px-3 py-1.5 text-sm text-white font-medium rounded transition-colors duration-200 ${confirmClass}">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Agregar al DOM
        document.body.appendChild(toast);

        // Animar entrada
        setTimeout(() => {
            toast.classList.add("translate-x-0", "opacity-100");
        }, 10);

        // Configurar eventos
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

        // Eventos de botones
        confirmBtn.addEventListener("click", () => {
            this.dispatchConfirmEvent();
            cleanup();
        });

        cancelBtn.addEventListener("click", () => {
            this.dispatchCancelEvent();
            cleanup();
        });

        closeBtn.addEventListener("click", () => {
            this.dispatchCancelEvent();
            cleanup();
        });

        // Auto-cerrar después de la duración especificada
        if (duration > 0) {
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    this.dispatchCancelEvent();
                    cleanup();
                }
            }, duration);
        }
    }

    // Obtener clases CSS según la posición
    getPositionClasses(position) {
        const baseClasses = "opacity-0";
        switch (position) {
            case "top-left":
                return `${baseClasses} top-4 left-4 -translate-x-full`;
            case "top-right":
                return `${baseClasses} top-4 right-4 translate-x-full`;
            case "bottom-left":
                return `${baseClasses} bottom-4 left-4 -translate-x-full`;
            case "bottom-right":
                return `${baseClasses} bottom-4 right-4 translate-x-full`;
            default:
                return `${baseClasses} top-4 right-4 translate-x-full`;
        }
    }

    // Método para confirmar la acción
    confirm() {
        console.log("✅ Confirm called");
        this.dispatchConfirmEvent();
        this.close();
    }

    // Método para cancelar la acción
    cancel() {
        console.log("❌ Cancel called");
        this.dispatchCancelEvent();
        this.close();
    }

    // Disparar evento de confirmación
    dispatchConfirmEvent() {
        console.log("📤 Dispatching confirm event");
        const confirmEvent = new CustomEvent("confirm:confirmed", {
            detail: { confirmed: true },
            bubbles: true,
        });
        this.element.dispatchEvent(confirmEvent);
    }

    // Disparar evento de cancelación
    dispatchCancelEvent() {
        console.log("📤 Dispatching cancel event");
        const cancelEvent = new CustomEvent("confirm:cancelled", {
            detail: { confirmed: false },
            bubbles: true,
        });
        this.element.dispatchEvent(cancelEvent);
    }

    // Cerrar el modal
    close() {
        console.log("🚪 Closing modal");
        this.modalTarget.classList.add("hidden");
        this.modalTarget.classList.remove("flex");

        // Remover listeners
        document.removeEventListener("click", this.boundHandleOutsideClick);
        document.removeEventListener("keydown", this.boundHandleEscape);
    }

    // Manejar clic fuera del modal
    handleOutsideClick(event) {
        if (event.target === this.modalTarget) {
            this.cancel();
        }
    }

    // Manejar tecla Escape
    handleEscape(event) {
        if (event.key === "Escape") {
            this.cancel();
        }
    }

    // Método estático para uso programático - Modal
    static async confirm(options = {}) {
        console.log("🎯 Static confirm called with options:", options);
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
                console.log("✅ Static confirm - user confirmed");
                cleanup();
                resolve(true);
            });

            cancelBtn.addEventListener("click", () => {
                console.log("❌ Static confirm - user cancelled");
                cleanup();
                resolve(false);
            });

            // Cerrar con Escape
            const handleEscape = (event) => {
                if (event.key === "Escape") {
                    console.log("❌ Static confirm - user pressed escape");
                    cleanup();
                    resolve(false);
                }
            };
            document.addEventListener("keydown", handleEscape);

            // Cerrar con clic fuera
            modal.addEventListener("click", (event) => {
                if (event.target === modal) {
                    console.log("❌ Static confirm - user clicked outside");
                    cleanup();
                    resolve(false);
                }
            });

            document.body.appendChild(modal);
            cancelBtn.focus();
        });
    }

    // Método estático para uso programático - Toast
    static async confirmToast(options = {}) {
        console.log("🍞 Static confirmToast called with options:", options);
        return new Promise((resolve) => {
            const position = options.position || "top-right";
            const duration = options.duration || 5000;

            const toast = document.createElement("div");
            toast.className = `fixed z-50 transform transition-all duration-300 opacity-0 ${this.getPositionClassesStatic(
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
                console.log("✅ Static toast - user confirmed");
                cleanup();
                resolve(true);
            });

            cancelBtn.addEventListener("click", () => {
                console.log("❌ Static toast - user cancelled");
                cleanup();
                resolve(false);
            });

            closeBtn.addEventListener("click", () => {
                console.log("❌ Static toast - user closed");
                cleanup();
                resolve(false);
            });

            // Auto-cerrar después de la duración especificada
            if (duration > 0) {
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        console.log("⏰ Static toast - auto closing");
                        cleanup();
                        resolve(false);
                    }
                }, duration);
            }
        });
    }

    // Método estático para obtener clases de posición
    static getPositionClassesStatic(position) {
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
    }
}
