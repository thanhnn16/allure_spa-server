# -- Xóa database nếu tồn tại
DROP DATABASE IF EXISTS allure_dev;

#
# -- Tạo database mới
CREATE DATABASE allure_dev;

#
# -- Sử dụng database
USE allure_dev;

CREATE TABLE media (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type ENUM('image', 'video') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mediable_type ENUM('product', 'service', 'user', 'notification', 'banner') NOT NULL,
    mediable_id INT UNSIGNED NOT NULL,
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
    media_id INT UNSIGNED,
    loyalty_points INT UNSIGNED DEFAULT 0,
    skin_condition TEXT,
    note TEXT,
    purchase_count INT UNSIGNED DEFAULT 0 COMMENT 'Tổng số lần mua hàng (bao gồm cả sản phẩm và liệu trình)',
    FOREIGN KEY (media_id) REFERENCES media (id),
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
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    brand_description TEXT,
    `usage` TEXT,
    benefits TEXT,
    key_ingredients TEXT,
    ingredients TEXT,
    directions TEXT,
    storage_instructions TEXT,
    product_notes TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES product_categories (id)
);

CREATE INDEX idx_products_category_id ON products (category_id);

CREATE INDEX idx_products_name ON products (name);

-- 5. Bảng product_price_history
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

-- 9. Bảng service_categories
CREATE TABLE service_categories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    service_category_name VARCHAR(255) NOT NULL,
    parent_id INT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES service_categories (id) ON DELETE CASCADE
);

ALTER TABLE
    service_categories
ADD
    INDEX idx_service_categories_parent_id (parent_id);

-- 10. Bảng services
CREATE TABLE services (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT UNSIGNED NULL COMMENT 'Duration in minutes',
    category_id INT UNSIGNED NOT NULL,
    single_price DECIMAL(10, 2) UNSIGNED NOT NULL,
    combo_5_price DECIMAL(10, 2) UNSIGNED,
    combo_10_price DECIMAL(10, 2) UNSIGNED,
    validity_period INT UNSIGNED COMMENT 'Thời hạn gói tính bằng ngày',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES service_categories (id)
);


CREATE INDEX idx_services_name ON services (service_name);

CREATE INDEX idx_services_category_id ON services (category_id);

-- 11. Bảng service_price_history
CREATE TABLE service_price_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    service_id INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    effective_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    effective_to TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE
);

CREATE INDEX idx_service_price_history_service_id_effective_from ON service_price_history (service_id, effective_from);


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
    user_id CHAR(36) NULL,
    address TEXT NOT NULL,
    address_type ENUM ('home', 'work', 'shipping', 'others') NOT NULL DEFAULT 'home',
    is_temporary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX idx_addresses_user_id ON addresses (user_id);

CREATE INDEX idx_users_phone_number ON users (phone_number);

CREATE INDEX idx_users_role ON users (role);



-- 29. Bảng payment_methods
CREATE TABLE payment_methods (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
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
    min_order_value DECIMAL(10, 2) UNSIGNED,
    max_discount_amount DECIMAL(10, 2) UNSIGNED,
    usage_limit INT UNSIGNED,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);
    
-- 30. Bảng orders
CREATE TABLE orders (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    total_amount DECIMAL(10, 2) UNSIGNED NOT NULL,
    shipping_address_id INT UNSIGNED NULL,
    payment_method_id INT UNSIGNED NOT NULL,
    voucher_id INT UNSIGNED,
    discount_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_address_id) REFERENCES addresses (id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods (id) ON DELETE CASCADE,
        FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE
    SET
        NULL
);

-- 31. Bảng order_items
CREATE TABLE order_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    item_type ENUM ('product', 'service') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    service_type ENUM('single', 'combo_5', 'combo_10') NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    discount_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    discount_type ENUM('percentage', 'fixed_amount'),
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
);

CREATE INDEX idx_order_items_item_type_item_id ON order_items (item_type, item_id);

