<?php
$title = 'Staff Login';
$hideHeaderAuth = true;
$hideHeader     = true;

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/sprites.php';
?>

<link rel="stylesheet" href="<?= CSSPATH ?>/account.css">

<div class="body">
    <main class="main-content" id="main-content" tabindex="-1">
        <form method="POST" action="<?= ROOT ?>/admin/login_process">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? $_SESSION['csrf_token'] ?? ''); ?>">

            <h1>Staff Login</h1>
            <p class="form-label">For administrators only.</p>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="success-message">
                    <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <use href="#info-icon">
                    </svg>
                    <?= htmlspecialchars($_SESSION['flash_success']); ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <use href="#info-icon">
                    </svg>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <label for="username">Username or Email:</label>
            <input type="text" id="username" name="username"
                placeholder="Enter your username or email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password"
                placeholder="Enter your password" minlength="8" required>

            <button type="submit">Log In</button>

            <p class="underlined-p"><a class="underlined" href="<?= ROOT ?>/admin/forgot_password">Forgot Password</a></p>

            <p class="helper-label helper">
                Need staff access? Ask an existing admin for an invite link.
            </p>
        </form>
    </main>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>