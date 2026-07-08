<script setup lang="ts">
import type { ServiceHistory, Vehicle, Customer, PaymentReminder } from '~/types'
import { useReportsStore } from '~/stores/reports'
import { useQuotesStore } from '~/stores/quotes'

const api = useApi()
const reportsStore = useReportsStore()
const quotesStore = useQuotesStore()

interface UpcomingReturn {
  id: number
  return_date: string
  return_reason: string | null
  vehicle: Vehicle & { customer: Customer }
}

const stats = ref({ customers: 0, vehicles: 0, services: 0 })
const recentServices = ref<(ServiceHistory & { vehicle: Vehicle & { customer: Customer } })[]>([])
const upcomingReturns = ref<UpcomingReturn[]>([])
const upcomingPaymentReminders = ref<PaymentReminder[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [s, r, u, pr] = await Promise.all([
      api.get<typeof stats.value>('/dashboard/stats'),
      api.get<typeof recentServices.value>('/dashboard/recent-services'),
      api.get<UpcomingReturn[]>('/dashboard/upcoming-returns'),
      api.get<PaymentReminder[]>('/dashboard/upcoming-payment-reminders'),
    ])
    stats.value = s
    recentServices.value = r
    upcomingReturns.value = u
    upcomingPaymentReminders.value = pr
    reportsStore.fetchFinancial()
    quotesStore.fetchQuotes()
  } finally {
    loading.value = false
  }
})

function parseDate(date: string | null | undefined): Date | null {
  if (!date) return null
  const d = new Date(date.includes('T') ? date : date + 'T00:00:00')
  return isNaN(d.getTime()) ? null : d
}

function formatDate(date: string | null | undefined) {
  const d = parseDate(date)
  return d ? d.toLocaleDateString('pt-BR') : '—'
}

function daysUntil(date: string | null | undefined) {
  const d = parseDate(date)
  if (!d) return ''
  const diff = Math.ceil((d.getTime() - new Date().setHours(0, 0, 0, 0)) / 86400000)
  if (diff < 0) return `${Math.abs(diff)}d atraso`
  if (diff === 0) return 'hoje'
  return `em ${diff}d`
}

