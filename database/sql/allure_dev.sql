# -- Xóa database nếu tồn tại
DROP DATABASE IF EXISTS allure_dev;
#
# -- Tạo database mới
CREATE DATABASE allure_dev;
#
# -- Sử dụng database
USE allure_dev;
-- 1. Bảng images
CREATE TABLE images (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- 1.1 Bảng videos
CREATE TABLE videos (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    video_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- 2. Bảng users
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    phone_number VARCHAR(255) NULL UNIQUE,
    email VARCHAR(255) NULL UNIQUE,
    password TEXT NULL,
    role ENUM ('user', 'admin', 'staff') NOT NULL DEFAULT 'user',
    remember_token VARCHAR(100),
    full_name VARCHAR(255),
    gender ENUM ('male', 'female', 'other') DEFAULT 'other',
    date_of_birth TIMESTAMP,
    image_id INT UNSIGNED,
    loyalty_points INT UNSIGNED DEFAULT 0,
    skin_condition TEXT,
    note TEXT,
    purchase_count INT UNSIGNED DEFAULT 0 COMMENT 'Tổng số lần mua hàng (bao gồm cả sản phẩm và liệu trình)',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- 3. Bảng product_categories
CREATE TABLE product_categories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL,
    parent_id INT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES product_categories (id) ON DELETE CASCADE
);

ALTER TABLE
    product_categories
ADD
    INDEX idx_product_categories_parent_id (parent_id);

-- 4. Bảng products
CREATE TABLE products (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    image_id INT UNSIGNED,
    video_id INT UNSIGNED,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES product_categories (id),
    FOREIGN KEY (image_id) REFERENCES images (id),
    FOREIGN KEY (video_id) REFERENCES videos (id)
);

CREATE INDEX idx_products_category_id ON products (category_id);

CREATE INDEX idx_products_name ON products (name);

-- 5. Bảng product_details
CREATE TABLE product_details (
    product_id INT UNSIGNED PRIMARY KEY,
    brand_description TEXT,
    `usage` TEXT,
    benefits TEXT,
    key_ingredients TEXT,
    ingredients TEXT,
    directions TEXT,
    storage_instructions TEXT,
    product_notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

-- 6. Bảng product_price_history
CREATE TABLE product_price_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    effective_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    effective_to TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

CREATE INDEX idx_product_price_history_product_id_effective_from ON product_price_history (product_id, effective_from);

CREATE INDEX idx_products_product_name ON products (name);

-- 7. Bảng attributes
CREATE TABLE attributes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    attribute_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- 8. Bảng product_attributes
CREATE TABLE product_attributes (
    product_id INT UNSIGNED NOT NULL,
    attribute_id INT UNSIGNED NOT NULL,
    attribute_value VARCHAR(255),
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (product_id, attribute_id),
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE
);

-- 9. Bảng treatment_categories
CREATE TABLE treatment_categories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL,
    parent_id INT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES treatment_categories (id) ON DELETE CASCADE
);

ALTER TABLE
    treatment_categories
ADD
    INDEX idx_treatment_categories_parent_id (parent_id);

-- 10. Bảng treatments
CREATE TABLE treatments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    description TEXT,
    duration INT UNSIGNED NULL COMMENT 'Duration in minutes',
    image_id INT UNSIGNED,
    video_id INT UNSIGNED,
    category_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES treatment_categories (id),
    FOREIGN KEY (image_id) REFERENCES images (id),
    FOREIGN KEY (video_id) REFERENCES videos (id)
);

CREATE INDEX idx_treatments_name ON treatments (name);

CREATE INDEX idx_treatments_category_id ON treatments (category_id);

-- 11. Bảng treatment_price_history
CREATE TABLE treatment_price_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    treatment_id INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    effective_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    effective_to TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE
);

CREATE INDEX idx_treatment_price_history_treatment_id_effective_from ON treatment_price_history (treatment_id, effective_from);

-- 12. Bảng treatment_combos
CREATE TABLE treatment_combos (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    treatment_id INT UNSIGNED NOT NULL,
    combo_type ENUM ('5_times', '10_times') NOT NULL,
    combo_price DECIMAL(10, 2) UNSIGNED NOT NULL,
    validity_period INT UNSIGNED COMMENT 'Thời hạn gói tính bằng ngày',
    FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE
);

