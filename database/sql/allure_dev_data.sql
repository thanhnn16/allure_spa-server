# USE allure_dev;

--
-- Dữ liệu mẫu cho Allure Spa
--
--
-- Mỹ phẩm Allure Spa
--
-- Chèn dữ liệu vào bảng cart_item_types
INSERT INTO
        cart_item_types (type_name)
VALUES
        ('product'),
        ('treatment');

-- Chèn dữ liệu vào bảng product_categories
INSERT INTO
        product_categories (id, category_name)
VALUES
        (1,'Làm sạch'),
        (2,'Rửa mặt'),
        (3,'Tinh chất'),
        (4,'Nước hoa hồng'),
        (5,'Gel'),
        (6,'Kem chống nắng'),
        (7,'Kem nền');

-- Chèn dữ liệu vào bảng products
INSERT INTO
        products (
                category_id,
                product_name,
                product_line,
                description,
                price,
                volume,
                stock_quantity
        )
VALUES
        (
                1,
                'FAITH Members Club Face Lamela Veil EX Cleansing',
                'Faith',
                'Dành cho mọi loại da, kể cả da nhạy cảm. Giúp loại bỏ lớp trang điểm và bã nhờn dư thừa mà không làm khô da.',
                1000000,
                '200ml',
                100
        ),
        (
                2,
                'FAITH Members Club Face Lamela Veil EX Wash',
                'Faith',
                'Sữa rửa mặt tạo bọt giúp loại bỏ bụi bẩn, bã nhờn và lớp trang điểm còn sót lại, cho làn da sạch thoáng, mềm mại.',
                800000,
                '80g',
                100
        ),
        (
                3,
                'FAITH Members Club Face Lamela Veil EX Moist Keep Essence',
                'Faith',
                'Cung cấp độ ẩm sâu, cho làn da căng mọng và đàn hồi. Giúp cải thiện tình trạng da khô, nếp nhăn, chảy xệ.',
                1200000,
                '50ml',
                100
        ),
        (
                4,
                'FAITH Members Club Face Lamela Veil EX Moist Keep Lotion',
                'Faith',
                'Nước hoa hồng dưỡng ẩm, giúp cân bằng độ pH cho da, se khít lỗ chân lông và làm dịu da. Tăng cường hiệu quả của các bước dưỡng da tiếp theo.',
                900000,
                '120ml',
                100
        ),
        (
                5,
                'FAITH Members Club Face Lamela Veil EX Moist Keep Gel',
                'Faith',
                'Kem dưỡng ẩm dạng gel thẩm thấu nhanh, cung cấp độ ẩm chuyên sâu, ngăn ngừa khô da và sần sùi. Mang đến làn da căng mọng, ẩm mịn và rạng rỡ.',
                1100000,
                '30g',
                100
        ),
        (
                6,
                'FAITH Members Club Face Insist Lamela Sun Protector Essence N1',
                'Faith',
                'Kem chống nắng dạng tinh chất với các thành phần dịu nhẹ cho da, bảo vệ và dưỡng ẩm cho làn da suốt cả ngày.',
                1300000,
                '50ml',
                100
        ),
        (
                7,
                'FAITH Members Club Face Insist Lamela Gel Foundation N1 G10(G10)',
                'Faith',
                'Kem nền dạng gel cao cấp giúp che phủ khuyết điểm, cho làn da mịn màng, rạng rỡ tự nhiên.',
                1500000,
                '30g',
                100
        );

-- Chèn dữ liệu vào bảng products (phần usage, benefits, key_ingredients, ingredients, directions)
UPDATE
        products
