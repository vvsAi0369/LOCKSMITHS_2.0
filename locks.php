<?php
require "includes/auth.php";
require "includes/layout.php";

function find_lock(mysqli $conn, int $id): ?array
{
    $st = $conn->prepare("SELECT * FROM locks WHERE id = ?");
    $st->bind_param("i", $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();
    $action = $_POST["action"] ?? "";
    $id     = (int) ($_POST["lock_id"] ?? 0);

    if ($action === "add") {
        $name = trim($_POST["name"] ?? "");
        $loc  = trim($_POST["location"] ?? "");
        if ($name === "") {
            flash("error", "Give the lock a name.");
        } else {
            $key = bin2hex(random_bytes(12));
            $now = date("Y-m-d H:i:s");
            $st  = $conn->prepare("INSERT INTO locks (name, location, device_key, created_at) VALUES (?, ?, ?, ?)");
            $st->bind_param("ssss", $name, $loc, $key, $now);
            $st->execute();
            flash("ok", "Added $name.");
        }
    } elseif ($lock = find_lock($conn, $id)) {
        if ($action === "test") {
            [$ok, $msg] = process_event($conn, $lock, "test");
            flash($ok ? "ok" : "error", $lock["name"] . ": " . $msg);
        } elseif ($action === "save") {
            $name = trim($_POST["name"] ?? "");
            $loc  = trim($_POST["location"] ?? "");
            $no   = isset($_POST["notify_opened"]) ? 1 : 0;
            $nl   = isset($_POST["notify_locked"]) ? 1 : 0;
            $mu   = isset($_POST["muted"]) ? 1 : 0;
            if ($name === "") {
                flash("error", "A lock needs a name.");
            } else {
                $st = $conn->prepare("UPDATE locks SET name = ?, location = ?, notify_opened = ?, notify_locked = ?, muted = ? WHERE id = ?");
                $st->bind_param("ssiiii", $name, $loc, $no, $nl, $mu, $id);
                $st->execute();
                flash("ok", "Saved changes to $name.");
            }
        } elseif ($action === "delete") {
            $st = $conn->prepare("DELETE FROM locks WHERE id = ?");
            $st->bind_param("i", $id);
            $st->execute();
            flash("ok", "Removed " . $lock["name"] . " and its activity.");
        }
    }
    redirect("locks.php");
}

$locks = $conn->query("SELECT * FROM locks ORDER BY name")->fetch_all(MYSQLI_ASSOC);

page_start("Locks", "locks.php");
?>
<main class="wrap">
    <?php show_flash(); ?>

    <h1>Locks connected: <?php echo count($locks); ?></h1>
    <p class="lead">Every lock that can send you a notification. Each one has its own mute and alert settings.</p>

    <?php if (!$locks) : ?>
        <p class="empty">No locks yet. Add one below.</p>
    <?php endif; ?>

    <?php foreach ($locks as $l) :
        [$cls, $label, $note] = lock_status($l);
        $battery = $l["battery"];
        $bcls = $battery === null ? "" : ($battery <= 20 ? "low" : ($battery <= 50 ? "mid" : "good"));
    ?>
    <article class="lock">
        <div class="lock-head">
            <div>
                <h2><?php echo e($l["name"]); ?></h2>
                <?php if ($l["location"] !== "") : ?><p class="meta"><?php echo e($l["location"]); ?></p><?php endif; ?>
            </div>
            <p class="status <?php echo $cls; ?>"><span class="dot"></span><?php echo e($label); ?></p>
        </div>

        <dl class="facts">
            <div>
                <dt>Sensor</dt>
                <dd><?php echo e($note); ?></dd>
            </div>
            <div>
                <dt>Battery</dt>
                <dd>
                    <?php if ($battery === null) : ?>
                        Not reported
                    <?php else : ?>
                        <span class="battery <?php echo $bcls; ?>"><span style="width:<?php echo (int) $battery; ?>%"></span></span>
                        <?php echo (int) $battery; ?>%
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt>Alerts</dt>
                <dd>
                    <?php
                    if ($l["muted"]) {
                        echo "Muted. Tamper alerts still come through.";
                    } else {
                        $on = [];
                        if ($l["notify_opened"]) $on[] = "opened";
                        if ($l["notify_locked"]) $on[] = "locked";
                        echo $on ? "On for " . implode(" and ", $on) : "Only tamper and battery";
                    }
                    ?>
                </dd>
            </div>
        </dl>

        <form method="post" class="btn-row test-row">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="test">
            <input type="hidden" name="lock_id" value="<?php echo (int) $l["id"]; ?>">
            <button class="btn" type="submit">Send test notification</button>
        </form>

        <details>
            <summary>Alert settings</summary>
            <form method="post" class="stack settings-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="lock_id" value="<?php echo (int) $l["id"]; ?>">

                <label>Name <input type="text" name="name" value="<?php echo e($l["name"]); ?>" maxlength="60" required></label>
                <label>Where is it <input type="text" name="location" value="<?php echo e($l["location"]); ?>" maxlength="80"></label>

                <label class="check"><input type="checkbox" name="notify_opened" <?php echo $l["notify_opened"] ? "checked" : ""; ?>> Tell me when it opens</label>
                <label class="check"><input type="checkbox" name="notify_locked" <?php echo $l["notify_locked"] ? "checked" : ""; ?>> Tell me when it locks</label>
                <label class="check"><input type="checkbox" name="muted" <?php echo $l["muted"] ? "checked" : ""; ?>> Mute this lock (tamper and low battery still alert)</label>

                <button class="btn primary" type="submit">Save changes</button>
            </form>
        </details>

        <form method="post" class="remove" onsubmit="return confirm('Remove this lock and its activity?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="lock_id" value="<?php echo (int) $l["id"]; ?>">
            <button class="textbtn" type="submit">Remove this lock</button>
        </form>
    </article>
    <?php endforeach; ?>

    <section class="add">
        <h2>Add a lock</h2>
        <form method="post" class="stack">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <label>Name <input type="text" name="name" placeholder="Back door" maxlength="60" required></label>
            <label>Where is it <input type="text" name="location" placeholder="Kitchen side" maxlength="80"></label>
            <button class="btn primary" type="submit">Add lock</button>
        </form>
    </section>
</main>
<?php page_end(); ?>
