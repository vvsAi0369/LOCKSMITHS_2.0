<?php
require "includes/auth.php";
require "includes/layout.php";

if (isset($_POST["clear"])) {
    csrf_check();
    $conn->query("DELETE FROM events");
    flash("ok", "Activity cleared.");
    redirect("activity.php");
}

$lockId = (int) ($_GET["lock"] ?? 0);
$type   = $_GET["type"] ?? "";
$types  = ["opened", "locked", "tamper", "test", "low_battery"];

$sql = "SELECT e.*, l.name AS lock_name FROM events e JOIN locks l ON l.id = e.lock_id WHERE 1=1";
$bind = "";
$args = [];
if ($lockId > 0) {
    $sql .= " AND e.lock_id = ?";
    $bind .= "i";
    $args[] = $lockId;
}
if (in_array($type, $types, true)) {
    $sql .= " AND e.type = ?";
    $bind .= "s";
    $args[] = $type;
}
$sql .= " ORDER BY e.created_at DESC, e.id DESC LIMIT 100";

$st = $conn->prepare($sql);
if ($args) {
    $st->bind_param($bind, ...$args);
}
$st->execute();
$events = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$locks  = $conn->query("SELECT id, name FROM locks ORDER BY name")->fetch_all(MYSQLI_ASSOC);

page_start("Activity", "activity.php");
?>
<main class="wrap">
    <?php show_flash(); ?>

    <h1>Activity</h1>
    <p class="lead">The last 100 things your locks reported.</p>

    <form method="get" class="filters">
        <label>Lock
            <select name="lock">
                <option value="0">All locks</option>
                <?php foreach ($locks as $l) : ?>
                    <option value="<?php echo (int) $l["id"]; ?>"<?php echo $lockId === (int) $l["id"] ? " selected" : ""; ?>><?php echo e($l["name"]); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Event
            <select name="type">
                <option value="">All events</option>
                <?php foreach ($types as $t) : ?>
                    <option value="<?php echo $t; ?>"<?php echo $type === $t ? " selected" : ""; ?>><?php echo e(event_label($t)); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn" type="submit">Filter</button>
    </form>

    <?php if (!$events) : ?>
        <p class="empty">No activity matches. Events appear here as soon as a lock reports one.</p>
    <?php else : ?>
        <table class="log">
            <thead><tr><th>When</th><th>Lock</th><th>Event</th><th>Phone</th></tr></thead>
            <tbody>
            <?php foreach ($events as $ev) : ?>
                <tr class="<?php echo e($ev["type"]); ?>">
                    <td><?php echo e(date("j M, g:i a", strtotime($ev["created_at"]))); ?></td>
                    <td><?php echo e($ev["lock_name"]); ?></td>
                    <td><?php echo e(event_label($ev["type"])); ?></td>
                    <td><?php echo $ev["notified"] ? "Notified" : "Not sent"; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <form method="post" class="remove" onsubmit="return confirm('Clear all activity?');">
            <?php echo csrf_field(); ?>
            <button class="textbtn" name="clear" value="1">Clear all activity</button>
        </form>
    <?php endif; ?>
</main>
<?php page_end(); ?>
