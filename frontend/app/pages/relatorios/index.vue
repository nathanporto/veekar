<script setup lang="ts">
import { useReportsStore } from '~/stores/reports'

const store = useReportsStore()

onMounted(() => store.fetchFinancial())

function formatCurrency(value: number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

function formatGrowth(value: number | null) {
  if (value === null) return null
  return (value >= 0 ? '+' : '') + value + '%'
}

const maxChartAmount = computed(() => {
  if (!store.financial) return 1
  return Math.max(...store.financial.chart.map(m => m.amount), 1)
})

const maxCashFlowAmount = computed(() => {
  if (!store.financial) return 1
  const all = store.financial.cash_flow.chart.flatMap(m => [m.entradas, m.saidas])
  return Math.max(...all, 1)
})

function barWidth(value: number) {
  return (value / maxCashFlowAmount.value) * 100
}

const currentMonth = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
})

const exporting = ref(false)
const exportError = ref('')

async function downloadPdf() {
  exporting.value = true
  exportError.value = ''
  try {
    const config = useRuntimeConfig()
    const token = useCookie('veekar_token')
    const res = await fetch(`${config.public.apiBase}/reports/financial/pdf`, {
      headers: { Authorization: `Bearer ${token.value}` },
    })
    if (!res.ok) {
      exportError.value = 'Erro ao gerar PDF. Tente novamente.'
      return
    }
    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `relatorio-veekar-${new Date().toISOString().slice(0, 7)}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    exportError.value = 'Erro ao gerar PDF. Tente novamente.'
  } finally {
    exporting.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Relatórios</h1>
        <p class="text-gray-500 text-sm mt-1">Visão financeira da sua oficina</p>
      </div>
      <button
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60"
        :disabled="exporting || store.loading"
        @click="downloadPdf"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        {{ exporting ? 'Gerando...' : 'Exportar PDF' }}
      </button>
    </div>

    <div v-if="exportError" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">
      {{ exportError }}
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div v-for="i in 3" :key="i" class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 animate-pulse">
        <div class="h-3 bg-gray-200 rounded w-28 mb-4" />
        <div class="h-8 bg-gray-200 rounded w-24" />
      </div>
    </div>

    <template v-else-if="store.financial">
      <!-- Cards do mês -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Faturamento -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <p class="text-sm font-medium text-gray-500 mb-1 capitalize">{{ currentMonth }}</p>
          <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(store.financial.current_month.total) }}</p>
          <div class="mt-2 flex items-center gap-1.5">
            <span
              v-if="store.financial.growth_percent !== null"
              class="text-xs font-semibold px-2 py-0.5 rounded-full"
              :class="store.financial.growth_percent >= 0
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-600'"
            >
              {{ formatGrowth(store.financial.growth_percent) }}
            </span>
            <span class="text-xs text-gray-400">vs mês anterior</span>
          </div>
        </div>

        <!-- Atendimentos -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <p class="text-sm font-medium text-gray-500 mb-1">Atendimentos no mês</p>
          <p class="text-3xl font-bold text-gray-900">{{ store.financial.current_month.count }}</p>
          <p class="text-xs text-gray-400 mt-2">serviços realizados</p>
        </div>

        <!-- Ticket médio -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <p class="text-sm font-medium text-gray-500 mb-1">Ticket médio</p>
          <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(store.financial.current_month.avg) }}</p>
          <p class="text-xs text-gray-400 mt-2">por atendimento</p>
        </div>
      </div>

      <!-- Gráfico de barras — últimos 6 meses -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-6">Faturamento — últimos 6 meses</h2>

        <div class="flex items-end gap-3 h-48">
          <div
            v-for="(month, i) in store.financial.chart"
            :key="i"
            class="flex-1 flex flex-col items-center gap-2 group"
          >
            <!-- Tooltip valor -->
            <span class="text-xs text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
              {{ formatCurrency(month.amount) }}
            </span>

            <!-- Barra -->
            <div class="w-full relative flex items-end" style="height: 160px;">
              <div
                class="w-full rounded-t-lg transition-all duration-500"
                :class="i === store.financial.chart.length - 1 ? 'bg-blue-600' : 'bg-blue-200 group-hover:bg-blue-400'"
                :style="{ height: month.amount === 0 ? '4px' : `${(month.amount / maxChartAmount) * 100}%` }"
              />
            </div>

            <!-- Label mês -->
            <span class="text-xs text-gray-400 font-medium">{{ month.label }}</span>
          </div>
        </div>

        <div v-if="store.financial.chart.every(m => m.amount === 0)" class="text-center text-gray-400 text-sm py-4">
          Nenhum atendimento com valor registrado ainda.
        </div>
      </div>

      <!-- Recebido x A receber -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-1">
          <h2 class="text-base font-semibold text-gray-900">Recebido x A receber</h2>
          <NuxtLink to="/pagamentos" class="text-xs text-blue-600 hover:underline">Ver pagamentos →</NuxtLink>
        </div>
        <p class="text-gray-500 text-sm mb-6">Baseado no status de pagamento marcado em cada atendimento do mês</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="bg-green-50 rounded-lg p-4">
            <p class="text-xs text-green-700 mb-1">Recebido</p>
            <p class="text-xl font-bold text-green-800">{{ formatCurrency(store.financial.payment_summary.recebido) }}</p>
          </div>
          <div class="bg-amber-50 rounded-lg p-4">
            <p class="text-xs text-amber-700 mb-1">A receber (pendente/parcial)</p>
            <p class="text-xl font-bold text-amber-800">{{ formatCurrency(store.financial.payment_summary.a_receber) }}</p>
          </div>
        </div>
      </div>

      <!-- Entradas x Saídas -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-1">Entradas x Saídas</h2>
        <p class="text-gray-500 text-sm mb-6">Receita dos atendimentos + vendas de estoque vs. custo de compra de produtos</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-green-50 rounded-lg p-4">
            <p class="text-xs text-green-700 mb-1">Entradas (mês atual)</p>
            <p class="text-xl font-bold text-green-800">{{ formatCurrency(store.financial.cash_flow.current_month.entradas) }}</p>
          </div>
          <div class="bg-red-50 rounded-lg p-4">
            <p class="text-xs text-red-600 mb-1">Saídas (mês atual)</p>
            <p class="text-xl font-bold text-red-700">{{ formatCurrency(store.financial.cash_flow.current_month.saidas) }}</p>
          </div>
          <div class="rounded-lg p-4" :class="store.financial.cash_flow.current_month.saldo >= 0 ? 'bg-blue-50' : 'bg-amber-50'">
            <p class="text-xs mb-1" :class="store.financial.cash_flow.current_month.saldo >= 0 ? 'text-blue-700' : 'text-amber-700'">Saldo (mês atual)</p>
            <p class="text-xl font-bold" :class="store.financial.cash_flow.current_month.saldo >= 0 ? 'text-blue-800' : 'text-amber-800'">
              {{ formatCurrency(store.financial.cash_flow.current_month.saldo) }}
            </p>
          </div>
        </div>

        <div class="space-y-2">
          <div v-for="(month, i) in store.financial.cash_flow.chart" :key="i" class="flex items-center gap-3">
            <span class="w-14 text-gray-400 text-xs font-medium flex-shrink-0">{{ month.label }}</span>
            <div class="flex-1 flex items-center gap-1 h-4">
              <div class="bg-green-400 h-full rounded" :style="{ width: barWidth(month.entradas) + '%' }" />
              <div class="bg-red-400 h-full rounded" :style="{ width: barWidth(month.saidas) + '%' }" />
            </div>
            <span class="text-xs text-gray-500 w-44 text-right flex-shrink-0">
              +{{ formatCurrency(month.entradas) }} / -{{ formatCurrency(month.saidas) }}
            </span>
          </div>
        </div>

        <p v-if="store.financial.cash_flow.chart.every(m => m.saidas === 0)" class="text-center text-gray-400 text-sm py-4 mt-2">
          Nenhuma saída de estoque registrada ainda. Cadastre produtos em <NuxtLink to="/estoque" class="text-blue-600 hover:underline">Estoque</NuxtLink>.
        </p>
      </div>

      <!-- Mês anterior comparativo -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Comparativo</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-xs text-gray-500 mb-1">Mês atual</p>
            <p class="text-xl font-bold text-gray-900">{{ formatCurrency(store.financial.current_month.total) }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-xs text-gray-500 mb-1">Mês anterior</p>
            <p class="text-xl font-bold text-gray-900">{{ formatCurrency(store.financial.previous_month_total) }}</p>
          </div>
        </div>
        <div v-if="store.financial.growth_percent !== null" class="mt-4 flex items-center gap-2">
          <svg
            class="w-4 h-4"
            :class="store.financial.growth_percent >= 0 ? 'text-green-500' : 'text-red-500'"
            fill="none" viewBox="0 0 24 24" stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              :d="store.financial.growth_percent >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'"
            />
          </svg>
          <span
            class="text-sm font-semibold"
            :class="store.financial.growth_percent >= 0 ? 'text-green-600' : 'text-red-600'"
          >
            {{ formatGrowth(store.financial.growth_percent) }}
          </span>
          <span class="text-sm text-gray-500">em relação ao mês anterior</span>
        </div>
        <p v-else class="mt-4 text-sm text-gray-400">Sem dados do mês anterior para comparar.</p>
      </div>
    </template>
  </div>
</template>