SET
        `usage` = 'Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều. Massage nhẹ nhàng lên da khô để hòa tan lớp trang điểm và bụi bẩn. Rửa sạch lại với nước ấm.',
        benefits = 'Kết cấu sản phẩm mềm mượt, dễ tán đều, mang đến làn da sạch thoáng, ẩm mịn.',
        key_ingredients = 'Gelatin Collagen*1',
        ingredients = 'Water, Coconut Oil Fatty Acid PEG-7 Glyceryl, BG, Polysorbate 60, Pentylene Glycol, Glycerin, Water-Soluble Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Lactobacillus/Pear Juice Ferment Filtrate, Galactoarabinan, PCA-Na, Rosa Damascena Flower Water, Sodium Lauroyl Glutamate Lysine, Ectoin, Magnesium Ascorbyl Phosphate, Houttuynia Cordata Extract, Aloe Barbadensis Leaf Extract, Rosmarinus Officinalis (Rosemary) Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Tocopherol, Ceramide 3, Hydrogenated Lecithin, Cholesterol, Carbomer, Potassium Hydroxide'
WHERE
        id = 1;

UPDATE
        products
SET
        `usage` = 'Lấy một lượng vừa đủ (khoảng 1cm) ra lòng bàn tay và tạo bọt với nước. Nhẹ nhàng massage bọt lên mặt, sau đó rửa sạch với nước ấm.',
        benefits = 'Sữa rửa mặt tạo bọt mịn, dày, nhẹ nhàng làm sạch sâu, cho làn da mềm mại, mịn màng, sẵn sàng cho các bước chăm sóc da tiếp theo.',
        ingredients = 'Water, Glycerin, Myristic Acid, Stearic Acid, Potassium Hydroxide, DPG, Sorbitol, Lauric Acid, Glyceryl Stearate SE, Sodium Cocoyl Methyl Taurate, Sodium Lauroamphoacetate, Glycol Distearate, Ceramide NP, Sodium Hyaluronate, Hydrolyzed Elastin, Moroccan Lava Clay, Bisabolol, Glucosylrutin, Aloe Barbadensis Leaf Extract, Tormentilla Officinalis Root Extract, Chamomilla Recutita (Matricaria) Flower Extract, Glycyrrhizic Acid 2K, Ectoin, Sodium Lauroyl Glutamate Lysine, Polyquaternium-7, BG, Tetrasodium EDTA'
WHERE
        id = 2;

UPDATE
        products
SET
        `usage` = 'Lấy 2-3 lần bơm ra lòng bàn tay, thoa đều lên mặt sau khi đã làm sạch và cân bằng da. Thoa thêm sản phẩm lên những vùng da cần chăm sóc đặc biệt.',
        benefits = 'Cung cấp độ ẩm sâu, cho làn da căng mọng và đàn hồi. Giúp cải thiện tình trạng da khô, nếp nhăn, chảy xệ.',
        key_ingredients = 'Face Gelatin Collagen*2, Sodium Hyaluronate, Hydrolyzed Elastin',
        ingredients = 'Water, BG, Glycerin, Pentylene Glycol, Squalane, Water-Soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, Sodium ẞ-Sitosterol Sulfate, Leontopodium Alpinum Flower Extract, Glycine Soja (Soybean) Seed Extract, Acetyl Hydroxyproline, Ceramide NG, Acetyl Decapeptide-3, Glucosyl Ceramide, Punica Granatum Fruit Extract, Magnesium Ascorbyl Phosphate, RNA-Na, Ectoin, Sodium Lauroyl Glutamate Lysine, Arginine, Trehalose, Aloe Barbadensis Leaf Extract, Houttuynia Cordata Extract, Eugenia Caryophyllus (Clove) Flower Extract, Pyrus Cydonia Seed Extract, Sea Salt, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, α-Glucan, Polyglyceryl-5 Trioleate, Polyglyceryl-10 Diisostearate, (Acrylates/Alkyl Acrylate (C10-30)) Crosspolymer, Carbomer, Sodium Citrate, Citric Acid, Potassium Hydroxide'
WHERE
        id = 3;

UPDATE
        products
