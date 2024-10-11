USE allure_dev;

-- Chèn dữ liệu vào bảng product_categories
INSERT INTO product_categories (id, category_name) VALUES
(1,'Làm sạch'),
(2,'Rửa mặt'),
(3,'Tinh chất'),
(4,'Nước hoa hồng'),
(5,'Gel'),
(6,'Kem chống nắng'),
(7,'Kem nền');

-- Chèn dữ liệu vào bảng products
INSERT INTO products (category_id, product_name, product_line, description, price, volume, stock_quantity) VALUES
(1, 'FAITH Members Club Face Lamela Veil EX Cleansing', 'Faith', 'Dành cho mọi loại da, kể cả da nhạy cảm. Giúp loại bỏ lớp trang điểm và bã nhờn dư thừa mà không làm khô da.', 1000000, '200ml', 100),
(2, 'FAITH Members Club Face Lamela Veil EX Wash', 'Faith', 'Sữa rửa mặt tạo bọt giúp loại bỏ bụi bẩn, bã nhờn và lớp trang điểm còn sót lại, cho làn da sạch thoáng, mềm mại.', 800000, '80g', 100),
(3, 'FAITH Members Club Face Lamela Veil EX Moist Keep Essence', 'Faith', 'Cung cấp độ ẩm sâu, cho làn da căng mọng và đàn hồi. Giúp cải thiện tình trạng da khô, nếp nhăn, chảy xệ.', 1200000, '50ml', 100),
(4, 'FAITH Members Club Face Lamela Veil EX Moist Keep Lotion', 'Faith', 'Nước hoa hồng dưỡng ẩm, giúp cân bằng độ pH cho da, se khít lỗ chân lông và làm dịu da. Tăng cường hiệu quả của các bước dưỡng da tiếp theo.', 900000, '120ml', 100),
(5, 'FAITH Members Club Face Lamela Veil EX Moist Keep Gel', 'Faith', 'Kem dưỡng ẩm dạng gel thẩm thấu nhanh, cung cấp độ ẩm chuyên sâu, ngăn ngừa khô da và sần sùi. Mang đến làn da căng mọng, ẩm mịn và rạng rỡ.', 1100000, '30g', 100),
(6, 'FAITH Members Club Face Insist Lamela Sun Protector Essence N1', 'Faith', 'Kem chống nắng dạng tinh chất với các thành phần dịu nhẹ cho da, bảo vệ và dưỡng ẩm cho làn da suốt cả ngày.', 1300000, '50ml', 100),
(7, 'FAITH Members Club Face Insist Lamela Gel Foundation N1 G10(G10)', 'Faith', 'Kem nền dạng gel cao cấp giúp che phủ khuyết điểm, cho làn da mịn màng, rạng rỡ tự nhiên.', 1500000, '30g', 100);

-- Cập nhật thông tin chi tiết cho sản phẩm
UPDATE products SET
`usage` = 'Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều. Massage nhẹ nhàng lên da khô để hòa tan lớp trang điểm và bụi bẩn. Rửa sạch lại với nước ấm.',
benefits = 'Kết cấu sản phẩm mềm mượt, dễ tán đều, mang đến làn da sạch thoáng, ẩm mịn.',
key_ingredients = 'Gelatin Collagen*1',
ingredients = 'Water, Coconut Oil Fatty Acid PEG-7 Glyceryl, BG, Polysorbate 60, Pentylene Glycol, Glycerin, Water-Soluble Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Lactobacillus/Pear Juice Ferment Filtrate, Galactoarabinan, PCA-Na, Rosa Damascena Flower Water, Sodium Lauroyl Glutamate Lysine, Ectoin, Magnesium Ascorbyl Phosphate, Houttuynia Cordata Extract, Aloe Barbadensis Leaf Extract, Rosmarinus Officinalis (Rosemary) Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Tocopherol, Ceramide 3, Hydrogenated Lecithin, Cholesterol, Carbomer, Potassium Hydroxide'
WHERE id = 1;

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
INSERT INTO treatment_categories (id, category_name) VALUES
(1, 'Chăm sóc da mặt'),
(2, 'Massage'),
(3, 'Giảm béo'),
(4, 'Triệt lông');

