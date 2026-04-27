<?php
/**
 * Stage 1: Pre-Flight Checks
 */

$php_version = phpversion();
$openssl_ok = extension_loaded('openssl');
$curl_ok = extension_loaded('curl');
$pdo_ok = extension_loaded('pdo_mysql');
$inc_writable = is_writable('../inc');

$can_proceed = (version_compare($php_version, '7.4.0', '>=') && $openssl_ok && $curl_ok && $pdo_ok && $inc_writable);

?>

<h3>System Pre-Flight</h3>
<div class="check-item">
    <span>PHP Version (>= 7.4)</span>
    <span class="<?php echo version_compare($php_version, '7.4.0', '>=') ? 'status-ok' : 'status-fail'; ?>">
        <?php echo $php_version; ?>
    </span>
</div>
<div class="check-item">
    <span>OpenSSL Extension</span>
    <span class="<?php echo $openssl_ok ? 'status-ok' : 'status-fail'; ?>">
        <?php echo $openssl_ok ? 'Available' : 'Missing'; ?>
    </span>
</div>
<div class="check-item">
    <span>cURL Extension</span>
    <span class="<?php echo $curl_ok ? 'status-ok' : 'status-fail'; ?>">
        <?php echo $curl_ok ? 'Available' : 'Missing'; ?>
    </span>
</div>
<div class="check-item">
    <span>PDO MySQL Extension</span>
    <span class="<?php echo $pdo_ok ? 'status-ok' : 'status-fail'; ?>">
        <?php echo $pdo_ok ? 'Available' : 'Missing'; ?>
    </span>
</div>
<div class="check-item">
    <span>Writable /inc Directory</span>
    <span class="<?php echo $inc_writable ? 'status-ok' : 'status-fail'; ?>">
        <?php echo $inc_writable ? 'Yes' : 'No (Fix permissions)'; ?>
    </span>
</div>

<div style="margin-top: 2rem;">
    <?php if ($can_proceed): ?>
        <form action="index.php?stage=2" method="POST">
            <button type="submit" class="btn">Proceed to Database Setup</button>
        </form>
    <?php else: ?>
        <div class="alert alert-error">
            Please resolve the issues above to continue.
        </div>
        <button class="btn" onclick="window.location.reload();">Retry Checks</button>
    <?php endif; ?>
</div>
