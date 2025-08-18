/**
 * Tags Enhancement for Lost Pets Creation Form
 * Provides better UX for tag selection
 */

document.addEventListener("DOMContentLoaded", function () {
    // Initialize tag enhancement
    initTagEnhancement();
});

function initTagEnhancement() {
    const mostUsedTagsContainer = document.querySelector(
        ".most-used-tags-checkboxes"
    );
    const otherTagsInput = document.querySelector('input[name*="animalTags"]');

    if (!mostUsedTagsContainer || !otherTagsInput) {
        return;
    }

    // Add click handlers to tag checkboxes
    const tagCheckboxes = mostUsedTagsContainer.querySelectorAll(
        'input[type="checkbox"]'
    );

    tagCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            updateTagSelection(this);
        });
    });

    // Add visual feedback for selected tags
    function updateTagSelection(checkbox) {
        const label = checkbox.nextElementSibling;

        if (checkbox.checked) {
            label.classList.add("selected");
            // Add a small animation
            label.style.transform = "scale(1.05)";
            setTimeout(() => {
                label.style.transform = "scale(1)";
            }, 150);
        } else {
            label.classList.remove("selected");
        }
    }

    // Add keyboard navigation
    mostUsedTagsContainer.addEventListener("keydown", function (e) {
        const checkboxes = Array.from(
            this.querySelectorAll('input[type="checkbox"]')
        );
        const currentIndex = checkboxes.findIndex(
            (cb) => cb === document.activeElement
        );

        if (e.key === "ArrowRight" || e.key === "ArrowDown") {
            e.preventDefault();
            const nextIndex = (currentIndex + 1) % checkboxes.length;
            checkboxes[nextIndex].focus();
        } else if (e.key === "ArrowLeft" || e.key === "ArrowUp") {
            e.preventDefault();
            const prevIndex =
                currentIndex === 0 ? checkboxes.length - 1 : currentIndex - 1;
            checkboxes[prevIndex].focus();
        } else if (e.key === " ") {
            e.preventDefault();
            const checkbox = document.activeElement;
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event("change"));
        }
    });

    // Add tooltip for better UX
    const tagLabels = mostUsedTagsContainer.querySelectorAll("label");
    tagLabels.forEach((label) => {
        label.title = `Haz clic para seleccionar "${label.textContent.trim()}"`;
    });
}