SET
        `usage` = 'Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều lên mặt sau khi đã sử dụng tinh chất.',
        benefits = 'Nước hoa hồng dạng lotion mỏng nhẹ, thẩm thấu nhanh, cung cấp độ ẩm lâu dài cho làn da mềm mại, mịn màng và rạng rỡ. Sản phẩm giúp cải thiện kết cấu và độ trong suốt của da, mang đến làn da khỏe mạnh và tươi sáng. Sử dụng sau bước làm sạch, cân bằng da và tinh chất để đạt hiệu quả tối ưu.',
        key_ingredients = 'Biophospholipids (Hydrogenated Lecithin), Gelatin Collagen (Water-Soluble Collagen), Hydrolyzed Collagen, Phytosterols, ẞ-Sitosterol Sodium Sulfate',
        ingredients = 'Water, BG, Pentylene Glycol, Glycerin, Trehalose, Water-Soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, β-Sitosterol Sodium Sulfate, Acetyl Hydroxyproline, Glucosyl Ceramide, Prunus Domestica Fruit Extract, Punica Granatum Fruit Extract, Magnesium Ascorbyl Phosphate, RNA-Na, Ectoin, Sodium Lauroyl Glutamate Lysine, Aloe Barbadensis Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Rosa Damascena Flower Water, Pyrus Cydonia Seed Extract, Houttuynia Cordata Extract, Sea Salt, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, a-Glucan, Polyglyceryl-5 Trioleate, Polyglyceryl-10 Diisostearate, (Acrylates/Alkyl Acrylate (C10-30)) Crosspolymer, Sodium Citrate, Citric Acid, Potassium Hydroxide'
WHERE
        id = 4;

UPDATE
        products
SET
        `usage` = 'Sau khi thoa nước hoa hồng, lấy một lượng vừa đủ (2-3 lần bơm) ra lòng bàn tay, thoa đều lên mặt. Thoa thêm sản phẩm lên những vùng da cần chăm sóc đặc biệt.',
        benefits = 'Kem dưỡng ẩm dạng gel thẩm thấu nhanh, cung cấp độ ẩm chuyên sâu, ngăn ngừa khô da và sần sùi. Sản phẩm mang đến làn da căng mọng, ẩm mịn và rạng rỡ.',
        key_ingredients = 'Face Gelatin Collagen*, Sodium Hyaluronate, Hydrolyzed Elastin',
        ingredients = 'Water, BG, Glycerin, Squalane, Behenyl Alcohol, Sorbitan Stearate, Polyglyceryl-10 Stearate, Glyceryl Stearate, Water-soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, ẞ-Sitosterol Sodium Sulfate, Potentilla Erecta Root Extract, Acetyl Hydroxyproline, Ceramide NG, Caffeoyl Tetrapeptide-3, Acetyl Decapeptide-3, Centella Asiatica Extract, Magnesium Ascorbyl Phosphate, Ectoin, RNA-Na, Glucosyl Ceramide, Sodium Lauroyl Glutamate Lysine, Punica Granatum Fruit Extract, Trehalose, Arginine, α- Glucan, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, Stearic Acid, Butyrospermum Parkii (Shea) Butter, Tocopheryl Acetate, Dextran, Diisostearyl Malate, Dimethicone, Ethylhexylglycerin, (Methyl Vinyl Ether/Maleic Acid) Crosspolymer, Sodium Citrate, Citric Acid, Potassium Hydroxide'
WHERE
        id = 5;

UPDATE
        products
SET
        `usage` = 'Lắc đều trước khi sử dụng. Thoa đều lên mặt và cơ thể 15 phút trước khi tiếp xúc với ánh nắng mặt trời. Thoa lại sau mỗi hai giờ, đặc biệt là sau khi bơi, đổ mồ hôi hoặc lau bằng khăn.',
        benefits = 'Cung cấp khả năng chống nắng phổ rộng SPF 40 PA+++, bảo vệ da khỏi tác hại của tia UVA và UVB. Kết cấu nhẹ nhàng, thấm nhanh, không gây nhờn rít, phù hợp cho mọi loại da.',
        key_ingredients = 'Octinoxate, Zinc Oxide, Titanium Dioxide',
        ingredients = 'Water, Cyclopentasiloxane, Ethylhexyl Methoxycinnamate, Zinc Oxide, Titanium Dioxide, Butylene Glycol, Dimethicone, Glycerin, Isononyl Isononanoate, Pentylene Glycol, Silica, Cetyl PEG/PPG-10/1 Dimethicone, Niacinamide, Sodium Chloride, Caprylyl Methicone, Phenoxyethanol, Disteardimonium Hectorite, Tocopheryl Acetate, Hydrogen Dimethicone, Magnesium Sulfate, Triethoxycaprylylsilane, Aluminum Hydroxide, Fragrance, Dipropylene Glycol, Ethylhexylglycerin, Adenosine, Disodium EDTA, Hydrolyzed Collagen, Morus Alba Root Extract, Sodium Hyaluronate'
