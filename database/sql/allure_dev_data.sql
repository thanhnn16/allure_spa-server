USE allure_dev;

-- Thêm dữ liệu mẫu cho các khung giờ
INSERT INTO
  time_slots (id, start_time, end_time)
VALUES
  (1, '08:00:00', '09:00:00'),
  (2, '09:00:00', '10:00:00'),
  (3, '10:00:00', '11:00:00'),
  (4, '11:00:00', '12:00:00'),
  (5, '12:00:00', '13:00:00'),
  (6, '13:00:00', '14:00:00'),
  (7, '14:00:00', '15:00:00'),
  (8, '15:00:00', '16:00:00'),
  (9, '16:00:00', '17:00:00'),
  (10, '17:00:00', '18:00:00'),
  (11, '18:00:00', '18:30:00');

-- Chèn dữ liệu vào bảng media
INSERT INTO
  media (id, type, file_path, mediable_type, mediable_id)
VALUES
  (
    1,
    'image',
    '/images/products/cleansing.jpg',
    'product',
    1
  ),
  (
    2,
    'image',
    '/images/products/face_wash.jpg',
    'product',
    2
  ),
  (
    3,
    'image',
    '/images/products/essence.jpg',
    'product',
    3
  ),
  (
    4,
    'image',
    '/images/products/lotion.jpg',
    'product',
    4
  ),
  (
    5,
    'image',
    '/images/products/gel.jpg',
    'product',
    5
  ),
  (
    6,
    'image',
    '/images/products/sunscreen.jpg',
    'product',
    6
  ),
  (
    7,
    'image',
    '/images/products/foundation.jpg',
    'product',
    7
  ),
  (
    8,
    'image',
    '/images/users/default.png',
    'user',
    1
  );

-- Chèn dữ liệu vào bảng product_categories
INSERT INTO
  product_categories (id, category_name)
VALUES
  (1, 'Làm sạch'),
  (2, 'Rửa mặt'),
  (3, 'Tinh chất'),
  (4, 'Nước hoa hồng'),
  (5, 'Gel'),
  (6, 'Kem chống nắng'),
  (7, 'Kem nền');

-- Chèn dữ liệu vào bảng products
INSERT INTO
  products (
    id,
    name,
    price,
    category_id,
    quantity,
    brand_description,
    `usage`,
    benefits,
    key_ingredients,
    ingredients,
    directions,
    storage_instructions,
    product_notes
  )
