# -- Xóa database nếu tồn tại
DROP DATABASE IF EXISTS allure_dev;

# -- Tạo database mới
CREATE DATABASE allure_dev;


-- Sử dụng database
USE allure_dev;

--
-- Bảng product_categories
--
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

--
-- Bảng products
--
CREATE TABLE products (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_line ENUM ('Celbest', 'Faith', 'Other') NOT NULL DEFAULT 'Other',
    brand_description TEXT,
    description TEXT,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    volume VARCHAR(50) default 'N/A',
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    image_id INT UNSIGNED,
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
    FOREIGN KEY (category_id) REFERENCES product_categories (id) ON DELETE CASCADE,
    CONSTRAINT chk_product_price CHECK (price >= 0)
);


CREATE INDEX idx_products_category_id ON products (category_id);

CREATE INDEX idx_products_product_name ON products (product_name);

--
-- Bảng attributes
--
CREATE TABLE attributes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    attribute_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

--
-- Bảng product_attributes
--
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

--
-- Bảng treatment_categories
--
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

--
-- Bảng treatments
--
CREATE TABLE treatments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    treatment_name VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT UNSIGNED NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    image_id INT UNSIGNED,
    combo_type ENUM ('single', '5_times', '10_times') NOT NULL DEFAULT 'single',
    combo_price DECIMAL(10, 2) UNSIGNED NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    validity_period INT UNSIGNED COMMENT 'Thời hạn gói tính bằng ngày',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES treatment_categories (id) ON DELETE CASCADE
);

ALTER TABLE
    treatments
ADD
    INDEX idx_treatments_category_id (category_id);

--
-- Bảng users
--
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
    purchase_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

--
-- Bảng addresses
--
CREATE TABLE addresses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    address TEXT NOT NULL,
    address_type ENUM('home', 'work', 'others') NOT NULL DEFAULT 'home',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX idx_addresses_user_id ON addresses (user_id);

CREATE INDEX idx_users_email ON users (email);

CREATE INDEX idx_users_phone_number ON users (phone_number);

-- Bảng user_treatment_packages
CREATE TABLE user_treatment_packages (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    treatment_id INT UNSIGNED NOT NULL,
    combo_type ENUM ('single', '5_times', '10_times') NOT NULL,
    purchase_date TIMESTAMP NOT NULL,
    total_sessions INT UNSIGNED NOT NULL,
    remaining_sessions INT UNSIGNED NOT NULL,
    expiry_date TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE
);

CREATE INDEX idx_user_treatment_packages_user_id ON user_treatment_packages (user_id);


--
-- Bảng password_reset_tokens
--
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255),
    created_at TIMESTAMP NULL DEFAULT NULL
);

--
-- Bảng sessions
--
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id CHAR(36),
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT,
    last_activity INT,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng personal_access_tokens
--
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

--
-- Bảng cache
--
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    `value` MEDIUMTEXT,
    expiration INT
);

--
-- Bảng cache_locks
--
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255),
    expiration INT
);

--
-- Bảng jobs
--
CREATE TABLE jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL
);

--
-- Bảng job_batches
--
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

--
-- Bảng failed_jobs
--
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(255) UNIQUE,
    connection TEXT,
    queue TEXT,
    payload LONGTEXT,
    exception LONGTEXT,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Bảng vouchers
--
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

--
-- Bảng staffs
--
CREATE TABLE staffs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_name VARCHAR(255) NOT NULL,
    position VARCHAR(100),
    hire_date DATE,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Bảng treatment_usage_history (mới)
CREATE TABLE treatment_usage_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_treatment_package_id INT UNSIGNED NOT NULL,
    treatment_date TIMESTAMP NOT NULL,
    staff_id INT UNSIGNED,
    notes TEXT,
    session_result TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_treatment_package_id) REFERENCES user_treatment_packages (id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE
    SET
        NULL
);

CREATE INDEX idx_treatment_usage_history_user_treatment_package_id ON treatment_usage_history (user_treatment_package_id);

CREATE INDEX idx_treatment_usage_history_treatment_date ON treatment_usage_history (treatment_date);

-- Bảng employee_attendance (mới)
CREATE TABLE employee_attendance (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_id INT UNSIGNED NOT NULL,
    check_in TIMESTAMP NOT NULL,
    check_out TIMESTAMP,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE CASCADE
);

CREATE INDEX idx_employee_attendance_staff_id ON employee_attendance (staff_id);

CREATE INDEX idx_employee_attendance_date ON employee_attendance (date);

--
-- Bảng payment_methods
--
CREATE TABLE payment_methods (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

--
-- Bảng cart_item_types
--
CREATE TABLE cart_item_types (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

--
-- Bảng order_items
--
CREATE TABLE order_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    item_type_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    discount_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (item_type_id) REFERENCES cart_item_types (id) ON DELETE CASCADE
);

--
-- Bảng carts
--
CREATE TABLE carts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    -- Cập nhật kiểu dữ liệu user_id
    order_item_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE
);

--
-- Bảng invoices
--
CREATE TABLE invoices (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    voucher_id INT UNSIGNED,
    staff_id INT UNSIGNED,
    payment_method_id INT UNSIGNED NOT NULL,
    order_item_id INT UNSIGNED NOT NULL,
    created_by INT UNSIGNED,
    invoice_number VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    discount_amount DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    payment_status VARCHAR(255) NOT NULL DEFAULT 'unpaid',
    note VARCHAR(255),
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE
    SET
        NULL,
        FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE
    SET
        NULL,
        FOREIGN KEY (payment_method_id) REFERENCES payment_methods (id) ON DELETE RESTRICT,
        FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES staffs (id) ON DELETE
    SET
        NULL
);