WHERE
        id = 6;

UPDATE
        products
SET
        `usage` = 'Lấy một lượng vừa đủ, chấm 5 điểm lên mặt (trán, mũi, cằm và hai má). Dùng ngón tay hoặc mút tán đều từ trong ra ngoài và từ trên xuống dưới.',
        benefits = 'Kem nền dạng gel với độ che phủ tự nhiên, mang lại làn da mịn màng, rạng rỡ. Công thức nhẹ nhàng, không gây bít lỗ chân lông, phù hợp cho mọi loại da.',
        key_ingredients = 'Titanium Dioxide, Dimethicone, Silica',
        ingredients = 'Water, Cyclopentasiloxane, Titanium Dioxide, Ethylhexyl Methoxycinnamate, Butylene Glycol, Dimethicone, Glycerin, Pentylene Glycol, Niacinamide, PEG-10 Dimethicone, Silica, Cetyl PEG/PPG-10/1 Dimethicone, Sodium Chloride, Zinc Oxide, Phenoxyethanol, Disteardimonium Hectorite, Hydrogen Dimethicone, Triethoxycaprylylsilane, Aluminum Hydroxide, Fragrance, Dipropylene Glycol, Ethylhexylglycerin, Adenosine, Disodium EDTA, Hydrolyzed Collagen, Morus Alba Root Extract, Sodium Hyaluronate'
WHERE
        id = 7;

-- Chèn dữ liệu vào bảng treatment_categories
INSERT INTO
        treatment_categories (id, category_name)
VALUES
        (1, 'Chăm sóc da mặt'),
        (2,'Massage'),
        (3, 'Giảm béo'),
        (4, 'Triệt lông');

-- Chèn dữ liệu vào bảng treatments
INSERT INTO
        treatments (
                category_id,
                treatment_name,
                description,
                duration,
                price,
                combo_type,
                combo_price,
                is_default,
                validity_period
        )
VALUES
        (
                1,
                'Chăm sóc da cơ bản',
                'Liệu trình làm sạch và dưỡng ẩm cho da',
                60,
                500000,
                'single',
                NULL,
                1,
                NULL
        ),
        (
                1,
                'Chăm sóc da chuyên sâu',
                'Liệu trình đặc trị các vấn đề về da',
                90,
                1000000,
                'single',
                NULL,
                1,
                NULL
        ),
        (
                2,
                'Massage body',
                'Massage toàn thân thư giãn',
                60,
                400000,
                'single',
                NULL,
                1,
                NULL
        ),
        (
                2,
                'Massage mặt',
                'Massage mặt trẻ hóa làn da',
                30,
                300000,
                'single',
                NULL,
                1,
                NULL
        ),
        (
                3,
                'Giảm béo',
                'Liệu trình giảm béo toàn thân',
                120,
                1500000,
                '5_times',
                6000000,
                0,
                180
        ),
        (
                4,
                'Triệt lông nách',
                'Triệt lông vùng nách',
                30,
                200000,
                '10_times',
                1500000,
                0,
                365
        );

-- Chèn dữ liệu vào bảng staffs
INSERT INTO
        staffs (staff_name, position, hire_date)
VALUES
        (
                'Nguyễn Thị A',
                'Chuyên viên chăm sóc da',
                '2022-01-01'
        ),
        (
                'Trần Văn B',
                'Kỹ thuật viên massage',
                '2022-02-15'
        ),
        (
                'Lê Thị C',
                'Chuyên viên triệt lông',
                '2022-03-10'
        );

