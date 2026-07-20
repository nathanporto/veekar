<script setup lang="ts">
definePageMeta({ layout: 'auth' })

const route = useRoute()
const token = route.query.token as string
const email = route.query.email as string
const config = useRuntimeConfig()
const authCookie = useCookie('veekar_token')

const status = ref<'loading' | 'success' | 'error'>('loading')
const errorMsg = ref('')

onMounted(async () => {
  if (!token || !email) {
    status.value = 'error'
    errorMsg.value = 'Link inválido.'
    return
  }

  try {
    const res = await fetch(`${config.public.apiBase}/auth/verify-email`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ token, email }),
    })

    const data = await res.json()

    if (!res.ok) {
      status.value = 'error'
      errorMsg.value = data.message ?? 'Link inválido ou expirado.'
      return
    }

    authCookie.value = data.token
    status.value = 'success'
    window.fbq?.('trackCustom', 'EmailVerified')

    setTimeout(() => navigateTo('/dashboard'), 2000)
  } catch {
    status.value = 'error'
    errorMsg.value = 'Erro ao verificar. Tente novamente.'
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 max-w-md w-full text-center">

      <!-- Loading -->
      <div v-if="status === 'loading'">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-2xl mb-6">
          <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Verificando seu e-mail...</h1>
      </div>

      <!-- Sucesso -->
      <div v-else-if="status === 'success'">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-50 rounded-2xl mb-6">
          <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">E-mail confirmado!</h1>
        <p class="text-gray-500 text-sm">Redirecionando para o dashboard...</p>
      </div>

      <!-- Erro -->
      <div v-else>
        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-50 rounded-2xl mb-6">
          <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Link inválido</h1>
        <p class="text-gray-500 text-sm mb-6">{{ errorMsg }}</p>
        <NuxtLink to="/register" class="text-blue-600 hover:underline text-sm">Criar nova conta</NuxtLink>
      </div>

    </div>
  </div>
</template>