CREATE INDEX idx_treatment_combos_treatment_id ON treatment_combos (treatment_id);

-- 13. Bảng treatment_combo_price_history
CREATE TABLE treatment_combo_price_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    treatment_combo_id INT UNSIGNED NOT NULL,
    combo_price DECIMAL(10, 2) UNSIGNED NOT NULL,
    effective_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    effective_to TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (treatment_combo_id) REFERENCES treatment_combos (id) ON DELETE CASCADE
);

-- 14. Bảng staff_details
CREATE TABLE staff_details (
    user_id CHAR(36) PRIMARY KEY,
    position VARCHAR(100),
    hire_date DATE,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX idx_staff_details_hire_date ON staff_details (hire_date);

-- 15. Bảng addresses
CREATE TABLE addresses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    address TEXT NOT NULL,
    address_type ENUM ('home', 'work', 'others') NOT NULL DEFAULT 'home',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX idx_addresses_user_id ON addresses (user_id);

CREATE INDEX idx_users_phone_number ON users (phone_number);

CREATE INDEX idx_users_role ON users (role);

-- 16. Bảng user_treatment_packages
CREATE TABLE user_treatment_packages (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    treatment_id INT UNSIGNED NOT NULL,
    total_sessions INT UNSIGNED NOT NULL,
    used_sessions INT UNSIGNED NOT NULL DEFAULT 0,
    remaining_sessions INT UNSIGNED GENERATED ALWAYS AS (total_sessions - used_sessions) STORED,
    expiry_date DATE,
    is_combo BOOLEAN NOT NULL DEFAULT FALSE,
    combo_type ENUM ('5_times', '10_times') NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (treatment_id) REFERENCES treatments (id),
    CONSTRAINT chk_sessions CHECK (used_sessions <= total_sessions)
);

-- 17. Bảng invoices
CREATE TABLE invoices (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    staff_user_id CHAR(36),
    total_amount DECIMAL(10, 2) UNSIGNED NOT NULL,
    paid_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    remaining_amount DECIMAL(10, 2) UNSIGNED GENERATED ALWAYS AS (total_amount - paid_amount) STORED,
    status ENUM ('pending', 'partial', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50),
    note TEXT,
    created_by_user_id CHAR(36),
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (staff_user_id) REFERENCES users (id),
    FOREIGN KEY (created_by_user_id) REFERENCES users (id),
    CONSTRAINT chk_paid_amount CHECK (paid_amount <= total_amount)
);

CREATE INDEX idx_invoices_user_id ON invoices (user_id);

CREATE INDEX idx_invoices_staff_user_id ON invoices (staff_user_id);

CREATE INDEX idx_invoices_created_at ON invoices (created_at);

CREATE INDEX idx_invoices_status ON invoices (status);

-- 18. Bảng treatment_usage_history
CREATE TABLE treatment_usage_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_treatment_package_id INT UNSIGNED,
    user_id CHAR(36) NOT NULL,
    treatment_id INT UNSIGNED NOT NULL,
    staff_user_id CHAR(36),
    treatment_date TIMESTAMP NOT NULL,
    invoice_id CHAR(36),
    result TEXT,
    notes TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_treatment_package_id) REFERENCES user_treatment_packages (id) ON DELETE
    SET
        NULL,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE,
        FOREIGN KEY (staff_user_id) REFERENCES users (id) ON DELETE
    SET
        NULL,
        FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE
    SET
        NULL
);

-- 19. Bảng password_reset_tokens
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255),
    created_at TIMESTAMP NULL DEFAULT NULL
);

-- 20. Bảng sessions
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id CHAR(36),
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT,
    last_activity INT,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- 21. Bảng personal_access_tokens
CREATE TABLE personal_access_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tokenable_id CHAR(36),
    tokenable_type VARCHAR(255),
    name VARCHAR(255),
    token VARCHAR(64) UNIQUE,
    abilities TEXT,
    last_used_at DATETIME,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_personal_access_tokens_tokenable_id ON personal_access_tokens (tokenable_id);

CREATE INDEX idx_personal_access_tokens_tokenable_type ON personal_access_tokens (tokenable_type);

-- 22. Bảng cache
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    `value` MEDIUMTEXT,
    expiration INT
);

-- 23. Bảng cache_locks
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255),
    expiration INT
);