VALUES
  (
    1,
    'FAITH Members Club Face Lamela Veil EX Cleansing',
    1000000,
    1,
    100,
    'FAITH',
    'Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều. Massage nhẹ nhàng lên da khô để hòa tan lớp trang điểm và bụi bẩn. Rửa sạch lại với nước ấm.',
    'Kết cấu sản phẩm mềm mượt, dễ tán đều, mang đến làn da sạch thoáng, ẩm mịn.',
    'Gelatin Collagen*1',
    'Water, Coconut Oil Fatty Acid PEG-7 Glyceryl, BG, Polysorbate 60, Pentylene Glycol, Glycerin, Water-Soluble Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Lactobacillus/Pear Juice Ferment Filtrate, Galactoarabinan, PCA-Na, Rosa Damascena Flower Water, Sodium Lauroyl Glutamate Lysine, Ectoin, Magnesium Ascorbyl Phosphate, Houttuynia Cordata Extract, Aloe Barbadensis Leaf Extract, Rosmarinus Officinalis (Rosemary) Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Tocopherol, Ceramide 3, Hydrogenated Lecithin, Cholesterol, Carbomer, Potassium Hydroxide',
    'Sử dụng hàng ngày, sáng và tối',
    'Bảo quản nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp',
    'Phù hợp cho mọi loại da'
  ),
  (
    2,
    'FAITH Members Club Face Lamela Veil EX Wash',
    800000,
    2,
    100,
    'FAITH',
    'Lấy một lượng vừa đủ (khoảng 1cm) ra lòng bàn tay và tạo bọt với nước. Nhẹ nhàng massage bọt lên mặt, sau đó rửa sạch với nước ấm.',
    'Sữa rửa mặt tạo bọt mịn, dày, nhẹ nhàng làm sạch sâu, cho làn da mềm mại, mịn màng, sẵn sàng cho các bước chăm sóc da tiếp theo.',
    NULL,
    'Water, Glycerin, Myristic Acid, Stearic Acid, Potassium Hydroxide, DPG, Sorbitol, Lauric Acid, Glyceryl Stearate SE, Sodium Cocoyl Methyl Taurate, Sodium Lauroamphoacetate, Glycol Distearate, Ceramide NP, Sodium Hyaluronate, Hydrolyzed Elastin, Moroccan Lava Clay, Bisabolol, Glucosylrutin, Aloe Barbadensis Leaf Extract, Tormentilla Officinalis Root Extract, Chamomilla Recutita (Matricaria) Flower Extract, Glycyrrhizic Acid 2K, Ectoin, Sodium Lauroyl Glutamate Lysine, Polyquaternium-7, BG, Tetrasodium EDTA',
    'Sử dụng hàng ngày, sáng và tối',
    'Bảo quản nơi khô ráo, thoáng mát',
    'Phù hợp cho mọi loại da'
  ),
  (
    3,
    'FAITH Members Club Face Lamela Veil EX Moist Keep Essence',
    1200000,
    3,
    100,
    'FAITH',
    'Lấy 2-3 lần bơm ra lòng bàn tay, thoa đều lên mặt sau khi đã làm sạch và cân bằng da. Thoa thêm sản phẩm lên những vùng da cần chăm sóc đặc biệt.',
    'Cung cấp độ ẩm sâu, cho làn da căng mọng và đàn hồi. Giúp cải thiện tình trạng da khô, nếp nhăn, chảy xệ.',
    'Face Gelatin Collagen*2, Sodium Hyaluronate, Hydrolyzed Elastin',
    'Water, BG, Glycerin, Pentylene Glycol, Squalane, Water-Soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, Sodium ẞ-Sitosterol Sulfate, Leontopodium Alpinum Flower Extract, Glycine Soja (Soybean) Seed Extract, Acetyl Hydroxyproline, Ceramide NG, Acetyl Decapeptide-3, Glucosyl Ceramide, Punica Granatum Fruit Extract, Magnesium Ascorbyl Phosphate, RNA-Na, Ectoin, Sodium Lauroyl Glutamate Lysine, Arginine, Trehalose, Aloe Barbadensis Leaf Extract, Houttuynia Cordata Extract, Eugenia Caryophyllus (Clove) Flower Extract, Pyrus Cydonia Seed Extract, Sea Salt, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, α-Glucan, Polyglyceryl-5 Trioleate, Polyglyceryl-10 Diisostearate, (Acrylates/Alkyl Acrylate (C10-30)) Crosspolymer, Carbomer, Sodium Citrate, Citric Acid, Potassium Hydroxide',
    'Sử dụng hàng ngày, sáng và tối',
    'Bảo quản nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp',
    'Phù hợp cho mọi loại da'
  ),
  (
    4,
    'FAITH Members Club Face Lamela Veil EX Moist Keep Lotion',
    900000,
    4,
    100,
    'FAITH',
    'Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều lên mặt sau khi đã sử dụng tinh chất.',
    'Nước hoa hồng dạng lotion mỏng nhẹ, thẩm thấu nhanh, cung cấp độ ẩm lâu dài cho làn da mềm mại, mịn màng và rạng rỡ. Sản phẩm giúp cải thiện kết cấu và độ trong suốt của da, mang đến làn da khỏe mạnh và tươi sáng. Sử dụng sau bước làm sạch, cân bằng da và tinh chất để đạt hiệu quả tối ưu.',
    'Biophospholipids (Hydrogenated Lecithin), Gelatin Collagen (Water-Soluble Collagen), Hydrolyzed Collagen, Phytosterols, ẞ-Sitosterol Sodium Sulfate',
    'Water, BG, Pentylene Glycol, Glycerin, Trehalose, Water-Soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, β-Sitosterol Sodium Sulfate, Acetyl Hydroxyproline, Glucosyl Ceramide, Prunus Domestica Fruit Extract, Punica Granatum Fruit Extract, Magnesium Ascorbyl Phosphate, RNA-Na, Ectoin, Sodium Lauroyl Glutamate Lysine, Aloe Barbadensis Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Rosa Damascena Flower Water, Pyrus Cydonia Seed Extract, Houttuynia Cordata Extract, Sea Salt, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, a-Glucan, Polyglyceryl-5 Trioleate, Polyglyceryl-10 Diisostearate, (Acrylates/Alkyl Acrylate (C10-30)) Crosspolymer, Sodium Citrate, Citric Acid, Potassium Hydroxide',
    'Sử dụng hàng ngày, sáng và tối',
    'Bảo quản nơi khô ráo, thoáng mát',
    'Phù hợp cho mọi loại da'
  ),
  (
    5,
    'FAITH Members Club Face Lamela Veil EX Moist Keep Gel',
    1100000,
    5,
    100,
    'FAITH',
    'Sau khi thoa nước hoa hồng, lấy một lượng vừa đủ (2-3 lần bơm) ra lòng bàn tay, thoa đều lên mặt. Thoa thêm sản phẩm lên những vùng da cần chăm sóc đặc biệt.',
    'Kem dưỡng ẩm dạng gel thẩm thấu nhanh, cung cấp độ ẩm chuyên sâu, ngăn ngừa khô da và sần sùi. Sản phẩm mang đến làn da căng mọng, ẩm mịn và rạng rỡ.',
    'Face Gelatin Collagen*, Sodium Hyaluronate, Hydrolyzed Elastin',
    'Water, BG, Glycerin, Squalane, Behenyl Alcohol, Sorbitan Stearate, Polyglyceryl-10 Stearate, Glyceryl Stearate, Water-soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, ẞ-Sitosterol Sodium Sulfate, Potentilla Erecta Root Extract, Acetyl Hydroxyproline, Ceramide NG, Caffeoyl Tetrapeptide-3, Acetyl Decapeptide-3, Centella Asiatica Extract, Magnesium Ascorbyl Phosphate, Ectoin, RNA-Na, Glucosyl Ceramide, Sodium Lauroyl Glutamate Lysine, Punica Granatum Fruit Extract, Trehalose, Arginine, α- Glucan, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, Stearic Acid, Butyrospermum Parkii (Shea) Butter, Tocopheryl Acetate, Dextran, Diisostearyl Malate, Dimethicone, Ethylhexylglycerin, (Methyl Vinyl Ether/Maleic Acid) Crosspolymer, Sodium Citrate, Citric Acid, Potassium Hydroxide',
    'Sử dụng hàng ngày, sáng và tối',
    'Bảo quản nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp',
    'Phù hợp cho mọi loại da'
  ),
  (
    6,
    'FAITH Members Club Face Insist Lamela Sun Protector Essence N1',
    1300000,
    6,
    100,
    'FAITH',
    'Lắc đều trước khi sử dụng. Thoa đều lên mặt và cơ thể 15 phút trước khi tiếp xúc với ánh nắng mặt trời. Thoa lại sau mỗi hai giờ, đặc biệt là sau khi bơi, đổ mồ hôi hoặc lau bằng khăn.',
    'Cung cấp khả năng chống nắng phổ rộng SPF 40 PA+++, bảo vệ da khỏi tác hại của tia UVA và UVB. Kết cấu nhẹ nhàng, thấm nhanh, không gây nhờn rít, phù hợp cho mọi loại da.',
    'Octinoxate, Zinc Oxide, Titanium Dioxide',
    'Water, Cyclopentasiloxane, Ethylhexyl Methoxycinnamate, Zinc Oxide, Titanium Dioxide, Butylene Glycol, Dimethicone, Glycerin, Isononyl Isononanoate, Pentylene Glycol, Silica, Cetyl PEG/PPG-10/1 Dimethicone, Niacinamide, Sodium Chloride, Caprylyl Methicone, Phenoxyethanol, Disteardimonium Hectorite, Tocopheryl Acetate, Hydrogen Dimethicone, Magnesium Sulfate, Triethoxycaprylylsilane, Aluminum Hydroxide, Fragrance, Dipropylene Glycol, Ethylhexylglycerin, Adenosine, Disodium EDTA, Hydrolyzed Collagen, Morus Alba Root Extract, Sodium Hyaluronate',
    'Sử dụng hàng ngày, 15-30 phút trước khi ra nắng',
    'Bảo quản nơi khô ráo, thong mát, tránh ánh nng trực tiếp',
    'Phù hợp cho mọi loại da'
  ),
  (
    7,
    'FAITH Members Club Face Insist Lamela Gel Foundation N1 G10(G10)',
    1500000,
    7,
    100,
    'FAITH',
    'Lấy một lượng vừa đủ, chấm 5 điểm lên mặt (trán, mũi, cằm và hai má). Dùng ngón tay hoặc mút tán đều từ trong ra ngoài và từ trên xuống dưới.',
    'Kem nền dạng gel với độ che phủ tự nhiên, mang lại làn da mịn màng, rạng rỡ. Công thức nhẹ nhàng, không gây bít lỗ chân lông, phù hợp cho mọi loại da.',
    'Titanium Dioxide, Dimethicone, Silica',
    'Water, Cyclopentasiloxane, Titanium Dioxide, Ethylhexyl Methoxycinnamate, Butylene Glycol, Dimethicone, Glycerin, Pentylene Glycol, Niacinamide, PEG-10 Dimethicone, Silica, Cetyl PEG/PPG-10/1 Dimethicone, Sodium Chloride, Zinc Oxide, Phenoxyethanol, Disteardimonium Hectorite, Hydrogen Dimethicone, Triethoxycaprylylsilane, Aluminum Hydroxide, Fragrance, Dipropylene Glycol, Ethylhexylglycerin, Adenosine, Disodium EDTA, Hydrolyzed Collagen, Morus Alba Root Extract, Sodium Hyaluronate',
    'Sử dụng hàng ngày, sau bước dưỡng da',
    'Bảo quản nơi khô ráo, thoáng mát',
    'Phù hợp cho mọi loại da'
  );

