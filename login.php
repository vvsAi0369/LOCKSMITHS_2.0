<?php
require "includes/layout.php";
require "config.php";

if (!empty($_SESSION["owner"])) {
    redirect("dashboard.php");
}

$error = "";

if (isset($_POST["login"])) {
    csrf_check();
    // Small pause makes password guessing slow.
    usleep(400000);
    if (hash_equals(OWNER_PASSWORD, $_POST["password"] ?? "")) {
        session_regenerate_id(true);
        $_SESSION["owner"] = true;
        redirect("dashboard.php");
    }
    $error = "That password is wrong. Try again.";
}

page_start("Log in", "login.php", false);
?>
<main class="narrow">
    <h1>Log in</h1>
    <p class="lead">Enter your password to see your locks.</p>

    <?php if ($error !== "") : ?>
        <p class="flash error" role="alert"><?php echo e($error); ?></p>
    <?php endif; ?>

    <form method="post" class="stack">
        <?php echo csrf_field(); ?>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required autofocus>
        <button type="submit" name="login" class="btn primary">Log in</button>
    </form>
</main>
<?php page_end(); ?>
