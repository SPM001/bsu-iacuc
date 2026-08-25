// ===== DARK MODE TOGGLE =====

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

window
  .matchMedia("(prefers-color-scheme: dark)")
  .addEventListener("change", (e) => {
    if (localStorage.getItem("theme")) return;
    setTheme(e.matches ? "dark" : "light");
  });
