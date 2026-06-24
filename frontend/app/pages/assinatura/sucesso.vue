<script setup lang="ts">
import { useSubscriptionStore } from '~/stores/subscription'

const subscriptionStore = useSubscriptionStore()
const route = useRoute()
const verifying = ref(true)
const success = ref(false)
const error = ref('')

onMounted(async () => {
  const sessionId = route.query.session_id as string | undefined

  if (!sessionId) {
    error.value = 'Session ID não encontrado.'
    verifying.value = false
    return
  }

  try {
    const api = useApi()
    await api.get(`/subscription/verify-session?session_id=${sessionId}`)
    await subscriptionStore.fetchStatus()
    success.value = true
  } catch {
    error.value = 'Não foi possível confirmar o pagamento. Se foi cobrado, entre em contato com o suporte.'
  } finally {
    verifying.value = false
  }
})
</script>

<template>
  <div class="max-w-md mx-auto mt-16 text-center space-y-6">
    <!-- Verificando -->
    <template v-if="verifying">
      <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto">
        <svg class="w-8 h-8 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>
      <p class="text-gray-500">Confirmando pagamento...</p>
    </template>

    <!-- Sucesso -->
    <template v-else-if="success">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
        <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <div>
        <h1 class="text-2xl font-bold text-gray-900">Pagamento confirmado!</h1>
        <p class="text-gray-500 mt-2">
          Sua assinatura do Veekar Premium está ativa. Acesso ilimitado liberado!
        </p>
      </div>

      <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
        Clientes, veículos e atendimentos <strong>ilimitados</strong> liberados.
      </div>

      <NuxtLink
        to="/dashboard"
        class="inline-block px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors"
      >
        Ir para o Dashboard
      </NuxtLink>
    </template>

    <!-- Erro -->
    <template v-else>
      <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
        <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">Não foi possível confirmar</h1>
      <p class="text-red-600 text-sm">{{ error }}</p>
      <NuxtLink to="/assinatura" class="inline-block px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
        Voltar para Plano
      </NuxtLink>
    </template>
  </div>
</template>
