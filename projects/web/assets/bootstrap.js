import { startStimulusApp } from "@symfony/stimulus-bridge";

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(
    require.context(
        "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
        true,
        /\.[jt]sx?$/
    )
);

// Make Stimulus available globally for debugging
window.Stimulus = app;

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
