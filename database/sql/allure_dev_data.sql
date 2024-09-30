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

-- Chèn dữ liệu vào bảng brands
INSERT INTO brands (brand_name)
VALUES ('FAITH');

-- Chèn dữ liệu vào bảng product_categories
INSERT INTO product_categories (category_name)
VALUES ('Làm sạch'),
       ('Rửa mặt'),
       ('Tinh chất'),
       ('Nước hoa hồng'),
       ('Gel'),
       ('Kem chống nắng'),
       ('Kem nền');

-- Chèn dữ liệu vào bảng products
INSERT INTO products (category_id, brand_id, product_name, product_line, language, description, price, volume,
                      stock_quantity)
VALUES (1, 1, 'FAITH Members Club Face Lamela Veil EX Cleansing', 'Faith', 'vi',
        'Dành cho mọi loại da, kể cả da nhạy cảm. Giúp loại bỏ lớp trang điểm và bã nhờn dư thừa mà không làm khô da.',
        1000000, '200ml', 100),
       (2, 1, 'FAITH Members Club Face Lamela Veil EX Wash', 'Faith', 'vi',
        'Sữa rửa mặt tạo bọt giúp loại bỏ bụi bẩn, bã nhờn và lớp trang điểm còn sót lại, cho làn da sạch thoáng, mềm mại.',
        800000, '80g', 100),
       (3, 1, 'FAITH Members Club Face Lamela Veil EX Moist Keep Essence', 'Faith', 'vi',
        'Cung cấp độ ẩm sâu, cho làn da căng mọng và đàn hồi. Giúp cải thiện tình trạng da khô, nếp nhăn, chảy xệ.',
        1200000, '50ml', 100),
       (4, 1, 'FAITH Members Club Face Lamela Veil EX Moist Keep Lotion', 'Faith', 'vi',
        'Nước hoa hồng dưỡng ẩm, giúp cân bằng độ pH cho da, se khít lỗ chân lông và làm dịu da. Tăng cường hiệu quả của các bước dưỡng da tiếp theo.',
        900000, '120ml', 100),
       (5, 1, 'FAITH Members Club Face Lamela Veil EX Moist Keep Gel', 'Faith', 'vi',
        'Kem dưỡng ẩm dạng gel thẩm thấu nhanh, cung cấp độ ẩm chuyên sâu, ngăn ngừa khô da và sần sùi. Mang đến làn da căng mọng, ẩm mịn và rạng rỡ.',
        1100000, '30g', 100),
       (6, 1, 'FAITH Members Club Face Insist Lamela Sun Protector Essence N1', 'Faith', 'vi',
        'Kem chống nắng dạng tinh chất với các thành phần dịu nhẹ cho da, bảo vệ và dưỡng ẩm cho làn da suốt cả ngày.',
        1300000, '50ml', 100),
       (7, 1, 'FAITH Members Club Face Insist Lamela Gel Foundation N1 G10(G10)', 'Faith', 'vi',
        'Kem nền dạng gel cao cấp giúp che phủ khuyết điểm, cho làn da mịn màng, rạng rỡ tự nhiên.', 1500000, '30g',
        100);

-- Chèn dữ liệu vào bảng product_details (dựa trên thông tin trong file PDF)
-- Do không có đủ thông tin chi tiết cho từng sản phẩm, tôi sẽ để trống một số trường và bạn có thể bổ sung sau
INSERT INTO product_details (product_id, `usage`, benefits, key_ingredients, ingredients, directions,
                             storage_instructions, product_notes)
