<script setup lang="ts">
import { useVehiclesStore } from '~/stores/vehicles'
import { useServiceHistoryStore } from '~/stores/serviceHistory'
import type { Vehicle } from '~/types'

const vehiclesStore = useVehiclesStore()
const historyStore = useServiceHistoryStore()
const route = useRoute()
const id = Number(route.params.id)

const vehicle = ref<Vehicle | null>(null)
const loading = ref(true)
const deleting = ref<number | null>(null)
const showDeleteModal = ref(false)
const pendingDeleteId = ref<number | null>(null)

onMounted(async () => {
  const [v] = await Promise.all([
    vehiclesStore.fetchOne(id),
    historyStore.fetchByVehicle(id),
  ])
  vehicle.value = v
  loading.value = false
})

function askDeleteHistory(historyId: number) {
  pendingDeleteId.value = historyId
  showDeleteModal.value = true
}

async function doDeleteHistory() {
  if (!pendingDeleteId.value) return
  deleting.value = pendingDeleteId.value
  await historyStore.remove(id, pendingDeleteId.value)
  deleting.value = null
  pendingDeleteId.value = null
  showDeleteModal.value = false
}

function formatDate(date: string) {
  return new Date(date + 'T00:00:00').toLocaleDateString('pt-BR')
}

function formatCurrency(value: string | null) {
  if (!value) return null
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <NuxtLink to="/veiculos" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </NuxtLink>
      <h1 class="text-2xl font-bold text-gray-900">Ficha do Veículo</h1>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="i in 2" :key="i" class="bg-white rounded-xl p-6 shadow-sm animate-pulse space-y-3">
        <div class="h-4 bg-gray-200 rounded w-32" />
        <div class="h-6 bg-gray-200 rounded w-48" />
        <div class="h-4 bg-gray-200 rounded w-40" />
      </div>
    </div>

    <template v-else-if="vehicle">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-start justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Dados do Veículo</h2>
            <NuxtLink
              :to="`/veiculos/${id}/editar`"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium"
            >
              Editar
            </NuxtLink>
          </div>

          <div class="space-y-3">
            <div>
              <span class="font-mono text-2xl font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-lg inline-block tracking-widest">
                {{ vehicle.plate }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
              <div>
                <span class="text-gray-500">Marca/Modelo</span>
                <p class="font-medium text-gray-900">{{ vehicle.brand }} {{ vehicle.model }}</p>
              </div>
              <div>
                <span class="text-gray-500">Ano</span>
                <p class="font-medium text-gray-900">{{ vehicle.year }}</p>
              </div>
              <div>
                <span class="text-gray-500">Cor</span>
                <p class="font-medium text-gray-900">{{ vehicle.color }}</p>
              </div>
              <div>
                <span class="text-gray-500">Quilometragem</span>
                <p class="font-medium text-gray-900">{{ vehicle.mileage.toLocaleString('pt-BR') }} km</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-start justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Proprietário</h2>
            <NuxtLink
              v-if="vehicle.customer"
              :to="`/clientes/${vehicle.customer.id}/editar`"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium"
            >
              Editar
            </NuxtLink>
          </div>

          <div v-if="vehicle.customer" class="space-y-2 text-sm">
            <div>
              <span class="text-gray-500">Nome</span>
              <p class="font-medium text-gray-900 text-base">{{ vehicle.customer.name }}</p>
            </div>
            <div v-if="vehicle.customer.cpf">
              <span class="text-gray-500">CPF</span>
              <p class="font-medium text-gray-900 font-mono">{{ vehicle.customer.cpf }}</p>
            </div>
            <div>
              <span class="text-gray-500">Telefone</span>
              <p class="font-medium text-gray-900">{{ vehicle.customer.phone }}</p>
            </div>
            <div v-if="vehicle.customer.email">
              <span class="text-gray-500">E-mail</span>
              <p class="font-medium text-gray-900">{{ vehicle.customer.email }}</p>
            </div>
            <div v-if="vehicle.customer.notes">
              <span class="text-gray-500">Observações</span>
              <p class="text-gray-700 mt-0.5">{{ vehicle.customer.notes }}</p>
            </div>
          </div>
        </div>
      </div>

      <ConfirmModal
        :open="showDeleteModal"
        title="Excluir atendimento"
        description="Este atendimento e todos os seus itens serão removidos permanentemente."
        confirm-label="Sim, excluir"
        :loading="deleting !== null"
        @confirm="doDeleteHistory"
        @cancel="showDeleteModal = false"
      />

      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-base font-semibold text-gray-900">Histórico de Atendimentos</h2>
            <p class="text-gray-500 text-sm mt-0.5">{{ historyStore.histories.length }} atendimento(s)</p>
          </div>
          <NuxtLink
            :to="`/veiculos/${id}/atendimento/novo`"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Atendimento
          </NuxtLink>
        </div>

        <div v-if="historyStore.loading" class="p-6 space-y-3">
          <div v-for="i in 3" :key="i" class="h-20 bg-gray-100 rounded animate-pulse" />
        </div>

        <div v-else-if="historyStore.histories.length === 0" class="p-12 text-center text-gray-400">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <p>Nenhum atendimento registrado.</p>
          <NuxtLink :to="`/veiculos/${id}/atendimento/novo`" class="text-blue-600 hover:underline text-sm mt-1 inline-block">
            Registrar primeiro atendimento
          </NuxtLink>
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div
            v-for="h in historyStore.histories"
            :key="h.id"
            class="px-6 py-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-1.5 flex-wrap">
                  <span class="text-sm font-semibold text-gray-900">{{ formatDate(h.service_date) }}</span>
                  <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                    {{ h.mileage.toLocaleString('pt-BR') }} km
                  </span>
                  <span v-if="h.amount" class="text-xs font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded">
                    {{ formatCurrency(h.amount) }}
                  </span>
                </div>
                <p class="text-sm text-gray-900 font-medium">{{ h.description }}</p>
                <p v-if="h.notes" class="text-sm text-gray-500 mt-1">{{ h.notes }}</p>

                <!-- Itens do atendimento -->
                <div v-if="h.items && h.items.length > 0" class="mt-3 border border-gray-100 rounded-lg overflow-hidden">
                  <table class="w-full text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wide">
                      <tr>
                        <th class="text-left px-3 py-2 font-medium">Descrição</th>
                        <th class="text-center px-3 py-2 font-medium w-16">Qtd</th>
                        <th class="text-right px-3 py-2 font-medium w-24">Unit.</th>
                        <th class="text-right px-3 py-2 font-medium w-24">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                      <tr v-for="item in h.items" :key="item.id" class="bg-white">
                        <td class="px-3 py-2 text-gray-800">{{ item.description }}</td>
                        <td class="px-3 py-2 text-gray-600 text-center">{{ Number(item.quantity) }}</td>
                        <td class="px-3 py-2 text-gray-600 text-right">{{ formatCurrency(item.unit_price) }}</td>
                        <td class="px-3 py-2 text-gray-900 font-medium text-right">
                          {{ formatCurrency(String(Number(item.quantity) * Number(item.unit_price))) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <button
                class="text-red-400 hover:text-red-600 flex-shrink-0 disabled:opacity-40"
                :disabled="deleting === h.id"
                title="Excluir atendimento"
                @click="askDeleteHistory(h.id)"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
