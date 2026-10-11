-- Import AFTER schema.sql. Uses the same tables; only fills in data so the demo looks lived-in.
USE locksmith;

UPDATE locks SET battery = 84, last_seen = NOW(), state = 'locked'
WHERE name = 'Main door';

INSERT INTO locks (name, location, device_key, battery, last_seen, state, created_at)
SELECT 'Back door', 'Kitchen side', SUBSTRING(MD5(RAND()), 1, 24), 71, NOW(), 'locked', NOW() - INTERVAL 20 DAY
WHERE NOT EXISTS (SELECT 1 FROM locks WHERE name = 'Back door');

-- A few days of believable history (only added if the log is empty).
INSERT INTO events (lock_id, type, source, notified, created_at)
SELECT * FROM (
    SELECT (SELECT id FROM locks WHERE name = 'Main door' LIMIT 1) AS lock_id, 'opened' AS type, 'sensor' AS source, 1 AS notified, NOW() - INTERVAL 3 DAY - INTERVAL 2 HOUR AS created_at
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Main door' LIMIT 1), 'locked', 'sensor', 1, NOW() - INTERVAL 3 DAY - INTERVAL 1 HOUR
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Back door' LIMIT 1), 'opened', 'sensor', 1, NOW() - INTERVAL 2 DAY - INTERVAL 5 HOUR
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Back door' LIMIT 1), 'locked', 'sensor', 1, NOW() - INTERVAL 2 DAY - INTERVAL 4 HOUR
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Main door' LIMIT 1), 'opened', 'sensor', 1, NOW() - INTERVAL 1 DAY - INTERVAL 9 HOUR
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Main door' LIMIT 1), 'locked', 'sensor', 1, NOW() - INTERVAL 1 DAY - INTERVAL 8 HOUR
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Main door' LIMIT 1), 'opened', 'sensor', 1, NOW() - INTERVAL 7 HOUR
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Main door' LIMIT 1), 'locked', 'sensor', 1, NOW() - INTERVAL 6 HOUR
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Back door' LIMIT 1), 'opened', 'sensor', 1, NOW() - INTERVAL 90 MINUTE
    UNION ALL SELECT (SELECT id FROM locks WHERE name = 'Back door' LIMIT 1), 'locked', 'sensor', 1, NOW() - INTERVAL 80 MINUTE
) AS t
WHERE NOT EXISTS (SELECT 1 FROM events);
