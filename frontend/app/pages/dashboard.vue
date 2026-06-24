<script setup lang="ts">
import type { ServiceHistory, Vehicle, Customer } from '~/types'

const api = useApi()

const stats = ref({ customers: 0, vehicles: 0, services: 0 })
const recentServices = ref<(ServiceHistory & { vehicle: Vehicle & { customer: Customer } })[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [s, r] = await Promise.all([
      api.get<typeof stats.value>('/dashboard/stats'),
      api.get<typeof recentServices.value>('/dashboard/recent-services'),
    ])
    stats.value = s
    recentServices.value = r
  } finally {
    loading.value = false
  }
})

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('pt-BR')
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