-- Chèn dữ liệu vào bảng payment_methods
INSERT INTO
        payment_methods (method_name)
VALUES
        ('Tiền mặt'),
        ('Thẻ tín dụng'),
        ('Chuyển khoản ngân hàng'),
        ('Ví điện tử');

-- Chèn dữ liệu vào bảng users
INSERT INTO
        users (
                id,
                phone_number,
                email,
                role,
                full_name,
                gender,
                date_of_birth
        )
VALUES
        (
                UUID(),
                '0123456789',
                'user1@example.com',
                'user',
                'Nguyễn Văn A',
                'male',
                '1990-01-01'
        ),
        (
                UUID(),
                '0987654321',
                'user2@example.com',
                'user',
                'Trần Thị B',
                'female',
                '1995-05-15'
        ),
        (
                UUID(),
                '0369852147',
                'admin@example.com',
                'admin',
                'Admin',
                'other',
                '1985-12-31'
        );

-- Chèn dữ liệu vào bảng addresses
INSERT INTO
        addresses (user_id, address, address_type)
SELECT
        id,
        '123 Đường ABC, Quận 1, TP.HCM',
        'home'
FROM
        users
WHERE
        email = 'user1@example.com'
UNION
ALL
SELECT
        id,
        '456 Đường XYZ, Quận 2, TP.HCM',
        'work'
FROM
        users
WHERE
        email = 'user2@example.com';

-- Chèn dữ liệu vào bảng vouchers
INSERT INTO
        vouchers (
                code,
                description,
                discount_type,
                discount_value,
                start_date,
                end_date
        )
VALUES
        (
                'WELCOME10',
                'Giảm 10% cho khách hàng mới',
                'percentage',
                10,
                '2023-01-01',
                '2023-12-31'
        ),
        (
                'SUMMER50K',
                'Giảm 50,000đ cho đơn hàng từ 500,000đ',
                'fixed',
                50000,
                '2023-06-01',
                '2023-08-31'
        );

-- Chèn dữ liệu vào bảng user_vouchers
INSERT INTO
        user_vouchers (user_id, voucher_id)
SELECT
        u.id,
        v.id
FROM
        users u,
        vouchers v
WHERE
        u.email = 'user1@example.com'
        AND v.code = 'WELCOME10';

-- Chèn dữ liệu vào bảng order_items
INSERT INTO
        order_items (item_type_id, item_id, quantity, price)
SELECT
        ct.id,
        p.id,
        1,
        p.price
FROM
        cart_item_types ct,
        products p
WHERE
        ct.type_name = 'product'
        AND p.product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing'
UNION
ALL
SELECT
        ct.id,
        t.id,
        1,
        t.price
FROM
        cart_item_types ct,
        treatments t
WHERE
        ct.type_name = 'treatment'
        AND t.treatment_name = 'Chăm sóc da cơ bản';

-- Chèn dữ liệu vào bảng invoices
INSERT INTO
        invoices (
                user_id,
                payment_method_id,
                order_item_id,
                created_by,
                invoice_number,
                total_amount,
                payment_status,
                status
        )
SELECT
        u.id,
        pm.id,
        oi.id,
        s.id,
        'INV-001',
        1500000,
        'paid',
        'completed'
FROM
        users u,
        payment_methods pm,
        order_items oi,
        staffs s
WHERE
        u.email = 'user1@example.com'
        AND pm.method_name = 'Tiền mặt'
        AND oi.id = 1
        AND s.staff_name = 'Nguyễn Thị A';

-- Chèn dữ liệu vào bảng appointments
INSERT INTO
        appointments (
                user_id,
                appointment_type_id,
                staff_id,
                order_item_id,
                start_date,
                end_date,
                status
        )
SELECT
        u.id,
        at.id,
        s.id,
        oi.id,
        '2023-07-01 10:00:00',
        '2023-07-01 11:00:00',
        'confirmed'
