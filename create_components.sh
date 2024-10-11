#!/bin/bash

# Danh sách đầy đủ các model cần tạo (tương ứng với các bảng trong cơ sở dữ liệu)
models=(
    "User"
    "ProductCategory"
    "Product"
    "ProductDetail"
    "ProductPriceHistory"
    "Attribute"
    "ProductAttribute"
    "TreatmentCategory"
    "Treatment"
    "TreatmentPriceHistory"
    "TreatmentCombo"
    "TreatmentComboPriceHistory"
    "StaffDetail"
    "Address"
    "UserTreatmentPackage"
    "TreatmentUsageHistory"
    "Voucher"
    "EmployeeAttendance"
    "PaymentMethod"
    "Order"
    "OrderItem"
    "Cart"
    "CartItem"
    "Invoice"
    "InvoicePayment"
    "Rating"
    "Favorite"
    "Notification"
    "Appointment"
    "UserVoucher"
    "History"
    "FcmToken"
    "StockMovement"
    "PaymentHistory"
    "Image"
    "RewardItem"
    "PointRedemptionHistory"
    "ProductTranslation"
    "TreatmentTranslation"
    "ProductCategoryTranslation"
    "AttributeTranslation"
    "TreatmentCategoryTranslation"
    "VoucherTranslation"
    "RewardItemTranslation"
    "Banner"
    "AiChatConfig"
    "Chat"
    "ChatMessage"
)

# Danh sách các controller chính cần tạo
main_controllers=(
    "User"
    "Product"
    "Treatment"
    "Order"
    "Cart"
    "Invoice"
    "Appointment"
    "Voucher"
    "Rating"
    "Notification"
    "Chat"
)

# Tạo models và services
for model in "${models[@]}"
do
    # Tạo model (không có migration)
    php artisan make:model $model

    # Tạo service
    mkdir -p app/Services
    cat > app/Services/${model}Service.php << EOL
<?php

namespace App\Services;

use App\Models\\$model;

class ${model}Service
{
    // Add your service methods here
}
EOL

    echo "Created model and service for $model"
done

# Tạo controllers cho web và API
for controller in "${main_controllers[@]}"
do
    # Tạo controller cho web
    php artisan make:controller ${controller}Controller --resource

    # Tạo controller cho API
    php artisan make:controller Api/${controller}Controller --api

    echo "Created web and API controllers for $controller"
done

echo "All components have been created!"