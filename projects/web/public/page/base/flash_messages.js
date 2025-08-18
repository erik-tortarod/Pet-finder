// Flash Messages - Enhanced with better animation handling
document.addEventListener("DOMContentLoaded", function () {
    const flashMessages = document.querySelectorAll(".flash-message");

    flashMessages.forEach(function (message, index) {
        // Manual close button
        const closeBtn = message.querySelector(".flash-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", function (e) {
                e.preventDefault();
                removeFlashMessage(message);
            });
        }
    });
});

// Enhanced function to remove a single flash message
function removeFlashMessage(message) {
    if (!message || !message.parentNode) return;

    message.classList.add("removing");
    message.style.pointerEvents = "none"; // Prevent interaction during removal

    setTimeout(function () {
        if (message.parentNode) {
            message.parentNode.removeChild(message);
        }
    }, 300);
}

// Enhanced function to remove all existing flash messages
function removeAllFlashMessages() {
    const container = document.getElementById("flash-container");
    if (!container) return;

    const existingMessages = container.querySelectorAll(".flash-message");
    existingMessages.forEach(function (message) {
        removeFlashMessage(message);
    });
}

// Enhanced function to create new flash messages dynamically
function createFlashMessage(type, text) {
    const container = document.getElementById("flash-container");
    if (!container) return;

    // Create the new message first
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

    // Remove existing messages with a small delay to prevent animation conflicts
    const existingMessages = container.querySelectorAll(".flash-message");
    if (existingMessages.length > 0) {
        existingMessages.forEach(function (existingMessage) {
            removeFlashMessage(existingMessage);
        });

        // Add new message after a brief delay to ensure smooth transition
        setTimeout(() => {
            container.appendChild(message);
            setupMessageEvents(message);
        }, 50);
    } else {
        // No existing messages, add immediately
        container.appendChild(message);
        setupMessageEvents(message);
    }
}

// Function to setup event listeners for a message
function setupMessageEvents(message) {
    const closeButton = message.querySelector(".flash-close");
    if (closeButton) {
        closeButton.addEventListener("click", function (e) {
            e.preventDefault();
            removeFlashMessage(message);
        });
    }
}

function getFlashIcon(type) {
    switch (type) {
        case "success":
            return '<i class="fas fa-check-circle"></i>';
        case "error":
            return '<i class="fas fa-times-circle"></i>';
        case "warning":
            return '<i class="fas fa-exclamation-triangle"></i>';
        case "info":
            return '<i class="fas fa-info-circle"></i>';
        default:
            return '<i class="fas fa-info-circle"></i>';
    }
}