FROM
        users u,
        appointment_types at,
        staffs s,
        order_items oi
WHERE
        u.email = 'user1@example.com'
        AND at.type_name = 'Chăm sóc da'
        AND s.staff_name = 'Nguyễn Thị A'
        AND oi.id = 2;

-- Chèn dữ liệu vào bảng ratings
INSERT INTO
        ratings (rating_type, item_id, user_id, comment, stars)
SELECT
        'product',
        p.id,
        u.id,
        'Sản phẩm rất tốt, da mềm mịn hơn',
        5
FROM
        products p,
        users u
WHERE
        p.product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing'
        AND u.email = 'user1@example.com';

-- Chèn dữ liệu vào bảng favorites
INSERT INTO
        favorites (favorite_type, item_id, user_id)
SELECT
        'product',
        p.id,
        u.id
FROM
        products p,
        users u
WHERE
        p.product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing'
        AND u.email = 'user1@example.com';

-- Chèn dữ liệu vào bảng notifications
INSERT INTO
        notifications (user_id, content)
SELECT
        id,
        'Chào mừng bạn đến với Allure Spa!'
FROM
        users
WHERE
        email = 'user1@example.com';

-- Chèn dữ liệu vào bảng histories
INSERT INTO
        histories (
                user_id,
                table_name,
                row_id,
                action,
                changes,
                action_timestamp
        )
SELECT
        u.id,
        'products',
        p.id,
        'insert',
        '{"product_name": "FAITH Members Club Face Lamela Veil EX Cleansing", "price": 1000000}',
        NOW()
FROM
        users u,
        products p
WHERE
        u.email = 'admin@example.com'
        AND p.product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing';

-- Chèn dữ liệu vào bảng stock_movements
INSERT INTO
        stock_movements (
                product_id,
                movement_type,
                quantity,
                unit_price,
                note
        )
SELECT
        id,
        'import',
        100,
        800000,
        'Nhập hàng lần đầu'
FROM
        products
WHERE
        product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing';

-- Chèn dữ liệu vào bảng customer_treatment_histories
INSERT INTO
        customer_treatment_histories (
                user_id,
                treatment_id,
                staff_id,
                treatment_date,
                result,
                note
        )
SELECT
        u.id,
        t.id,
        s.id,
        '2023-07-01 10:00:00',
        'Da sáng và mềm mịn hơn',
        'Khách hàng hài lòng với kết quả'
FROM
        users u,
        treatments t,
        staffs s
WHERE
        u.email = 'user1@example.com'
        AND t.treatment_name = 'Chăm sóc da cơ bản'
        AND s.staff_name = 'Nguyễn Thị A';

-- Chèn dữ liệu vào bảng images
INSERT INTO
        images (image_path)
VALUES
        ('/images/products/faith_cleansing.jpg'),
        ('/images/treatments/facial_care.jpg'),
        ('/images/users/user1_avatar.jpg');

-- Cập nhật image_id cho sản phẩm, liệu trình và người dùng
UPDATE
        products
SET
        image_id = 1
WHERE
        product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing';

UPDATE
        treatments
SET
        image_id = 2
WHERE
        treatment_name = 'Chăm sóc da cơ bản';

UPDATE
        users
SET
        image_id = 3
WHERE
        email = 'user1@example.com';

-- Chèn dữ liệu vào bảng reward_items
INSERT INTO
        reward_items (
                name,
                description,
                item_type,
                item_id,
                points_required
        )
SELECT
        'Giảm giá 10% cho sản phẩm FAITH Cleansing',
        'Áp dụng cho sản phẩm FAITH Members Club Face Lamela Veil EX Cleansing',
        'product',
        id,
        100
FROM
        products
WHERE
        product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing'
UNION
ALL
SELECT
        'Miễn phí 1 lần Chăm sóc da cơ bản',
        'Áp dụng cho liệu trình Chăm sóc da cơ bản',
        'treatment_combo',
        id,
        500
FROM
        treatments
WHERE
        treatment_name = 'Chăm sóc da cơ bản';

