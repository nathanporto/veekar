<script setup lang="ts">
import { useApi } from '~/composables/useApi'

const api = useApi()

interface AgendaItem {
  id: number
  _type: 'delivery' | 'return'
  vehicle_id: number
  description: string
  estimated_delivery?: string | null
  return_date?: string | null
  return_reason?: string | null
  tracking_token?: string | null
  vehicle?: {
    id: number
    plate: string
    brand: string
    model: string
    customer?: { name: string } | null
  }
}

const deliveries = ref<AgendaItem[]>([])
const returns = ref<AgendaItem[]>([])
const loading = ref(true)
const filter = ref<'all' | 'today' | 'week'>('all')

function parseDate(d: string | null | undefined): Date | null {
  if (!d) return null
  const dt = new Date(d.includes('T') ? d : d + 'T00:00:00')
  return isNaN(dt.getTime()) ? null : dt
}

function formatDate(d: string | null | undefined) {
  const dt = parseDate(d)
  return dt ? dt.toLocaleDateString('pt-BR') : '—'
}

function daysUntil(d: string | null | undefined) {
  const dt = parseDate(d)
  if (!dt) return ''
  const diff = Math.ceil((dt.getTime() - new Date().setHours(0, 0, 0, 0)) / 86400000)
  if (diff < 0) return `${Math.abs(diff)}d atraso`
  if (diff === 0) return 'hoje'
  if (diff === 1) return 'amanhã'
  return `em ${diff}d`
}

function isOverdue(d: string | null | undefined) {
  const dt = parseDate(d)
  if (!dt) return false
  return dt.getTime() < new Date().setHours(0, 0, 0, 0)
}

function isToday(d: string | null | undefined) {
  const dt = parseDate(d)
  if (!dt) return false
  const today = new Date()
  return dt.getDate() === today.getDate() &&
    dt.getMonth() === today.getMonth() &&
    dt.getFullYear() === today.getFullYear()
}

function isThisWeek(d: string | null | undefined) {
  const dt = parseDate(d)
  if (!dt) return false
  const diff = Math.ceil((dt.getTime() - new Date().setHours(0, 0, 0, 0)) / 86400000)
  return diff >= 0 && diff <= 7
}

function matchesFilter(d: string | null | undefined) {
  if (filter.value === 'all') return true
  if (filter.value === 'today') return isToday(d)
  if (filter.value === 'week') return isThisWeek(d) || isOverdue(d)
  return true
}

const filteredDeliveries = computed(() =>
  deliveries.value.filter(i => matchesFilter(i.estimated_delivery)),
)

const filteredReturns = computed(() =>
  returns.value.filter(i => matchesFilter(i.return_date)),
)

const totalToday = computed(() => {
  const del = deliveries.value.filter(i => isToday(i.estimated_delivery)).length
  const ret = returns.value.filter(i => isToday(i.return_date)).length
  return del + ret
})

const totalOverdue = computed(() => {
  const del = deliveries.value.filter(i => isOverdue(i.estimated_delivery)).length
  const ret = returns.value.filter(i => isOverdue(i.return_date)).length
  return del + ret
})

