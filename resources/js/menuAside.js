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
                route: 'invoices.create'
            },
            {
                label: 'Danh sách đơn hàng',
                route: 'invoices.index'
            },
        ]
    },
    {
        label: 'Quản lý mỹ phẩm',
        icon: mdiCube,
        menu: [
            {
                label: 'Tất cả',
                route: 'products.index'
            },
            {
                label: 'Bộ FAITH',
                route: 'products.faith'
            },
        ]
    },
    {
        label: 'Quản lý liệu trình',
        icon: mdiListBox,
        menu: [
            {
                label: 'Tất cả',
                route: 'treatments.index'
            },
            {
                label: 'Giảm béo',
                route: 'treatments.reduceFat'
            },
            {
                label: 'Massage',
                route: 'treatments.massage'
            },
            {
                label: 'Facial',
                route: 'treatments.facial'
            },
            {
                label: 'Triệt lông',
                route: 'treatments.hairRemoval'
            },
        ]
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
                label: 'Nhân viên',
                route: 'staff.index'
            },
            {
                label: 'Bảng lương',
                route: 'staff.salary'
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
                label: 'Chat',
                route: 'mobileapp.chat'
            },
            {
                label: 'Banner App',
                route: 'mobileapp.banners'
            },
            {
                label: 'Hỗ trợ trực tuyến',
                route: 'mobileapp.support'
            },
        ]
    },
    {
        label: 'Tạo báo cáo',
        icon: mdiFileDocument,
        route: 'reports.index'
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
