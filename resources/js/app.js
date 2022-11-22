import { createApp, h } from 'vue'
import { InertiaProgress } from '@inertiajs/progress'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import ElementPlus from 'element-plus'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    wsHost: '127.0.0.1',
    wsPort: 6001,
    forceTLS: false,
    disableStats: true
    // key: 'ff10b5d5d37dbbf18920',
    // cluster: 'eu',
    // wsHost: '127.0.0.1',
    // wsPort: 6001,
    // forceTLS: true
});

InertiaProgress.init()

createInertiaApp({
  resolve: name => require(`./Pages/${name}`),
  title: title => title ? `${title} - BOT` : 'BOT',
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ElementPlus)
      .mount(el)
  },
})
