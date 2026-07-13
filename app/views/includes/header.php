<?php
include 'sprites.php';

/** @var array|null $user */
$first_name = $user['first_name'] ?? '';
$role = $user['role'] ?? '';
$hideHeaderAuth = $hideHeaderAuth ?? false;
$hideHeader     = $hideHeader     ?? false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php $default = "BSU-IACUC"; ?>
  <title><?= isset($title) ? "$title - $default" : $default ?></title>

  <!-- set the theme before anything paints, so there's no flash of the wrong theme -->
  <script>
    (function() {
      var savedTheme = localStorage.getItem('theme');
      var systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      var theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', theme);
    })();
  </script>

  <link rel="stylesheet" href="<?= CSSPATH ?>/header.css">
  <link rel="stylesheet" href="<?= CSSPATH ?>/body.css">
  <link rel="stylesheet" href="<?= CSSPATH ?>/modals.css">
  <link rel="stylesheet" href="<?= CSSPATH ?>/action-queue.css">

  <link rel="icon" href="<?= IMGPATH ?>/favicon.ico" type="image/x-icon">

  <script src="<?= JSPATH ?>/header.js" defer></script>
  <script src="<?= JSPATH ?>/theme-toggle.js" defer></script>
  <script src="<?= JSPATH ?>/modals.js" defer></script>
  <script src="<?= JSPATH ?>/action-queue.js" defer></script>
  <script src="<?= JSPATH ?>/sw-register.js" data-root="<?= ROOT ?>" defer></script>
  <script src="<?= JSPATH ?>/password-toggle.js" defer></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Akt:wght@100..900&family=Alfa+Slab+One&family=Bitter:ital,wght@0,100..900;1,100..900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>

<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <?php if (!$hideHeader): ?>
    <header>
      <div class="header-logo-cont">
        <a href="<?= ROOT ?>" class="header-logo">
          <div>
            <!-- <img src="<?= IMGPATH ?>/bsu.webp" alt=""> -->
            <!-- <img src="<?= IMGPATH ?>/ccard.webp" alt=""> -->
          </div>
          <div>BSU-<span>IACUC</span></div>
        </a>
      </div>

      <?php if ($user): ?>

        <!-- RESEARCHER MOBILE NAVIGATION -->
        <?php if ($role === 'researcher'): ?>
          <nav id="mobileNav" aria-label="Mobile navigation" aria-hidden="true">
            <ul class="nav-sidebar" id="nav-sidebar" inert>
              <li><a href="<?= ROOT ?>/home">Home</a></li>
              <li><a href="<?= ROOT ?>/submissions">My Protocols</a></li>
              <li><a href="<?= ROOT ?>/announcements">Announcements</a></li>
              <li><a href="<?= ROOT ?>/contact">Contact</a></li>
            </ul>
          </nav>

          <!-- STAFF (ADMIN / REVIEWER) MOBILE NAVIGATION -->
        <?php elseif ($role === 'admin' || $role === 'reviewer'): ?>
          <nav id="mobileNav" aria-label="Mobile navigation" aria-hidden="true">
            <ul class="nav-sidebar" id="nav-sidebar" inert>
              <li><a href="<?= ROOT ?>/admin/home">Dashboard</a></li>
              <li><a href="<?= ROOT ?>/admin/records">Records</a></li>
              <?php if ($role === 'admin'): ?>
                <li><a href="<?= ROOT ?>/admin/announcements">Announcements</a></li>
                <li><a href="<?= ROOT ?>/admin/accounts">Manage Accounts</a></li>
              <?php endif; ?>
            </ul>
          </nav>
        <?php endif; ?>

      <?php else: ?>
        <!-- PUBLIC MOBILE NAVIGATION -->
        <nav id="mobileNav" aria-label="Mobile navigation" aria-hidden="true">
          <ul class="nav-sidebar" id="nav-sidebar" inert>
            <li><a href="<?= ROOT ?>/home">Home</a></li>
            <li><a href="<?= ROOT ?>/announcements">Announcements</a></li>
            <li><a href="<?= ROOT ?>/contact">Contact</a></li>
          </ul>
        </nav>
      <?php endif; ?>

      <!-- DARK MODE TOGGLE -->
      <?php include 'theme-toggle.php'; ?>

      <!-- ACCOUNT DROPDOWN (LOGGED IN) -->
      <?php if (!$hideHeaderAuth): ?>
        <div class="header-auth">
          <?php if ($user) { ?>
            <!-- <span class="greeting">Hello, </span> -->
            <button class="my-account-dropdown"
              aria-expanded="false"
              aria-haspopup="true"
              aria-label="Show account dropdown menu"
              aria-controls="account-dropdown">

              <img src="<?= IMGPATH ?>/scientist.webp" alt="">

              <span><?= htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') ?></span>

              <span class="chev-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <use href="#chev-down-icon" />
                </svg>
              </span>
            </button>

            <div id="account-dropdown">
              <a href="<?= ROOT ?>/users/account">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <use href="#account-dropdown-icon" />
                </svg>
                My Profile</a>

              <?php if ($role === 'researcher'): ?>
                <a data-confirm-message="Confirm to log out?" data-confirm-ok-text="Log Out" href="<?= ROOT ?>/users/logout">
                <?php elseif ($role === 'admin' || $role === 'reviewer'): ?>
                  <a data-confirm-message="Confirm to log out?" data-confirm-ok-text="Log Out" href="<?= ROOT ?>/admin/logout">
                  <?php endif; ?>
                  <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <use href="#log-out-icon" />
                  </svg>
                  Log Out</a>
            </div>

            <!-- LOG IN/REGISTER (NOT LOGGED IN) -->
          <?php } else { ?>
            <a href="<?= ROOT ?>/users/login" id="headerLogin" class="auth-btn">Login</a>
            <a href="<?= ROOT ?>/users/register" id="headerRegister" class="auth-btn">Register</a>
          <?php } ?>

          <!-- MOBILE HAMBURGER ICON -->
          <button
            class="mobile-menu"
            aria-expanded="false"
            aria-controls="nav-sidebar"
            aria-label="Toggle navigation menu">
            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <use href="#menu-icon" />
            </svg>
          </button>
        </div>
      <?php endif; ?>
    </header>
  <?php endif; ?>

  <div id="sidebar-backdrop" aria-hidden="true"></div>