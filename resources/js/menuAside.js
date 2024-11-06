import {
    mdiAccountCircle,
    mdiMonitor,
    mdiTable,
    mdiAccountGroup,
    mdiCalendar,
    mdiFacebook,
    mdiBallot,
    mdiCube,
    mdiListBox,
    mdiAccountMultiple,
    mdiBell,
    mdiChat,
    mdiFileDocument,
    mdiCellphone,
    mdiRobot,
} from '@mdi/js'

export default [
    {
        route: 'dashboard',
        icon: mdiMonitor,
        label: 'Tổng quan'
    },
    {
        label: 'Lịch hẹn',
        icon: mdiCalendar,
        route: 'appointments.index'
    },
    {
        label: 'Quản lý khách hàng',
        icon: mdiAccountGroup,
        route: 'users.index'
    },
    {
        label: 'Quản lý hoá đơn',
        icon: mdiBallot,
        menu: [
            {
                label: 'Tạo hoá đơn mới',
                route: 'invoices.create'
            },
            {
                label: 'Danh sách hoá đơn',
                route: 'invoices.index'
            },
        ]
    },
    {
        label: 'Quản lý mỹ phẩm',
        icon: mdiCube,
        route: 'products.index'
    },
    {
        label: 'Quản lý liệu trình',
        icon: mdiListBox,
        route: 'services.index'
    },
    {
        label: 'Quản lý kho hàng',
        icon: mdiTable,
        menu: [
            {
                label: 'Nhập hàng',
                route: 'stock-movements.imports'
            },
            {
                label: 'Xuất hàng',
                route: 'stock-movements.exports'
            },
        ]
    },
    {
        label: 'Quản lý nhân viên',
        icon: mdiAccountMultiple,
        menu: [
            {
                label: 'Đang xây dựng...',
            },
        ]
    },
    {
        label: 'Mobile App',
        icon: mdiCellphone,
        menu: [
            {
                label: 'Thông báo',
                route: 'notifications.index'
            },
            {
                label: 'Chat - Hỗ trợ trực tuyến',
                route: 'chats.index'
            },
            {
                label: 'Banner App',
                route: 'mobileapp.banners'
            },
            {
                label: 'Cấu hình AI',
                route: 'ai-config.index',
                icon: mdiRobot
            },
        ]
    },
    {
        label: 'Tạo báo cáo',
        icon: mdiFileDocument,
        menu: [
            {
                label: 'Báo cáo doanh thu',
                route: 'reports.revenue'
            },
            {
                label: 'Báo cáo lợi nhuận',
                route: 'reports.profit'
            },
            {
                label: 'Báo cáo khách hàng',
                route: 'reports.customers'
            },
            {
                label: 'Báo cáo hàng tồn kho',
                route: 'reports.stock'
            },
            {
                label: 'Báo cáo nhân viên',
                route: 'reports.staff'
            },
            {
                label: 'Báo cáo lịch hẹn',
                route: 'reports.appointments'
            },
            {
                label: 'Báo cáo hoá đơn',
                route: 'reports.invoices'
            },
            {
                label: 'Báo cáo sử dụng AI',
                route: 'reports.ai'
            },
        ]
    },
    {
        label: 'Hồ sơ của tôi',
        route: 'users.profile',
        icon: mdiAccountCircle
    },
    {
        href: 'https://www.facebook.com/AllureJapanEsthetic',
        label: 'Facebook',
        icon: mdiFacebook,
        target: '_blank'
    },
]
