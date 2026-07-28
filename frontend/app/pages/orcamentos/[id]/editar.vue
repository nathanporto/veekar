<script setup lang="ts">
import { useQuotesStore } from '~/stores/quotes'

const store = useQuotesStore()
const api = useApi()
const route = useRoute()
const router = useRouter()
const quoteId = Number(route.params.id)

const loadingData = ref(true)
const notFound = ref(false)

// ─── Busca de cliente ────────────────────────────────────────────
const customerSearch = ref('')
const customerResults = ref<{ id: number; name: string; cpf?: string; email?: string }[]>([])
const selectedCustomer = ref<{ id: number; name: string } | null>(null)
const searchingCustomer = ref(false)
const showDropdown = ref(false)

let debounceTimer: ReturnType<typeof setTimeout>

watch(customerSearch, (val) => {
  clearTimeout(debounceTimer)
  if (!val.trim()) {
    customerResults.value = []
    showDropdown.value = false
    return
  }
  debounceTimer = setTimeout(() => searchCustomers(val), 300)
})

async function searchCustomers(q: string) {
  searchingCustomer.value = true
  try {
    const res = await api.get<{ data: typeof customerResults.value }>(`/customers?search=${encodeURIComponent(q)}`)
    customerResults.value = (res as any).data ?? (res as any)
    showDropdown.value = true
  } finally {
    searchingCustomer.value = false
  }
}

function selectCustomer(c: { id: number; name: string }) {
  selectedCustomer.value = c
  customerSearch.value = c.name
  showDropdown.value = false
  form.customer_id = c.id
  loadVehicles(c.id)
}

function clearCustomer() {
  selectedCustomer.value = null
  customerSearch.value = ''
  form.customer_id = null
  form.vehicle_id = null
  vehicles.value = []
  customerResults.value = []
}

// ─── Veículos do cliente ─────────────────────────────────────────
const vehicles = ref<{ id: number; plate: string; brand: string; model: string }[]>([])
const loadingVehicles = ref(false)

async function loadVehicles(customerId: number, keepSelected?: number | null) {
  vehicles.value = []
  loadingVehicles.value = true
  try {
    const res = await api.get<{ data: typeof vehicles.value }>(`/vehicles?customer_id=${customerId}`)
    vehicles.value = (res as any).data ?? (res as any)
    form.vehicle_id = keepSelected ?? null
  } finally {
    loadingVehicles.value = false
  }
}

// ─── Formulário ───────────────────────────────────────────────────
const form = reactive({
  customer_id: null as number | null,
  vehicle_id: null as number | null,
  notes: '',
  expires_at: '',
  items: [{ description: '', quantity: 1, unit_price: 0 }],
})

onMounted(async () => {
  try {
    const quote = await api.get<any>(`/quotes/${quoteId}`)

    if (quote.status !== 'pending') {
      notFound.value = true
      return
    }

    form.notes = quote.notes ?? ''
    form.expires_at = quote.expires_at ? quote.expires_at.slice(0, 10) : ''
    form.items = quote.items.map((i: any) => ({
      description: i.description,
      quantity: i.quantity,
      unit_price: Number(i.unit_price),
    }))

    if (quote.customer) {
      selectedCustomer.value = quote.customer
      customerSearch.value = quote.customer.name
      form.customer_id = quote.customer.id
      await loadVehicles(quote.customer.id, quote.vehicle?.id ?? null)
    }
  } catch {
    notFound.value = true
  } finally {
    loadingData.value = false
  }
})

