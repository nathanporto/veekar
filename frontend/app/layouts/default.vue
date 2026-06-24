<script setup lang="ts">
import { useSubscriptionStore } from '~/stores/subscription'
import { useAuthStore } from '~/stores/auth'

const auth = useAuthStore()
const subscriptionStore = useSubscriptionStore()

onMounted(async () => {
  if (auth.isAuthenticated) {
    await subscriptionStore.fetchStatus()
  }
})
</script>

<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">
    <AppSidebar />

    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <AppHeader />

      <!-- Banner trial -->
      <div
        v-if="subscriptionStore.isTrial"
        class="bg-amber-50 border-b border-amber-200 px-6 py-2 flex items-center justify-between gap-4 flex-shrink-0"
      >
        <div class="flex items-center gap-3 text-sm text-amber-800">
          <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>
            Período gratuito —
            <strong>{{ subscriptionStore.trialCustomersLeft }} cliente(s)</strong> e
            <strong>{{ subscriptionStore.trialVehiclesLeft }} veículo(s)</strong> restantes
          </span>
        </div>
        <NuxtLink
          to="/assinatura"
          class="text-xs font-semibold text-amber-700 hover:text-amber-900 bg-amber-100 hover:bg-amber-200 px-3 py-1 rounded-full transition-colors whitespace-nowrap"
        >
          Assinar agora
        </NuxtLink>
      </div>

      <!-- Banner expirado -->
      <div
        v-else-if="subscriptionStore.isExpired"
        class="bg-red-50 border-b border-red-200 px-6 py-2 flex items-center justify-between gap-4 flex-shrink-0"
      >
        <div class="flex items-center gap-3 text-sm text-red-800">
          <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <span>Sua assinatura expirou. Os dados estão preservados, mas novos cadastros estão bloqueados.</span>
        </div>
        <NuxtLink
          to="/assinatura"
          class="text-xs font-semibold text-red-700 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-full transition-colors whitespace-nowrap"
        >
          Renovar agora
        </NuxtLink>
      </div>

      <main class="flex-1 overflow-y-auto p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
