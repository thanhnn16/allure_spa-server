USE allure_dev;

-- View cho tiến độ gói liệu trình
CREATE VIEW treatment_package_progress AS
SELECT
    utp.id AS package_id,
    u.full_name AS user_name,
    t.treatment_name,
    utp.combo_type,
    utp.total_sessions,
    utp.remaining_sessions,
    (utp.total_sessions - utp.remaining_sessions) AS used_sessions,
    utp.expiry_date
FROM
    user_treatment_packages utp
    JOIN users u ON utp.user_id = u.id
    JOIN treatments t ON utp.treatment_id = t.id;

-- View cho tổng kết điểm thưởng của người dùng
CREATE VIEW user_loyalty_summary AS
SELECT
    u.id AS user_id,
    u.full_name,
    u.loyalty_points,
    COUNT(prh.id) AS total_redemptions,
    SUM(ri.points_required) AS total_points_used
FROM
    users u
    LEFT JOIN point_redemption_history prh ON u.id = prh.user_id
    LEFT JOIN reward_items ri ON prh.reward_item_id = ri.id
GROUP BY
    u.id;

-- View cho thống kê sử dụng liệu trình
CREATE VIEW treatment_usage_statistics AS
SELECT
    t.id AS treatment_id,
    t.treatment_name,
    COUNT(tuh.id) AS total_usages,
    AVG(
        TIMESTAMPDIFF(MINUTE, utp.purchase_date, tuh.treatment_date)
    ) AS avg_days_to_first_use
FROM
    treatments t
    LEFT JOIN user_treatment_packages utp ON t.id = utp.treatment_id
    LEFT JOIN treatment_usage_history tuh ON utp.id = tuh.user_treatment_package_id
GROUP BY
    t.id;

-- View cho thống kê đánh giá sản phẩm và liệu trình
CREATE VIEW rating_statistics AS
SELECT
    r.rating_type,
    r.item_id,
    CASE
        WHEN r.rating_type = 'product' THEN p.product_name
        WHEN r.rating_type = 'treatment' THEN t.treatment_name
        ELSE s.staff_name
    END AS item_name,
    AVG(r.stars) AS average_rating,
    COUNT(r.id) AS total_ratings
FROM
    ratings r
    LEFT JOIN products p ON r.rating_type = 'product'
    AND r.item_id = p.id
    LEFT JOIN treatments t ON r.rating_type = 'treatment'
    AND r.item_id = t.id
    LEFT JOIN staffs s ON r.rating_type = 'staff'
    AND r.item_id = s.id
GROUP BY
    r.rating_type,
    r.item_id;