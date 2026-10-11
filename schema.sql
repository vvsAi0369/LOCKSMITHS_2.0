-- Import this once (phpMyAdmin > Import, or the SQL tab).
CREATE DATABASE IF NOT EXISTS locksmith;
USE locksmith;

CREATE TABLE IF NOT EXISTS locks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    location VARCHAR(80) NOT NULL DEFAULT '',
    device_key CHAR(24) NOT NULL UNIQUE,
    state ENUM('locked', 'opened') NOT NULL DEFAULT 'locked',
    battery TINYINT UNSIGNED NULL,
    last_seen DATETIME NULL,
    notify_opened TINYINT(1) NOT NULL DEFAULT 1,
    notify_locked TINYINT(1) NOT NULL DEFAULT 1,
    muted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lock_id INT NOT NULL,
    type ENUM('opened', 'locked', 'tamper', 'test', 'low_battery') NOT NULL,
    source ENUM('website', 'sensor') NOT NULL,
    notified TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (lock_id) REFERENCES locks(id) ON DELETE CASCADE,
    INDEX (created_at)
);

CREATE TABLE IF NOT EXISTS settings (
    k VARCHAR(40) PRIMARY KEY,
    v VARCHAR(100) NOT NULL
);

INSERT IGNORE INTO settings (k, v) VALUES
    ('quiet_on', '0'),
    ('quiet_start', '22:00'),
    ('quiet_end', '06:00');

-- One starter lock so the Locks page isn't empty.
INSERT INTO locks (name, location, device_key, created_at)
SELECT 'Main door', 'Front entrance', SUBSTRING(MD5(RAND()), 1, 24), NOW()
WHERE NOT EXISTS (SELECT 1 FROM locks);
