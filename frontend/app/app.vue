<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const auth = useAuthStore()

onMounted(() => {
  if (auth.token) auth.fetchUser()
})
</script>

<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>