function formatCurrency(value: string | null) {
  if (!value) return '—'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-gray-500 text-sm mt-1">Visão geral do sistema</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div v-for="i in 3" :key="i" class="bg-white rounded-xl p-6 shadow-sm animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-24 mb-3" />
        <div class="h-8 bg-gray-200 rounded w-16" />
      </div>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500 mb-1">Total de Clientes</p>
        <p class="text-3xl font-bold text-gray-900">{{ stats.customers }}</p>
      </div>
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500 mb-1">Total de Veículos</p>
        <p class="text-3xl font-bold text-gray-900">{{ stats.vehicles }}</p>
      </div>
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <p class="text-sm font-medium text-gray-500 mb-1">Atendimentos</p>
        <p class="text-3xl font-bold text-gray-900">{{ stats.services }}</p>
      </div>
    </div>

    <!-- Mini widget financeiro -->
    <NuxtLink
      to="/relatorios"
      class="block bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-5 shadow-sm hover:from-blue-700 hover:to-blue-800 transition-all"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-blue-200 text-xs font-medium uppercase tracking-wide mb-1">Faturamento do mês</p>
          <p v-if="reportsStore.loading || !reportsStore.financial" class="text-2xl font-bold text-white">—</p>
          <p v-else class="text-2xl font-bold text-white">
            {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(reportsStore.financial.current_month.total) }}
          </p>
          <p v-if="reportsStore.financial?.growth_percent !== null && reportsStore.financial?.growth_percent !== undefined" class="text-blue-200 text-xs mt-1">
            <span :class="reportsStore.financial.growth_percent >= 0 ? 'text-green-300' : 'text-red-300'" class="font-semibold">
              {{ reportsStore.financial.growth_percent >= 0 ? '+' : '' }}{{ reportsStore.financial.growth_percent }}%
            </span>
            vs mês anterior
          </p>
        </div>
        <div class="flex flex-col items-end gap-1">
          <svg class="w-8 h-8 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span class="text-blue-300 text-xs">Ver relatório →</span>
        </div>
      </div>
    </NuxtLink>

    <!-- Widget orçamentos -->
    <NuxtLink
      to="/orcamentos"
      class="block bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl p-5 shadow-sm hover:from-slate-800 hover:to-slate-900 transition-all"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-slate-300 text-xs font-medium uppercase tracking-wide mb-1">Orçamentos pendentes</p>
          <p class="text-2xl font-bold text-white">
            {{ quotesStore.loading ? '—' : quotesStore.pendingCount }}
          </p>
          <p class="text-slate-300 text-xs mt-1">aguardando resposta do cliente</p>
        </div>
        <div class="flex flex-col items-end gap-1">
          <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span class="text-slate-400 text-xs">Ver orçamentos →</span>
        </div>
      </div>
    </NuxtLink>

    <!-- Widget retornos próximos -->
    <div v-if="upcomingReturns.length > 0" class="bg-white rounded-xl shadow-sm border border-orange-100">
      <div class="px-6 py-4 border-b border-orange-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h2 class="text-base font-semibold text-gray-900">Retornos Próximos</h2>
        <span class="ml-auto text-xs text-gray-400">próximos 30 dias</span>
      </div>
      <div class="divide-y divide-gray-50">
        <div
          v-for="r in upcomingReturns"
          :key="r.id"
          class="px-6 py-3 flex items-center justify-between gap-4 hover:bg-orange-50 cursor-pointer transition-colors"
          @click="$router.push(`/veiculos/${r.vehicle?.id}`)"
        >
          <div class="flex items-center gap-3 min-w-0">
            <span class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded flex-shrink-0">
              {{ r.vehicle?.plate }}
            </span>
            <div class="min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ r.vehicle?.customer?.name }}</p>
              <p v-if="r.return_reason" class="text-xs text-gray-500 truncate">{{ r.return_reason }}</p>
            </div>
          </div>
          <div class="text-right flex-shrink-0">
            <p class="text-sm font-semibold text-gray-900">{{ formatDate(r.return_date) }}</p>
            <p class="text-xs font-medium"
              :class="new Date(r.return_date + 'T00:00:00') < new Date() ? 'text-red-500' : 'text-orange-500'">
              {{ daysUntil(r.return_date) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Widget lembretes de pagamento -->
    <div v-if="upcomingPaymentReminders.length > 0" class="bg-white rounded-xl shadow-sm border border-red-100">
      <div class="px-6 py-4 border-b border-red-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <h2 class="text-base font-semibold text-gray-900">Lembretes de Pagamento</h2>
        <span class="ml-auto text-xs text-gray-400">próximos 30 dias</span>
      </div>
      <div class="divide-y divide-gray-50">
        <div
          v-for="r in upcomingPaymentReminders"
          :key="r.id"
          class="px-6 py-3 flex items-center justify-between gap-4 hover:bg-red-50 cursor-pointer transition-colors"
          @click="$router.push('/pagamentos')"
        >
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ r.description }}</p>
          </div>
          <div class="text-right flex-shrink-0">
            <p v-if="r.amount" class="text-sm font-semibold text-gray-900">{{ formatCurrency(r.amount) }}</p>
            <p class="text-xs font-medium"
              :class="new Date(r.due_date + 'T00:00:00') < new Date() ? 'text-red-500' : 'text-amber-600'">
              {{ formatDate(r.due_date) }} ({{ daysUntil(r.due_date) }})
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Últimos Atendimentos</h2>
      </div>

      <div v-if="loading" class="p-6 space-y-3">
        <div v-for="i in 5" :key="i" class="h-12 bg-gray-100 rounded animate-pulse" />
      </div>

      <div v-else-if="recentServices.length === 0" class="p-12 text-center text-gray-400">
        <p>Nenhum atendimento registrado ainda.</p>
      </div>

      <div v-else>
        <!-- Tabela desktop -->
        <table class="w-full hidden md:table">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Placa</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Veículo</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Cliente</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Serviço</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Data</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Valor</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="s in recentServices"
              :key="s.id"
              class="hover:bg-gray-50 cursor-pointer transition-colors"
              @click="$router.push(`/veiculos/${s.vehicle_id}`)"
            >
              <td class="px-6 py-4">
                <span class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                  {{ s.vehicle?.plate }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ s.vehicle?.brand }} {{ s.vehicle?.model }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ s.vehicle?.customer?.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ s.description }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(s.service_date) }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ formatCurrency(s.amount) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Cards mobile -->
        <div class="divide-y divide-gray-100 md:hidden">
          <div
            v-for="s in recentServices"
            :key="s.id"
            class="px-4 py-4 flex items-start justify-between gap-3 cursor-pointer active:bg-gray-50"
            @click="$router.push(`/veiculos/${s.vehicle_id}`)"
          >
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                  {{ s.vehicle?.plate }}
                </span>
                <span class="text-xs text-gray-500">{{ s.vehicle?.brand }} {{ s.vehicle?.model }}</span>
              </div>
              <p class="text-sm text-gray-900 truncate">{{ s.description }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ s.vehicle?.customer?.name }}</p>
            </div>
            <div class="flex-shrink-0 text-right">
              <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(s.amount) }}</p>
              <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(s.service_date) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