-- 17. Bảng invoices
CREATE TABLE invoices (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    staff_user_id CHAR(36),
    total_amount DECIMAL(10, 2) UNSIGNED NOT NULL,
    paid_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    remaining_amount DECIMAL(10, 2) UNSIGNED GENERATED ALWAYS AS (total_amount - paid_amount) STORED,
    status ENUM ('pending', 'partial', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    order_id INT UNSIGNED,
    note TEXT,
    created_by_user_id CHAR(36),
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (staff_user_id) REFERENCES users (id),
    FOREIGN KEY (created_by_user_id) REFERENCES users (id),
    FOREIGN KEY (order_id) REFERENCES orders (id),
    CONSTRAINT chk_paid_amount CHECK (paid_amount <= total_amount)
);

CREATE INDEX idx_invoices_user_id ON invoices (user_id);

CREATE INDEX idx_invoices_staff_user_id ON invoices (staff_user_id);

CREATE INDEX idx_invoices_order_id ON invoices (order_id);

CREATE INDEX idx_invoices_created_at ON invoices (created_at);

CREATE INDEX idx_invoices_status ON invoices (status);


-- 16. Bảng user_service_packages
CREATE TABLE user_service_packages (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    total_sessions INT UNSIGNED NOT NULL,
    used_sessions INT UNSIGNED NOT NULL DEFAULT 0,
    remaining_sessions INT UNSIGNED GENERATED ALWAYS AS (total_sessions - used_sessions) STORED,
    expiry_date DATE,
    is_combo BOOLEAN NOT NULL DEFAULT FALSE,
    combo_type ENUM ('5_times', '10_times') NULL,
    order_id INT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (service_id) REFERENCES services (id),
    FOREIGN KEY (order_id) REFERENCES orders (id),
    CONSTRAINT chk_sessions CHECK (used_sessions <= total_sessions)
);



-- 28. Bảng employee_attendances
CREATE TABLE employee_attendances (
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

CREATE INDEX idx_employee_attendances_user_id ON employee_attendances (user_id);

CREATE INDEX idx_employee_attendances_date ON employee_attendances (date);


-- 18. Bảng service_usage_histories
CREATE TABLE service_usage_histories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_service_package_id INT UNSIGNED,
    staff_user_id CHAR(36),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    result TEXT,
    notes TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_service_package_id) REFERENCES user_service_packages (id) ON DELETE
    SET
        NULL,
    FOREIGN KEY (staff_user_id) REFERENCES users (id) ON DELETE
    SET
        NULL
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
    item_type ENUM ('product', 'service') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE
);

-- 34. Bảng ratings
CREATE TABLE ratings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    rating_type ENUM('service', 'product') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    stars TINYINT UNSIGNED NOT NULL,
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (item_id) REFERENCES services (id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT chk_stars CHECK (
        stars BETWEEN 1
        AND 5
    )
);

CREATE INDEX idx_ratings_user_id ON ratings (user_id);

CREATE INDEX idx_ratings_item_id ON ratings (item_id);

-- 35. Bảng favorites
CREATE TABLE favorites (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    favorite_type ENUM ('product', 'service') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES services (id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES products (id) ON DELETE CASCADE
);

-- 36. Bảng notifications
CREATE TABLE notifications (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    media_id INT UNSIGNED,
    content TEXT NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'unseen',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (media_id) REFERENCES media (id)
);

-- 37. Bảng appointments
CREATE TABLE appointments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    staff_user_id CHAR(36),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
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
    FOREIGN KEY (service_id) REFERENCES services (id),
    FOREIGN KEY (staff_user_id) REFERENCES users (id)
);

CREATE INDEX idx_appointments_user_id ON appointments (user_id);
CREATE INDEX idx_appointments_service_id ON appointments (service_id);
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
    item_type ENUM ('product', 'service') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    points_required INT UNSIGNED NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (item_id) REFERENCES services (id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES products (id) ON DELETE CASCADE
);

-- 45. Bảng point_redemption_histories
CREATE TABLE point_redemption_histories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    reward_item_id INT UNSIGNED NOT NULL,
    points_used INT UNSIGNED NOT NULL,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (reward_item_id) REFERENCES reward_items (id) ON DELETE CASCADE
);

CREATE TABLE translations (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    translatable_type VARCHAR(255) NOT NULL,
    translatable_id INT UNSIGNED NOT NULL,
    language CHAR(2) NOT NULL DEFAULT 'vi',
    field VARCHAR(255) NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE INDEX idx_translations_translatable ON translations (translatable_type, translatable_id, language);

-- 53. Bảng banners
CREATE TABLE banners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    media_id INT UNSIGNED NOT NULL,
    link VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    `order` INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (media_id) REFERENCES media (id)
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
