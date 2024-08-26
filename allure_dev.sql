-- Xóa database nếu tồn tại
DROP DATABASE IF EXISTS allure_dev;

-- Tạo database mới
CREATE DATABASE allure_dev;

-- Sử dụng database
USE allure_dev;

--
-- Bảng brands
--
CREATE TABLE brands
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    brand_name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP    NULL DEFAULT NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng product_categories
--
CREATE TABLE product_categories
(
    id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL,
    parent_id     INT UNSIGNED,
    created_at    TIMESTAMP    NULL DEFAULT NULL,
    updated_at    TIMESTAMP    NULL DEFAULT NULL,
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES product_categories (id) ON DELETE CASCADE
);

ALTER TABLE product_categories
    ADD INDEX idx_product_categories_parent_id (parent_id);

--
-- Bảng products
--
CREATE TABLE products
(
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id    INT UNSIGNED              NOT NULL,
    brand_id       INT UNSIGNED              NOT NULL,
    product_name   VARCHAR(255)              NOT NULL,
    product_line   ENUM ('Celbest', 'Faith') NOT NULL, -- Thêm cột product_line
    description    TEXT,
    price          DECIMAL(10, 2) UNSIGNED   NOT NULL,
    stock_quantity INT UNSIGNED              NOT NULL DEFAULT 0,
    image_id       INT UNSIGNED,                       -- Thay đổi image_path thành image_id
    created_at     TIMESTAMP                 NULL     DEFAULT NULL,
    updated_at     TIMESTAMP                 NULL     DEFAULT NULL,
    deleted_at     TIMESTAMP                 NULL     DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES product_categories (id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE
);

ALTER TABLE products
    ADD INDEX idx_products_category_id (category_id),
    ADD INDEX idx_products_brand_id (brand_id);

--
-- Bảng product_details
--
CREATE TABLE product_details
(
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id  INT UNSIGNED NOT NULL UNIQUE,
    `usage`     TEXT, -- Công dụng
    ingredients TEXT, -- Thành phần
    directions  TEXT, -- Cách sử dụng
    created_at  TIMESTAMP    NULL DEFAULT NULL,
    updated_at  TIMESTAMP    NULL DEFAULT NULL,
    deleted_at  TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

--
-- Bảng attributes
--
CREATE TABLE attributes
(
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    attribute_name VARCHAR(255) NOT NULL,
    created_at     TIMESTAMP    NULL DEFAULT NULL,
    updated_at     TIMESTAMP    NULL DEFAULT NULL,
    deleted_at     TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng product_attributes
--
CREATE TABLE product_attributes
(
    product_id      INT UNSIGNED NOT NULL,
    attribute_id    INT UNSIGNED NOT NULL,
    attribute_value VARCHAR(255),
    created_at      TIMESTAMP    NULL DEFAULT NULL,
    updated_at      TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (product_id, attribute_id),
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE
);

--
-- Bảng treatment_categories
--
CREATE TABLE treatment_categories
(
    id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL,
    parent_id     INT UNSIGNED,
    created_at    TIMESTAMP    NULL DEFAULT NULL,
    updated_at    TIMESTAMP    NULL DEFAULT NULL,
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES treatment_categories (id) ON DELETE CASCADE
);

ALTER TABLE treatment_categories
    ADD INDEX idx_treatment_categories_parent_id (parent_id);

--
-- Bảng treatments
--
CREATE TABLE treatments
(
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id    INT UNSIGNED            NOT NULL,
    treatment_name VARCHAR(255)            NOT NULL,
    description    TEXT,
    duration       INT UNSIGNED            NULL,
    price          DECIMAL(10, 2) UNSIGNED NOT NULL,
    image_id       INT UNSIGNED, -- Thay đổi image_path thành image_id
    created_at     TIMESTAMP               NULL DEFAULT NULL,
    updated_at     TIMESTAMP               NULL DEFAULT NULL,
    deleted_at     TIMESTAMP               NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES treatment_categories (id) ON DELETE CASCADE
);

ALTER TABLE treatments
    ADD INDEX idx_treatments_category_id (category_id);

--
-- Bảng treatment_combos
--
CREATE TABLE treatment_combos
(
    id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    treatment_id INT UNSIGNED                 NOT NULL,
    duration     INT UNSIGNED                 NULL,
    combo_type   ENUM ('5_times', '10_times') NOT NULL,
    combo_price  DECIMAL(10, 2) UNSIGNED      NULL,
    is_default   TINYINT(1)                   NOT NULL DEFAULT 0, -- Thêm cột is_default
    created_at   TIMESTAMP                    NULL     DEFAULT NULL,
    updated_at   TIMESTAMP                    NULL     DEFAULT NULL,
    deleted_at   TIMESTAMP                    NULL     DEFAULT NULL,
    FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE
);

--
-- Bảng users
--
CREATE TABLE users
(
    id             CHAR(36) PRIMARY KEY,                               -- Sử dụng UUID cho users
    phone_number   VARCHAR(255)                    NOT NULL UNIQUE,
    email          VARCHAR(255)                    NOT NULL UNIQUE,
    password       TEXT                            NOT NULL,
    role           ENUM ('user', 'admin', 'staff') NOT NULL DEFAULT 'user',
    remember_token VARCHAR(100),
    full_name      VARCHAR(255),
    gender         VARCHAR(255),
    address        TEXT,
    date_of_birth  TIMESTAMP,
    image_id       INT UNSIGNED,                                       -- Thay đổi image_path thành image_id
    point          INT UNSIGNED                             DEFAULT 0, -- Thêm cột point
    note           TEXT,
    created_at     TIMESTAMP                       NULL     DEFAULT NULL,
    updated_at     TIMESTAMP                       NULL     DEFAULT NULL,
    deleted_at     TIMESTAMP                       NULL     DEFAULT NULL
);

--
-- Bảng password_reset_tokens
--
CREATE TABLE password_reset_tokens
(
    email      VARCHAR(255) PRIMARY KEY,
    token      VARCHAR(255),
    created_at TIMESTAMP NULL DEFAULT NULL
);

--
-- Bảng sessions
--
CREATE TABLE sessions
(
    id            VARCHAR(255) PRIMARY KEY,
    user_id       CHAR(36), -- Cập nhật kiểu dữ liệu user_id
    ip_address    VARCHAR(45),
    user_agent    TEXT,
    payload       LONGTEXT,
    last_activity INT,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng cache
--
CREATE TABLE cache
(
    `key`      VARCHAR(255) PRIMARY KEY,
    `value`    MEDIUMTEXT,
    expiration INT
);

--
-- Bảng cache_locks
--
CREATE TABLE cache_locks
(
    `key`      VARCHAR(255) PRIMARY KEY,
    owner      VARCHAR(255),
    expiration INT
);

--
-- Bảng jobs
--
CREATE TABLE jobs
(
    id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue        VARCHAR(255)     NOT NULL,
    payload      LONGTEXT         NOT NULL,
    attempts     TINYINT UNSIGNED NOT NULL,
    reserved_at  INT UNSIGNED,
    available_at INT UNSIGNED     NOT NULL,
    created_at   INT UNSIGNED     NOT NULL
);

--
-- Bảng job_batches
--
CREATE TABLE job_batches
(
    id             VARCHAR(255) PRIMARY KEY,
    name           VARCHAR(255),
    total_jobs     INT UNSIGNED,
    pending_jobs   INT UNSIGNED,
    failed_jobs    INT UNSIGNED,
    failed_job_ids MEDIUMTEXT,
    options        MEDIUMTEXT,
    cancelled_at   INT UNSIGNED,
    created_at     INT UNSIGNED,
    finished_at    INT UNSIGNED
);

--
-- Bảng failed_jobs
--
CREATE TABLE failed_jobs
(
    id         BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid       VARCHAR(255) UNIQUE,
    connection TEXT,
    queue      TEXT,
    payload    LONGTEXT,
    exception  LONGTEXT,
    failed_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Bảng skin_conditions
--
CREATE TABLE skin_conditions
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP    NULL DEFAULT NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng user_skin_conditions
--
CREATE TABLE user_skin_conditions
(
    user_id           CHAR(36)     NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    skin_condition_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, skin_condition_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (skin_condition_id) REFERENCES skin_conditions (id) ON DELETE CASCADE
);

--
-- Bảng vouchers
--
CREATE TABLE vouchers
(
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code           VARCHAR(255)            NOT NULL UNIQUE,
    description    TEXT,
    discount_type  VARCHAR(255)            NOT NULL DEFAULT 'percentage',
    is_unlimited   TINYINT UNSIGNED        NOT NULL DEFAULT 0,
    status         VARCHAR(255)            NOT NULL DEFAULT 'active',
    used_times     INT UNSIGNED            NOT NULL DEFAULT 0,
    discount_value DECIMAL(10, 2) UNSIGNED NOT NULL,
    start_date     TIMESTAMP               NOT NULL,
    end_date       TIMESTAMP               NOT NULL,
    created_at     TIMESTAMP               NULL     DEFAULT NULL,
    updated_at     TIMESTAMP               NULL     DEFAULT NULL,
    deleted_at     TIMESTAMP               NULL     DEFAULT NULL
);

--
-- Bảng staffs
--
CREATE TABLE staffs
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_name VARCHAR(255)     NOT NULL,
    is_active  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP        NULL     DEFAULT NULL,
    updated_at TIMESTAMP        NULL     DEFAULT NULL,
    deleted_at TIMESTAMP        NULL     DEFAULT NULL
);

--
-- Bảng payment_methods
--
CREATE TABLE payment_methods
(
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP    NULL DEFAULT NULL,
    updated_at  TIMESTAMP    NULL DEFAULT NULL,
    deleted_at  TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng cart_item_types
--
CREATE TABLE cart_item_types
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type_name  VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NULL DEFAULT NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng order_items
--
CREATE TABLE order_items
(
    id              INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    item_type_id    INT UNSIGNED            NOT NULL,
    item_id         INT UNSIGNED            NOT NULL,
    quantity        INT UNSIGNED            NOT NULL,
    price           DECIMAL(10, 2) UNSIGNED NOT NULL,
    discount_amount DECIMAL(10, 2) UNSIGNED      DEFAULT 0,
    created_at      TIMESTAMP               NULL DEFAULT NULL,
    updated_at      TIMESTAMP               NULL DEFAULT NULL,
    FOREIGN KEY (item_type_id) REFERENCES cart_item_types (id) ON DELETE CASCADE
);

--
-- Bảng carts
--
CREATE TABLE carts
(
    id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id       CHAR(36)     NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    order_item_id INT UNSIGNED NOT NULL,
    created_at    TIMESTAMP    NULL DEFAULT NULL,
    updated_at    TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE
);

--
-- Bảng invoices
--
CREATE TABLE invoices
(
    id                INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id           CHAR(36)     NOT NULL,                                  -- Cập nhật kiểu dữ liệu user_id
    voucher_id        INT UNSIGNED,
    staff_id          INT UNSIGNED,
    payment_method_id INT UNSIGNED NOT NULL,
    order_item_id     INT UNSIGNED NOT NULL,                                  -- Thêm khóa ngoại đến order_items
    invoice_number    VARCHAR(255) NOT NULL,
    total_amount      DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    discount_amount   DECIMAL(10, 2) UNSIGNED DEFAULT 0,
    payment_status    VARCHAR(255) NOT NULL   DEFAULT 'unpaid',
    note              VARCHAR(255),
    status            VARCHAR(255) NOT NULL   DEFAULT 'pending',
    created_at        TIMESTAMP    NULL       DEFAULT NULL,
    updated_at        TIMESTAMP    NULL       DEFAULT NULL,
    deleted_at        TIMESTAMP    NULL       DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE SET NULL,
    FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods (id) ON DELETE RESTRICT,
    FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE -- Thêm ràng buộc khóa ngoại
);

--
-- Bảng rating_types
--
CREATE TABLE rating_types
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type_name  VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NULL DEFAULT NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng ratings
--
CREATE TABLE ratings
(
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rating_type_id INT UNSIGNED NOT NULL,
    item_id        INT UNSIGNED NOT NULL,
    user_id        CHAR(36)     NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    comment        TEXT,
    image_id       INT UNSIGNED,          -- Thay đổi image thành image_id
    stars          INT UNSIGNED NOT NULL,
    created_at     TIMESTAMP    NULL DEFAULT NULL,
    updated_at     TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (rating_type_id) REFERENCES rating_types (id) ON DELETE CASCADE
);

--
-- Bảng favorite_types
--
CREATE TABLE favorite_types
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type_name  VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NULL DEFAULT NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng favorites
--
CREATE TABLE favorites
(
    id               INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    favorite_type_id INT UNSIGNED NOT NULL,
    item_id          INT UNSIGNED NOT NULL,
    user_id          CHAR(36)     NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    created_at       TIMESTAMP    NULL DEFAULT NULL,
    updated_at       TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (favorite_type_id) REFERENCES favorite_types (id) ON DELETE CASCADE
);

--
-- Bảng notifications
--
CREATE TABLE notifications
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id    CHAR(36)     NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    image_id   INT UNSIGNED,          -- Thay đổi image_path thành image_id
    content    TEXT         NOT NULL,
    status     VARCHAR(255) NOT NULL DEFAULT 'unseen',
    created_at TIMESTAMP    NULL     DEFAULT NULL,
    updated_at TIMESTAMP    NULL     DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng appointment_types
--
CREATE TABLE appointment_types
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type_name  VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NULL DEFAULT NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL
);

--
-- Bảng appointments
--
CREATE TABLE appointments
(
    id                  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id             CHAR(36)         NOT NULL,                            -- Cập nhật kiểu dữ liệu user_id
    appointment_type_id INT UNSIGNED     NOT NULL,
    staff_id            INT UNSIGNED,
    order_item_id       INT UNSIGNED     NOT NULL,                            -- Thêm khóa ngoại đến order_items
    start_date          TIMESTAMP        NOT NULL,
    end_date            TIMESTAMP        NOT NULL,
    is_all_day          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status              VARCHAR(255)     NOT NULL DEFAULT 'pending',
    note                TEXT,
    created_at          TIMESTAMP        NULL     DEFAULT NULL,
    updated_at          TIMESTAMP        NULL     DEFAULT NULL,
    deleted_at          TIMESTAMP        NULL     DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE SET NULL,
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types (id) ON DELETE RESTRICT,
    FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE -- Thêm ràng buộc khóa ngoại
);

--
-- Bảng user_vouchers
--
CREATE TABLE user_vouchers
(
    user_id    CHAR(36)         NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    voucher_id INT UNSIGNED     NOT NULL,
    used_at    TIMESTAMP,
    is_used    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP        NULL     DEFAULT NULL,
    updated_at TIMESTAMP        NULL     DEFAULT NULL,
    PRIMARY KEY (user_id, voucher_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE CASCADE
);


--
-- Bảng histories
--
CREATE TABLE histories
(
    id               INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id          CHAR(36)     NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    table_name       VARCHAR(255) NOT NULL,
    row_id           INT UNSIGNED NOT NULL,
    action           VARCHAR(255) NOT NULL,
    changes          JSON,
    action_timestamp TIMESTAMP    NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng fcm_tokens
--
CREATE TABLE fcm_tokens
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id    CHAR(36)  NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    fcm_token  TEXT      NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

--
-- Bảng stock_movements
--
CREATE TABLE stock_movements
(
    id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id    INT UNSIGNED              NOT NULL,
    movement_type ENUM ('import', 'export') NOT NULL,
    quantity      INT UNSIGNED              NOT NULL,
    unit_price    DECIMAL(10, 2) UNSIGNED   NOT NULL,
    note          TEXT,
    created_at    TIMESTAMP                 NULL DEFAULT NULL,
    updated_at    TIMESTAMP                 NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

--
-- Bảng customer_treatment_histories
--
CREATE TABLE customer_treatment_histories
(
    id             INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id        CHAR(36)     NOT NULL, -- Cập nhật kiểu dữ liệu user_id
    treatment_id   INT UNSIGNED NOT NULL,
    staff_id       INT UNSIGNED,
    treatment_date TIMESTAMP    NOT NULL,
    result         TEXT,
    note           TEXT,
    created_at     TIMESTAMP    NULL DEFAULT NULL,
    updated_at     TIMESTAMP    NULL DEFAULT NULL,
    deleted_at     TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staffs (id) ON DELETE SET NULL
);

--
-- Bảng payment_histories (Lịch sử thanh toán)
--
CREATE TABLE payment_histories
(
    id                 INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id         INT UNSIGNED NOT NULL,
    old_payment_status VARCHAR(255) NOT NULL,
    new_payment_status VARCHAR(255) NOT NULL,
    updated_at         TIMESTAMP    NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE
);

--
-- Bảng images
--
CREATE TABLE images
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NULL DEFAULT NULL,
    updated_at TIMESTAMP    NULL DEFAULT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL
);

-- Chèn dữ liệu vào bảng brands
INSERT INTO brands (brand_name)
VALUES ('Celbest'),
       ('Faith');

-- Chèn dữ liệu vào bảng product_categories
INSERT INTO product_categories (category_name)
VALUES ('Tẩy trang'),
       ('Sữa rửa mặt'),
       ('Toner'),
       ('Serum/Essence'),
       ('Kem dưỡng'),
       ('Kem chống nắng'),
       ('Mặt nạ'),
       ('Kem nền'),
       ('Phấn phủ'),
       ('Son môi'),
       ('Chăm sóc tóc'),
       ('Chăm sóc cơ thể');

-- Chèn dữ liệu vào bảng products
INSERT INTO products (category_id, brand_id, product_name, product_line, price)
VALUES (1, 2, 'Cleansing - Tẩy trang', 'Faith', 1092000),           -- Tẩy trang - Faith
       (3, 2, 'Toning - Cân bằng', 'Faith', 1052000),               -- Toner - Faith
       (5, 2, 'Whitening - Tẩy trắng', 'Faith', 1575000),           -- Kem dưỡng - Faith
       (5, 2, 'Collagen tươi - Chống lão hoá', 'Faith', 2000000),   -- Kem dưỡng - Faith
       (4, 2, 'Essence - Chống lão hoá', 'Faith', 1260000),         -- Serum/Essence - Faith
       (5, 2, 'Lotion - Dưỡng ẩm', 'Faith', 1155000),               -- Kem dưỡng - Faith
       (5, 2, 'Gel - Khoá ẩm', 'Faith', 1315000),                   -- Kem dưỡng - Faith
       (8, 2, 'Foundation - Kem nền', 'Faith', 948000),             -- Kem nền - Faith
       (1, 1, 'Cleansing - Tẩy trang', 'Celbest', 1092000),         -- Tẩy trang - Celbest
       (3, 1, 'Toning - Cân bằng', 'Celbest', 1052000),             -- Toner - Celbest
       (5, 1, 'Whitening - Tẩy trắng', 'Celbest', 1575000),         -- Kem dưỡng - Celbest
       (5, 1, 'Collagen tươi - Chống lão hoá', 'Celbest', 2000000), -- Kem dưỡng - Celbest
       (4, 1, 'Essence - Chống lão hoá', 'Celbest', 1260000),       -- Serum/Essence - Celbest
       (5, 1, 'Lotion - Dưỡng ẩm', 'Celbest', 1155000),             -- Kem dưỡng - Celbest
       (5, 1, 'Gel - Khoá ẩm', 'Celbest', 1315000),                 -- Kem dưỡng - Celbest
       (8, 1, 'Foundation - Kem nền', 'Celbest', 948000);
-- Kem nền - Celbest

-- Chèn dữ liệu vào bảng treatment_categories
INSERT INTO treatment_categories (category_name)
VALUES ('Facial - Chăm sóc da mặt'),
       ('Massage'),
       ('Giảm béo'),
       ('Triệt lông');

-- Chèn dữ liệu vào bảng treatments
-- Facial - Chăm sóc da mặt
INSERT INTO treatments (category_id, treatment_name, duration, price)
VALUES (1, 'Chăm sóc da mặt - Amino - Phủ hợp mọi loại da', 60, 1350000),
       (1, 'Chăm sóc da mặt - Chống lão hóa - Hifu', 45, 1350000),
       (1, 'Chăm sóc da mặt - Chống lão hóa - Photo', 80, 550000),
       (1, 'Chăm sóc da mặt - Dưỡng trắng - Trị nám', 55, 1250000),
       (1, 'Chăm sóc da mặt - Collagen Tươi', 50, 1800000),
       (1, 'Chăm sóc da mặt - Peeling da', 70, 2500000),
       (1, 'Chăm sóc da mặt - Massage body', 30, 1200000);

-- Massage
INSERT INTO treatments (category_id, treatment_name, duration, price)
VALUES (2, 'Massage body', 60, 390000),
       (2, 'Thải độc ruột - Massage nội tạng', 60, 750000),
       (2, 'Massage cổ vai gáy', 60, 350000),
       (2, 'Massage nâng cơ vùng 1', 60, 950000),
       (2, 'Đắp mặt (Cổ, Vai, Gáy)', 45, 160000),
       (2, 'Gội đầu dưỡng sinh kết hợp massage cổ, vai, gáy', 60, 180000),
       (2, 'Hồng xông mặt ngại cứu', 60, 400000),
       (2, 'Xăm môi, mí, mày, nghỉ thuật', 45, 3000000),
       (2, 'Gáy, béo bụng', NULL, 600000);

-- Giảm béo
INSERT INTO treatments (category_id, treatment_name, price)
VALUES (3, 'Giảm béo bụng', 550000),
       (3, 'Giảm béo eo', 310000),
       (3, 'Giảm béo mông', 1000000),
       (3, 'Giảm béo tay', 550000),
       (3, 'Giảm béo toàn thân', 2100000),
       (3, 'Giảm béo chân', 1900000);

-- Triệt lông
INSERT INTO treatments (category_id, treatment_name, price)
VALUES (4, 'Triệt lông nách', 250000),
       (4, 'Triệt lông bikini', 800000),
       (4, 'Triệt lông mép', 220000),
       (4, 'Triệt lông 1/2 chân', 350000),
       (4, 'Triệt lông (nguyên chân)', 700000),
       (4, 'Triệt lông tay (nguyên)', 450000),
       (4, 'Triệt lông toàn mặt', 2500000);

-- Chèn dữ liệu vào bảng treatment_combos
-- Facial - Chăm sóc da mặt
INSERT INTO treatment_combos (treatment_id, duration, combo_type, combo_price)
VALUES (1, 60, '5_times', 550000),
       (1, 60, '10_times', 1000000),
       (2, 45, '5_times', 550000),
       (2, 45, '10_times', 1000000),
       (3, 80, '5_times', 2000000),
       (3, 80, '10_times', 3800000),
       (4, 55, '5_times', 500000),
       (4, 55, '10_times', 900000),
       (5, 50, '5_times', 700000),
       (5, 50, '10_times', 1200000),
       (6, 70, '5_times', 1000000),
       (6, 70, '10_times', 1800000),
       (7, 30, '5_times', 500000),
       (7, 30, '10_times', 900000);

-- Giảm béo
INSERT INTO treatment_combos (treatment_id, combo_type, combo_price)
VALUES (10, '5_times', NULL),
       (10, '10_times', NULL),
       (11, '5_times', NULL),
       (11, '10_times', NULL),
       (12, '5_times', NULL),
       (12, '10_times', NULL),
       (13, '5_times', NULL),
       (13, '10_times', NULL),
       (14, '5_times', NULL),
       (14, '10_times', NULL),
       (15, '5_times', NULL),
       (15, '10_times', NULL);

-- Triệt lông
INSERT INTO treatment_combos (treatment_id, combo_type, combo_price)
VALUES (16, '5_times', 750000),
       (16, '10_times', 1400000),
       (17, '5_times', 2800000),
       (17, '10_times', 5000000),
       (18, '5_times', 650000),
       (18, '10_times', 1200000),
       (19, '5_times', 1500000),
       (19, '10_times', 2800000),
       (20, '5_times', 3000000),
       (20, '10_times', 5500000),
       (21, '5_times', 1750000),
       (21, '10_times', 3300000),
       (22, '5_times', 700000),
       (22, '10_times', 1300000);

-- Chèn dữ liệu vào bảng cart_item_types
INSERT INTO cart_item_types (type_name)
VALUES ('product'),
       ('treatment');
