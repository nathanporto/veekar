<script setup lang="ts">
import { useCommissionsStore } from '~/stores/commissions'

const store = useCommissionsStore()

onMounted(() => store.fetchAll())

function formatCurrency(value: number | string) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
}

function formatDate(value: string) {
  return new Date(`${value.slice(0, 10)}T00:00:00`).toLocaleDateString('pt-BR')
}

function commissionLabel(type: string, value: string | null) {
  if (type === 'percentual') return `${Number(value)}% por atendimento`
  if (type === 'fixo') return `${formatCurrency(Number(value))} por atendimento`
  return 'Sem comissão'
}

const expanded = ref<Record<number, boolean>>({})
function toggle(employeeId: number) {
  expanded.value[employeeId] = !expanded.value[employeeId]
}

const monthLabel = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
})
</script>

<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Comissões</h1>
      <p class="text-gray-500 text-sm mt-1 capitalize">Referente a {{ monthLabel }} — atendimentos pagos no mês</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 3" :key="i" class="h-16 bg-gray-100 rounded animate-pulse" />
      </div>

      <div v-else-if="store.commissions.length === 0" class="p-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1" />
        </svg>
        <p>Nenhuma comissão registrada neste mês.</p>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div v-for="ec in store.commissions" :key="ec.employee_id" class="px-6 py-4">
          <div class="flex items-center justify-between gap-4 flex-wrap cursor-pointer" @click="toggle(ec.employee_id)">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">{{ ec.employee_name }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ commissionLabel(ec.commission_type, ec.commission_value) }} · {{ ec.services.length }} atendimento(s)</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
              <span class="text-base font-bold text-green-600">{{ formatCurrency(ec.total_commission) }}</span>
              <svg class="w-4 h-4 text-gray-400 transition-transform" :class="expanded[ec.employee_id] ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>

          <div v-if="expanded[ec.employee_id]" class="mt-3 border-t border-gray-100 pt-3 space-y-2">
            <div v-for="s in ec.services" :key="s.id" class="flex items-center justify-between text-sm gap-4">
              <div class="min-w-0">
                <p class="text-gray-700 truncate">{{ s.description }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(s.service_date) }}</p>
              </div>
              <div class="text-right flex-shrink-0">
                <p class="text-gray-500 text-xs">{{ formatCurrency(s.amount) }}</p>
                <p class="text-green-600 font-medium">{{ formatCurrency(s.commission_amount) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
