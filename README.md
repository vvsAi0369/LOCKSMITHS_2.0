# LockSmith

A small website for a smart lock. Its only job is to send notifications to your phone.

## Pages
- `index.php`      Home, with the reassurance about what the lock does and doesn't do
- `login.php`      Password login
- `dashboard.php`  Status headline, "Locks connected" button, recent activity, urgent test alert
- `locks.php`      Every lock: status, battery, alert settings, test notification, add/remove
- `activity.php`   Full event log with filters
- `settings.php`   Quiet hours and phone setup

## Setup (XAMPP)
1. Start **Apache** and **MySQL** in the XAMPP panel.
2. Open http://localhost/phpmyadmin, go to **Import**, choose `schema.sql`, run it.
3. Copy this folder to `C:\xampp\htdocs\locksmith\`.
4. Edit `config.php`: set `OWNER_PASSWORD` and a long random `NTFY_TOPIC`.
5. Import `demo_data.sql` the same way.
6. Install the **ntfy** app on your phone and subscribe to that topic.
7. Open http://localhost/locksmith/ and log in. Go to Locks and press **Send test**.

`extension=curl` must be enabled in php.ini (it usually is).

## Demo data
After `schema.sql`, import `demo_data.sql` too. It fills in battery levels, a second lock and a few days of activity so everything looks live. The locks always show as Connected.

## Notification rules
- Opened / locked alerts are skipped if the lock is muted, that alert is turned off, or quiet hours are on.
- Tamper alerts always come through.
- **Send test notification** (per lock, and on Settings) sends a real notification to your phone.
