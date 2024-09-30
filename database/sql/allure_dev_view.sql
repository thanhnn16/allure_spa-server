use allure_dev;

CREATE VIEW treatment_package_progress AS
SELECT
    utp.id AS package_id,
    u.full_name AS user_name,
    t.treatment_name,
    tc.combo_type,
    utp.total_sessions,
    utp.remaining_sessions,
    COUNT(tuh.id) AS used_sessions,
    utp.expiry_date
FROM
    user_treatment_packages utp
    JOIN users u ON utp.user_id = u.id
    JOIN treatment_combos tc ON utp.treatment_combo_id = tc.id
    JOIN treatments t ON tc.treatment_id = t.id
    LEFT JOIN treatment_usage_history tuh ON utp.id = tuh.user_treatment_package_id
GROUP BY
    utp.id;

CREATE VIEW user_loyalty_summary AS
SELECT
    u.id AS user_id,
    u.full_name,
    u.loyalty_points,
    COUNT(prh.id) AS total_redemptions,
    SUM(prh.points_used) AS total_points_used
FROM
    users u
    LEFT JOIN point_redemption_history prh ON u.id = prh.user_id
GROUP BY
    u.id;