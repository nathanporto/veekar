<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'auth' })

const auth = useAuthStore()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(email.value, password.value)
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Credenciais inválidas'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-600 rounded-2xl mb-4">
        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-white">Veekar</h1>
      <p class="text-slate-400 text-sm mt-1">Sistema de histórico automotivo</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-6">Entrar na conta</h2>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
          <input
            v-model="email"
            type="email"
            autocomplete="email"
            required
            placeholder="seu@email.com"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Senha</label>
          <input
            v-model="password"
            type="password"
            autocomplete="current-password"
            required
            placeholder="••••••••"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <button
          type="submit"
          class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-colors disabled:opacity-60"
          :disabled="loading"
        >
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <div class="mt-6 space-y-3 text-center text-sm">
        <p class="text-gray-500">
          Não tem uma conta?
          <NuxtLink to="/register" class="text-blue-600 hover:text-blue-800 font-medium">Criar conta</NuxtLink>
        </p>
        <p>
          <NuxtLink to="/esqueci-senha" class="text-gray-400 hover:text-gray-600 transition-colors">Esqueceu a senha?</NuxtLink>
        </p>
      </div>
    </div>
  </div>
</template>
