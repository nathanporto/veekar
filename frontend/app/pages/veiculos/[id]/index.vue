<script setup lang="ts">
import { useVehiclesStore } from '~/stores/vehicles'
import { useServiceHistoryStore } from '~/stores/serviceHistory'
import type { Vehicle } from '~/types'

const vehiclesStore = useVehiclesStore()
const historyStore = useServiceHistoryStore()
const config = useRuntimeConfig()
const token = useCookie('veekar_token')
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

async function downloadChecklist(historyId: number) {
  const res = await fetch(`${config.public.apiBase}/vehicles/${id}/service-histories/${historyId}/checklist-pdf`, {
    headers: { Authorization: `Bearer ${token.value}` },
  })
  if (!res.ok) return
  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `checklist-${historyId}.pdf`
  a.click()
  URL.revokeObjectURL(url)
}

async function downloadClientSummary(historyId: number) {
  const res = await fetch(`${config.public.apiBase}/vehicles/${id}/service-histories/${historyId}/client-summary-pdf`, {
    headers: { Authorization: `Bearer ${token.value}` },
  })
  if (!res.ok) return
  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `resumo-${historyId}.pdf`
  a.click()
  URL.revokeObjectURL(url)
}

const updatingPayment = ref<number | null>(null)
const partialModalFor = ref<{ historyId: number; amount: number } | null>(null)
const savingPartial = ref(false)

async function changePaymentStatus(historyId: number, status: 'pendente' | 'parcial' | 'pago', amount: number) {
  if (status === 'parcial') {
    partialModalFor.value = { historyId, amount }
    return
  }
  updatingPayment.value = historyId
  try {
    await historyStore.updatePaymentStatus(id, historyId, status)
  } finally {
    updatingPayment.value = null
  }
}

async function confirmPartialPayment(value: number) {
  if (!partialModalFor.value) return
  savingPartial.value = true
  try {
    await historyStore.updatePaymentStatus(id, partialModalFor.value.historyId, 'parcial', value)
    partialModalFor.value = null
  } finally {
    savingPartial.value = false
  }
}

const paymentStatusLabels: Record<string, string> = { pendente: 'Pendente', parcial: 'Pago parcial', pago: 'Pago' }

function formatCurrency(value: string | null | undefined) {
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
                {{ vehicle.plate ?? 'Sem placa' }}
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
                <p class="font-medium text-gray-900">{{ vehicle.color ?? '—' }}</p>
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

      <PartialPaymentModal
        :open="!!partialModalFor"
        :total-amount="partialModalFor?.amount ?? 0"
        :loading="savingPartial"
        @confirm="confirmPartialPayment"
        @cancel="partialModalFor = null"
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
                  <select
                    v-if="h.amount"
                    :value="h.payment_status ?? 'pendente'"
                    :disabled="updatingPayment === h.id"
                    :title="paymentStatusLabels[h.payment_status ?? 'pendente']"
                    class="text-xs font-semibold rounded px-2 py-0.5 border-0 cursor-pointer disabled:opacity-50"
                    :class="{
                      'bg-red-50 text-red-600': (h.payment_status ?? 'pendente') === 'pendente',
                      'bg-amber-50 text-amber-700': h.payment_status === 'parcial',
                      'bg-green-50 text-green-700': h.payment_status === 'pago',
                    }"
                    @click.stop
                    @change="changePaymentStatus(h.id, ($event.target as HTMLSelectElement).value as 'pendente' | 'parcial' | 'pago', Number(h.amount))"
                  >
                    <option value="pendente">Pendente</option>
                    <option value="parcial">Pago parcial</option>
                    <option value="pago">Pago</option>
                  </select>
                </div>
                <p v-if="h.payment_status === 'parcial'" class="text-xs text-amber-600 mt-1">
                  Pago: {{ formatCurrency(h.amount_paid) }} de {{ formatCurrency(h.amount) }}
                </p>
                <p v-if="h.employee" class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  {{ h.employee.name }}
                  <span v-if="h.payment_status === 'pago' && h.commission_amount" class="text-green-600 font-medium">
                    · comissão {{ formatCurrency(h.commission_amount) }}
                  </span>
                </p>
                <p class="text-sm text-gray-900 font-medium">{{ h.description }}</p>
                <p v-if="h.notes" class="text-sm text-gray-500 mt-1">{{ h.notes }}</p>

                <!-- Retorno agendado -->
                <div v-if="h.return_date" class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                  :class="new Date(h.return_date + 'T00:00:00') < new Date() ? 'bg-red-50 text-red-600' : 'bg-orange-50 text-orange-600'">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Retorno {{ formatDate(h.return_date) }} ({{ daysUntil(h.return_date) }})
                  <span v-if="h.return_reason" class="text-opacity-80">· {{ h.return_reason }}</span>
                </div>

                <!-- Badge seguro -->
                <span
                  v-if="h.insurance_status"
                  class="mt-2 ml-2 inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                  :class="{
                    'bg-yellow-50 text-yellow-700': h.insurance_status === 'aguardando',
                    'bg-green-50 text-green-700': h.insurance_status === 'aprovado',
                    'bg-red-50 text-red-600': h.insurance_status === 'recusado',
                  }"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  Seguro {{ h.insurer ? `· ${h.insurer}` : '' }}
                  <span class="capitalize">· {{ h.insurance_status }}</span>
                </span>

                <!-- Etapa do serviço -->
                <span
                  v-if="(h as any).service_status && (h as any).service_status !== 'entregue'"
                  class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                  :class="{
                    'bg-gray-100 text-gray-600':   (h as any).service_status === 'recebido',
                    'bg-blue-50 text-blue-700':    (h as any).service_status === 'em_diagnostico',
                    'bg-amber-50 text-amber-700':  (h as any).service_status === 'aguardando_pecas',
                    'bg-indigo-50 text-indigo-700':(h as any).service_status === 'em_servico',
                    'bg-green-50 text-green-700':  (h as any).service_status === 'pronto',
                  }"
                >
                  {{
                    { recebido: 'Recebido', em_diagnostico: 'Em diagnóstico', aguardando_pecas: 'Aguardando peças',
                      em_servico: 'Em serviço', pronto: 'Pronto' }[(h as any).service_status] ?? (h as any).service_status
                  }}
                </span>

                <!-- Previsão de entrega -->
                <div v-if="(h as any).estimated_delivery" class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                  :class="new Date((h as any).estimated_delivery + 'T00:00:00') < new Date() ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                  </svg>
                  Entrega {{ formatDate((h as any).estimated_delivery) }} ({{ daysUntil((h as any).estimated_delivery) }})
                </div>

                <!-- Botão checklist PDF -->
                <button
                  v-if="h.entry_checklist"
                  type="button"
                  class="mt-2 ml-2 inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
                  @click.stop="downloadChecklist(h.id)"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Checklist PDF
                </button>

                <!-- Botão resumo para o cliente -->
                <button
                  v-if="h.amount"
                  type="button"
                  class="mt-2 ml-2 inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors"
                  @click.stop="downloadClientSummary(h.id)"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Resumo do Cliente
                </button>

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
              <div class="flex items-center gap-2 flex-shrink-0">
                <SharePopup v-if="(h as any).tracking_token" :token="(h as any).tracking_token" />
                <NuxtLink
                  :to="`/veiculos/${id}/atendimento/${h.id}/editar`"
                  title="Editar atendimento"
                  class="text-gray-400 hover:text-blue-600"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </NuxtLink>
                <button
                  class="text-red-400 hover:text-red-600 disabled:opacity-40"
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
      </div>
    </template>
  </div>
</template>
