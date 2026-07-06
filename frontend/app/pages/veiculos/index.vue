<script setup lang="ts">
import { useVehiclesStore } from '~/stores/vehicles'

const store = useVehiclesStore()
const search = ref('')
const deleteTarget = ref<number | null>(null)
const deleting = ref(false)
const showDeleteModal = ref(false)
const pendingDeleteId = ref<number | null>(null)

onMounted(() => store.fetchAll())

watch(search, useDebounceFn(() => store.fetchAll(search.value), 400))

function askDelete(id: number) {
  pendingDeleteId.value = id
  showDeleteModal.value = true
}

async function doDelete() {
  if (!pendingDeleteId.value) return
  deleting.value = true
  deleteTarget.value = pendingDeleteId.value
  await store.remove(pendingDeleteId.value)
  deleting.value = false
  deleteTarget.value = null
  pendingDeleteId.value = null
  showDeleteModal.value = false
}

function useDebounceFn(fn: () => void, delay: number) {
  let timer: ReturnType<typeof setTimeout>
  return () => {
    clearTimeout(timer)
    timer = setTimeout(fn, delay)
  }
}
</script>

<template>
  <div class="space-y-5">
  <ConfirmModal
    :open="showDeleteModal"
    title="Excluir veículo"
    description="Todo o histórico de atendimentos vinculado também será removido. Esta ação não pode ser desfeita."
    confirm-label="Sim, excluir"
    :loading="deleting"
    @confirm="doDelete"
    @cancel="showDeleteModal = false"
  />
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Veículos</h1>
        <p class="text-gray-500 text-sm mt-1">{{ store.total }} veículo(s) cadastrado(s)</p>
      </div>
      <NuxtLink
        to="/veiculos/novo"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Novo Veículo
      </NuxtLink>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div class="px-6 py-4 border-b border-gray-100">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por placa, modelo ou cliente..."
          class="w-full max-w-xs px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 5" :key="i" class="h-14 bg-gray-100 rounded animate-pulse" />
      </div>

      <div v-else-if="store.vehicles.length === 0" class="p-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
        </svg>
        <p>Nenhum veículo encontrado.</p>
        <NuxtLink to="/veiculos/novo" class="text-blue-600 hover:underline text-sm mt-1 inline-block">
          Cadastrar primeiro veículo
        </NuxtLink>
      </div>

      <div v-else>
        <!-- Tabela desktop -->
        <table class="w-full hidden md:table">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Placa</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Veículo</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Ano / Cor</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Cliente</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Km Atual</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="v in store.vehicles" :key="v.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <NuxtLink :to="`/veiculos/${v.id}`">
                  <span class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded hover:bg-blue-100 transition-colors">
                    {{ v.plate ?? 'Sem placa' }}
                  </span>
                </NuxtLink>
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ v.brand }} {{ v.model }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ v.year }} / {{ v.color ?? '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ v.customer?.name ?? '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ v.mileage.toLocaleString('pt-BR') }} km</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <NuxtLink :to="`/veiculos/${v.id}`" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Ver</NuxtLink>
                  <span class="text-gray-300">|</span>
                  <NuxtLink :to="`/veiculos/${v.id}/editar`" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Editar</NuxtLink>
                  <span class="text-gray-300">|</span>
                  <button
                    class="text-red-500 hover:text-red-700 text-sm font-medium disabled:opacity-40"
                    :disabled="deleting && deleteTarget === v.id"
                    @click="askDelete(v.id)"
                  >Excluir</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Cards mobile -->
        <div class="divide-y divide-gray-100 md:hidden">
          <div
            v-for="v in store.vehicles"
            :key="v.id"
            class="px-4 py-4 flex items-start justify-between gap-3"
          >
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-1">
                <NuxtLink :to="`/veiculos/${v.id}`">
                  <span class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                    {{ v.plate ?? 'Sem placa' }}
                  </span>
                </NuxtLink>
              </div>
              <p class="text-sm font-semibold text-gray-900">{{ v.brand }} {{ v.model }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ v.year }} · {{ v.color ?? '—' }} · {{ v.mileage.toLocaleString('pt-BR') }} km</p>
              <p v-if="v.customer" class="text-xs text-gray-400 mt-0.5">{{ v.customer.name }}</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0 pt-0.5">
              <NuxtLink :to="`/veiculos/${v.id}`" class="text-blue-600 text-sm font-medium">Ver</NuxtLink>
              <NuxtLink :to="`/veiculos/${v.id}/editar`" class="text-blue-600 text-sm font-medium">Editar</NuxtLink>
              <button
                class="text-red-500 text-sm font-medium disabled:opacity-40"
                :disabled="deleting && deleteTarget === v.id"
                @click="askDelete(v.id)"
              >Excluir</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