VALUES (1,
        'Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều. Massage nhẹ nhàng lên da khô để hòa tan lớp trang điểm và bụi bẩn. Rửa sạch lại với nước ấm.',
        'Kết cấu sản phẩm mềm mượt, dễ tán đều, mang đến làn da sạch thoáng, ẩm mịn.',
        'Gelatin Collagen*1',
        'Water, Coconut Oil Fatty Acid PEG-7 Glyceryl, BG, Polysorbate 60, Pentylene Glycol, Glycerin, Water-Soluble Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Lactobacillus/Pear Juice Ferment Filtrate, Galactoarabinan, PCA-Na, Rosa Damascena Flower Water, Sodium Lauroyl Glutamate Lysine, Ectoin, Magnesium Ascorbyl Phosphate, Houttuynia Cordata Extract, Aloe Barbadensis Leaf Extract, Rosmarinus Officinalis (Rosemary) Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Tocopherol, Ceramide 3, Hydrogenated Lecithin, Cholesterol, Carbomer, Potassium Hydroxide',
        NULL, NULL, NULL),
       (2,
        'Lấy một lượng vừa đủ (khoảng 1cm) ra lòng bàn tay và tạo bọt với nước. Nhẹ nhàng massage bọt lên mặt, sau đó rửa sạch với nước ấm.',
        'Sữa rửa mặt tạo bọt mịn, dày, nhẹ nhàng làm sạch sâu, cho làn da mềm mại, mịn màng, sẵn sàng cho các bước chăm sóc da tiếp theo.',
        NULL,
        'Water, Glycerin, Myristic Acid, Stearic Acid, Potassium Hydroxide, DPG, Sorbitol, Lauric Acid, Glyceryl Stearate SE, Sodium Cocoyl Methyl Taurate, Sodium Lauroamphoacetate, Glycol Distearate, Ceramide NP, Sodium Hyaluronate, Hydrolyzed Elastin, Moroccan Lava Clay, Bisabolol, Glucosylrutin, Aloe Barbadensis Leaf Extract, Tormentilla Officinalis Root Extract, Chamomilla Recutita (Matricaria) Flower Extract, Glycyrrhizic Acid 2K, Ectoin, Sodium Lauroyl Glutamate Lysine, Polyquaternium-7, BG, Tetrasodium EDTA',
        NULL, NULL, NULL),
       (3,
        'Lấy 2-3 lần bơm ra lòng bàn tay, thoa đều lên mặt sau khi đã làm sạch và cân bằng da. Thoa thêm sản phẩm lên những vùng da cần chăm sóc đặc biệt.',
        'Cung cấp độ ẩm sâu, cho làn da căng mọng và đàn hồi. Giúp cải thiện tình trạng da khô, nếp nhăn, chảy xệ.',
        'Face Gelatin Collagen*2, Sodium Hyaluronate, Hydrolyzed Elastin',
        'Water, BG, Glycerin, Pentylene Glycol, Squalane, Water-Soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, Sodium ẞ-Sitosterol Sulfate, Leontopodium Alpinum Flower Extract, Glycine Soja (Soybean) Seed Extract, Acetyl Hydroxyproline, Ceramide NG, Acetyl Decapeptide-3, Glucosyl Ceramide, Punica Granatum Fruit Extract, Magnesium Ascorbyl Phosphate, RNA-Na, Ectoin, Sodium Lauroyl Glutamate Lysine, Arginine, Trehalose, Aloe Barbadensis Leaf Extract, Houttuynia Cordata Extract, Eugenia Caryophyllus (Clove) Flower Extract, Pyrus Cydonia Seed Extract, Sea Salt, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, α-Glucan, Polyglyceryl-5 Trioleate, Polyglyceryl-10 Diisostearate, (Acrylates/Alkyl Acrylate (C10-30)) Crosspolymer, Carbomer, Sodium Citrate, Citric Acid, Potassium Hydroxide',
        NULL, NULL, NULL),
       (4,
        'Lấy một lượng vừa đủ (3-4 lần bơm) ra lòng bàn tay, thoa đều lên mặt sau khi đã sử dụng tinh chất.',
        'Nước hoa hồng dạng lotion mỏng nhẹ, thẩm thấu nhanh, cung cấp độ ẩm lâu dài cho làn da mềm mại, mịn màng và rạng rỡ. Sản phẩm giúp cải thiện kết cấu và độ trong suốt của da, mang đến làn da khỏe mạnh và tươi sáng. Sử dụng sau bước làm sạch, cân bằng da và tinh chất để đạt hiệu quả tối ưu.',
        'Biophospholipids (Hydrogenated Lecithin), Gelatin Collagen (Water-Soluble Collagen), Hydrolyzed Collagen, Phytosterols, ẞ-Sitosterol Sodium Sulfate',
        'Water, BG, Pentylene Glycol, Glycerin, Trehalose, Water-Soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, β-Sitosterol Sodium Sulfate, Acetyl Hydroxyproline, Glucosyl Ceramide, Prunus Domestica Fruit Extract, Punica Granatum Fruit Extract, Magnesium Ascorbyl Phosphate, RNA-Na, Ectoin, Sodium Lauroyl Glutamate Lysine, Aloe Barbadensis Leaf Extract, Eugenia Caryophyllus (Clove) Flower Extract, Arginine, Rosa Damascena Flower Water, Pyrus Cydonia Seed Extract, Houttuynia Cordata Extract, Sea Salt, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, a-Glucan, Polyglyceryl-5 Trioleate, Polyglyceryl-10 Diisostearate, (Acrylates/Alkyl Acrylate (C10-30)) Crosspolymer, Sodium Citrate, Citric Acid, Potassium Hydroxide',
        NULL, NULL, NULL),
       (5,
        'Sau khi thoa nước hoa hồng, lấy một lượng vừa đủ (2-3 lần bơm) ra lòng bàn tay, thoa đều lên mặt. Thoa thêm sản phẩm lên những vùng da cần chăm sóc đặc biệt.',
        'Kem dưỡng ẩm dạng gel thẩm thấu nhanh, cung cấp độ ẩm chuyên sâu, ngăn ngừa khô da và sần sùi. Sản phẩm mang đến làn da căng mọng, ẩm mịn và rạng rỡ.',
        'Face Gelatin Collagen*, Sodium Hyaluronate, Hydrolyzed Elastin',
        'Water, BG, Glycerin, Squalane, Behenyl Alcohol, Sorbitan Stearate, Polyglyceryl-10 Stearate, Glyceryl Stearate, Water-soluble Collagen, Hydrolyzed Collagen, Sodium Hyaluronate, Hydrolyzed Elastin, Hydrogenated Lecithin, Phytosterols, ẞ-Sitosterol Sodium Sulfate, Potentilla Erecta Root Extract, Acetyl Hydroxyproline, Ceramide NG, Caffeoyl Tetrapeptide-3, Acetyl Decapeptide-3, Centella Asiatica Extract, Magnesium Ascorbyl Phosphate, Ectoin, RNA-Na, Glucosyl Ceramide, Sodium Lauroyl Glutamate Lysine, Punica Granatum Fruit Extract, Trehalose, Arginine, α- Glucan, Mannitol, Sodium Carboxymethyl Dextran, Oleic Acid, Sphingomyelin, Sitosterol, Stearic Acid, Butyrospermum Parkii (Shea) Butter, Tocopheryl Acetate, Dextran, Diisostearyl Malate, Dimethicone, Ethylhexylglycerin, (Methyl Vinyl Ether/Maleic Acid) Crosspolymer, Sodium Citrate, Citric Acid, Potassium Hydroxide',
        NULL, NULL, NULL),
       (6,
        'Lắc đều trước khi sử dụng. Thoa đều lên mặt và cơ thể 15 phút trước khi tiếp xúc với ánh nắng mặt trời. Thoa lại sau mỗi hai giờ, đặc biệt là sau khi bơi, đổ mồ hôi hoặc lau bằng khăn.',
        'Cung cấp khả năng chống nắng phổ rộng SPF 40 PA+++, bảo vệ da khỏi tác hại của tia UVA và UVB, giúp ngăn ngừa cháy nắng, sạm da và lão hóa sớm. Không chứa chất hấp thụ tia UV, dịu nhẹ cho cả làn da nhạy cảm. Lớp nền tự nhiên, không gây trắng bệch, phù hợp với mọi tông da. Dưỡng ẩm và mang lại cảm giác thoải mái khi sử dụng, cho làn da ẩm mịn và được bảo vệ.',
        'Lauroyl Glutamate Di(Phytosteryl/Octyldodecyl), Magnesium Ascorbyl Phosphate, Houttuynia Cordata Extract, Geranium Thunbergii Extract, Origanum Majorana Leaf Extract, Houttuynia Cordata Extract, Saxifraga Sarmentosa Extract, Dipeptide-4, Citrus Junos Fruit Extract, Foeniculum Vulgare (Fennel) Fruit Extract',
        'Cyclopentasiloxane, Water, Zinc Oxide, Titanium Dioxide, BG, Isononyl Isononanoate, Dimethicone, Polyglyceryl-3 Polydimethylsiloxyethyl Dimethicone, Glycerin, PEG-9 Polydimethylsiloxyethyl Dimethicone, (Hydrogen Dimethicone/Octyldodecyl Silsesquioxane) Copolymer, Silica, Dipotassium Glycyrrhizate, Scutellaria Baicalensis Root Extract, Xanthophyll, Lauroyl Glutamate Di(Phytosteryl/Octyldodecyl), Magnesium Ascorbyl Phosphate, Hydrogenated Lecithin, Lonicera Japonica (Honeysuckle) Flower Extract, Geranium Thunbergii Extract, Origanum Majorana Leaf Extract, Houttuynia Cordata Extract, Saxifraga Sarmentosa Extract, Dipeptide-4, Citrus Junos Fruit Extract, Foeniculum Vulgare (Fennel) Fruit Extract, Lauroyl Glutamate Di(Phytosteryl/Octyldodecyl), Tocopherol, Phytosterols, Polyglyceryl-10 Stearate, Polyglyceryl-10 Myristate, (Dimethicone/Vinyl Dimethicone) Crosspolymer, Caprylic/Capric Triglyceride, (Dimethicone/(PEG-10/15)) Crosspolymer, Polyglyceryl-10 Oleate, Citric Acid, Carthamus Tinctorius (Safflower) Seed Oil, Zea Mays (Corn) Oil, Sodium Chloride, Sodium Citrate, Aluminum Hydroxide',
        NULL, NULL, NULL),
       (7,
        'Sau khi thoa kem lót, lấy một lượng vừa đủ ra đầu ngón tay hoặc bông mút. Thoa đều lên mặt, bắt đầu từ giữa mặt và tán đều ra ngoài. Để có lớp nền hoàn hảo và mịn màng hơn, hãy sử dụng bông mút Face Insist Lamellar Multi Blender Sponge. Dặm phấn phủ Face Insist Lamellar Powdery Foundation N hoặc Face Insist Lamellar Lucent Powder N để giữ lớp nền lâu trôi hơn và hoàn hảo hơn.',
        'Cung cấp độ che phủ từ trung bình đến cao, tùy thuộc vào nhu cầu của bạn. Tạo lớp nền tự nhiên, rạng rỡ, không gây bết dính hay nặng mặt. Dưỡng ẩm cho da và giúp cải thiện kết cấu da theo thời gian, nhờ các thành phần dưỡng ẩm. Giữ màu lâu trôi, thoải mái suốt cả ngày.',
        'Sodium Hyaluronate, Averrhoa Carambola Leaf Extract, Lauroyl Glutamate Di(Octyldodecyl/Phytosteryl/Behenyl), Sophora Flavescens Root Extract',
        'Water, Cyclopentasiloxane, Squalane, Glycerin, Pentylene Glycol, Diglycerin, PEG/PPG-19/19 Dimethicone, Enterococcus Faecalis, Averrhoa Carambola Leaf Extract, Leontopodium Alpinum Flower Extract, Sodium Hyaluronate, Hydrolyzed Collagen, Hydrolyzed Elastin, a-Glucan, Glucosyl Ceramide, Lauroyl Glutamate Di(Octyldodecyl/Phytosteryl/Behenyl), Ascorbyl Tetraisopalmitate, Sophora Flavescens Root Extract, Polyquaternium-51, BG, Tocopherol, Dipentaerythrityl Tri-Polyhydroxystearate, Trimethylsiloxysilicate, Sorbitan Isostearate, (Vinyl Dimethicone/Methicone Silsesquioxane) Crosspolymer, Hexa(Hydroxystearic Acid/Stearic Acid/Rosin Acid) Dipentaerythrityl, Aluminum Hydroxide, Hydrogen Dimethicone, Methicone, Magnesium Sulfate, Mica, (+/-) Titanium Dioxide, Synthetic Fluorphlogopite, Iron Oxides.',
        NULL, NULL, NULL);


