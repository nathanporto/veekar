<script setup lang="ts">
import { usePaymentsStore } from '~/stores/payments'
import { usePaymentRemindersStore } from '~/stores/paymentReminders'

const store = usePaymentsStore()
const remindersStore = usePaymentRemindersStore()

const activeTab = ref<'pagamentos' | 'lembretes'>('pagamentos')

onMounted(() => {
  store.fetchAll()
  remindersStore.fetchAll()
})

function formatCurrency(value: string | number | null | undefined) {
  if (value === null || value === undefined) return '—'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
}

function parseDate(date: string) {
  return new Date(date.includes('T') ? date : date + 'T00:00:00')
}

function formatDate(date: string) {
  const d = parseDate(date)
  return isNaN(d.getTime()) ? '—' : d.toLocaleDateString('pt-BR')
}

function daysUntil(date: string) {
  const d = parseDate(date)
  if (isNaN(d.getTime())) return ''
  const diff = Math.ceil((d.getTime() - new Date().setHours(0, 0, 0, 0)) / 86400000)
  if (diff < 0) return `${Math.abs(diff)}d atraso`
  if (diff === 0) return 'hoje'
  return `em ${diff}d`
}

// Pagamentos
const updatingPayment = ref<number | null>(null)
const partialModalFor = ref<{ historyId: number; vehicleId: number; amount: number } | null>(null)
const savingPartial = ref(false)

async function changeStatus(historyId: number, vehicleId: number, status: 'pendente' | 'parcial' | 'pago', amount: number) {
  if (status === 'parcial') {
    partialModalFor.value = { historyId, vehicleId, amount }
    return
  }
  updatingPayment.value = historyId
  try {
    await store.updateStatus(vehicleId, historyId, status)
  } finally {
    updatingPayment.value = null
  }
}

async function confirmPartialPayment(value: number) {
  if (!partialModalFor.value) return
  savingPartial.value = true
  try {
    await store.updateStatus(partialModalFor.value.vehicleId, partialModalFor.value.historyId, 'parcial', value)
    partialModalFor.value = null
  } finally {
    savingPartial.value = false
  }
}

// Lembretes de pagamento
const showNewReminderForm = ref(false)
const newReminder = reactive({ description: '', amount: '', due_date: '' })
const creatingReminder = ref(false)
const reminderError = ref('')
const togglingReminder = ref<number | null>(null)
const deletingReminder = ref<number | null>(null)

async function createReminder() {
  creatingReminder.value = true
  reminderError.value = ''
  try {
    await remindersStore.create({
      description: newReminder.description,
      amount: newReminder.amount ? Number(newReminder.amount) : null,
      due_date: newReminder.due_date,
    })
    newReminder.description = ''
    newReminder.amount = ''
    newReminder.due_date = ''
    showNewReminderForm.value = false
  } catch (e) {
    reminderError.value = e instanceof Error ? e.message : 'Erro ao criar lembrete.'
  } finally {
    creatingReminder.value = false
  }
}

async function toggleReminderPaid(id: number, paid: boolean) {
  togglingReminder.value = id
  try {
    await remindersStore.markPaid(id, paid)
  } finally {
    togglingReminder.value = null
  }
}

async function deleteReminder(id: number) {
  deletingReminder.value = id
  try {
    await remindersStore.remove(id)
  } finally {
    deletingReminder.value = null
  }
}
</script>

