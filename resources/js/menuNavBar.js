import {
  mdiWeatherSunny,
  mdiBell
} from '@mdi/js'

export default [
  {
    isCurrentUser: true,
  },
  {
    icon: mdiWeatherSunny,
    label: 'Giao diện',
    isToggleLightDark: true
  },
  {
    icon: mdiBell,
    label: 'Thông báo',
    isDesktopNoLabel: false,
    menu: [],
    isNotification: true
  }
]
