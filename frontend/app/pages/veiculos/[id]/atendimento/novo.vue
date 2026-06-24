<script setup lang="ts">
import { useServiceHistoryStore } from '~/stores/serviceHistory'
import { useVehiclesStore } from '~/stores/vehicles'
import type { Vehicle } from '~/types'

const historyStore = useServiceHistoryStore()
const vehiclesStore = useVehiclesStore()
const router = useRouter()
const route = useRoute()
const vehicleId = Number(route.params.id)

const vehicle = ref<Vehicle | null>(null)
const today = new Date().toISOString().split('T')[0]

interface Item {
  description: string
  quantity: number
  unit_price: number
}

const form = reactive({
  service_date: today,
  description: '',
  mileage: 0,
  notes: '',
})

const items = ref<Item[]>([])
const useItems = ref(false)

const loading = ref(false)
const error = ref('')

onMounted(async () => {
  vehicle.value = await vehiclesStore.fetchOne(vehicleId)
  form.mileage = vehicle.value.mileage
})

function addItem() {
  items.value.push({ description: '', quantity: 1, unit_price: 0 })
}

function removeItem(index: number) {
  items.value.splice(index, 1)
}

const itemsTotal = computed(() =>
  items.value.reduce((sum, i) => sum + i.quantity * i.unit_price, 0),
)

function formatCurrency(value: number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

async function submit() {
  loading.value = true
  error.value = ''
  try {
    const payload: Record<string, unknown> = {
      service_date: form.service_date,
      description: form.description,
      mileage: Number(form.mileage),
      notes: form.notes || null,
    }

    if (useItems.value && items.value.length > 0) {
      payload.items = items.value
    }

    await historyStore.create(vehicleId, payload as Parameters<typeof historyStore.create>[1])
    await router.push(`/veiculos/${vehicleId}`)
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao salvar'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl space-y-5">
    <div class="flex items-center gap-3">
      <NuxtLink :to="`/veiculos/${vehicleId}`" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </NuxtLink>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Novo Atendimento</h1>
        <p v-if="vehicle" class="text-sm text-gray-500">
          {{ vehicle.plate }} — {{ vehicle.brand }} {{ vehicle.model }}
        </p>
      </div>
    </div>

    <form class="space-y-4" @submit.prevent="submit">
      <!-- Dados gerais -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Dados do Atendimento</h2>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Data *</label>
            <input
              v-model="form.service_date"
              type="date"
              required
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quilometragem *</label>
            <input
              v-model.number="form.mileage"
              type="number"
              required
              min="0"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Descrição do Serviço *</label>
          <textarea
            v-model="form.description"
            rows="2"
            required
            placeholder="Descreva o serviço realizado..."
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Observações
            <span class="text-gray-400 font-normal">(opcional)</span>
          </label>
          <textarea
            v-model="form.notes"
            rows="2"
            placeholder="Observações adicionais..."
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>
      </div>

      <!-- Itens / Peças -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Peças e Serviços</h2>
          <label class="flex items-center gap-2 cursor-pointer">
            <div
              class="relative w-10 h-5 rounded-full transition-colors"
              :class="useItems ? 'bg-blue-600' : 'bg-gray-300'"
              @click="useItems = !useItems"
            >
              <div
                class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"
                :class="useItems ? 'translate-x-5' : 'translate-x-0.5'"
              />
            </div>
            <span class="text-sm text-gray-600">Detalhar itens</span>
          </label>
        </div>

        <template v-if="!useItems">
          <p class="text-sm text-gray-400">Ative para detalhar peças e serviços individualmente com valores.</p>
        </template>

        <template v-else>
          <!-- Cabeçalho da tabela -->
          <div v-if="items.length > 0" class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-500 uppercase tracking-wide px-1">
            <span class="col-span-5">Descrição</span>
            <span class="col-span-2 text-center">Qtd</span>
            <span class="col-span-3 text-right">Valor Unit.</span>
            <span class="col-span-1 text-right">Subtotal</span>
            <span class="col-span-1" />
          </div>

          <!-- Lista de itens -->
          <div class="space-y-2">
            <div
              v-for="(item, index) in items"
              :key="index"
              class="grid grid-cols-12 gap-2 items-center"
            >
              <input
                v-model="item.description"
                type="text"
                placeholder="Ex: Óleo de motor, Filtro..."
                required
                class="col-span-5 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <input
                v-model.number="item.quantity"
                type="number"
                min="0.001"
                step="0.001"
                required
                class="col-span-2 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
              />
              <div class="col-span-3 relative">
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">R$</span>
                <input
                  v-model.number="item.unit_price"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  class="w-full pl-8 pr-2 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
                />
              </div>
              <span class="col-span-1 text-sm text-gray-700 text-right font-medium">
                {{ formatCurrency(item.quantity * item.unit_price) }}
              </span>
              <button
                type="button"
                class="col-span-1 flex justify-center text-red-400 hover:text-red-600"
                @click="removeItem(index)"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <button
            type="button"
            class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium"
            @click="addItem"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar item
          </button>

          <div v-if="items.length > 0" class="border-t border-gray-100 pt-3 flex justify-end">
            <div class="text-right">
              <p class="text-sm text-gray-500">Total do atendimento</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(itemsTotal) }}</p>
            </div>
          </div>
        </template>
      </div>

      <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">
        {{ error }}
      </div>

      <div class="flex gap-3">
        <button
          type="submit"
          class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60"
          :disabled="loading"
        >
          {{ loading ? 'Salvando...' : 'Registrar Atendimento' }}
        </button>
        <NuxtLink
          :to="`/veiculos/${vehicleId}`"
          class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
        >
          Cancelar
        </NuxtLink>
      </div>
    </form>
  </div>
</template>
