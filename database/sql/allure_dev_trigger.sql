use allure_dev;

-- Xóa các trigger và event hiện có
DROP TRIGGER IF EXISTS after_stock_movement;
DROP TRIGGER IF EXISTS after_voucher_use;
DROP TRIGGER IF EXISTS after_treatment_usage;
DROP TRIGGER IF EXISTS after_invoice_create;
DROP TRIGGER IF EXISTS before_invoice_insert;
DROP EVENT IF EXISTS update_expired_vouchers;
DROP EVENT IF EXISTS notify_expiring_packages;
DROP PROCEDURE IF EXISTS redeem_points;

-- Tự động cập nhật số lượng sản phẩm trong kho:
DELIMITER //
CREATE TRIGGER after_stock_movement
AFTER INSERT ON stock_movements
FOR EACH ROW
BEGIN
    IF NEW.movement_type = 'import' THEN
        UPDATE products
        SET stock_quantity = stock_quantity + NEW.quantity
        WHERE id = NEW.product_id;
    ELSEIF NEW.movement_type = 'export' THEN
        UPDATE products
        SET stock_quantity = stock_quantity - NEW.quantity
        WHERE id = NEW.product_id;
    END IF;
END //
DELIMITER ;

-- Tự động cập nhật số lần sử dụng voucher:
DELIMITER //
CREATE TRIGGER after_voucher_use
AFTER INSERT ON user_vouchers
FOR EACH ROW
BEGIN
    UPDATE vouchers
    SET used_times = used_times + 1
    WHERE id = NEW.voucher_id;
END //
DELIMITER ;

-- Tự động cập nhật số buổi còn lại trong gói liệu trình:
DELIMITER //
CREATE TRIGGER after_treatment_usage
AFTER INSERT ON treatment_usage_history
FOR EACH ROW
BEGIN
    UPDATE user_treatment_packages
    SET remaining_sessions = remaining_sessions - 1
    WHERE id = NEW.user_treatment_package_id;
END //
DELIMITER ;

-- Tự động cập nhật tổng số lần mua hàng và điểm cho người dùng:
DELIMITER //
CREATE TRIGGER after_invoice_create
AFTER INSERT ON invoices
FOR EACH ROW
BEGIN
    DECLARE total_products INT;
    DECLARE points_to_add INT;

    -- Đếm số lượng sản phẩm trong hóa đơn
    SELECT SUM(quantity) INTO total_products
    FROM order_items oi
    JOIN cart_item_types cit ON oi.item_type_id = cit.id
    WHERE oi.id = NEW.order_item_id AND cit.type_name = 'product';

    -- Tính điểm (100 điểm cho mỗi sản phẩm)
    SET points_to_add = total_products * 100;

    -- Cập nhật điểm và tổng số lần mua hàng cho user
    UPDATE users
    SET loyalty_points = loyalty_points + points_to_add,
        purchase_count = purchase_count + 1
    WHERE id = NEW.user_id;
END //
DELIMITER ;

-- Tự động tính tổng giá trị hóa đơn:
DELIMITER //
CREATE TRIGGER before_invoice_insert
BEFORE INSERT ON invoices
FOR EACH ROW
BEGIN
    DECLARE total DECIMAL(10, 2);

    SELECT SUM(price * quantity) INTO total
    FROM order_items
    WHERE id = NEW.order_item_id;

    SET NEW.total_amount = total - NEW.discount_amount;
END //
DELIMITER ;

-- Tự động cập nhật trạng thái voucher khi hết hạn:
DELIMITER //
CREATE EVENT update_expired_vouchers
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE vouchers
    SET status = 'expired'
    WHERE end_date < CURDATE() AND status = 'active';
END //
DELIMITER ;

-- Tự động tạo thông báo khi gói liệu trình sắp hết hạn:
DELIMITER //
CREATE EVENT notify_expiring_packages
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    INSERT INTO notifications (user_id, content, status, created_at)
    SELECT user_id,
           CONCAT('Gói liệu trình của bạn sẽ hết hạn vào ngày ', expiry_date),
           'unseen',
           NOW()
    FROM user_treatment_packages
    WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY);
END //
DELIMITER ;

-- Thủ tục đổi điểm thưởng
DELIMITER //
CREATE PROCEDURE redeem_points(
    IN p_user_id CHAR(36),
    IN p_reward_item_id INT UNSIGNED
)
BEGIN
    DECLARE user_points INT;
    DECLARE required_points INT;
    DECLARE item_type VARCHAR(20);
    DECLARE item_id INT UNSIGNED;

    -- Lấy số điểm hiện tại của user
    SELECT loyalty_points INTO user_points
    FROM users
    WHERE id = p_user_id;

    -- Lấy thông tin về phần thưởng
    SELECT points_required, item_type, item_id
    INTO required_points, item_type, item_id
    FROM reward_items
    WHERE id = p_reward_item_id AND is_active = 1;

    -- Kiểm tra xem có đủ điểm không
    IF user_points >= required_points THEN
        -- Trừ điểm
        UPDATE users
        SET loyalty_points = loyalty_points - required_points
        WHERE id = p_user_id;

        -- Ghi lại lịch sử đổi điểm
        INSERT INTO point_redemption_history (user_id, reward_item_id, points_used)
        VALUES (p_user_id, p_reward_item_id, required_points);
        
        -- Thêm logic xử lý phần thưởng tại đây (ví dụ: cập nhật voucher, gói dịch vụ, v.v.)
    END IF;
END //
DELIMITER ;