<script setup lang="ts">
definePageMeta({ layout: 'auth' })

const api = useApi()
const route = useRoute()

const form = reactive({
  token: route.query.token as string ?? '',
  email: route.query.email as string ?? '',
  password: '',
  passwordConfirmation: '',
})
const loading = ref(false)
const success = ref(false)
const error = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const invalidLink = computed(() => !form.token || !form.email)

async function submit() {
  if (form.password !== form.passwordConfirmation) {
    error.value = 'As senhas não coincidem'
    return
  }
  loading.value = true
  error.value = ''
  try {
    await api.post('/auth/reset-password', {
      token: form.token,
      email: form.email,
      password: form.password,
      password_confirmation: form.passwordConfirmation,
    })
    success.value = true
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao redefinir senha'
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
      <!-- Link inválido -->
      <template v-if="invalidLink">
        <div class="text-center space-y-4">
          <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Link inválido</h2>
            <p class="text-sm text-gray-500 mt-2">Este link de redefinição é inválido ou expirou.</p>
          </div>
          <NuxtLink to="/esqueci-senha" class="inline-block w-full text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Solicitar novo link
          </NuxtLink>
        </div>
      </template>

      <!-- Sucesso -->
      <template v-else-if="success">
        <div class="text-center space-y-4">
          <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Senha redefinida!</h2>
            <p class="text-sm text-gray-500 mt-2">Sua nova senha foi salva com sucesso.</p>
          </div>
          <NuxtLink to="/login" class="inline-block w-full text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Fazer login
          </NuxtLink>
        </div>
      </template>

      <!-- Formulário -->
      <template v-else>
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Criar nova senha</h2>

        <form class="space-y-4" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nova senha</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                minlength="8"
                placeholder="Mínimo 8 caracteres"
                class="w-full px-3.5 py-2.5 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar nova senha</label>
            <div class="relative">
              <input
                v-model="form.passwordConfirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                placeholder="Repita a senha"
                class="w-full px-3.5 py-2.5 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="form.passwordConfirmation && form.password !== form.passwordConfirmation ? 'border-red-400 ring-1 ring-red-400' : ''"
              />
              <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" @click="showConfirmPassword = !showConfirmPassword">
                <svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <p v-if="form.passwordConfirmation && form.password !== form.passwordConfirmation" class="text-xs text-red-500 mt-1">
              As senhas não coincidem
            </p>
          </div>

          <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">{{ error }}</div>

          <button
            type="submit"
            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-colors disabled:opacity-60"
            :disabled="loading || (!!form.passwordConfirmation && form.password !== form.passwordConfirmation)"
          >
            {{ loading ? 'Salvando...' : 'Salvar nova senha' }}
          </button>
        </form>
      </template>
    </div>
  </div>
</template>
