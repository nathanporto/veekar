<script setup lang="ts">
import type { Customer, PaginatedResponse } from '~/types'

const props = defineProps<{
  modelValue: number | null
  initialCustomer?: Customer | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
}>()

const query = ref('')
const results = ref<Customer[]>([])
const selected = ref<Customer | null>(null)
const open = ref(false)
const searching = ref(false)
const containerRef = ref<HTMLElement | null>(null)

watch(
  () => props.initialCustomer,
  (c) => {
    if (c && !selected.value) {
      selected.value = c
      query.value = c.name
    }
  },
  { immediate: true },
)

let timer: ReturnType<typeof setTimeout>

async function onInput(e: Event) {
  const q = (e.target as HTMLInputElement).value
  query.value = q
  selected.value = null
  emit('update:modelValue', null)

  clearTimeout(timer)

  if (!q.trim()) {
    results.value = []
    open.value = false
    return
  }

  timer = setTimeout(async () => {
    searching.value = true
    try {
      const api = useApi()
      const data = await api.get<PaginatedResponse<Customer>>(
        `/customers?search=${encodeURIComponent(q)}`,
      )
      results.value = data.data
      open.value = data.data.length > 0
    } finally {
      searching.value = false
    }
  }, 300)
}

function select(customer: Customer) {
  selected.value = customer
  query.value = customer.name
  results.value = []
  open.value = false
  emit('update:modelValue', customer.id)
}

function clear() {
  selected.value = null
  query.value = ''
  results.value = []
  open.value = false
  emit('update:modelValue', null)
}

function onBlur() {
  setTimeout(() => {
    open.value = false
    // Se o usuário digitou mas não selecionou, restaura o nome do selecionado anterior
    if (!selected.value) query.value = ''
    else query.value = selected.value.name
  }, 150)
}
</script>

<template>
  <div ref="containerRef" class="relative">
    <div class="relative">
      <input
        :value="query"
        type="text"
        placeholder="Buscar por nome, CPF ou telefone..."
        autocomplete="off"
        class="w-full px-3.5 py-2.5 pr-8 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        :class="{ 'border-blue-500 ring-2 ring-blue-500': selected }"
        @input="onInput"
        @focus="open = results.length > 0"
        @blur="onBlur"
      />

      <!-- Ícone de loading ou limpar -->
      <div class="absolute right-2.5 top-1/2 -translate-y-1/2">
        <svg v-if="searching" class="w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <button
          v-else-if="selected"
          type="button"
          class="text-gray-400 hover:text-gray-600"
          @mousedown.prevent="clear"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <svg v-else class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Cliente selecionado -->
    <div v-if="selected" class="mt-1.5 flex items-center gap-2 text-xs text-green-700 bg-green-50 px-3 py-1.5 rounded-lg border border-green-200">
      <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>
        <strong>{{ selected.name }}</strong>
        <template v-if="selected.cpf"> · CPF {{ selected.cpf }}</template>
        · {{ selected.phone }}
      </span>
    </div>

    <!-- Dropdown de resultados -->
    <div
      v-if="open && results.length > 0"
      class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
    >
      <ul class="max-h-56 overflow-y-auto divide-y divide-gray-100">
        <li
          v-for="customer in results"
          :key="customer.id"
          class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition-colors"
          @mousedown.prevent="select(customer)"
        >
          <p class="text-sm font-medium text-gray-900">{{ customer.name }}</p>
          <p class="text-xs text-gray-500 mt-0.5">
            {{ customer.phone }}
            <template v-if="customer.cpf"> · CPF {{ customer.cpf }}</template>
          </p>
        </li>
      </ul>
    </div>

    <!-- Sem resultados -->
    <div
      v-else-if="open && !searching && query.trim() && results.length === 0"
      class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg px-4 py-3 text-sm text-gray-500"
    >
      Nenhum cliente encontrado para "<strong>{{ query }}</strong>"
    </div>
  </div>
</template>
