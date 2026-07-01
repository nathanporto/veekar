<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'auth' })

const auth = useAuthStore()
const { applyMask, validate, getType } = useDocument()

const form = reactive({
  name: '',
  companyName: '',
  document: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const acceptedTerms = ref(false)

function onDocumentInput(e: Event) {
  form.document = applyMask((e.target as HTMLInputElement).value)
}

const documentLabel = computed(() => getType(form.document) ?? 'CPF ou CNPJ')

const documentError = computed(() => {
  if (!form.document) return ''
  const digits = form.document.replace(/\D/g, '')
  const type = getType(form.document)
  if (type === 'CPF' && digits.length < 11) return 'CPF incompleto'
  if (type === 'CNPJ' && digits.length < 14) return 'CNPJ incompleto'
  if (!validate(form.document)) return `${type} inválido`
  return ''
})

const documentValid = computed(() => !!form.document && !documentError.value && validate(form.document))

async function submit() {
  if (form.password !== form.passwordConfirmation) {
    error.value = 'As senhas não coincidem'
    return
  }
  if (!documentValid.value) {
    error.value = documentError.value || 'Informe um CPF ou CNPJ válido'
    return
  }
  if (!acceptedTerms.value) {
    error.value = 'Você precisa aceitar os Termos de Uso e a Política de Privacidade'
    return
  }

  loading.value = true
  error.value = ''

  try {
    await auth.register(form.name, form.companyName, form.document, form.email, form.password, form.passwordConfirmation, acceptedTerms.value)
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao criar conta'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-sm">
    <div class="mb-4">
      <NuxtLink to="/login" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-white text-sm transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Voltar ao login
      </NuxtLink>
    </div>

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
      <h2 class="text-lg font-semibold text-gray-900 mb-6">Criar conta</h2>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nome completo</label>
          <input
            v-model="form.name"
            type="text"
            autocomplete="name"
            required
            placeholder="Seu nome"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nome da empresa</label>
          <input
            v-model="form.companyName"
            type="text"
            autocomplete="organization"
            required
            placeholder="Ex: Oficina do João"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ documentLabel }}</label>
          <input
            :value="form.document"
            type="text"
            inputmode="numeric"
            required
            placeholder="000.000.000-00 ou 00.000.000/0000-00"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono tracking-wide"
            :class="documentError ? 'border-red-400 ring-1 ring-red-400' : (documentValid ? 'border-green-400' : '')"
            @input="onDocumentInput"
          />
          <p v-if="documentError" class="text-xs text-red-500 mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ documentError }}
          </p>
          <p v-else-if="documentValid" class="text-xs text-green-600 mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ documentLabel }} válido
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
            placeholder="seu@email.com"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Senha</label>
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
            <button
              type="button"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
              @click="showPassword = !showPassword"
            >
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
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar senha</label>
          <div class="relative">
            <input
              v-model="form.passwordConfirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              autocomplete="new-password"
              required
              placeholder="Repita a senha"
              class="w-full px-3.5 py-2.5 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="form.passwordConfirmation && form.password !== form.passwordConfirmation
                ? 'border-red-400 ring-1 ring-red-400'
                : ''"
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              <svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>
          <p
            v-if="form.passwordConfirmation && form.password !== form.passwordConfirmation"
            class="text-xs text-red-500 mt-1"
          >
            As senhas não coincidem
          </p>
        </div>

        <!-- Aceite dos termos -->
        <div class="flex items-start gap-3 pt-1">
          <input
            id="accept-terms"
            v-model="acceptedTerms"
            type="checkbox"
            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
          />
          <label for="accept-terms" class="text-xs text-gray-600 leading-relaxed cursor-pointer">
            Li e concordo com os
            <NuxtLink to="/termos" target="_blank" class="text-blue-600 hover:underline font-medium">Termos de Uso</NuxtLink>
            e a
            <NuxtLink to="/privacidade" target="_blank" class="text-blue-600 hover:underline font-medium">Política de Privacidade</NuxtLink>
            do Veekar, incluindo o tratamento de dados conforme a LGPD.
          </label>
        </div>

        <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <button
          type="submit"
          class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-colors disabled:opacity-60"
          :disabled="loading || (!!form.passwordConfirmation && form.password !== form.passwordConfirmation) || !acceptedTerms"
        >
          {{ loading ? 'Criando conta...' : 'Criar conta' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Já tem uma conta?
        <NuxtLink to="/login" class="text-blue-600 hover:text-blue-800 font-medium">
          Entrar
        </NuxtLink>
      </p>
    </div>
  </div>
</template>
