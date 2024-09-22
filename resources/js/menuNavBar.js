import {
  mdiMenu,
  mdiClockOutline,
  mdiCloud,
  mdiCrop,
  mdiAccount,
  mdiCogOutline,
  mdiEmail,
  mdiLogout,
  mdiThemeLightDark,
  mdiGithub,
  mdiReact
} from '@mdi/js'

export default [
  {
    icon: mdiMenu,  
    label: 'Menu mẫu',
    menu: [
      {
        icon: mdiClockOutline,
        label: 'Mục Một'
      },
      {
        icon: mdiCloud,
        label: 'Mục Hai'
      },
      {
        isDivider: true
      },
      {
        icon: mdiCrop,
        label: 'Mục Cuối'
      }
    ]
  },
  {
    isCurrentUser: true,
    menu: [
      {
        icon: mdiAccount,
        label: 'Hồ sơ',
        route: 'profile'
      },
      {
        icon: mdiCogOutline,
        label: 'Cài đặt'
      },
      {
        icon: mdiEmail,
        label: 'Tin nhắn'
      },
      {
        isDivider: true
      },
      {
        icon: mdiLogout,
        label: 'Đăng xuất',
        isLogout: true
      }
    ]
  },
  {
    icon: mdiThemeLightDark,
    label: 'Sáng/Tối',
    isDesktopNoLabel: true,
    isToggleLightDark: true
  },
  // {
  //   icon: mdiGithub,
  //   label: 'GitHub',
  //   isDesktopNoLabel: true,
  //   href: 'https://github.com/justboil/admin-one-vue-tailwind',
  //   target: '_blank'
  // },
  // {
  //   icon: mdiReact,
  //   label: 'Phiên bản React',
  //   isDesktopNoLabel: true,
  //   href: 'https://github.com/justboil/admin-one-react-tailwind',
  //   target: '_blank'
  // },
  {
    icon: mdiLogout,
    label: 'Đăng xuất',
    isDesktopNoLabel: true,
    isLogout: true
  }
]
