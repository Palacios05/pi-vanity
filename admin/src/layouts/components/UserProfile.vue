<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import avatar1 from '@images/avatars/avatar-1.png'

const user = localStorage.getItem("user") ? JSON.parse(localStorage.getItem("user")) : null;
const router = useRouter();
const logout = async() => {
  localStorage.removeItem("user");
  localStorage.removeItem("token");

  // await router.push("/login");
  window.location.reload();
}
</script>

<template>
  <VBadge
    dot
    bordered
    location="bottom right"
    offset-x="2"
    offset-y="2"
    color="success"
    class="user-profile-badge"
  >
    <VAvatar
      class="cursor-pointer"
      size="38"
    >
      <VImg :src="user && user.avatar ? user.avatar : avatar1" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="15px"
      >
        <VList>
          <VListItem class="px-4">
            <div class="d-flex gap-x-2 align-center" v-if="user">
              <VAvatar>
                <VImg :src="user.avatar ? user.avatar : avatar1" />
              </VAvatar>

              <div>
                <div class="text-body-2 font-weight-medium text-high-emphasis">
                  {{ user.full_name }}
                </div>
                <div class="text-capitalize text-caption text-disabled">
                  {{ user.role ? user.role.name : 'Sin rol' }}
                </div>
              </div>
            </div>
          </VListItem>

          <VDivider class="my-1" />

          <VListItem class="px-4">
            <VBtn
              block
              color="error"
              size="small"
              append-icon="ri-logout-box-r-line"
              @click="logout"
            >
              Cerrar sesión
            </VBtn>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>

<style lang="scss">
.user-profile-badge {
  &.v-badge--bordered.v-badge--dot .v-badge__badge::after {
    color: rgb(var(--v-theme-background));
  }
}
</style>