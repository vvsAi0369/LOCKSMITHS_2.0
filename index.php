<?php
require "includes/layout.php";
page_start("Home", "", false);
?>
<main>
<section class="hero">
    <div class="hero-text">
        <h1>Your door is locked. If that changes, you'll hear first.</h1>
        <p class="lead">LockSmith watches your lock and sends a notification to your phone when it opens, locks, or is tampered with. That is all it does.</p>
        <p><a class="btn primary" href="login.php">Log in</a></p>
    </div>
    <div class="hero-door" aria-hidden="true">
        <svg viewBox="0 0 320 480" role="img" focusable="false">
            <rect x="64" y="48" width="192" height="384" rx="16" fill="#c69a35"/>
            <rect x="74" y="58" width="172" height="364" rx="10" fill="none" stroke="#8f6b17" stroke-width="2"/>
            <circle cx="90" cy="76" r="5" fill="#8f6b17"/><circle cx="230" cy="76" r="5" fill="#8f6b17"/>
            <circle cx="90" cy="404" r="5" fill="#8f6b17"/><circle cx="230" cy="404" r="5" fill="#8f6b17"/>
            <circle cx="160" cy="112" r="7" fill="#2e7d4f" class="lamp"/>
            <circle cx="160" cy="214" r="50" fill="#8f6b17"/>
            <circle cx="160" cy="214" r="42" fill="#d9b457"/>
            <circle cx="160" cy="205" r="11" fill="#14201c"/>
            <path d="M154 210 L166 210 L169 238 L151 238 Z" fill="#14201c"/>
            <rect x="104" y="304" width="112" height="24" rx="12" fill="#8f6b17"/>
            <rect x="104" y="300" width="112" height="24" rx="12" fill="#d9b457"/>
        </svg>
    </div>
</section>

<section class="assure">
    <div>
        <h2>It can't open your door</h2>
        <p>LockSmith only reports what the lock does. Your key works exactly as before, even if the website is switched off.</p>
    </div>
    <div>
        <h2>Alerts go to one phone</h2>
        <p>Notifications are sent to the phone you choose and nowhere else.</p>
    </div>
    <div>
        <h2>You decide what you hear about</h2>
        <p>Mute a lock, turn off opened or locked alerts, or set quiet hours for the night.</p>
    </div>
    <div>
        <h2>Tampering is never silenced</h2>
        <p>Even with a lock muted or quiet hours on, a tamper alert always comes through.</p>
    </div>
</section>
</main>
<?php page_end(); ?>
