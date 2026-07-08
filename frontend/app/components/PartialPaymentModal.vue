<script setup lang="ts">
const props = defineProps<{
  open: boolean
  totalAmount: number
  initialValue?: number
  loading?: boolean
}>()

const emit = defineEmits<{
  confirm: [value: number]
  cancel: []
}>()

const value = ref('')

watch(() => props.open, (isOpen) => {
  if (isOpen) value.value = props.initialValue ? String(props.initialValue) : ''
})

function formatCurrency(v: number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v)
}

const error = computed(() => {
  if (!value.value) return ''
  const num = Number(value.value)
  if (isNaN(num) || num <= 0) return 'Informe um valor válido'
  if (num > props.totalAmount) return `O valor não pode ser maior que ${formatCurrency(props.totalAmount)}`
  return ''
})

function confirm() {
  if (error.value || !value.value) return
  emit('confirm', Number(value.value))
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 space-y-4">
      <h2 class="text-base font-semibold text-gray-900">Pagamento parcial</h2>
      <p class="text-sm text-gray-500">Valor total do serviço: <strong class="text-gray-900">{{ formatCurrency(totalAmount) }}</strong></p>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Quanto o cliente pagou?</label>
        <input
          v-model="value"
          type="number"
          step="0.01"
          min="0"
          :max="totalAmount"
          autofocus
          placeholder="0,00"
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <p v-if="error" class="text-xs text-red-500 mt-1.5">{{ error }}</p>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800" @click="emit('cancel')">
          Cancelar
        </button>
        <button
          type="button"
          :disabled="!!error || !value || loading"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60"
          @click="confirm"
        >
          {{ loading ? 'Salvando...' : 'Confirmar' }}
        </button>
      </div>
    </div>
  </div>
</template>