onMounted(async () => {
  try {
    const data = await api.get<{ deliveries: AgendaItem[], returns: AgendaItem[] }>('/dashboard/agenda')
    deliveries.value = data.deliveries
    returns.value = data.returns
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Agenda</h1>
        <p class="text-sm text-gray-500 mt-0.5">Entregas previstas e retornos agendados</p>
      </div>
      <div class="flex gap-2">
        <button
          v-for="f in [{ key: 'all', label: 'Todos' }, { key: 'today', label: 'Hoje' }, { key: 'week', label: 'Esta semana' }]"
          :key="f.key"
          class="px-3.5 py-1.5 text-sm font-medium rounded-lg transition-colors"
          :class="filter === f.key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
          @click="filter = f.key as typeof filter"
        >
          {{ f.label }}
        </button>
      </div>
    </div>

    <!-- Summary chips -->
    <div class="flex gap-3">
      <div v-if="totalOverdue > 0" class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-lg text-sm font-medium">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        {{ totalOverdue }} em atraso
      </div>
      <div v-if="totalToday > 0" class="flex items-center gap-2 bg-orange-50 border border-orange-200 text-orange-700 px-4 py-2 rounded-lg text-sm font-medium">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        {{ totalToday }} para hoje
      </div>
      <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm">
        {{ deliveries.length }} entrega{{ deliveries.length !== 1 ? 's' : '' }} · {{ returns.length }} retorno{{ returns.length !== 1 ? 's' : '' }}
      </div>
    </div>

    <div v-if="loading" class="grid gap-4 md:grid-cols-2">
      <div v-for="i in 4" :key="i" class="bg-white rounded-xl p-5 shadow-sm animate-pulse h-28" />
    </div>

    <div v-else class="grid gap-6 md:grid-cols-2 items-start">
      <!-- Entregas -->
      <div>
        <div class="flex items-center gap-2 mb-3">
          <div class="w-2 h-2 rounded-full bg-blue-500" />
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Previsão de Entrega</h2>
          <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ filteredDeliveries.length }}</span>
        </div>

        <div v-if="filteredDeliveries.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
          <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
          </svg>
          <p class="text-sm text-gray-400">Nenhuma entrega agendada</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="item in filteredDeliveries"
            :key="item.id"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-blue-200 transition-colors"
          >
            <div class="flex items-start justify-between gap-3">
              <NuxtLink :to="`/veiculos/${item.vehicle_id}`" class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-semibold text-gray-900 text-sm">{{ item.vehicle?.plate }}</span>
                  <span class="text-gray-400 text-sm">{{ item.vehicle?.brand }} {{ item.vehicle?.model }}</span>
                </div>
                <p class="text-xs text-gray-500 truncate mb-2">{{ item.vehicle?.customer?.name ?? 'Cliente não informado' }}</p>
                <p class="text-xs text-gray-600 line-clamp-1">{{ item.description }}</p>
              </NuxtLink>
              <div class="flex-shrink-0 flex flex-col items-end gap-2">
                <div class="flex items-center gap-2">
                  <SharePopup v-if="item.tracking_token" :token="item.tracking_token" />
                  <p class="text-sm font-medium text-gray-800">{{ formatDate(item.estimated_delivery) }}</p>
                </div>
                <span
                  class="inline-block text-xs font-medium px-2 py-0.5 rounded-full"
                  :class="isOverdue(item.estimated_delivery)
                    ? 'bg-red-100 text-red-700'
                    : isToday(item.estimated_delivery)
                      ? 'bg-orange-100 text-orange-700'
                      : 'bg-blue-50 text-blue-700'"
                >
                  {{ daysUntil(item.estimated_delivery) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Retornos -->
      <div>
        <div class="flex items-center gap-2 mb-3">
          <div class="w-2 h-2 rounded-full bg-orange-500" />
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Retornos Agendados</h2>
          <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-medium">{{ filteredReturns.length }}</span>
        </div>

        <div v-if="filteredReturns.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
          <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="text-sm text-gray-400">Nenhum retorno agendado</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="item in filteredReturns"
            :key="item.id"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-orange-200 transition-colors"
          >
            <div class="flex items-start justify-between gap-3">
              <NuxtLink :to="`/veiculos/${item.vehicle_id}`" class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-semibold text-gray-900 text-sm">{{ item.vehicle?.plate }}</span>
                  <span class="text-gray-400 text-sm">{{ item.vehicle?.brand }} {{ item.vehicle?.model }}</span>
                </div>
                <p class="text-xs text-gray-500 truncate mb-2">{{ item.vehicle?.customer?.name ?? 'Cliente não informado' }}</p>
                <p v-if="item.return_reason" class="text-xs text-gray-600 line-clamp-1">
                  <span class="text-gray-400">Motivo:</span> {{ item.return_reason }}
                </p>
                <p v-else class="text-xs text-gray-600 line-clamp-1">{{ item.description }}</p>
              </NuxtLink>
              <div class="flex-shrink-0 flex flex-col items-end gap-2">
                <div class="flex items-center gap-2">
                  <SharePopup v-if="item.tracking_token" :token="item.tracking_token" />
                  <p class="text-sm font-medium text-gray-800">{{ formatDate(item.return_date) }}</p>
                </div>
                <span
                  class="inline-block text-xs font-medium px-2 py-0.5 rounded-full"
                  :class="isOverdue(item.return_date)
                    ? 'bg-red-100 text-red-700'
                    : isToday(item.return_date)
                      ? 'bg-orange-100 text-orange-700'
                      : 'bg-orange-50 text-orange-700'"
                >
                  {{ daysUntil(item.return_date) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