function addItem() {
  form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

function removeItem(i: number) {
  if (form.items.length > 1) form.items.splice(i, 1)
}

const total = computed(() =>
  form.items.reduce((sum, item) => sum + item.quantity * item.unit_price, 0),
)

function formatCurrency(value: number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

const submitting = ref(false)
const error = ref('')

async function submit() {
  if (form.items.some(i => !i.description.trim())) {
    error.value = 'Preencha a descrição de todos os itens.'
    return
  }
  submitting.value = true
  error.value = ''
  try {
    await store.updateQuote(quoteId, {
      customer_id: form.customer_id,
      vehicle_id: form.vehicle_id,
      notes: form.notes || undefined,
      expires_at: form.expires_at || undefined,
      items: form.items.map(i => ({
        description: i.description,
        quantity: i.quantity,
        unit_price: i.unit_price,
      })),
    })
    await router.push('/orcamentos')
  } catch (e: any) {
    error.value = e.message ?? 'Erro ao salvar orçamento.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="space-y-6 max-w-2xl">
    <div class="flex items-center gap-3">
      <NuxtLink to="/orcamentos" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </NuxtLink>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Editar Orçamento</h1>
        <p class="text-gray-500 text-sm mt-0.5">Ajuste os itens e valores do orçamento</p>
      </div>
    </div>

    <div v-if="loadingData" class="space-y-4">
      <div v-for="i in 3" :key="i" class="bg-white rounded-xl p-6 shadow-sm animate-pulse h-32" />
    </div>

    <div v-else-if="notFound" class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
      <p class="text-amber-800 font-medium">Este orçamento não pode mais ser editado.</p>
      <p class="text-amber-700 text-sm mt-1">O cliente já respondeu (aprovou ou recusou), ou o orçamento não foi encontrado.</p>
      <NuxtLink to="/orcamentos" class="inline-block mt-4 text-blue-600 hover:underline text-sm">
        Voltar para orçamentos
      </NuxtLink>
    </div>

    <!-- Formulário -->
    <form v-else class="space-y-5" @submit.prevent="submit">
      <!-- Cliente e Veículo -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
        <h2 class="text-sm font-semibold text-gray-700">Cliente e Veículo <span class="font-normal text-gray-400">(opcional)</span></h2>

        <!-- Busca de cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Pesquisar cliente</label>
          <div class="relative">
            <div class="relative flex items-center">
              <svg class="absolute left-3 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="customerSearch"
                type="text"
                :placeholder="selectedCustomer ? selectedCustomer.name : 'Nome, CPF ou e-mail...'"
                :readonly="!!selectedCustomer"
                class="w-full border border-gray-300 rounded-lg pl-9 pr-9 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="selectedCustomer ? 'bg-blue-50 text-blue-700 cursor-default' : ''"
                @focus="!selectedCustomer && customerSearch && (showDropdown = true)"
              />
              <button
                v-if="selectedCustomer"
                type="button"
                class="absolute right-2 p-1 text-gray-400 hover:text-gray-600"
                @click="clearCustomer"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <svg v-else-if="searchingCustomer" class="absolute right-3 w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
            </div>

            <!-- Dropdown de resultados -->
            <div
              v-if="showDropdown && customerResults.length"
              class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
            >
              <button
                v-for="c in customerResults"
                :key="c.id"
                type="button"
                class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors border-b border-gray-50 last:border-0"
                @click="selectCustomer(c)"
              >
                <p class="text-sm font-medium text-gray-900">{{ c.name }}</p>
                <p v-if="c.cpf || c.email" class="text-xs text-gray-400 mt-0.5">
                  {{ [c.cpf, c.email].filter(Boolean).join(' · ') }}
                </p>
              </button>
            </div>

            <p v-else-if="showDropdown && !searchingCustomer && customerSearch" class="text-xs text-gray-400 mt-2 px-1">
              Nenhum cliente encontrado.
            </p>
          </div>
        </div>

        <!-- Veículo do cliente selecionado -->
        <div v-if="selectedCustomer">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Veículo</label>
          <div v-if="loadingVehicles" class="h-10 bg-gray-100 rounded-lg animate-pulse" />
          <select
            v-else
            v-model="form.vehicle_id"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option :value="null">— Sem veículo —</option>
            <option v-for="v in vehicles" :key="v.id" :value="v.id">
              {{ v.plate }} — {{ v.brand }} {{ v.model }}
            </option>
          </select>
          <p v-if="!loadingVehicles && vehicles.length === 0" class="text-xs text-gray-400 mt-1.5">
            Nenhum veículo cadastrado para este cliente.
          </p>
        </div>
      </div>

      <!-- Itens -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
        <h2 class="text-sm font-semibold text-gray-700">Itens do orçamento</h2>

        <div v-for="(item, i) in form.items" :key="i" class="flex gap-2 items-start">
          <div class="flex-1">
            <input
              v-model="item.description"
              placeholder="Descrição do serviço / peça"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div class="w-16">
            <input
              v-model.number="item.quantity"
              type="number"
              min="1"
              placeholder="Qtd"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
            />
          </div>
          <div class="w-28">
            <input
              v-model.number="item.unit_price"
              type="number"
              min="0"
              step="0.01"
              placeholder="R$ 0,00"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <button
            type="button"
            class="p-2 text-gray-400 hover:text-red-500 transition-colors mt-0.5"
            title="Remover item"
            @click="removeItem(i)"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <button
          type="button"
          class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors"
          @click="addItem"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Adicionar item
        </button>

        <div class="pt-2 border-t border-gray-100 flex justify-end">
          <p class="text-base font-bold text-gray-900">Total: {{ formatCurrency(total) }}</p>
        </div>
      </div>

      <!-- Observações e Validade -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
        <h2 class="text-sm font-semibold text-gray-700">Detalhes adicionais</h2>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Observações</label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Prazo de entrega, condições de pagamento..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Válido até <span class="text-gray-400 font-normal">(opcional)</span></label>
          <input
            v-model="form.expires_at"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">
        {{ error }}
      </div>

      <div class="flex gap-3">
        <button
          type="submit"
          :disabled="submitting"
          class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
        >
          {{ submitting ? 'Salvando...' : 'Salvar alterações' }}
        </button>
        <NuxtLink
          to="/orcamentos"
          class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors"
        >
          Cancelar
        </NuxtLink>
      </div>
    </form>
  </div>
</template>
