<?php

/** @var string|null $themeToggleExtraClass Extra class(es) for positioning/coloring this instance */
$themeToggleExtraClass = $themeToggleExtraClass ?? '';
?>
<button
  id="theme-toggle"
  class="theme-toggle <?= htmlspecialchars($themeToggleExtraClass, ENT_QUOTES, 'UTF-8') ?>"
  type="button"
  aria-pressed="false"
  aria-label="Switch to dark mode"
  title="Switch to dark/light theme">
  <svg class="theme-toggle-icon theme-toggle-icon-moon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    <use href="#moon-icon" />
  </svg>
  <svg class="theme-toggle-icon theme-toggle-icon-sun" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
    <use href="#sun-icon" />
  </svg>
</button>