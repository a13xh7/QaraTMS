/**
 * QaraTMS Modern UI Enhancements
 * Additional interactive effects and animations
 */

document.addEventListener("DOMContentLoaded", function () {
    // Add hover effects to cards
    const cards = document.querySelectorAll(".card, .base_block");
    cards.forEach((card) => {
        card.addEventListener("mouseenter", function () {
            this.style.transform = "translateY(-2px)";
        });

        card.addEventListener("mouseleave", function () {
            this.style.transform = "translateY(0)";
        });
    });

    // Enhanced button click effects
    const buttons = document.querySelectorAll(".btn");
    buttons.forEach((button) => {
        button.addEventListener("click", function (e) {
            // Create ripple effect
            const ripple = document.createElement("span");
            ripple.classList.add("ripple");
            this.appendChild(ripple);

            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + "px";
            ripple.style.left = x + "px";
            ripple.style.top = y + "px";

            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.parentNode.removeChild(ripple);
                }
            }, 600);
        });
    });

    // Smooth scroll for anchors
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach((link) => {
        link.addEventListener("click", function (e) {
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        });
    });

    // Add loading states to forms
    const forms = document.querySelectorAll("form");
    forms.forEach((form) => {
        form.addEventListener("submit", function () {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML =
                    '<i class="bi bi-hourglass-split"></i> ' +
                    (window.translations?.loading || "Loading...");
                submitBtn.disabled = true;

                // Reset after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });

    // Enhanced form validation visual feedback
    const inputs = document.querySelectorAll(".form-control");
    inputs.forEach((input) => {
        input.addEventListener("blur", function () {
            if (this.checkValidity()) {
                this.classList.remove("is-invalid");
                this.classList.add("is-valid");
            } else {
                this.classList.remove("is-valid");
                this.classList.add("is-invalid");
            }
        });

        input.addEventListener("input", function () {
            this.classList.remove("is-invalid", "is-valid");
        });
    });

    // Smooth fade-in for page content
    const content = document.querySelector("main, .container-fluid");
    if (content) {
        content.style.opacity = "0";
        content.style.transform = "translateY(20px)";

        setTimeout(() => {
            content.style.transition = "all 0.5s ease-out";
            content.style.opacity = "1";
            content.style.transform = "translateY(0)";
        }, 100);
    }

    // Toast notifications for better user feedback
    window.showToast = function (message, type = "info") {
        const toastContainer =
            document.getElementById("toast-container") ||
            createToastContainer();

        const toast = document.createElement("div");
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute("role", "alert");
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener("hidden.bs.toast", () => {
            toast.remove();
        });
    };

    function createToastContainer() {
        const container = document.createElement("div");
        container.id = "toast-container";
        container.className =
            "toast-container position-fixed bottom-0 end-0 p-3";
        container.style.zIndex = "1055";
        document.body.appendChild(container);
        return container;
    }

    // Theme change feedback
    window.addEventListener("themeChanged", function (e) {
        const themeName =
            window.translations?.[e.detail.theme + "_theme"] || e.detail.theme;
        showToast(`Theme changed to ${themeName}`, "success");
    });
});

// CSS for ripple effect
const rippleCSS = `
.btn {
    position: relative;
    overflow: hidden;
}

.ripple {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

.is-valid {
    border-color: var(--success-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25) !important;
}

.is-invalid {
    border-color: var(--danger-color) !important;
    box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.25) !important;
}
`;

// Inject CSS
const style = document.createElement("style");
style.textContent = rippleCSS;
document.head.appendChild(style);