-- Chèn dữ liệu vào bảng point_redemption_history
INSERT INTO
        point_redemption_history (user_id, reward_item_id, points_used)
SELECT
        u.id,
        ri.id,
        ri.points_required
FROM
        users u,
        reward_items ri
WHERE
        u.email = 'user1@example.com'
        AND ri.name = 'Giảm giá 10% cho sản phẩm FAITH Cleansing';

-- Chèn dữ liệu vào bảng product_translations
INSERT INTO
        product_translations (product_id, language, product_name, description)
SELECT
        id,
        'en',
        'FAITH Members Club Face Lamela Veil EX Cleansing',
        'Suitable for all skin types, including sensitive skin. Helps remove excess makeup and sebum without drying out the skin.'
FROM
        products
WHERE
        product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing';

-- Chèn dữ liệu vào bảng treatment_translations
INSERT INTO
        treatment_translations (
                treatment_id,
                language,
                treatment_name,
                description
        )
SELECT
        id,
        'en',
        'Basic Facial Care',
        'A cleansing and moisturizing treatment for your skin'
FROM
        treatments
WHERE
        treatment_name = 'Chăm sóc da cơ bản';

-- Cập nhật loyalty_points cho người dùng
UPDATE
        users
SET
        loyalty_points = 100
WHERE
        email = 'user1@example.com';

-- Chèn dữ liệu vào bảng employee_attendance
INSERT INTO
        employee_attendance (staff_id, check_in, check_out, date, notes)
SELECT
        id,
        '2023-07-01 08:00:00',
        '2023-07-01 17:00:00',
        '2023-07-01',
        'Đúng giờ'
FROM
        staffs
WHERE
        staff_name = 'Nguyễn Thị A';

-- Chèn dữ liệu vào bảng user_treatment_packages
INSERT INTO
        user_treatment_packages (
                user_id,
                treatment_id,
                combo_type,
                purchase_date,
                total_sessions,
                remaining_sessions,
                expiry_date
        )
SELECT
        u.id,
        t.id,
        'single',
        '2023-07-01',
        1,
        1,
        '2023-12-31'
FROM
        users u,
        treatments t
WHERE
        u.email = 'user1@example.com'
        AND t.treatment_name = 'Chăm sóc da cơ bản';

-- Chèn dữ liệu vào bảng treatment_usage_history
INSERT INTO
        treatment_usage_history (
                user_treatment_package_id,
                treatment_date,
                staff_id,
                notes,
                session_result
        )
SELECT
        utp.id,
        '2023-07-01 10:00:00',
        s.id,
        'Khách hàng hài lòng với dịch vụ',
        'Da sáng và mềm mịn hơn'
FROM
        user_treatment_packages utp
        JOIN users u ON utp.user_id = u.id
        JOIN treatments t ON utp.treatment_id = t.id
        JOIN staffs s ON s.staff_name = 'Nguyễn Thị A'
WHERE
        u.email = 'user1@example.com'
        AND t.treatment_name = 'Chăm sóc da cơ bản';

-- Cập nhật remaining_sessions trong user_treatment_packages
UPDATE
        user_treatment_packages utp
        JOIN users u ON utp.user_id = u.id
        JOIN treatments t ON utp.treatment_id = t.id
SET
        utp.remaining_sessions = utp.remaining_sessions - 1
WHERE
        u.email = 'user1@example.com'
        AND t.treatment_name = 'Chăm sóc da cơ bản';

-- Chèn dữ liệu vào bảng payment_histories
INSERT INTO
        payment_histories (
                invoice_id,
                old_payment_status,
                new_payment_status
        )
SELECT
        id,
        'unpaid',
        'paid'
FROM
        invoices
WHERE
        invoice_number = 'INV-001';

-- Chèn dữ liệu vào bảng fcm_tokens
INSERT INTO
        fcm_tokens (user_id, fcm_token)
SELECT
        id,
        'example_fcm_token_123456'
FROM
        users
WHERE
        email = 'user1@example.com';