<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'auth' })

const route = useRoute()
const auth = useAuthStore()
const email = route.query.email as string | undefined

const sending = ref(false)
const sent = ref(false)

async function resend() {
  if (!email || sending.value) return
  sending.value = true
  try {
    await auth.resendVerification(email)
    sent.value = true
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 max-w-md w-full text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-2xl mb-6">
        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>

      <h1 class="text-2xl font-bold text-gray-900 mb-3">Confirme seu e-mail</h1>
      <p class="text-gray-500 text-sm leading-relaxed mb-2">
        Enviamos um link de confirmação para
        <strong v-if="email" class="text-gray-700">{{ email }}</strong>
        <span v-else>o seu e-mail</span>.
        Clique no link para ativar sua conta e começar a usar o Veekar.
      </p>

      <p v-if="sent" class="text-sm text-green-600 mt-4">
        Link reenviado! Verifique sua caixa de entrada.
      </p>

      <button
        v-else-if="email"
        type="button"
        class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-800 disabled:opacity-60 disabled:cursor-not-allowed"
        :disabled="sending"
        @click="resend"
      >
        {{ sending ? 'Reenviando...' : 'Não recebeu? Reenviar link' }}
      </button>

      <p class="text-xs text-gray-400 mt-6">
        Verifique também a pasta de spam, ou
        <NuxtLink to="/login" class="text-blue-600 hover:underline">volte para o login</NuxtLink>.
      </p>
    </div>
  </div>
</template>
