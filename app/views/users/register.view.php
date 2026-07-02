<?php
$title = 'Register';
$hideHeader = true;
include dirname(__DIR__) . '/includes/header.php';
?>

<?php if (!empty($success)): ?>
    <meta http-equiv="refresh" content="2; url=<?= ROOT ?>/users/login">
<?php endif; ?>

<link rel="stylesheet" href="<?= CSSPATH ?>/account.css">

<div class="body">
    <main class="main-content wide" id="main-content" tabindex="-1">
        <a class="btn-back button" href="<?= $_SERVER['HTTP_REFERER'] ?? ROOT . '/home' ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <use href="#back-icon">
            </svg>
            Back
        </a>

        <form method="POST" action="<?= ROOT ?>/users/register_process">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? ''); ?>">

            <h1>Register</h1>

            <?php if (!empty($success)): ?>
                <div class="success-message">
                    Account created successfully!
                    <!-- <span>Redirecting in <span id="countdown">1</span> second...</span> -->

                    <span>Redirecting...</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <span class="helper">(Alphabetic characters (including accented and non-English letters), hyphens, and apostrophes only.)</span>
            <div class="label-group">
                <div>
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Juan"
                        value="<?= htmlspecialchars($old['first_name'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Santos"
                        value="<?= htmlspecialchars($old['last_name'] ?? ''); ?>" required>
                </div>
            </div>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="JuanSantos_123"
                value="<?= htmlspecialchars($old['username'] ?? ''); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="juan.santos@example.com"
                value="<?= htmlspecialchars($old['email'] ?? ''); ?>" required>

            <label for="password">Create Password</label>
            <input type="password" id="password" name="password"
                placeholder="Enter your password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                placeholder="Confirm your password" required>

            <div class="password-requirements">
                Password must contain:
                <ul>
                    <li>At least 8 characters</li>
                    <li>Uppercase letter</li>
                    <li>Lowercase letter</li>
                    <li>Number</li>
                    <li>Special character (! @ # $ % ^ &amp; *)</li>
                </ul>
            </div>

            <button type="submit">Register</button>
            <p class="underlined-p">Already have an account? <a class="underlined" href="<?= ROOT ?>/users/login">Log in here</a></p>
        </form>
    </main>
</div>

<?php if (!empty($success)): ?>
    <script>
        let seconds = 1;
        const el = document.getElementById('countdown');
        if (el) {
            const timer = setInterval(() => {
                seconds--;
                if (seconds > 0) el.textContent = seconds;
                else clearInterval(timer);
            }, 1000);
        }
    </script>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>