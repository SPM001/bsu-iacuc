<?php
$title = 'Manage Announcements';
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/scroll-top.php';

$user = $user ?? $_SESSION['user'] ?? [];
$csrf = $csrf ?? '';
$first_name = $user['first_name'];
?>

<!-- <link rel="stylesheet" href="<?= CSSPATH ?>/admin-home.css"> -->

<div class="body">
    <?php include dirname(__DIR__) . '/includes/navigation.php'; ?>

    <main class="main-content" id="main-content" tabindex="-1">

        <h1>Manage Announcements</h1>

    </main>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>