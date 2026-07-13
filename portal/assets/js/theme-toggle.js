// =========================== DARK MODE TOGGLE ===========================
// The initial theme is already set by the inline script in <head> (before
// paint, to avoid a flash of the wrong theme). This file just wires up the
// button so the person can switch themes, and remembers their choice.

const themeToggleButton = document.querySelector("#theme-toggle");

function getCurrentTheme() {
  return document.documentElement.getAttribute("data-theme") === "dark"
    ? "dark"
    : "light";
}

function updateToggleButton(theme) {
  if (!themeToggleButton) return;
  const isDark = theme === "dark";
  themeToggleButton.setAttribute("aria-pressed", String(isDark));
  themeToggleButton.setAttribute(
    "aria-label",
    isDark ? "Switch to light mode" : "Switch to dark mode",
  );
}

function setTheme(theme) {
  document.documentElement.setAttribute("data-theme", theme);
  localStorage.setItem("theme", theme);
  updateToggleButton(theme);
}

if (themeToggleButton) {
  updateToggleButton(getCurrentTheme());

  themeToggleButton.addEventListener("click", () => {
    const nextTheme = getCurrentTheme() === "dark" ? "light" : "dark";
    setTheme(nextTheme);
  });
}

// keep in sync with the OS-level setting if the person never picked manually
window
  .matchMedia("(prefers-color-scheme: dark)")
  .addEventListener("change", (e) => {
    if (localStorage.getItem("theme")) return; // they made an explicit choice, don't override it
    setTheme(e.matches ? "dark" : "light");
  });
