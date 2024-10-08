-- Trigger để cập nhật số lượng tồn kho sau khi thêm hoặc cập nhật stock_movements
DELIMITER / / CREATE TRIGGER after_stock_movement_insert_update
AFTER
INSERT
    ON stock_movements FOR EACH ROW BEGIN IF NEW.movement_type = 'import' THEN
UPDATE
    products
SET
    stock_quantity = stock_quantity + NEW.quantity
WHERE
    id = NEW.product_id;

ELSEIF NEW.movement_type = 'export' THEN
UPDATE
    products
SET
    stock_quantity = stock_quantity - NEW.quantity
WHERE
    id = NEW.product_id;

END IF;

END;

/ / DELIMITER;

-- Trigger để cập nhật trạng thái thanh toán trong invoices
DELIMITER / / CREATE TRIGGER after_payment_history_insert
AFTER
INSERT
    ON payment_histories FOR EACH ROW BEGIN
UPDATE
    invoices
SET
    payment_status = NEW.new_payment_status
WHERE
    id = NEW.invoice_id;

END;

/ / DELIMITER;

-- Trigger để cập nhật số lần sử dụng của voucher sau khi thêm vào user_vouchers
DELIMITER / / CREATE TRIGGER after_user_voucher_insert
AFTER
INSERT
    ON user_vouchers FOR EACH ROW BEGIN
UPDATE
    vouchers
SET
    used_times = used_times + 1
WHERE
    id = NEW.voucher_id;

END;

/ / DELIMITER;

-- Trigger để cập nhật số điểm tích lũy của người dùng sau khi thêm hóa đơn mới
DELIMITER / / CREATE TRIGGER after_invoice_insert
AFTER
INSERT
    ON invoices FOR EACH ROW BEGIN DECLARE points_earned INT;

SET
    points_earned = FLOOR(NEW.total_amount / 100000);

-- Giả sử cứ 100,000 VND được 1 điểm
UPDATE
    users
SET
    loyalty_points = loyalty_points + points_earned,
    purchase_count = purchase_count + 1
WHERE
    id = NEW.user_id;

END;

/ / DELIMITER;

-- Trigger để cập nhật số lượng phiên còn lại trong user_treatment_packages sau khi sử dụng
DELIMITER / / CREATE TRIGGER after_treatment_usage_insert
AFTER
INSERT
    ON treatment_usage_history FOR EACH ROW BEGIN
UPDATE
    user_treatment_packages
SET
    remaining_sessions = remaining_sessions - 1
WHERE
    id = NEW.user_treatment_package_id;

END;

/ / DELIMITER;

-- Trigger để tự động xóa các gói điều trị đã hết hạn
DELIMITER / / CREATE EVENT delete_expired_treatment_packages ON SCHEDULE EVERY 1 DAY DO BEGIN
DELETE FROM
    user_treatment_packages
WHERE
    expiry_date < CURDATE()
    AND remaining_sessions > 0;

END;

/ / DELIMITER;