<script setup lang="ts">
definePageMeta({ layout: 'auth' })

const api = useApi()
const email = ref('')
const loading = ref(false)
const sent = ref(false)
const error = ref('')

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    sent.value = true
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao enviar e-mail'
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
      <!-- Sucesso -->
      <template v-if="sent">
        <div class="text-center space-y-4">
          <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">E-mail enviado!</h2>
            <p class="text-sm text-gray-500 mt-2">
              Se <strong>{{ email }}</strong> estiver cadastrado, você receberá o link para redefinir a senha em breve.
            </p>
            <p class="text-xs text-gray-400 mt-2">Verifique também a caixa de spam.</p>
          </div>
          <NuxtLink to="/login" class="inline-block w-full text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Voltar ao login
          </NuxtLink>
        </div>
      </template>

      <!-- Formulário -->
      <template v-else>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Esqueceu a senha?</h2>
        <p class="text-sm text-gray-500 mb-6">Digite seu e-mail e enviaremos um link para redefinir sua senha.</p>

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

          <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">{{ error }}</div>

          <button
            type="submit"
            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-colors disabled:opacity-60"
            :disabled="loading"
          >
            {{ loading ? 'Enviando...' : 'Enviar link de redefinição' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          <NuxtLink to="/login" class="text-blue-600 hover:text-blue-800 font-medium">
            Voltar ao login
          </NuxtLink>
        </p>
      </template>
    </div>
  </div>
</template>