-- 24. Bảng jobs
CREATE TABLE jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL
);

-- 25. Bảng job_batches
CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255),
    total_jobs INT UNSIGNED,
    pending_jobs INT UNSIGNED,
    failed_jobs INT UNSIGNED,
    failed_job_ids MEDIUMTEXT,
    options MEDIUMTEXT,
    cancelled_at INT UNSIGNED,
    created_at INT UNSIGNED,
    finished_at INT UNSIGNED
);

-- 26. Bảng failed_jobs
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(255) UNIQUE,
    connection TEXT,
    queue TEXT,
    payload LONGTEXT,
    exception LONGTEXT,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 27. Bảng vouchers
CREATE TABLE vouchers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    discount_type VARCHAR(255) NOT NULL DEFAULT 'percentage',
    is_unlimited TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(255) NOT NULL DEFAULT 'active',
    used_times INT UNSIGNED NOT NULL DEFAULT 0,
    discount_value DECIMAL(10, 2) UNSIGNED NOT NULL,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- 28. Bảng employee_attendance
CREATE TABLE employee_attendance (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    check_in TIMESTAMP NOT NULL,
    check_out TIMESTAMP,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX idx_employee_attendance_user_id ON employee_attendance (user_id);

CREATE INDEX idx_employee_attendance_date ON employee_attendance (date);

-- 29. Bảng payment_methods
CREATE TABLE payment_methods (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- 30. Bảng orders
CREATE TABLE orders (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    total_amount DECIMAL(10, 2) UNSIGNED NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- 31. Bảng order_items
CREATE TABLE order_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    item_type_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    discount_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
);

-- 32. Bảng carts
CREATE TABLE carts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- 33. Bảng cart_items
CREATE TABLE cart_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cart_id INT UNSIGNED NOT NULL,
    item_type_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE
);

-- 34. Bảng invoice_payments
CREATE TABLE invoice_payments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id CHAR(36) NOT NULL,
    amount DECIMAL(10, 2) UNSIGNED NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note TEXT,
    created_by_user_id CHAR(36),
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices (id),
    FOREIGN KEY (created_by_user_id) REFERENCES users (id)
);

CREATE INDEX idx_invoice_payments_invoice_id ON invoice_payments (invoice_id);

CREATE INDEX idx_invoice_payments_payment_date ON invoice_payments (payment_date);

-- 35. Bảng ratings
CREATE TABLE ratings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    rating_type ENUM('treatment', 'product') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    stars TINYINT UNSIGNED NOT NULL,
    comment TEXT,
    image_id INT UNSIGNED,
    video_id INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (item_id) REFERENCES treatments (id),
    FOREIGN KEY (item_id) REFERENCES products (id),
    FOREIGN KEY (image_id) REFERENCES images (id),
    FOREIGN KEY (video_id) REFERENCES videos (id),
    CONSTRAINT chk_stars CHECK (
        stars BETWEEN 1
        AND 5
    )
);

CREATE INDEX idx_ratings_user_id ON ratings (user_id);

CREATE INDEX idx_ratings_item_id ON ratings (item_id);

