<?php
ob_start();
/** @var string $first_name */
?>

<p>Hi <?= htmlspecialchars($first_name) ?>,</p>
<p>Your BSU-IACUC staff account has been <strong>approved</strong>!</p>
<p>You can now log in at: <a href="<?= ROOT ?>/admin/login"><?= ROOT ?>/admin/login</a></p>
<p>— BSU-IACUC Team</p>
<?php return ob_get_clean(); ?>