-- Update service_categories
INSERT INTO
  service_categories (id, service_category_name)
VALUES
  (1, 'Chăm sóc da mặt'),
  (2, 'Massage'),
  (3, 'Giảm béo'),
  (4, 'Triệt lông'),
  (5, 'Xăm thẩm mỹ');

-- Update services
INSERT INTO
  services (
    id,
    category_id,
    service_name,
    description,
    duration,
    single_price
  )
VALUES
  (
    1,
    1,
    'Chăm da Amino - Phù hợp mọi loại da',
    'Liệu trình chăm sóc da mặt phù hợp cho mọi loại da',
    60,
    1350000
  ),
  (
    2,
    1,
    'Chống lão hoá - Photo',
    'Liệu trình chống lão hóa sử dụng công nghệ Photo Facial kết hợp với Tế bào gốc',
    45,
    1350000
  ),
  (
    3,
    1,
    'Dưỡng trắng - Trị nám',
    'Liệu trình dưỡng trắng và trị nám theo phương pháp FluorOxygen+C',
    90,
    1800000
  ),
  (
    4,
    1,
    'Collagen Tươi',
    'Liệu trình chăm sóc da mặt với collagen tươi',
    55,
    1250000
  ),
  (
    5,
    1,
    'Peeling da',
    'Liệu trình peeling da chuyên sâu',
    70,
    2500000
  ),
  (
    6,
    2,
    'Massage body',
    'Massage toàn thân thư giãn',
    60,
    390000
  ),
  (
    7,
    2,
    'Thải độc ruột - Massage nội tạng',
    'Liệu trình thải độc ruột kết hợp massage nội tạng',
    60,
    750000
  ),
  (
    8,
    2,
    'Massage Nâng cơ vòng 1',
    'Liệu trình massage nâng cơ vùng ngực',
    60,
    950000
  ),
  (
    9,
    2,
    'Massage Cổ, Vai, Gáy',
    'Massage tập trung vào vùng cổ, vai, gáy',
    60,
    350000
  ),
  (
    10,
    2,
    'Gội đầu chống rụng tóc kết hợp massage cổ, vai, gáy',
    'Liệu trình gội đầu và massage',
    45,
    160000
  ),
  (
    11,
    2,
    'Xông hơi ngải cứu',
    'Liệu trình xông hơi với ngải cứu',
    60,
    400000
  ),
  (
    12,
    3,
    'Giảm béo bụng',
    'Liệu trình giảm béo vùng bụng',
    NULL,
    600000
  ),
  (
    13,
    3,
    'Giảm béo eo',
    'Liệu trình giảm béo vùng eo',
    NULL,
    400000
  ),
  (
    14,
    3,
    'Giảm béo mông',
    'Liệu trình giảm béo vùng mông',
    NULL,
    400000
  ),
  (
    15,
    3,
    'Giảm béo tay',
    'Liệu trình giảm béo vùng tay',
    NULL,
    400000
  ),
  (
    16,
    3,
    'Giảm béo chân',
    'Liệu trình giảm béo vùng chân',
    NULL,
    600000
  ),
  (
    17,
    3,
    'Giảm béo toàn thân',
    'Liệu trình giảm béo toàn thân',
    NULL,
    2400000
  ),
  (
    18,
    4,
    'Triệt lông Bikini',
    'Liệu trình triệt lông vùng bikini',
    NULL,
    800000
  ),
  (
    19,
    4,
    'Triệt lông nách',
    'Liệu trình triệt lông vùng nách',
    NULL,
    250000
  ),
  (
    20,
    4,
    'Triệt lông chân (nửa chân)',
    'Liệu trình triệt lông nửa chân',
    NULL,
    350000
  ),
  (
    21,
    4,
    'Triệt lông chân (nguyên chân)',
    'Liệu trình triệt lông toàn bộ chân',
    NULL,
    700000
  ),
  (
    22,
    4,
    'Triệt lông tay',
    'Liệu trình triệt lông vùng tay',
    NULL,
    450000
  ),
  (
    23,
    4,
    'Triệt lông mặt, ria mép',
    'Liệu trình triệt lông vùng mặt và ria mép',
    NULL,
    250000
  ),
  (
    24,
    4,
    'Triệt lông toàn thân',
    'Liệu trình triệt lông toàn thân',
    NULL,
    2200000
  ),
  (
    25,
    5,
    'Xăm mày, môi nghệ thuật',
    'Dịch vụ xăm mày và môi nghệ thuật',
    NULL,
    4000000
  );

