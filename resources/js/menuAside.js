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
} from '@mdi/js'

export default [
    {
        route: 'login.store',
    },
    {
        route: '/dashboard',
        icon: mdiMonitor,
        label: 'Tổng quan'
    },
    {
        label: 'Lịch hẹn',
        icon: mdiCalendar,
        route: '/appointments'
    },
    {
        label: 'Quản lý khách hàng',
        icon: mdiAccountGroup,
        route: '/customers'
    },
    {
        label: 'Quản lý đơn hàng',
        icon: mdiBallot,
        menu: [
            {
                label: 'Đơn hàng mới',
                route: '/orders/new'
            },
            {
                label: 'Danh sách đơn hàng',
                route: '/orders'
            },
        ]
    },
    {
        label: 'Quản lý mỹ phẩm',
        icon: mdiCube,
        menu: [
            {
                label: 'Tất cả',
                route: '/products'
            },
            {
                label: 'Bộ FAITH',
                route: '/products/faith'
            },
            {
                label: 'Mỹ phẩm khác',
                route: '/products/other'
            },
        ]
    },
    {
        label: 'Quản lý liệu trình',
        icon: mdiListBox,
        menu: [
            {
                label: 'Tất cả',
                route: '/procedures'
            },
            {
                label: 'Giảm béo',
                route: '/procedures/reduce-fat'
            },
            {
                label: 'Massage',
                route: '/procedures/massage'
            },

        ]
    },
    {
        label: 'Quản lý kho hàng',
        icon: mdiTable,
        menu: [
            {
                label: 'Nhập hàng',
                route: '/warehouses/imports'
            },
            {
                label: 'Xuất hàng',
                route: '/warehouses/exports'
            },
        ]
    },
    {
        label: 'Quản lý nhân viên',
        icon: mdiAccountMultiple,
        menu: [
            {
                label: 'Nhân viên',
                route: '/employees'
            },
            {
                label: 'Bảng lương',
                route: '/salaries'
            },
            {
                label: 'Phân quyền',
                route: '/roles'
            },
        ]
    },
    {
        label: 'Mobile App',
        icon: mdiCellphone,
        menu: [
            {
                label: 'Thông báo',
                route: '/notifications'
            },
            {
                label: 'Chat',
                route: '/chat'
            },
            {
                label: 'Banner App',
                route: '/banners'
            },
            {
                label: 'Hỗ trợ trực tuyến',
                route: '/support'
            },
        ]
    },
    {
        label: 'Tạo báo cáo',
        icon: mdiFileDocument,
        route: '/reports'
    },
    {
        label: 'Hồ sơ của tôi',
        route: '/profile',
        icon: mdiAccountCircle
    },
    {
        href: 'https://www.facebook.com/AllureJapanEsthetic',
        label: 'Facebook',
        icon: mdiFacebook,
        target: '_blank'
    },
]
