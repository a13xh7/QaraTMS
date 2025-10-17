/**
 * QaraTMS Modern Theme System
 * Handles light/dark theme switching with system detection
 */

class ThemeManager {
    constructor() {
        this.themes = {
            AUTO: "auto",
            LIGHT: "light",
            DARK: "dark",
        };

        // Priority: 1) localStorage (current session), 2) user preference (DB), 3) auto
        this.currentTheme =
            this.getStoredTheme() ||
            this.getUserPreference() ||
            this.themes.AUTO;
        this.systemTheme = this.getSystemTheme();

        this.init();
    }

    getUserPreference() {
        return window.userThemePreference || null;
    }

    init() {
        // Ensure theme persistence across page loads
        this.ensureThemePersistence();

        // Check if theme was already applied by the inline script
        const currentDataTheme =
            document.documentElement.getAttribute("data-theme");
        const currentThemeClass = document.documentElement.classList.contains(
            "theme-dark"
        )
            ? "dark"
            : document.documentElement.classList.contains("theme-light")
            ? "light"
            : null;

        // Only apply theme if it wasn't already applied or if it's different
        if (!currentDataTheme && !currentThemeClass) {
            this.applyTheme(this.currentTheme);
        } else {
            // Sync with already applied theme
            this.updateToggleButton();
        }

        this.setupEventListeners();

        // Listen for system theme changes
        if (window.matchMedia) {
            window
                .matchMedia("(prefers-color-scheme: dark)")
                .addEventListener("change", (e) => {
                    this.systemTheme = e.matches
                        ? this.themes.DARK
                        : this.themes.LIGHT;
                    if (this.currentTheme === this.themes.AUTO) {
                        this.applyTheme(this.themes.AUTO);
                    }
                });
        }
    }

    ensureThemePersistence() {
        // If we have a stored theme, it takes precedence
        const storedTheme = this.getStoredTheme();
        if (storedTheme && storedTheme !== this.currentTheme) {
            this.currentTheme = storedTheme;
        }

        // Store the current theme if none is stored
        if (!storedTheme && this.currentTheme) {
            this.setStoredTheme(this.currentTheme);
        }
    }

    getSystemTheme() {
        if (
            window.matchMedia &&
            window.matchMedia("(prefers-color-scheme: dark)").matches
        ) {
            return this.themes.DARK;
        }
        return this.themes.LIGHT;
    }

    getStoredTheme() {
        return localStorage.getItem("qaratms-theme");
    }

    setStoredTheme(theme) {
        localStorage.setItem("qaratms-theme", theme);
    }

    applyTheme(theme) {
        const html = document.documentElement;

        // Remove existing theme classes and attributes
        html.removeAttribute("data-theme");
        html.classList.remove("theme-dark", "theme-light");

        if (theme === this.themes.AUTO) {
            // Don't set data-theme, let CSS media queries handle it
            this.systemTheme = this.getSystemTheme();
        } else {
            // Set explicit theme
            html.setAttribute("data-theme", theme);
        }

        // Update html class for immediate styling (prevents flash)
        const effectiveTheme = this.getEffectiveTheme();
        html.classList.add(`theme-${effectiveTheme}`);

        // Update body class for additional styling if needed
        document.body.classList.toggle(
            "theme-dark",
            effectiveTheme === this.themes.DARK
        );
        document.body.classList.toggle(
            "theme-light",
            effectiveTheme === this.themes.LIGHT
        );

        // Trigger custom event for other components
        window.dispatchEvent(
            new CustomEvent("themeChanged", {
                detail: {
                    theme: theme,
                    effectiveTheme: effectiveTheme,
                },
            })
        );
    }

    getEffectiveTheme() {
        if (this.currentTheme === this.themes.AUTO) {
            return this.systemTheme;
        }
        return this.currentTheme;
    }

    toggleTheme() {
        const themes = [this.themes.AUTO, this.themes.LIGHT, this.themes.DARK];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themes.length;

        this.setTheme(themes[nextIndex]);
    }

    setTheme(theme) {
        this.currentTheme = theme;
        this.applyTheme(theme);
        this.updateToggleButton();
        this.saveUserPreference(theme);
    }

    updateToggleButton() {
        const toggleBtn =
            document.getElementById("theme-toggle") ||
            document.querySelector(".header-theme-mode");
        if (!toggleBtn) return;

        const effectiveTheme = this.getEffectiveTheme();
        const icons = {
            [this.themes.AUTO]: "bi-gear-fill",
            [this.themes.LIGHT]: "bi-sun-fill",
            [this.themes.DARK]: "bi-moon-fill",
        };

        const titles = {
            [this.themes.AUTO]:
                window.translations?.auto_theme || "Auto (System)",
            [this.themes.LIGHT]:
                window.translations?.light_theme || "Light Theme",
            [this.themes.DARK]: window.translations?.dark_theme || "Dark Theme",
        };

        toggleBtn.innerHTML = `
            <i class="theme-icon bi ${icons[this.currentTheme]}"></i>
            <span class="theme-label d-none d-lg-inline ms-1">${
                titles[this.currentTheme]
            }</span>
        `;

        toggleBtn.title = titles[this.currentTheme];
        toggleBtn.setAttribute("aria-label", titles[this.currentTheme]);
    }

    setupEventListeners() {
        // Theme toggle link/button
        document.addEventListener("click", (e) => {
            if (
                e.target.closest("#theme-toggle") ||
                e.target.closest(".header-theme-mode")
            ) {
                e.preventDefault();
                this.toggleTheme();
            }
        });

        // Keyboard shortcut (Ctrl/Cmd + Shift + T)
        document.addEventListener("keydown", (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === "T") {
                e.preventDefault();
                this.toggleTheme();
            }
        });

        // Ensure theme persistence before page unload
        window.addEventListener("beforeunload", () => {
            this.setStoredTheme(this.currentTheme);
        });

        // Handle page visibility changes to maintain theme
        document.addEventListener("visibilitychange", () => {
            if (!document.hidden) {
                this.ensureThemePersistence();
            }
        });
    }

    saveUserPreference(theme) {
        // Always save to localStorage first for immediate persistence
        this.setStoredTheme(theme);

        // Save to server if user is logged in
        if (window.userLoggedIn && window.csrfToken) {
            fetch("/api/user/theme-preference", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.csrfToken,
                },
                body: JSON.stringify({ theme: theme }),
            })
                .then((response) => {
                    if (response.ok) {
                        // Update the global user preference variable
                        window.userThemePreference = theme;
                    }
                })
                .catch((error) => {
                    console.warn("Failed to save theme preference:", error);
                });
        }
    }

    // Method to be called from server-side to set user's preferred theme
    setUserPreference(theme) {
        if (this.themes[theme.toUpperCase()]) {
            this.setTheme(theme);
        }
    }

    // Debug method to check theme status
    getThemeStatus() {
        return {
            currentTheme: this.currentTheme,
            storedTheme: this.getStoredTheme(),
            userPreference: this.getUserPreference(),
            systemTheme: this.systemTheme,
            effectiveTheme: this.getEffectiveTheme(),
        };
    }
}

// Immediate theme application (before DOMContentLoaded)
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeThemeManager);
} else {
    initializeThemeManager();
}

function initializeThemeManager() {
    window.themeManager = new ThemeManager();

    // Add smooth loading animation
    document.body.classList.add("fade-in");

    // Remove any residual theme flash
    document.documentElement.style.visibility = "visible";
}

// Export for global access
window.ThemeManager = ThemeManager;
