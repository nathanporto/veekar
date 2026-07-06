<script setup lang="ts">
import { useVehiclesStore } from '~/stores/vehicles'

const vehiclesStore = useVehiclesStore()
const router = useRouter()

const form = reactive({
  customer_id: null as number | null,
  brand: '',
  model: '',
  year: new Date().getFullYear(),
  color: '',
  plate: '',
  mileage: 0,
})
const loading = ref(false)
const error = ref('')

async function submit() {
  if (!form.customer_id) {
    error.value = 'Selecione um cliente'
    return
  }
  loading.value = true
  error.value = ''
  try {
    const vehicle = await vehiclesStore.create({
      ...form,
      customer_id: form.customer_id,
      plate: form.plate.toUpperCase().replace(/\s+/g, ''),
    })
    await router.push(`/veiculos/${vehicle.id}`)
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao salvar'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-lg space-y-5">
    <div class="flex items-center gap-3">
      <NuxtLink to="/veiculos" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </NuxtLink>
      <h1 class="text-2xl font-bold text-gray-900">Novo Veículo</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Cliente *</label>
          <CustomerSearch v-model="form.customer_id" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Marca *</label>
            <input
              v-model="form.brand"
              type="text"
              required
              placeholder="Ex: Toyota"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Modelo *</label>
            <input
              v-model="form.model"
              type="text"
              required
              placeholder="Ex: Corolla"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Ano *</label>
            <input
              v-model.number="form.year"
              type="number"
              required
              :min="1950"
              :max="new Date().getFullYear() + 1"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Cor <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input
              v-model="form.color"
              type="text"
              placeholder="Ex: Branco"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Placa <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input
              v-model="form.plate"
              type="text"
              placeholder="ABC1D23"
              maxlength="8"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quilometragem Atual *</label>
            <input
              v-model.number="form.mileage"
              type="number"
              required
              min="0"
              placeholder="0"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <div class="flex gap-3 pt-2">
          <button
            type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60"
            :disabled="loading"
          >
            {{ loading ? 'Salvando...' : 'Salvar Veículo' }}
          </button>
          <NuxtLink
            to="/veiculos"
            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
          >
            Cancelar
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>
