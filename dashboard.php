<?php
require "includes/auth.php";
require "includes/layout.php";

// "Send urgent alert" button.
if (isset($_POST["urgent"])) {
    csrf_check();
    $ok = send_notification("LockSmith: urgent test", "Highest priority alert. If you saw this and heard it, you're set.", 5, ["loudspeaker"]);
    flash($ok ? "ok" : "error", $ok ? "Urgent alert sent to your phone." : "Couldn't reach the notification service.");
    redirect("dashboard.php");
}

$locks = $conn->query("SELECT * FROM locks ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$total = count($locks);

$open = 0;
$openName = "";
foreach ($locks as $l) {
    [$cls] = lock_status($l);
    if ($cls === "open") { $open++; $openName = $l["name"]; }
}

if ($total === 0) {
    $headline = "No locks yet.";
    $sub = "Add your first lock to start getting notifications.";
} elseif ($open > 0) {
    $headline = $open === 1 ? $openName . " is open." : "$open locks are open.";
    $sub = "You were notified when it opened, unless that lock is muted.";
} else {
    $headline = "All locks are secure.";
    $sub = "Nothing has changed that you need to know about.";
}

$recent = $conn->query(
    "SELECT e.*, l.name AS lock_name FROM events e JOIN locks l ON l.id = e.lock_id ORDER BY e.created_at DESC, e.id DESC LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);

page_start("Dashboard", "dashboard.php");
?>
<main class="wrap">
    <?php show_flash(); ?>

    <h1><?php echo e($headline); ?></h1>
    <p class="lead"><?php echo e($sub); ?></p>

    <p class="primary-action">
        <a class="btn primary big" href="locks.php">Locks connected: <?php echo $total; ?></a>
    </p>

    <div class="two-col">
        <section>
            <h2>Recent activity</h2>
            <?php if (!$recent) : ?>
                <p class="empty">Nothing yet. Events appear here as your locks report them.</p>
            <?php else : ?>
                <ul class="rows">
                    <?php foreach ($recent as $r) : ?>
                        <li>
                            <span class="what"><strong><?php echo e($r["lock_name"]); ?></strong> <?php echo e(strtolower(event_label($r["type"]))); ?></span>
                            <span class="meta"><?php echo e(time_ago($r["created_at"])); ?><?php echo $r["notified"] ? "" : ", no notification"; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><a class="textlink" href="activity.php">See all activity</a></p>
            <?php endif; ?>
        </section>

        <aside>
            <h2>Your phone</h2>
            <p>Notifications arrive in the ntfy app, subscribed to the topic in <code>config.php</code>.</p>
            <form method="post" class="inline-form">
                <?php echo csrf_field(); ?>
                <button class="btn" name="urgent" value="1">Send urgent alert</button>
            </form>
            <p class="hint">Uses the highest priority so it stands out. Handy for checking your phone's volume and Do Not Disturb settings.</p>

            <h2>Quiet hours</h2>
            <p>
                <?php if (get_setting($conn, "quiet_on") === "1") : ?>
                    On, from <?php echo e(get_setting($conn, "quiet_start")); ?> to <?php echo e(get_setting($conn, "quiet_end")); ?>. Opened and locked alerts are held back. Tamper alerts still come through.
                <?php else : ?>
                    Off. <a class="textlink" href="settings.php">Set quiet hours</a>
                <?php endif; ?>
            </p>
        </aside>
    </div>
</main>
<?php page_end(); ?>