--
-- Liệu trình Allure Spa
--

-- Chèn dữ liệu vào bảng treatment_categories
INSERT INTO treatment_categories (category_name, parent_id)
VALUES ('Facial - Chăm sóc da mặt', NULL),
       ('Massage', NULL),
       ('Giảm béo', NULL),
       ('Triệt lông', NULL),
       ('Chăm da Amino - Phù hợp mọi loại da', 1),
       ('Chống lão hoá - Photo', 1),
       ('Dưỡng trắng - Trị nám', 1),
       ('Collagen Tươi', 1),
       ('Peeling da', 1),
       ('Massage body', 2),
       ('Thải độc ruột - Massage nội tạng', 2),
       ('Massage Nâng cơ vòng 1', 2),
       ('Massage Cổ, Vai, Gáy', 2),
       ('Gội đầu chống rụng tóc kết hợp massage cổ, vai, gáy', 2),
       ('Xông hơi ngải cứu', 2),
       ('Xăm mày, môi nghệ thuật', 2),
       ('Giảm béo bụng', 3),
       ('Giảm béo eo', 3),
       ('Giảm béo mông', 3),
       ('Giảm béo tay', 3),
       ('Giảm béo chân', 3),
       ('Giảm béo toàn thân', 3),
       ('Triệt lông Bikini', 4),
       ('Triệt lông nách', 4),
       ('Triệt lông chân (nửa chân)', 4),
       ('Triệt lông chân (nguyên chân)', 4),
       ('Triệt lông tay', 4),
       ('Triệt lông mặt, ria mép', 4),
       ('Triệt lông toàn thân', 4);


