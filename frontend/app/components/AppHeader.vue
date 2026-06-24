<script setup lang="ts">
import { useVehiclesStore } from '~/stores/vehicles'

const vehiclesStore = useVehiclesStore()
const router = useRouter()
const { toggle } = useMobileMenu()

const search = ref('')
const searching = ref(false)
const error = ref('')

async function searchByPlate() {
  const plate = search.value.trim().toUpperCase().replace(/\s+/g, '')
  if (!plate) return

  searching.value = true
  error.value = ''

  const vehicle = await vehiclesStore.searchByPlate(plate)
  searching.value = false

  if (vehicle) {
    search.value = ''
    await router.push(`/veiculos/${vehicle.id}`)
  } else {
    error.value = 'Placa não encontrada'
    setTimeout(() => { error.value = '' }, 3000)
  }
}
</script>

<template>
  <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center gap-3">
    <button
      class="md:hidden flex-shrink-0 p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
      @click="toggle()"
    >
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <form class="flex-1 max-w-md flex gap-2" @submit.prevent="searchByPlate">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por placa (ex: ABC1D23)"
          class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase placeholder:normal-case"
          :class="error ? 'border-red-400 ring-1 ring-red-400' : ''"
          maxlength="8"
        />
      </div>
      <button
        type="submit"
        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-60 transition-colors"
        :disabled="searching"
      >
        {{ searching ? '...' : 'Buscar' }}
      </button>
    </form>

    <transition name="fade">
      <span v-if="error" class="text-red-500 text-sm">{{ error }}</span>
    </transition>
  </header>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
