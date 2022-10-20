<template>
  <div class="common-layout">
    <el-container>
      <el-aside width="200px">
        <el-menu
          default-active="2"
          class="el-menu-vertical-demo"
          :collapse="false"
          @mouseenter="handleMouseEnter"
          @mouseleave="handleMouseLeave"
        >
          <el-menu-item index="1">
            <el-icon><Histogram /></el-icon>
            <template #title><Link :href="`/`">Bot Information</Link></template>
          </el-menu-item>
<!--          <el-sub-menu index="1">-->
<!--            <template #title>-->
<!--&lt;!&ndash;              <el-icon><location /></el-icon>&ndash;&gt;-->
<!--              <span>BOT Information</span>-->
<!--            </template>-->
<!--            <el-menu-item-group>-->
<!--              <template #title><span>Curr</span></template>-->
<!--              <el-menu-item index="1-1">item one</el-menu-item>-->
<!--              <el-menu-item index="1-2">item two</el-menu-item>-->
<!--            </el-menu-item-group>-->
<!--            <el-menu-item-group title="Group Two">-->
<!--              <el-menu-item index="1-3">item three</el-menu-item>-->
<!--            </el-menu-item-group>-->
<!--            <el-sub-menu index="1-4">-->
<!--              <template #title><span>item four</span></template>-->
<!--              <el-menu-item index="1-4-1">item one</el-menu-item>-->
<!--            </el-sub-menu>-->
<!--          </el-sub-menu>-->
          <el-menu-item index="2">
            <el-icon><Avatar /></el-icon>
            <template #title><Link :href="`/users/${auth.user.id}/edit`">My Profile</Link></template>
          </el-menu-item>
<!--          <el-menu-item index="3">-->
<!--            <template #title><Link href="/users">Manage Users</Link></template>-->
<!--          </el-menu-item>-->
          <el-menu-item index="3">
            <el-icon><Avatar /></el-icon>
            <template #title><Link href="/logout" method="delete" as="button">Logout</Link></template>
          </el-menu-item>
        </el-menu>
      </el-aside>
      <el-container>
        <el-header>Header</el-header>
        <el-main><slot /></el-main>
        <el-footer>Footer</el-footer>
      </el-container>
    </el-container>
  </div>
  <!-- <div>
    <div id="dropdown" />
    <div class="md:flex md:flex-col">
      <div class="md:flex md:flex-col md:h-screen">
        <div class="md:flex md:flex-shrink-0">
          <div class="flex items-center justify-between px-6 py-4 bg-indigo-900 md:flex-shrink-0 md:justify-center md:w-56">
            <Link class="mt-1" href="/">
              <logo class="fill-white" width="120" height="28" />
            </Link>
            <dropdown class="md:hidden" placement="bottom-end">
              <template #default>
                <svg class="w-6 h-6 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" /></svg>
              </template>
              <template #dropdown>
                <div class="mt-2 px-8 py-4 bg-indigo-800 rounded shadow-lg">
                  <main-menu />
                </div>
              </template>
            </dropdown>
          </div>
          <div class="md:text-md flex items-center justify-between p-4 w-full text-sm bg-white border-b md:px-12 md:py-0">
            <div class="mr-4 mt-1">{{ auth.user.account.name }}</div>
            <dropdown class="mt-1" placement="bottom-end">
              <template #default>
                <div class="group flex items-center cursor-pointer select-none">
                  <div class="mr-1 text-gray-700 group-hover:text-indigo-600 focus:text-indigo-600 whitespace-nowrap">
                    <span>{{ auth.user.first_name }}</span>
                    <span class="hidden md:inline">&nbsp;{{ auth.user.last_name }}</span>
                  </div>
                  <icon class="w-5 h-5 fill-gray-700 group-hover:fill-indigo-600 focus:fill-indigo-600" name="cheveron-down" />
                </div>
              </template>
              <template #dropdown>
                <div class="mt-2 py-2 text-sm bg-white rounded shadow-xl">

                </div>
              </template>
            </dropdown>
          </div>
        </div>
        <div class="md:flex md:flex-grow md:overflow-hidden">
          <main-menu class="hidden flex-shrink-0 p-12 w-56 bg-indigo-800 overflow-y-auto md:block" />
          <div class="px-4 py-8 md:flex-1 md:p-12 md:overflow-y-auto" scroll-region>
            <flash-messages />
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div> -->
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'
import { Avatar, Histogram } from '@element-plus/icons-vue'
export default {
  components: {
    Link,
    Avatar,
    Histogram,
  },
  data() {
    return {
      timeoutId: null,
      collapsed: true,
    }
  },
  props: {
    auth: Object,
  },
  methods: {
    handleMouseLeave () {
      this.collapsed = true;
      // Clear the timeout
      if (this.timeoutId) {
        clearTimeout(this.timeoutId);
      }
    },
    handleMouseEnter () {
      this.collapsed = false;
      // Set the sidebar to open after Xms
      this.timeoutId = setTimeout(() => {
        this.timeoutId = null;
      },
      300)
    },
  },
}
</script>

<style>
.common-layout {
  height: 100%;
}
.el-menu-vertical-demo:not(.el-menu--collapse) {
  width: 200px;
  min-height: 100vh;
}
</style>
