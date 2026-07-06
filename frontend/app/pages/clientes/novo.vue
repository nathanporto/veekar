<script setup lang="ts">
import { useCustomersStore } from '~/stores/customers'

const store = useCustomersStore()
const router = useRouter()
const { applyMask, validateCpf } = useDocument()
const { applyMask: applyPhoneMask } = usePhone()

const form = reactive({ name: '', cpf: '', phone: '', email: '', notes: '' })
const loading = ref(false)
const error = ref('')

function onCpfInput(e: Event) {
  form.cpf = applyMask((e.target as HTMLInputElement).value)
}

function onPhoneInput(e: Event) {
  form.phone = applyPhoneMask((e.target as HTMLInputElement).value)
}

const cpfError = computed(() => {
  if (!form.cpf) return ''
  const digits = form.cpf.replace(/\D/g, '')
  if (digits.length < 11) return 'CPF incompleto'
  if (!validateCpf(form.cpf)) return 'CPF inválido'
  return ''
})

async function submit() {
  if (cpfError.value) return
  loading.value = true
  error.value = ''
  try {
    await store.create(form)
    await router.push('/clientes')
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao salvar'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-lg space-y-5">
    <div class="flex items-center gap-3">
      <NuxtLink to="/clientes" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </NuxtLink>
      <h1 class="text-2xl font-bold text-gray-900">Novo Cliente</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Nome *</label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="Nome completo"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            CPF
            <span class="text-gray-400 font-normal">(opcional)</span>
          </label>
          <input
            :value="form.cpf"
            type="text"
            inputmode="numeric"
            placeholder="000.000.000-00"
            maxlength="14"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono tracking-wide"
            :class="cpfError ? 'border-red-400 ring-1 ring-red-400' : (form.cpf && !cpfError ? 'border-green-400' : '')"
            @input="onCpfInput"
          />
          <p v-if="cpfError" class="text-xs text-red-500 mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ cpfError }}
          </p>
          <p v-else-if="form.cpf && form.cpf.replace(/\D/g, '').length === 11" class="text-xs text-green-600 mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            CPF válido
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Telefone *</label>
          <input
            :value="form.phone"
            type="tel"
            inputmode="numeric"
            required
            placeholder="(11) 99999-9999"
            maxlength="15"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="onPhoneInput"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="email@exemplo.com"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Observações</label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Observações sobre o cliente..."
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>

        <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <div class="flex gap-3 pt-2">
          <button
            type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60"
            :disabled="loading || !!cpfError"
          >
            {{ loading ? 'Salvando...' : 'Salvar Cliente' }}
          </button>
          <NuxtLink
            to="/clientes"
            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
          >
            Cancelar
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>
