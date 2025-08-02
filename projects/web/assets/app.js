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
    console.log("Turbo load event - page loaded");
});

document.addEventListener("turbo:render", function () {
    console.log("Turbo render event - page rendered");
});

document.addEventListener("turbo:before-render", function () {
    console.log("Turbo before-render event - cleaning up before navigation");
    // Clean up all maps globally before navigation
    if (window.mapRegistry) {
        window.mapRegistry.forEach((controller, id) => {
            console.log(
                `Cleaning up map from controller ${id} before navigation`
            );
            controller.cleanupMap();
        });
    }
});

document.addEventListener("turbo:before-cache", function () {
    console.log("Turbo before-cache event - caching current page");
    // Clean up all maps globally before caching
    if (window.mapRegistry) {
        window.mapRegistry.forEach((controller, id) => {
            console.log(`Cleaning up map from controller ${id} before caching`);
            controller.cleanupMap();
        });
    }
});
