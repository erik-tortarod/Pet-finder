// Flash Messages Auto-dismiss
document.addEventListener("DOMContentLoaded", function () {
    const flashMessages = document.querySelectorAll(".flash-message");

    flashMessages.forEach(function (message, index) {
        // Auto-dismiss after 3 seconds
        setTimeout(function () {
            if (message && message.parentNode) {
                message.classList.add("removing");
                setTimeout(function () {
                    if (message.parentNode) {
                        message.parentNode.removeChild(message);
                    }
                }, 300);
            }
        }, 3000);

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

// Function to create new flash messages dynamically
function createFlashMessage(type, text) {
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
        <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    `;

    container.appendChild(message);

    // Auto-dismiss after 3 seconds
    setTimeout(function () {
        if (message && message.parentNode) {
            message.classList.add("removing");
            setTimeout(function () {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 300);
        }
    }, 3000);

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
