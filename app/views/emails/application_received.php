<?php
ob_start();
/** @var string $first_name */
/** @var string $role */
?>

<p>Hi <?= htmlspecialchars($first_name) ?>,</p>
<p>We've received your application to join BSU-IACUC as <strong><?= htmlspecialchars($role) ?></strong>.</p>
<p>An admin will review your application and you'll be notified once a decision is made.</p>
<p>— BSU-IACUC Team</p>
<?php return ob_get_clean(); ?>