-- Chèn dữ liệu vào bảng treatments
INSERT INTO treatments (category_id, treatment_name, description, duration, price, combo_type, combo_price, is_default, validity_period) VALUES
(1, 'Chăm sóc da cơ bản', 'Liệu trình làm sạch và dưỡng ẩm cho da', 60, 500000, 'single', NULL, 1, NULL),
(1, 'Chăm sóc da chuyên sâu', 'Liệu trình đặc trị các vấn đề về da', 90, 1000000, 'single', NULL, 1, NULL),
(2, 'Massage body', 'Massage toàn thân thư giãn', 60, 400000, 'single', NULL, 1, NULL),
(2, 'Massage mặt', 'Massage mặt trẻ hóa làn da', 30, 300000, 'single', NULL, 1, NULL),
(3, 'Giảm béo', 'Liệu trình giảm béo toàn thân', 120, 1500000, '5_times', 6000000, 0, 180),
(4, 'Triệt lông nách', 'Triệt lông vùng nách', 30, 200000, '10_times', 1500000, 0, 365);

-- Chèn dữ liệu vào bảng payment_methods
INSERT INTO payment_methods (method_name) VALUES
('Tiền mặt'),
('Thẻ tín dụng'),
('Chuyển khoản ngân hàng'),
('Ví điện tử');

-- Chèn dữ liệu vào bảng addresses
INSERT INTO addresses (user_id, address, address_type)
SELECT id, '123 Đường ABC, Quận 1, TP.HCM', 'home' FROM users WHERE email = 'admin@example.com'
UNION ALL
SELECT id, '456 Đường XYZ, Quận 2, TP.HCM', 'work' FROM users WHERE email = 'user@example.com';

-- Chèn dữ liệu vào bảng vouchers
INSERT INTO vouchers (code, description, discount_type, discount_value, start_date, end_date) VALUES
('WELCOME10', 'Giảm 10% cho khách hàng mới', 'percentage', 10, '2023-01-01', '2023-12-31'),
('SUMMER50K', 'Giảm 50,000đ cho đơn hàng từ 500,000đ', 'fixed', 50000, '2023-06-01', '2023-08-31');

-- Chèn dữ liệu vào bảng user_vouchers
INSERT INTO user_vouchers (user_id, voucher_id)
SELECT u.id, v.id
FROM users u, vouchers v
WHERE u.email = 'user@example.com' AND v.code = 'WELCOME10';

-- Chèn dữ liệu vào bảng orders
INSERT INTO orders (user_id, total_amount, status)
SELECT id, 1500000, 'completed'
FROM users
WHERE email = 'user@example.com';

-- Chèn dữ liệu vào bảng order_items
INSERT INTO order_items (order_id, item_type_id, item_id, quantity, price)
SELECT o.id, 1, p.id, 1, p.price
FROM orders o, products p, users u
WHERE u.email = 'user@example.com' AND p.product_name = 'FAITH Members Club Face Lamela Veil EX Cleansing' AND o.user_id = u.id
UNION ALL
SELECT o.id, 2, t.id, 1, t.price
FROM orders o, treatments t, users u
WHERE u.email = 'user@example.com' AND t.treatment_name = 'Chăm sóc da cơ bản' AND o.user_id = u.id;

-- Chèn dữ liệu vào bảng invoices
INSERT INTO invoices (user_id, order_id, payment_method_id, created_by, invoice_number, total_amount, payment_status, status)
SELECT u.id, o.id, pm.id, s.id, 'INV-001', 1500000, 'paid', 'completed'
FROM users u, orders o, payment_methods pm, staffs s
WHERE u.email = 'user@example.com' AND pm.method_name = 'Tiền mặt' AND s.user_id = (SELECT id FROM users WHERE email = 'staff@example.com') AND o.user_id = u.id;

-- Chèn dữ liệu vào bảng appointments
INSERT INTO appointments (user_id, appointment_type, staff_id, order_item_id, start_date, end_date, status)
SELECT u.id, 'facial', s.id, oi.id, '2023-07-01 10:00:00', '2023-07-01 11:00:00', 'confirmed'
FROM users u, staffs s, order_items oi, orders o
WHERE u.email = 'user@example.com' AND s.user_id = (SELECT id FROM users WHERE email = 'staff@example.com') AND o.user_id = u.id AND oi.order_id = o.id
LIMIT 1;
