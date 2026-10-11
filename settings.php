<?php
require "includes/auth.php";
require "includes/layout.php";

if (isset($_POST["quiet"])) {
    csrf_check();
    $on    = isset($_POST["quiet_on"]) ? "1" : "0";
    $start = $_POST["quiet_start"] ?? "22:00";
    $end   = $_POST["quiet_end"] ?? "06:00";

    $valid = fn($t) => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);
    if (!$valid($start) || !$valid($end)) {
        flash("error", "Enter both times like 22:00.");
    } else {
        set_setting($conn, "quiet_on", $on);
        set_setting($conn, "quiet_start", $start);
        set_setting($conn, "quiet_end", $end);
        flash("ok", "Quiet hours saved.");
    }
    redirect("settings.php");
}

if (isset($_POST["test_all"])) {
    csrf_check();
    $ok = send_notification("LockSmith: test", "This is a test. Your phone is set up correctly.", 3, ["bell"]);
    flash($ok ? "ok" : "error", $ok ? "Test sent to your phone." : "Couldn't reach the notification service. Check your internet and the topic in config.php.");
    redirect("settings.php");
}

$on    = get_setting($conn, "quiet_on", "0") === "1";
$start = get_setting($conn, "quiet_start", "22:00");
$end   = get_setting($conn, "quiet_end", "06:00");

page_start("Settings", "settings.php");
?>
<main class="wrap">
    <?php show_flash(); ?>

    <h1>Settings</h1>

    <section class="block">
        <h2>Quiet hours</h2>
        <p>During quiet hours, opened and locked alerts are held back and only logged. Tamper and low battery alerts always come through.</p>
        <form method="post" class="stack settings-form">
            <?php echo csrf_field(); ?>
            <label class="check"><input type="checkbox" name="quiet_on" <?php echo $on ? "checked" : ""; ?>> Turn on quiet hours</label>
            <div class="time-row">
                <label>From <input type="time" name="quiet_start" value="<?php echo e($start); ?>" required></label>
                <label>Until <input type="time" name="quiet_end" value="<?php echo e($end); ?>" required></label>
            </div>
            <button class="btn primary" name="quiet" value="1">Save quiet hours</button>
        </form>
    </section>

    <section class="block">
        <h2>Set up your phone</h2>
        <ol class="steps">
            <li>Install the <strong>ntfy</strong> app from the Play Store or App Store.</li>
            <li>Tap the plus button and subscribe to this topic: <code><?php echo e(NTFY_TOPIC); ?></code></li>
            <li>Press the button below. Your phone should buzz within a few seconds.</li>
        </ol>
        <form method="post">
            <?php echo csrf_field(); ?>
            <button class="btn" name="test_all" value="1">Send a test notification</button>
        </form>
        <p class="hint">To change the topic or the password, edit <code>config.php</code>. Anyone who knows the topic can read your alerts, so keep it long and random.</p>
    </section>
</main>
<?php page_end(); ?>
