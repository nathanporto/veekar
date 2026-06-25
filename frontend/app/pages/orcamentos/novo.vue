<script setup lang="ts">
import { useQuotesStore } from '~/stores/quotes'

const store = useQuotesStore()
const api = useApi()

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

async function loadVehicles(customerId: number) {
  form.vehicle_id = null
  vehicles.value = []
  loadingVehicles.value = true
  try {
    const res = await api.get<{ data: typeof vehicles.value }>(`/vehicles?customer_id=${customerId}`)
    vehicles.value = (res as any).data ?? (res as any)
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
const createdToken = ref('')

async function submit() {
  if (form.items.some(i => !i.description.trim())) {
    error.value = 'Preencha a descrição de todos os itens.'
    return
  }
  submitting.value = true
  error.value = ''
  try {
    const quote = await store.createQuote({
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
    createdToken.value = quote.token
  } catch (e: any) {
    error.value = e.message ?? 'Erro ao criar orçamento.'
  } finally {
    submitting.value = false
  }
}

const publicLink = computed(() =>
  createdToken.value ? `${window.location.origin}/orcamento/${createdToken.value}` : '',
)

const copied = ref(false)
async function copyLink() {
  await navigator.clipboard.writeText(publicLink.value)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
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
        <h1 class="text-2xl font-bold text-gray-900">Novo Orçamento</h1>
        <p class="text-gray-500 text-sm mt-0.5">Preencha os dados e gere o link para o cliente</p>
      </div>
    </div>

    <!-- Sucesso: link gerado -->
    <div v-if="createdToken" class="bg-green-50 border border-green-200 rounded-xl p-6 space-y-4">
      <div class="flex items-center gap-2 text-green-700">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="font-semibold">Orçamento criado com sucesso!</span>
      </div>
      <p class="text-sm text-green-700">Copie o link abaixo e envie pelo WhatsApp para o cliente:</p>
      <div class="flex items-center gap-2">
        <input
          :value="publicLink"
          readonly
          class="flex-1 text-sm bg-white border border-green-300 rounded-lg px-3 py-2 text-gray-700 select-all"
        />
        <button
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap"
          @click="copyLink"
        >
          {{ copied ? 'Copiado!' : 'Copiar link' }}
        </button>
      </div>
      <div class="flex gap-3 flex-wrap">
        <a
          :href="`https://wa.me/?text=${encodeURIComponent('Olá! Segue o link do seu orçamento: ' + publicLink)}`"
          target="_blank"
          class="flex items-center gap-2 px-4 py-2 bg-[#25D366] hover:bg-[#1ebe5d] text-white text-sm font-medium rounded-lg transition-colors"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.558 4.121 1.531 5.856L.066 23.934l6.235-1.636A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.908 0-3.687-.516-5.22-1.413l-.375-.22-3.87 1.016 1.034-3.77-.245-.39A9.969 9.969 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
          </svg>
          Compartilhar no WhatsApp
        </a>
        <NuxtLink
          to="/orcamentos"
          class="px-4 py-2 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
        >
          Ver todos os orçamentos
        </NuxtLink>
      </div>
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

      <button
        type="submit"
        :disabled="submitting"
        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
      >
        {{ submitting ? 'Gerando...' : 'Gerar link do orçamento' }}
      </button>
    </form>
  </div>
</template>