<template>
  <div class="space-y-5">
    <PartialPaymentModal
      :open="!!partialModalFor"
      :total-amount="partialModalFor?.amount ?? 0"
      :loading="savingPartial"
      @confirm="confirmPartialPayment"
      @cancel="partialModalFor = null"
    />

    <div>
      <h1 class="text-2xl font-bold text-gray-900">Pagamentos</h1>
      <p class="text-gray-500 text-sm mt-1">Controle de pagamentos dos atendimentos</p>
    </div>

    <!-- Abas -->
    <div class="border-b border-gray-200 flex gap-6">
      <button
        class="pb-3 text-sm font-medium border-b-2 transition-colors"
        :class="activeTab === 'pagamentos' ? 'text-blue-600 border-blue-600' : 'text-gray-400 border-transparent hover:text-gray-600'"
        @click="activeTab = 'pagamentos'"
      >
        Pagamentos
      </button>
      <button
        class="pb-3 text-sm font-medium border-b-2 transition-colors"
        :class="activeTab === 'lembretes' ? 'text-blue-600 border-blue-600' : 'text-gray-400 border-transparent hover:text-gray-600'"
        @click="activeTab = 'lembretes'"
      >
        Lembrete de Pagamentos
      </button>
      <span class="pb-3 text-sm font-medium text-gray-300 cursor-not-allowed flex items-center gap-1.5">
        Notas Fiscais
        <span class="text-[10px] font-bold bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded-full">Em breve</span>
      </span>
    </div>

    <!-- Aba: Pagamentos -->
    <div v-if="activeTab === 'pagamentos'" class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 5" :key="i" class="h-16 bg-gray-100 rounded animate-pulse" />
      </div>

      <div v-else-if="store.payments.length === 0" class="p-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <p>Nenhum atendimento com valor registrado ainda.</p>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div
          v-for="p in store.payments"
          :key="p.id"
          class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap"
        >
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <span class="font-mono text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                {{ p.vehicle?.plate ?? 'Sem placa' }}
              </span>
              <span class="text-sm font-medium text-gray-900">{{ p.vehicle?.customer?.name ?? '—' }}</span>
              <span class="text-xs text-gray-400">{{ formatDate(p.service_date) }}</span>
            </div>
            <p class="text-sm text-gray-600">{{ p.description }}</p>
            <p v-if="p.payment_status === 'parcial'" class="text-xs text-amber-600 mt-1">
              Pago: {{ formatCurrency(p.amount_paid) }} de {{ formatCurrency(p.amount) }}
            </p>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(p.amount) }}</span>
            <select
              :value="p.payment_status ?? 'pendente'"
              :disabled="updatingPayment === p.id"
              class="text-xs font-semibold rounded px-2 py-1 border-0 cursor-pointer disabled:opacity-50"
              :class="{
                'bg-red-50 text-red-600': (p.payment_status ?? 'pendente') === 'pendente',
                'bg-amber-50 text-amber-700': p.payment_status === 'parcial',
                'bg-green-50 text-green-700': p.payment_status === 'pago',
              }"
              @change="changeStatus(p.id, p.vehicle_id, ($event.target as HTMLSelectElement).value as 'pendente' | 'parcial' | 'pago', Number(p.amount))"
            >
              <option value="pendente">Pendente</option>
              <option value="parcial">Pago parcial</option>
              <option value="pago">Pago</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Aba: Lembrete de Pagamentos -->
    <div v-else-if="activeTab === 'lembretes'" class="space-y-4">
      <div class="flex justify-end">
        <button
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
          @click="showNewReminderForm = !showNewReminderForm"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Lembrete
        </button>
      </div>

      <div v-if="showNewReminderForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h2 class="text-base font-semibold text-gray-900">Novo lembrete de pagamento</h2>
        <div v-if="reminderError" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg">{{ reminderError }}</div>
        <form class="grid grid-cols-1 md:grid-cols-3 gap-4" @submit.prevent="createReminder">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">O que precisa pagar?</label>
            <input v-model="newReminder.description" type="text" required placeholder="Ex: Fornecedor de peças"
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Valor <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input v-model="newReminder.amount" type="number" step="0.01" min="0" placeholder="0,00"
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de vencimento</label>
            <input v-model="newReminder.due_date" type="date" required
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div class="md:col-span-3 flex justify-end gap-2">
            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800" @click="showNewReminderForm = false">
              Cancelar
            </button>
            <button type="submit" :disabled="creatingReminder"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
              {{ creatingReminder ? 'Salvando...' : 'Salvar lembrete' }}
            </button>
          </div>
        </form>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div v-if="remindersStore.loading" class="p-6 space-y-3">
          <div v-for="i in 3" :key="i" class="h-16 bg-gray-100 rounded animate-pulse" />
        </div>

        <div v-else-if="remindersStore.reminders.length === 0" class="p-12 text-center text-gray-400">
          <p>Nenhum lembrete cadastrado ainda.</p>
          <button class="text-blue-600 hover:underline text-sm mt-1 inline-block" @click="showNewReminderForm = true">
            Criar primeiro lembrete
          </button>
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div
            v-for="r in remindersStore.reminders"
            :key="r.id"
            class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap"
            :class="r.paid ? 'opacity-50' : ''"
          >
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900" :class="r.paid ? 'line-through' : ''">{{ r.description }}</p>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-xs text-gray-500">Vencimento: {{ formatDate(r.due_date) }}</span>
                <span
                  v-if="!r.paid"
                  class="text-xs font-medium"
                  :class="parseDate(r.due_date) < new Date(new Date().setHours(0,0,0,0)) ? 'text-red-500' : 'text-amber-600'"
                >
                  ({{ daysUntil(r.due_date) }})
                </span>
              </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
              <span v-if="r.amount" class="text-sm font-semibold text-gray-900">{{ formatCurrency(r.amount) }}</span>
              <button
                type="button"
                :disabled="togglingReminder === r.id"
                class="text-xs font-medium px-3 py-1.5 rounded-full transition-colors disabled:opacity-50"
                :class="r.paid ? 'bg-gray-100 text-gray-500 hover:bg-gray-200' : 'bg-green-50 text-green-700 hover:bg-green-100'"
                @click="toggleReminderPaid(r.id, !r.paid)"
              >
                {{ r.paid ? 'Marcar como não pago' : 'Marcar como pago' }}
              </button>
              <button
                type="button"
                :disabled="deletingReminder === r.id"
                class="text-red-400 hover:text-red-600 disabled:opacity-40"
                title="Excluir lembrete"
                @click="deleteReminder(r.id)"
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
  </div>
</template>