-- Update service_combos
UPDATE
  services
SET
  combo_5_price = single_price * 4,
  combo_10_price = single_price * 7,
  validity_period = 365;

-- Chèn dữ liệu vào bảng payment_methods
INSERT INTO
  payment_methods (id, method_name)
VALUES
  (1, 'Tiền mặt'),
  (2, 'Thẻ tín dụng'),
  (3, 'Chuyển khoản ngân hàng');

-- Chèn dữ liệu vào bảng addresses
INSERT INTO
  addresses (id, user_id, address, address_type)
SELECT
  1,
  id,
  '123 Đường ABC, Quận 1, TP.HCM',
  'home'
FROM
  users
WHERE
  email = 'admin@example.com'
UNION
ALL
SELECT
  2,
  id,
  '456 Đường XYZ, Quận 2, TP.HCM',
  'work'
FROM
  users
WHERE
  email = 'user@example.com';

-- Chèn dữ liệu vào bảng vouchers
INSERT INTO
  vouchers (
    id,
    code,
    description,
    discount_type,
    discount_value,
    start_date,
    end_date
  )
VALUES
  (
    1,
    'WELCOME10',
    'Giảm 10% cho khách hàng mới',
    'percentage',
    10,
    '2023-01-01',
    '2023-12-31'
  ),
  (
    2,
    'SUMMER50K',
    'Giảm 50,000đ cho đơn hàng từ 500,000đ',
    'fixed_amount',
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
  u.email = 'user@example.com'
  AND v.code = 'WELCOME10';

-- Chèn dữ liệu vào bảng orders
INSERT INTO
  orders (
    id,
    user_id,
    payment_method_id,
    total_amount,
    status
  )
SELECT
  1,
  id,
  1,
  1500000,
  'completed'
FROM
  users
WHERE
  email = 'user@example.com';

-- Chèn dữ liệu vào bảng order_items
INSERT INTO
  order_items (
    id,
    order_id,
    item_type,
    item_id,
    quantity,
    price
  )
SELECT
  1,
  o.id,
  'product',
  p.id,
  1,
  p.price
FROM
  orders o
  JOIN users u ON o.user_id = u.id
  JOIN products p ON p.name = 'FAITH Members Club Face Lamela Veil EX Cleansing'
WHERE
  u.email = 'user@example.com'
UNION
ALL
SELECT
  2,
  o.id,
  'service',
  s.id,
  1,
  s.single_price
FROM
  orders o
  JOIN users u ON o.user_id = u.id
  JOIN services s ON s.service_name = 'Chăm da Amino - Phù hợp mọi loại da'
WHERE
  u.email = 'user@example.com';

-- Chèn dữ liệu vào bảng invoices
INSERT INTO
  invoices (
    id,
    user_id,
    staff_user_id,
    total_amount,
    paid_amount,
    status,
    note,
    created_by_user_id,
    created_at
  )
SELECT
  UUID(),
  -- id
  u.id,
  -- user_id
  s.id,
  -- staff_user_id
  1500000,
  -- total_amount
  1500000,
  -- paid_amount (assuming fully paid)
  'paid',
  -- status
  'Thanh toán đơn hàng',
  -- note
  s.id,
  -- created_by_user_id
  NOW() -- created_at
FROM
  users u
  JOIN users s ON s.email = 'staff@example.com'
  JOIN orders o ON o.user_id = u.id
WHERE
  u.email = 'user@example.com'
LIMIT
  1;

-- Chèn dữ liệu vào bảng appointments
INSERT INTO
  appointments (
    id,
    user_id,
    service_id,
    staff_user_id,
    start_time,
    end_time,
    appointment_type,
    status
  )
SELECT
  1,
  u.id,
  s.id,
  staff.id,
  '2024-11-01 10:00:00',
  '2024-11-01 11:00:00',
  'facial',
  'confirmed'
FROM
  users u
  JOIN users staff ON staff.email = 'staff@example.com'
  JOIN services s ON s.service_name = 'Chăm da Amino - Phù hợp mọi loại da'
  JOIN orders o ON o.user_id = u.id
  JOIN order_items oi ON oi.order_id = o.id
  AND oi.item_type = 'service'
  AND oi.item_id = s.id
WHERE
  u.email = 'user@example.com'
LIMIT
  1;