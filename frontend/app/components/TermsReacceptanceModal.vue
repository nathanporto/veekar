<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const auth = useAuthStore()
const accepting = ref(false)
const error = ref('')

async function accept() {
  accepting.value = true
  error.value = ''
  try {
    await auth.acceptTerms()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Erro ao registrar aceite. Tente novamente.'
  } finally {
    accepting.value = false
  }
}
</script>

<template>
  <div v-if="auth.user?.terms_reacceptance_required" class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
      <h2 class="text-lg font-bold text-gray-900">Atualizamos nossos Termos de Uso</h2>
      <p class="text-sm text-gray-600">
        Fizemos alterações importantes nos nossos Termos de Uso e Política de Privacidade.
        Para continuar usando o Veekar, é necessário ler e aceitar as novas condições.
      </p>

      <div class="flex flex-col gap-2">
        <NuxtLink to="/termos" target="_blank" class="text-sm text-blue-600 hover:underline">
          Ler os Termos de Uso →
        </NuxtLink>
        <NuxtLink to="/privacidade" target="_blank" class="text-sm text-blue-600 hover:underline">
          Ler a Política de Privacidade →
        </NuxtLink>
      </div>

      <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg">{{ error }}</div>

      <button
        type="button"
        :disabled="accepting"
        class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60"
        @click="accept"
      >
        {{ accepting ? 'Salvando...' : 'Li e concordo com os novos termos' }}
      </button>
    </div>
  </div>
</template>
