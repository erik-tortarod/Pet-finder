import "./bootstrap.js";
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.css";

// Handle Turbo navigation events to ensure proper controller initialization
document.addEventListener("turbo:load", function () {
    console.log(
        "Turbo load event - ensuring controllers are properly initialized"
    );

    // Force reconnection of controllers if needed
    if (window.Stimulus) {
        // Trigger a small delay to ensure DOM is ready
        setTimeout(() => {
            // Reconnect any controllers that might need it
            const locationSearchControllers = document.querySelectorAll(
                '[data-controller*="location-search"]'
            );
            locationSearchControllers.forEach((element) => {
                const controller =
                    window.Stimulus.getControllerForElementAndIdentifier(
                        element,
                        "location-search"
                    );
                if (controller && typeof controller.connect === "function") {
                    console.log("Reconnecting location-search controller");
                    controller.connect();
                }
            });

            const filtersControllers = document.querySelectorAll(
                '[data-controller*="filters"]'
            );
            filtersControllers.forEach((element) => {
                const controller =
                    window.Stimulus.getControllerForElementAndIdentifier(
                        element,
                        "filters"
                    );
                if (controller && typeof controller.connect === "function") {
                    console.log("Reconnecting filters controller");
                    controller.connect();
                }
            });
        }, 200);
    }
});

document.addEventListener("turbo:render", function () {
    console.log("Turbo render event - controllers should be ready");
});
