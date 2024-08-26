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
    label: 'Sample menu',
    menu: [
      {
        icon: mdiClockOutline,
        label: 'Item One'
      },
      {
        icon: mdiCloud,
        label: 'Item Two'
      },
      {
        isDivider: true
      },
      {
        icon: mdiCrop,
        label: 'Item Last'
      }
    ]
  },
  {
    isCurrentUser: true,
    menu: [
      {
        icon: mdiAccount,
        label: 'My Profile',
        route: '/profile'
      },
      {
        icon: mdiCogOutline,
        label: 'Settings'
      },
      {
        icon: mdiEmail,
        label: 'Messages'
      },
      {
        isDivider: true
      },
      {
        icon: mdiLogout,
        label: 'Log Out',
        isLogout: true
      }
    ]
  },
  {
    icon: mdiThemeLightDark,
    label: 'Light/Dark',
    isDeskroutepNoLabel: true,
    isrouteggleLightDark: true
  },
  {
    icon: mdiGithub,
    label: 'GitHub',
    isDeskroutepNoLabel: true,
    href: 'https://github.com/justboil/admin-one-vue-tailwind',
    target: '_blank'
  },
  {
    icon: mdiReact,
    label: 'React version',
    isDeskroutepNoLabel: true,
    href: 'https://github.com/justboil/admin-one-react-tailwind',
    target: '_blank'
  },
  {
    icon: mdiLogout,
    label: 'Log out',
    isDeskroutepNoLabel: true,
    isLogout: true
  }
]