CREATE INDEX idx_invoices_user_id ON invoices (user_id);

CREATE INDEX idx_invoices_created_by ON invoices (created_by);

CREATE INDEX idx_invoices_payment_status ON invoices (payment_status);

--
-- Bảng ratings
--
CREATE TABLE ratings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rating_type ENUM('product', 'treatment', 'staff') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    comment TEXT,
    image_id INT UNSIGNED,
    stars INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng favorites
--
CREATE TABLE favorites (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    favorite_type ENUM('product', 'treatment') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng notifications
--
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

--
-- Bảng appointment_types
--
CREATE TABLE appointment_types (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

--
-- Bảng appointments
--
CREATE TABLE appointments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    -- Cập nhật kiểu dữ liệu user_id
    appointment_type_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED,
    order_item_id INT UNSIGNED NOT NULL,
    -- Thêm khóa ngoại đến order_items
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    is_all_day TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    note TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE
    SET
        NULL,
        FOREIGN KEY (appointment_type_id) REFERENCES appointment_types (id) ON DELETE RESTRICT,
        FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE -- Thêm ràng buộc khóa ngoại
);

--
-- Bảng user_vouchers
--
CREATE TABLE user_vouchers (
    user_id CHAR(36) NOT NULL,
    -- Cập nhật kiểu dữ liệu user_id
    voucher_id INT UNSIGNED NOT NULL,
    used_at TIMESTAMP,
    is_used TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (user_id, voucher_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE CASCADE
);

--
-- Bảng histories
--
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

--
-- Bảng fcm_tokens
--
CREATE TABLE fcm_tokens (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    -- Cập nhật kiểu dữ liệu user_id
    fcm_token TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng stock_movements
--
CREATE TABLE stock_movements (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    movement_type ENUM ('import', 'export') NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10, 2) UNSIGNED NOT NULL,
    note TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

--
-- Bảng customer_treatment_histories
--
CREATE TABLE customer_treatment_histories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    -- Cập nhật kiểu dữ liệu user_id
    treatment_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED,
    treatment_date TIMESTAMP NOT NULL,
    result TEXT,
    note TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE
    SET
        NULL
);

--
-- Bảng payment_histories (Lịch sử thanh toán)
--
CREATE TABLE payment_histories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT UNSIGNED NOT NULL,
    old_payment_status VARCHAR(255) NOT NULL,
    new_payment_status VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE
);

--
-- Bảng images
--
CREATE TABLE images (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE reward_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    item_type ENUM('product', 'treatment_combo') NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    points_required INT UNSIGNED NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE point_redemption_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id CHAR(36) NOT NULL,
    reward_item_id INT UNSIGNED NOT NULL,
    points_used INT UNSIGNED NOT NULL,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_item_id) REFERENCES reward_items(id) ON DELETE CASCADE
);

-- Tạo bảng dịch cho sản phẩm
CREATE TABLE product_translations (
    product_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    PRIMARY KEY (product_id, language),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE INDEX idx_product_translations_language ON product_translations(language);

-- Tạo bảng dịch cho liệu trình
CREATE TABLE treatment_translations (
    treatment_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    treatment_name VARCHAR(255) NOT NULL,
    description TEXT,
    PRIMARY KEY (treatment_id, language),
    FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE
);

CREATE INDEX idx_treatment_translations_language ON treatment_translations(language);

-- Bảng dịch cho danh mục sản phẩm
CREATE TABLE product_category_translations (
    category_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (category_id, language),
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
);

CREATE INDEX idx_product_category_translations_language ON product_category_translations(language);

-- Bảng dịch cho thuộc tính
CREATE TABLE attribute_translations (
    attribute_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (attribute_id, language),
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
);

CREATE INDEX idx_attribute_translations_language ON attribute_translations(language);

-- Bảng dịch cho danh mục liệu trình
CREATE TABLE treatment_category_translations (
    category_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (category_id, language),
    FOREIGN KEY (category_id) REFERENCES treatment_categories(id) ON DELETE CASCADE
);

CREATE INDEX idx_treatment_category_translations_language ON treatment_category_translations(language);

-- Bảng dịch cho phiếu giảm giá
CREATE TABLE voucher_translations (
    voucher_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    description TEXT,
    PRIMARY KEY (voucher_id, language),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE
);

CREATE INDEX idx_voucher_translations_language ON voucher_translations(language);

-- Bảng dịch cho loại cuộc hẹn
CREATE TABLE appointment_type_translations (
    appointment_type_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    type_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (appointment_type_id, language),
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id) ON DELETE CASCADE
);

CREATE INDEX idx_appointment_type_translations_language ON appointment_type_translations(language);

-- Bảng dịch cho phần thưởng
CREATE TABLE reward_item_translations (
    reward_item_id INT UNSIGNED NOT NULL,
    language CHAR(2) DEFAULT 'vi' NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    PRIMARY KEY (reward_item_id, language),
    FOREIGN KEY (reward_item_id) REFERENCES reward_items(id) ON DELETE CASCADE
);

CREATE INDEX idx_reward_item_translations_language ON reward_item_translations(language);