-- Chèn dữ liệu vào bảng treatments
INSERT INTO treatments (category_id, treatment_name, description, duration, price)
VALUES (5, 'Chăm da Amino - Phù hợp mọi loại da', NULL, 60, 1350000),
       (6, 'Chống lão hoá - Photo',
        'Dịch vụ chăm sóc da mặt tại Allure Spa sử dụng công nghệ Photo Facial kết hợp với Tế bào gốc. Dải ánh sáng với tần suất phù hợp của công nghệ Photo facial kết hợp với tế bào gốc tại đặt trên mặt nạ Biocellulose với những sợi mịn cực nhỏ cấp độ 2-100 nano: giúp da hấp thu nhanh những hoạt chất giữ ẩm và hỗ trợ độ bám cho serum làm đẹp, thẩm thấu các thành phần làm đẹp và hiệu quả cho da.

Sự kết hợp hoàn hảo này mang lại hiệu quả bất ngờ:

Tác động sâu vào cấu trúc da, tiêu diệt hoàn toàn các ổ vi khuẩn gây mụn, nám… một cách nhẹ nhàng, không làm tổn thương, đau hay để lại sẹo.
Kích thích từng tế bào da, giúp các tế bào gốc được đưa sâu vào các mô bên dưới biểu bì, mang lại làn da khỏe khoắn, tươi trẻ từ bên trong
Đặc biệt khi áp dụng trong việc điều trị các vết nám, tàn nhang, mụn, da bị mẫn cảm… sẽ cho hiệu quả tức thì ngay từ lần đầu tiên!
Sau khi được nghệ nhân Nhật Bản tư vấn về quy trình, các kỹ thuật viên dưới sự giám sát của chuyên viên người Nhật, sẽ thực hiện quy trình gồm 6 bước:

Bước 1: Cleansing
Bước 2: Washing
Bước 3: Massage lưu thông hệ bạch huyết
Bước 4: Chăm sóc và phục hồi da với công nghệ PHOTO
Bước 5: Đi dưỡng chất tế bào gốc (BIONIC CELLCER)Bước 6: Đắp mặt nạ dưỡng trắng & massage thư giãn đầu, vai, cổ',
        45, 1350000),
       (7, 'Dưỡng trắng - Trị nám',
        'Dưỡng trắng da an toàn theo phương pháp FluorOxygen+C. Đây là liệu trình không làm da bị bong tróc mà từ từ trắng lên tự nhiên. Bạn sẽ cảm thấy làn da trắng mong manh như giọt sương mai nhưng vẫn giữ được độ dẻo dai và đàn hồi vốn có.
Phương pháp này ứng dụng kỹ thuật làm trắng sáng da bằng Oxy, Vitamin C và thành phần dưỡng trắng Bio-Light để giữ da trong mướt, mềm mịn hơn.
Vết nám, tàn nhang trên mặt có thể là nỗi tự ti của nhiều chị em. Đây là một dạng rối loạn sắc tố da do di truyền hoặc bởi tác động từ môi trường bên ngoài làm da bị ảnh hưởng. Nguyên nhân chủ yếu là do các sắc tố melanin (sắc tố khiến da đen sạm) tăng lên quá mức khiến da hình thành các đốm nâu nhạt, nâu đen hoặc đỏ, dần tạo thành nám và tàn nhang.

Phương pháp làm mờ tàn nhang tại Allure áp dụng liệu trình chăm sóc da bài bản, thực tế từ phụ nữ Nhật Bản. Trong đó, công nghệ Photo Facial – sử dụng ánh sáng với tần suất phù hợp được phối hợp thực hiện nhằm mang lại hiệu quả tốt nhất cho làn da.',
        90, 1800000),
       (8, 'Collagen Tươi', NULL, 55, 1250000),
       (9, 'Peeling da', NULL, 70, 2500000),
       (10, 'Massage body', NULL, 60, 390000),
       (11, 'Thải độc ruột - Massage nội tạng', NULL, 60, 750000),
       (12, 'Massage Nâng cơ vòng 1', NULL, 60, 950000),
       (13, 'Massage Cổ, Vai, Gáy', NULL, 60, 350000),
       (14, 'Gội đầu chống rụng tóc kết hợp massage cổ, vai, gáy', NULL, 45, 160000),
       (15, 'Xông hơi ngải cứu', NULL, 60, 400000),
       (16, 'Xăm mày, môi nghệ thuật', 'Xăm mày + môi + áp dụng cho 2 lần làm', NULL, 4000000),
       (17, 'Giảm béo bụng', NULL, NULL, 600000),
       (18, 'Giảm béo eo', NULL, NULL, 400000),
       (19, 'Giảm béo mông', NULL, NULL, 400000),
       (20, 'Giảm béo tay', NULL, NULL, 400000),
       (21, 'Giảm béo chân', NULL, NULL, 600000),
       (22, 'Giảm béo toàn thân', NULL, NULL, 2400000),
       (23, 'Triệt lông Bikini', NULL, NULL, 800000),
       (24, 'Triệt lông nách', NULL, NULL, 250000),
       (25, 'Triệt lông chân (nửa chân)', NULL, NULL, 350000),
       (26, 'Triệt lông chân (nguyên chân)', NULL, NULL, 700000),
       (27, 'Triệt lông tay', NULL, NULL, 450000),
       (28, 'Triệt lông mặt, ria mép', NULL, NULL, 250000),
       (29, 'Triệt lông toàn thân', NULL, NULL, 2200000);


-- Chèn dữ liệu vào bảng treatment_combos
INSERT INTO treatment_combos (treatment_id, duration, combo_type, combo_price, is_default, validity_period)
SELECT id,
       duration,
       '5_times',
       CASE WHEN price * 5 * 0.8 > 0 THEN price * 5 * 0.8 END,
       1,
       NULL
FROM treatments;

INSERT INTO treatment_combos (treatment_id, duration, combo_type, combo_price, is_default, validity_period)
SELECT id,
       duration,
       '10_times',
       CASE WHEN price * 10 * 0.7 > 0 THEN price * 10 * 0.7 END,
       0,
       NULL
FROM treatments;
