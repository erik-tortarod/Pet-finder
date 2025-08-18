/**
 * Custom Alert System for Pet Finder
 * Replaces default browser alerts with beautiful flash messages
 */

class CustomAlertSystem {
    constructor() {
        this.messageQueue = [];
        this.init();
    }

    init() {
        // Override default browser alerts
        this.overrideDefaultAlerts();

        // Add global functions
        this.addGlobalFunctions();

        // Add event listeners
        this.addEventListeners();
    }

    overrideDefaultAlerts() {
        // Store original functions
        window.originalAlert = window.alert;
        window.originalConfirm = window.confirm;

        // Override alert
        window.alert = (message, type = "info") => {
            this.show(message, type);
        };

        // Override confirm (with fallback for now)
        window.confirm = (message) => {
            return window.originalConfirm(message);
        };
    }

    addGlobalFunctions() {
        // Main alert function
        window.showAlert = (message, type = "info") => {
            this.show(message, type);
        };

        // Specific type functions
        window.showSuccess = (message) => {
            this.show(message, "success");
        };

        window.showError = (message) => {
            this.show(message, "error");
        };

        window.showWarning = (message) => {
            this.show(message, "warning");
        };

        window.showInfo = (message) => {
            this.show(message, "info");
        };

        // Confirmation dialog (custom implementation)
        window.showConfirm = (message, onConfirm, onCancel) => {
            this.showConfirmDialog(message, onConfirm, onCancel);
        };
    }

    show(message, type = "info") {
        if (typeof createFlashMessage === "function") {
            // Add a small delay to prevent rapid-fire messages from conflicting
            if (this.messageQueue.length > 0) {
                this.messageQueue.push({ message, type });
                return;
            }

            this.messageQueue.push({ message, type });
            this.processMessageQueue();
        } else {
            // Fallback to original alert
            window.originalAlert(message);
        }
    }

    processMessageQueue() {
        if (this.messageQueue.length === 0) return;

        const { message, type } = this.messageQueue.shift();
        createFlashMessage(type, message);

        // Process next message after a delay
        if (this.messageQueue.length > 0) {
            setTimeout(() => {
                this.processMessageQueue();
            }, 100);
        }
    }

    showConfirmDialog(message, onConfirm, onCancel) {
        // Create a custom confirm dialog
        const dialog = document.createElement("div");
        dialog.className = "custom-confirm-dialog";
        dialog.innerHTML = `
            <div class="confirm-backdrop"></div>
            <div class="confirm-modal">
                <div class="confirm-content">
                    <div class="confirm-message">${message}</div>
                    <div class="confirm-buttons">
                        <button class="confirm-btn confirm-yes">Sí</button>
                        <button class="confirm-btn confirm-no">No</button>
                    </div>
                </div>
            </div>
        `;

        // Add styles
        this.addConfirmStyles();

        // Add to body
        document.body.appendChild(dialog);

        // Add event listeners
        const yesBtn = dialog.querySelector(".confirm-yes");
        const noBtn = dialog.querySelector(".confirm-no");
        const backdrop = dialog.querySelector(".confirm-backdrop");

        const cleanup = () => {
            document.body.removeChild(dialog);
        };

        yesBtn.addEventListener("click", () => {
            cleanup();
            if (onConfirm) onConfirm();
        });

        noBtn.addEventListener("click", () => {
            cleanup();
            if (onCancel) onCancel();
        });

        backdrop.addEventListener("click", () => {
            cleanup();
            if (onCancel) onCancel();
        });

        // Focus on yes button
        yesBtn.focus();
    }

    addConfirmStyles() {
        if (document.getElementById("custom-confirm-styles")) return;

        const style = document.createElement("style");
        style.id = "custom-confirm-styles";
        style.textContent = `
            .custom-confirm-dialog {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .confirm-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(2px);
            }

            .confirm-modal {
                position: relative;
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                max-width: 400px;
                width: 90%;
                animation: confirmSlideIn 0.3s ease-out;
            }

            .confirm-content {
                padding: 24px;
            }

            .confirm-message {
                font-size: 16px;
                line-height: 1.5;
                color: #374151;
                margin-bottom: 24px;
                text-align: center;
            }

            .confirm-buttons {
                display: flex;
                gap: 12px;
                justify-content: center;
            }

            .confirm-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                min-width: 80px;
            }

            .confirm-yes {
                background-color: #ef4444;
                color: white;
            }

            .confirm-yes:hover {
                background-color: #dc2626;
            }

            .confirm-no {
                background-color: #6b7280;
                color: white;
            }

            .confirm-no:hover {
                background-color: #4b5563;
            }

            @keyframes confirmSlideIn {
                from {
                    opacity: 0;
                    transform: scale(0.9) translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            @media (max-width: 768px) {
                .confirm-modal {
                    margin: 20px;
                    width: calc(100% - 40px);
                }
            }
        `;

        document.head.appendChild(style);
    }

    addEventListeners() {
        // Form validation
        document.addEventListener("submit", (e) => {
            this.handleFormValidation(e);
        });

        // AJAX errors
        document.addEventListener("ajax:error", (e) => {
            this.handleAjaxError(e);
        });

        // Fetch errors
        window.addEventListener("unhandledrejection", (e) => {
            this.handleFetchError(e);
        });

        // Network errors
        window.addEventListener("online", () => {
            this.show("Conexión restaurada", "success");
        });

        window.addEventListener("offline", () => {
            this.show("Conexión perdida", "warning");
        });
    }

    handleFormValidation(e) {
        const form = e.target;
        const requiredFields = form.querySelectorAll("[required]");
        let hasErrors = false;
        let errorMessage = "";

        requiredFields.forEach((field) => {
            if (!field.value.trim()) {
                hasErrors = true;
                const fieldName =
                    field.getAttribute("placeholder") ||
                    field.getAttribute("name") ||
                    field.getAttribute("data-label") ||
                    "Campo requerido";
                errorMessage += `• ${fieldName} es obligatorio\n`;
            }
        });

        if (hasErrors) {
            e.preventDefault();
            this.show(errorMessage, "error");
            return false;
        }
    }

    handleAjaxError(e) {
        const error = e.detail;
        let message = "Ha ocurrido un error inesperado";

        if (error && error.message) {
            message = error.message;
        } else if (error && error.statusText) {
            message = `Error ${error.status}: ${error.statusText}`;
        }

        this.show(message, "error");
    }

    handleFetchError(e) {
        if (e.reason && e.reason.message) {
            this.show(e.reason.message, "error");
        } else {
            this.show("Ha ocurrido un error inesperado", "error");
        }
    }
}

// Initialize the custom alert system when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    window.customAlertSystem = new CustomAlertSystem();
});

// Export for use in other scripts
if (typeof module !== "undefined" && module.exports) {
    module.exports = CustomAlertSystem;
}
