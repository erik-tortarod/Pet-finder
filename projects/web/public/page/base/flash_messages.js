// Flash Messages - Block error messages only on lost/found pets pages
document.addEventListener("DOMContentLoaded", function () {
    // Check if we're on lost/found pets pages
    const currentPath = window.location.pathname;
    const isPetsPage =
        currentPath.includes("/lost/pets") ||
        currentPath.includes("/found/pets");

    // Remove error flash messages only on pets pages to prevent JavaScript conflicts
    if (isPetsPage) {
        const flashContainer = document.getElementById("flash-container");
        if (flashContainer) {
            const errorMessages =
                flashContainer.querySelectorAll(".flash-error");
            errorMessages.forEach(function (message) {
                if (message && message.parentNode) {
                    try {
                        message.parentNode.removeChild(message);
                    } catch (e) {
                        console.log("Error removing flash error message:", e);
                    }
                }
            });
        }
    }

    // Setup event listeners for remaining messages (success, info, warning)
    const flashMessages = document.querySelectorAll(
        ".flash-message:not(.flash-error)"
    );
    flashMessages.forEach(function (message, index) {
        if (!message) return;

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

    try {
        message.classList.add("removing");
        if (message.style) {
            message.style.pointerEvents = "none"; // Prevent interaction during removal
        }
    } catch (e) {
        console.log("Error setting message styles:", e);
    }

    setTimeout(function () {
        if (message && message.parentNode) {
            try {
                message.parentNode.removeChild(message);
            } catch (e) {
                console.log("Error removing message:", e);
            }
        }
    }, 300);
}

// Enhanced function to remove all existing flash messages
function removeAllFlashMessages() {
    const container = document.getElementById("flash-container");
    if (!container) return;

    const existingMessages = container.querySelectorAll(".flash-message");
    existingMessages.forEach(function (message) {
        if (message) {
            removeFlashMessage(message);
        }
    });
}

// Enhanced function to create new flash messages dynamically
function createFlashMessage(type, text) {
    // Block error messages only on lost/found pets pages
    const currentPath = window.location.pathname;
    const isPetsPage =
        currentPath.includes("/lost/pets") ||
        currentPath.includes("/found/pets");

    if (type === "error" && isPetsPage) {
        console.log("Error flash message blocked on pets page:", text);
        return;
    }

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
            if (existingMessage) {
                removeFlashMessage(existingMessage);
            }
        });

        // Add new message after a brief delay to ensure smooth transition
        setTimeout(() => {
            if (container && message) {
                container.appendChild(message);
                setupMessageEvents(message);
            }
        }, 50);
    } else {
        // No existing messages, add immediately
        if (container && message) {
            container.appendChild(message);
            setupMessageEvents(message);
        }
    }
}

// Function to setup event listeners for a message
function setupMessageEvents(message) {
    if (!message) return;

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
