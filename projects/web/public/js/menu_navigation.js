// Mobile menu functionality - Global script for all pages
function initializeMobileMenu() {
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");

    function createIconSVG(iconName, size = 20) {
        const icons = {
            menu: `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu">
                <line x1="4" x2="20" y1="12" y2="12"/>
                <line x1="4" x2="20" y1="6" y2="6"/>
                <line x1="4" x2="20" y1="18" y2="18"/>
            </svg>`,
            x: `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                <path d="M18 6 6 18"/>
                <path d="m6 6 12 12"/>
            </svg>`,
        };
        return icons[iconName] || icons.menu;
    }

    if (mobileMenuButton && mobileMenu) {
        const newButton = mobileMenuButton.cloneNode(true);
        mobileMenuButton.parentNode.replaceChild(newButton, mobileMenuButton);

        const newMobileMenu = mobileMenu.cloneNode(true);
        mobileMenu.parentNode.replaceChild(newMobileMenu, mobileMenu);

        newButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const isHidden = newMobileMenu.classList.contains("hidden");

            if (isHidden) {
                newMobileMenu.classList.remove("hidden");
                newButton.innerHTML = createIconSVG("x");
            } else {
                newMobileMenu.classList.add("hidden");
                newButton.innerHTML = createIconSVG("menu");
            }
        });

        // Close mobile menu when clicking on a link
        const mobileMenuLinks = newMobileMenu.querySelectorAll("a");
        mobileMenuLinks.forEach((link) => {
            link.addEventListener("click", function () {
                newMobileMenu.classList.add("hidden");
                newButton.innerHTML = createIconSVG("menu");
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener("click", function (e) {
            if (
                !newButton.contains(e.target) &&
                !newMobileMenu.contains(e.target)
            ) {
                newMobileMenu.classList.add("hidden");
                newButton.innerHTML = createIconSVG("menu");
            }
        });

        // Close mobile menu on window resize to desktop
        window.addEventListener("resize", function () {
            if (window.innerWidth >= 768) {
                // md breakpoint
                newMobileMenu.classList.add("hidden");
                newButton.innerHTML = createIconSVG("menu");
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", initializeMobileMenu);

document.addEventListener("turbo:load", initializeMobileMenu);
document.addEventListener("turbo:render", initializeMobileMenu);

window.addEventListener("load", initializeMobileMenu);
