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
    mdiFileDocumentPlus,
    mdiImport,
    mdiExport,
    mdiChartBar,
    mdiViewCarousel,
    mdiPackageVariant,
    mdiStar,
    mdiTicketPercent,
    mdiGift,
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
        label: 'Quản lý đơn hàng',
        icon: mdiBallot,
        menu: [
            {
                label: 'Đơn hàng mới',
                route: 'orders.create',
                icon: mdiFileDocumentPlus
            },
            {
                label: 'Danh sách đơn hàng',
                route: 'orders.index',
                icon: mdiBallot
            },
        ]
    },
    {
        label: 'Danh sách hoá đơn',
        icon: mdiBallot,
        route: 'invoices.index',
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
        route: 'stock-movements.index',
    },
    {
        label: 'Quản lý nhân viên',
        icon: mdiAccountMultiple,
        route: 'staff.index',
    },
    {
        label: 'Mobile App',
        icon: mdiCellphone,
        menu: [
            {
                label: 'Quản lý thông báo',
                route: 'notifications.manager',
                icon: mdiBell
            },
            {
                label: 'Chat - Hỗ trợ trực tuyến',
                route: 'chats.index',
                icon: mdiChat
            },
            {
                label: 'Banner App',
                route: 'banners.web',
                icon: mdiViewCarousel
            },
            {
                label: 'Cấu hình AI',
                route: 'ai-config.index',
                icon: mdiRobot
            },
            {
                label: 'Quản lý nhóm người dùng',
                route: 'user-groups.index',
                icon: mdiAccountGroup
            },
        ]
    },
    {
        label: 'Quản lý đánh giá',
        icon: mdiStar,
        route: 'ratings.index'
    },
    {
        label: 'Quản lý Voucher',
        icon: mdiTicketPercent,
        route: 'vouchers.index'
    },
    {
        label: 'Quản lý Rewards',
        icon: mdiGift,
        route: 'rewards.index'
    },
    {
        label: 'Tạo báo cáo',
        icon: mdiFileDocument,
        menu: [
            {
                label: 'Báo cáo doanh thu',
                route: 'reports.revenue',
                icon: mdiChartBar
            },
            {
                label: 'Báo cáo lợi nhuận',
                route: 'reports.profit',
                icon: mdiChartBar
            },
            {
                label: 'Báo cáo khách hàng',
                route: 'reports.customers',
                icon: mdiAccountGroup
            },
            {
                label: 'Báo cáo hàng tồn kho',
                route: 'reports.stock',
                icon: mdiTable
            },
            {
                label: 'Báo cáo nhân viên',
                route: 'reports.staff',
                icon: mdiAccountMultiple
            },
            {
                label: 'Báo cáo lịch hẹn',
                route: 'reports.appointments',
                icon: mdiCalendar
            },
            {
                label: 'Báo cáo hoá đơn',
                route: 'reports.invoices',
                icon: mdiFileDocument
            },
            {
                label: 'Báo cáo sử dụng AI',
                route: 'reports.ai',
                icon: mdiRobot
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
