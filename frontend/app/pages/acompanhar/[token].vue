<script setup lang="ts">
definePageMeta({ layout: false })

const route = useRoute()
const config = useRuntimeConfig()

type ServiceStatus = 'recebido' | 'em_diagnostico' | 'aguardando_pecas' | 'em_servico' | 'pronto' | 'entregue'

const STAGES: { key: ServiceStatus; label: string; icon: string }[] = [
  { key: 'recebido',         label: 'Recebido',         icon: '📋' },
  { key: 'em_diagnostico',   label: 'Diagnóstico',      icon: '🔍' },
  { key: 'aguardando_pecas', label: 'Aguard. peças',    icon: '📦' },
  { key: 'em_servico',       label: 'Em serviço',       icon: '🔧' },
  { key: 'pronto',           label: 'Pronto',           icon: '✅' },
  { key: 'entregue',         label: 'Entregue',         icon: '🏁' },
]

interface ServiceData {
  id: number
  description: string
  service_date: string | null
  estimated_delivery: string | null
  service_status: ServiceStatus | null
  notes: string | null
  amount: string | null
  items: { description: string; quantity: number; unit_price: string }[]
  vehicle: { plate: string; brand: string; model: string; year: number; color: string }
  customer: { name: string; phone: string } | null
}

const currentStageIndex = computed(() => {
  if (!data.value?.service_status) return 0
  return STAGES.findIndex(s => s.key === data.value!.service_status)
})

const data = ref<ServiceData | null>(null)
const loading = ref(true)
const notFound = ref(false)

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
  if (!dt) return null
  const diff = Math.ceil((dt.getTime() - new Date().setHours(0, 0, 0, 0)) / 86400000)
  if (diff < 0) return `${Math.abs(diff)} dia(s) de atraso`
  if (diff === 0) return 'Hoje!'
  if (diff === 1) return 'Amanhã'
  return `Em ${diff} dias`
}

function isOverdue(d: string | null | undefined) {
  const dt = parseDate(d)
  if (!dt) return false
  return dt.getTime() < new Date().setHours(0, 0, 0, 0)
}

function formatCurrency(value: string | number | null) {
  if (!value) return null
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
}

onMounted(async () => {
  try {
    const res = await fetch(`${config.public.apiBase}/public/service/${route.params.token}`)
    if (!res.ok) { notFound.value = true; return }
    data.value = await res.json()
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-lg mx-auto px-4 py-4 flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
          </svg>
        </div>
        <span class="font-bold text-gray-900 text-lg">Veekar</span>
        <span class="text-gray-300">|</span>
        <span class="text-sm text-gray-500">Acompanhamento de serviço</span>
      </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-8">
      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="bg-white rounded-xl p-5 animate-pulse h-24" />
      </div>

      <!-- Not found -->
      <div v-else-if="notFound" class="text-center py-16">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-700 mb-1">Serviço não encontrado</h2>
        <p class="text-sm text-gray-400">Este link pode ser inválido ou ter expirado.</p>
      </div>

      <!-- Content -->
      <div v-else-if="data" class="space-y-4">
        <!-- Veículo -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <span class="font-mono text-2xl font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-lg tracking-widest">
              {{ data.vehicle.plate }}
            </span>
            <span class="text-sm text-gray-500">{{ data.vehicle.brand }} {{ data.vehicle.model }} {{ data.vehicle.year }}</span>
          </div>
          <div v-if="data.customer" class="text-sm text-gray-600">
            <span class="text-gray-400">Proprietário:</span>
            <span class="font-medium text-gray-800 ml-1">{{ data.customer.name }}</span>
          </div>
        </div>

        <!-- Barra de etapas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Status do serviço</p>
          <div class="relative">
            <!-- Linha de progresso -->
            <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-200 mx-4" />
            <div
              class="absolute top-4 left-0 h-0.5 bg-blue-500 mx-4 transition-all duration-500"
              :style="{ width: `calc(${currentStageIndex / (STAGES.length - 1) * 100}% - 2rem)` }"
            />
            <!-- Pontos -->
            <div class="relative flex justify-between">
              <div
                v-for="(stage, i) in STAGES"
                :key="stage.key"
                class="flex flex-col items-center gap-1.5"
              >
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center text-sm border-2 transition-all duration-300 bg-white z-10 relative"
                  :class="i < currentStageIndex
                    ? 'border-blue-500 bg-blue-500 text-white'
                    : i === currentStageIndex
                      ? 'border-blue-500 text-blue-600 shadow-md shadow-blue-100'
                      : 'border-gray-200 text-gray-300'"
                >
                  <span v-if="i < currentStageIndex">✓</span>
                  <span v-else>{{ stage.icon }}</span>
                </div>
                <span
                  class="text-center leading-tight"
                  :class="[
                    i === currentStageIndex ? 'text-blue-700 font-semibold' : 'text-gray-400',
                    'text-[10px] w-12',
                  ]"
                >
                  {{ stage.label }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Previsão de entrega — destaque -->
        <div
          v-if="data.estimated_delivery"
          class="rounded-xl p-5 border-2"
          :class="isOverdue(data.estimated_delivery)
            ? 'bg-red-50 border-red-200'
            : 'bg-blue-50 border-blue-200'"
        >
          <p class="text-xs font-semibold uppercase tracking-wide mb-1"
            :class="isOverdue(data.estimated_delivery) ? 'text-red-500' : 'text-blue-500'">
            Previsão de Entrega
          </p>
          <p class="text-3xl font-bold mb-1"
            :class="isOverdue(data.estimated_delivery) ? 'text-red-700' : 'text-blue-700'">
            {{ formatDate(data.estimated_delivery) }}
          </p>
          <p class="text-sm font-medium"
            :class="isOverdue(data.estimated_delivery) ? 'text-red-500' : 'text-blue-500'">
            {{ daysUntil(data.estimated_delivery) }}
          </p>
        </div>

        <!-- Serviço -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Serviço</p>
          <p class="text-gray-900 font-medium">{{ data.description }}</p>
          <p v-if="data.notes" class="text-sm text-gray-500 mt-2">{{ data.notes }}</p>
          <p class="text-xs text-gray-400 mt-3">Entrada: {{ formatDate(data.service_date) }}</p>
        </div>

        <!-- Itens (se tiver) -->
        <div v-if="data.items.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Peças e Serviços</p>
          <div class="space-y-2">
            <div v-for="(item, i) in data.items" :key="i" class="flex items-center justify-between text-sm">
              <span class="text-gray-700">{{ item.description }}
                <span class="text-gray-400">× {{ Number(item.quantity) }}</span>
              </span>
              <span class="font-medium text-gray-900">{{ formatCurrency(String(Number(item.quantity) * Number(item.unit_price))) }}</span>
            </div>
          </div>
          <div v-if="data.amount" class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-sm font-semibold">
            <span class="text-gray-700">Total</span>
            <span class="text-gray-900">{{ formatCurrency(data.amount) }}</span>
          </div>
        </div>

        <!-- Rodapé -->
        <p class="text-center text-xs text-gray-400 pt-2">
          Gerado por Veekar · Sistema de gestão automotiva
        </p>
      </div>
    </div>
  </div>
</template>
