import {
    mdiAccountCircle,
    mdiMonitor, // Sửa từ mdiMonirouter thành mdiMonitor
    mdiGithub,
    mdiLock,
    mdiAlertCircle,
    mdiSquareEditOutline, // Sửa từ mdiSquareEdirouteutline thành mdiSquareEditOutline
    mdiTable,
    mdiViewList,
    mdiTelevisionGuide,
    mdiResponsive,
    mdiPalette,
    mdiReact
} from '@mdi/js'

export default [
    {
        route: 'login.store',
    },
    {
        route: '/dashboard',
        icon: mdiMonitor, // Sử dụng mdiMonitor ở đây
        label: 'Dashboard'
    },
    {
        route: '/tables',
        label: 'Tables',
        icon: mdiTable
    },
    {
        route: '/forms',
        label: 'Forms',
        icon: mdiSquareEditOutline // Sử dụng mdiSquareEditOutline ở đây
    },
    {
        route: '/ui',
        label: 'UI',
        icon: mdiTelevisionGuide
    },
    {
        route: '/responsive',
        label: 'Responsive',
        icon: mdiResponsive
    },
    {
        route: '/',
        label: 'Styles',
        icon: mdiPalette
    },
    {
        route: 'profile',  // Thay đổi từ '/profile' thành 'profile'
        label: 'Profile',
        icon: mdiAccountCircle
    },
    {
        route: '/login',
        label: 'Login',
        icon: mdiLock
    },
    {
        route: '/error',
        label: 'Error',
        icon: mdiAlertCircle
    },
    {
        label: 'Dropdown',
        icon: mdiViewList,
        menu: [
            {
                label: 'Item One'
            },
            {
                label: 'Item Two'
            }
        ]
    },
    {
        href: 'https://github.com/justboil/admin-one-vue-tailwind',
        label: 'GitHub',
        icon: mdiGithub,
        target: '_blank'
    },
    {
        href: 'https://github.com/justboil/admin-one-react-tailwind',
        label: 'React version',
        icon: mdiReact,
        target: '_blank'
    }
]
