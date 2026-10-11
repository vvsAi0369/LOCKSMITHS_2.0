<?php
// ---- EDIT THESE ----------------------------------------------------------
date_default_timezone_set("Asia/Kolkata");

// Database (XAMPP defaults). Import schema.sql first.
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "locksmith");

// Website login.
define("OWNER_PASSWORD", "change-me");

// ntfy = the free service that delivers notifications to your phone.
// The topic is your private channel: keep it long and random, and subscribe
// to the same name in the ntfy app.
define("NTFY_SERVER", "https://ntfy.sh");
define("NTFY_TOPIC", "locksmith-CHANGE-ME-q7x2m9d4k1");