-- 36. Bảng favorites
CREATE TABLE favorites (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    favorite_type ENUM ('product', 'treatment') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- 37. Bảng notifications
CREATE TABLE notifications (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    image_id INT UNSIGNED,
    content TEXT NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'unseen',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- 38. Bảng appointments
CREATE TABLE appointments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    treatment_id INT UNSIGNED NOT NULL,
    staff_user_id CHAR(36),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    actual_start_time TIMESTAMP NULL,
    actual_end_time TIMESTAMP NULL,
    appointment_type ENUM (
        'facial',
        'massage',
        'weight_loss',
        'hair_removal',
        'consultation',
        'others'
    ) NOT NULL DEFAULT 'others',
    status ENUM ('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    note TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (treatment_id) REFERENCES treatments (id),
    FOREIGN KEY (staff_user_id) REFERENCES users (id)
);

CREATE INDEX idx_appointments_appointment_type ON appointments (appointment_type);

CREATE INDEX idx_appointments_user_id ON appointments (user_id);

CREATE INDEX idx_appointments_treatment_id ON appointments (treatment_id);

CREATE INDEX idx_appointments_staff_user_id ON appointments (staff_user_id);

CREATE INDEX idx_appointments_start_time ON appointments (start_time);

CREATE INDEX idx_appointments_status ON appointments (status);

-- 39. Bảng user_vouchers
CREATE TABLE user_vouchers (
    user_id CHAR(36) NOT NULL,
    voucher_id INT UNSIGNED NOT NULL,
    used_at TIMESTAMP,
    is_used TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (user_id, voucher_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE CASCADE
);

-- 40. Bảng histories
CREATE TABLE histories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    -- Cập nhật kiểu dữ liệu user_id
    table_name VARCHAR(255) NOT NULL,
    row_id INT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    changes JSON,
    action_timestamp TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- 41. Bảng fcm_tokens
CREATE TABLE fcm_tokens (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    -- Cập nhật kiểu dữ liệu user_id
    fcm_token TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- 42. Bảng stock_movements
CREATE TABLE stock_movements (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    type ENUM ('in', 'out') NOT NULL,
    stock_after_movement INT UNSIGNED NOT NULL,
    note TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id)
);

CREATE INDEX idx_stock_movements_product_id ON stock_movements (product_id);

CREATE INDEX idx_stock_movements_created_at ON stock_movements (created_at);

-- 43. Bảng payment_histories
CREATE TABLE payment_histories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id CHAR(36) NOT NULL,
    old_payment_status VARCHAR(255) NOT NULL,
    new_payment_status VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE
);

-- 44. Bảng reward_items
CREATE TABLE reward_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    item_type ENUM ('product', 'treatment_combo') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    points_required INT UNSIGNED NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

-- 45. Bảng point_redemption_history
CREATE TABLE point_redemption_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    reward_item_id INT UNSIGNED NOT NULL,
    points_used INT UNSIGNED NOT NULL,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (reward_item_id) REFERENCES reward_items (id) ON DELETE CASCADE
);

-- 46. Bảng product_translations
CREATE TABLE product_translations (
    product_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    PRIMARY KEY (product_id, language),
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

CREATE INDEX idx_product_translations_language ON product_translations (language);

-- 47. Bảng treatment_translations
CREATE TABLE treatment_translations (
    treatment_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    treatment_name VARCHAR(255) NOT NULL,
    description TEXT,
    PRIMARY KEY (treatment_id, language),
    FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE
);

CREATE INDEX idx_treatment_translations_language ON treatment_translations (language);

-- 48. Bảng product_category_translations
CREATE TABLE product_category_translations (
    category_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (category_id, language),
    FOREIGN KEY (category_id) REFERENCES product_categories (id) ON DELETE CASCADE
);

CREATE INDEX idx_product_category_translations_language ON product_category_translations (language);

-- 49. Bảng attribute_translations
CREATE TABLE attribute_translations (
    attribute_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (attribute_id, language),
    FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE
);

CREATE INDEX idx_attribute_translations_language ON attribute_translations (language);

-- 50. Bảng treatment_category_translations
CREATE TABLE treatment_category_translations (
    category_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (category_id, language),
    FOREIGN KEY (category_id) REFERENCES treatment_categories (id) ON DELETE CASCADE
);

CREATE INDEX idx_treatment_category_translations_language ON treatment_category_translations (language);

-- 51. Bảng voucher_translations
CREATE TABLE voucher_translations (
    voucher_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    description TEXT,
    PRIMARY KEY (voucher_id, language),
    FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE CASCADE
);

CREATE INDEX idx_voucher_translations_language ON voucher_translations (language);

-- 52. Bảng reward_item_translations
CREATE TABLE reward_item_translations (
    reward_item_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    PRIMARY KEY (reward_item_id, language),
    FOREIGN KEY (reward_item_id) REFERENCES reward_items (id) ON DELETE CASCADE
);

CREATE INDEX idx_reward_item_translations_language ON reward_item_translations (language);

-- 53. Bảng banners
CREATE TABLE banners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    `order` INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 54. Bảng ai_chat_configs
CREATE TABLE ai_chat_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ai_name VARCHAR(255) NOT NULL,
    context TEXT NOT NULL,
    language VARCHAR(50) NOT NULL,
    gemini_settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 55. Bảng chats
CREATE TABLE chats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    staff_id CHAR(36),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users (id) ON DELETE
    SET
        NULL
);

-- 56. Bảng chat_messages
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT UNSIGNED NOT NULL,
    sender_id CHAR(36) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats (id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE
);