// Flash Messages - Manual close only (auto-hide handled by CSS)
document.addEventListener("DOMContentLoaded", function () {
    const flashMessages = document.querySelectorAll(".flash-message");

    flashMessages.forEach(function (message, index) {
        // Manual close button
        const closeBtn = message.querySelector(".flash-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", function (e) {
                e.preventDefault();
                message.classList.add("removing");
                setTimeout(function () {
                    if (message.parentNode) {
                        message.parentNode.removeChild(message);
                    }
                }, 300);
            });
        }
    });
});

// Function to remove all existing flash messages
function removeAllFlashMessages() {
    const container = document.getElementById("flash-container");
    if (!container) return;

    const existingMessages = container.querySelectorAll(".flash-message");
    existingMessages.forEach(function (message) {
        message.classList.add("removing");
        setTimeout(function () {
            if (message.parentNode) {
                message.parentNode.removeChild(message);
            }
        }, 300);
    });
}

// Function to create new flash messages dynamically
function createFlashMessage(type, text) {
    // Remove any existing flash messages first
    removeAllFlashMessages();

    const container = document.getElementById("flash-container");
    if (!container) return;

    const message = document.createElement("div");
    message.className = `flash-message flash-${type}`;
    message.setAttribute("data-type", type);

    const icon = getFlashIcon(type);

    message.innerHTML = `
        <div class="flash-content">
            <span class="flash-icon">${icon}</span>
            <span class="flash-text">${text}</span>
        </div>
        <button class="flash-close">×</button>
    `;

    container.appendChild(message);

    // Add click event to close button
    const closeButton = message.querySelector(".flash-close");
    if (closeButton) {
        closeButton.addEventListener("click", function (e) {
            e.preventDefault();
            message.classList.add("removing");
            setTimeout(function () {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 300);
        });
    }
}

function getFlashIcon(type) {
    switch (type) {
        case "success":
            return "✓";
        case "error":
            return "✕";
        case "warning":
            return "⚠";
        case "info":
            return "ℹ";
        default:
            return "ℹ";
    }
}
