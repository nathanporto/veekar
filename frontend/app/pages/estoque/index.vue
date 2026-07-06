<script setup lang="ts">
import { useProductsStore } from '~/stores/products'

const store = useProductsStore()

onMounted(() => store.fetchAll())

function formatCurrency(value: string | number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
}

function today() {
  return new Date().toISOString().slice(0, 10)
}

// Novo produto
const showNewForm = ref(false)
const newProduct = reactive({ name: '', unit_cost: '', unit_price: '' })
const creating = ref(false)
const createError = ref('')

async function createProduct() {
  creating.value = true
  createError.value = ''
  try {
    await store.create({
      name: newProduct.name,
      unit_cost: Number(newProduct.unit_cost),
      unit_price: Number(newProduct.unit_price),
    })
    newProduct.name = ''
    newProduct.unit_cost = ''
    newProduct.unit_price = ''
    showNewForm.value = false
  } catch (e) {
    createError.value = e instanceof Error ? e.message : 'Erro ao criar produto.'
  } finally {
    creating.value = false
  }
}

// Registrar entrada (compra) / saída (venda)
const openMovement = ref<{ productId: number; productName: string; type: 'entrada' | 'saida' } | null>(null)
const movementForm = reactive({ quantity: '', unit_cost: '', unit_price: '', movement_date: today(), notes: '' })
const savingMovement = ref(false)
const movementError = ref('')

function openEntrada(productId: number, productName: string) {
  openMovement.value = { productId, productName, type: 'entrada' }
  movementForm.quantity = ''
  movementForm.unit_cost = ''
  movementForm.movement_date = today()
  movementForm.notes = ''
  movementError.value = ''
}

function openSaida(productId: number, productName: string) {
  openMovement.value = { productId, productName, type: 'saida' }
  movementForm.quantity = ''
  movementForm.unit_price = ''
  movementForm.movement_date = today()
  movementForm.notes = ''
  movementError.value = ''
}

function closeMovement() {
  openMovement.value = null
}

async function saveMovement() {
  if (!openMovement.value) return
  savingMovement.value = true
  movementError.value = ''
  try {
    if (openMovement.value.type === 'entrada') {
      await store.registerEntrada(openMovement.value.productId, {
        quantity: Number(movementForm.quantity),
        unit_cost: Number(movementForm.unit_cost),
        movement_date: movementForm.movement_date,
        notes: movementForm.notes || undefined,
      })
    } else {
      await store.registerSaida(openMovement.value.productId, {
        quantity: Number(movementForm.quantity),
        unit_price: movementForm.unit_price ? Number(movementForm.unit_price) : undefined,
        movement_date: movementForm.movement_date,
        notes: movementForm.notes || undefined,
      })
    }
    closeMovement()
  } catch (e) {
    movementError.value = e instanceof Error ? e.message : 'Erro ao registrar movimento.'
  } finally {
    savingMovement.value = false
  }
}

// Excluir produto
const showDeleteModal = ref(false)
const pendingDeleteId = ref<number | null>(null)
const deleting = ref(false)

function askDelete(id: number) {
  pendingDeleteId.value = id
  showDeleteModal.value = true
}

async function doDelete() {
  if (!pendingDeleteId.value) return
  deleting.value = true
  await store.remove(pendingDeleteId.value)
  deleting.value = false
  pendingDeleteId.value = null
  showDeleteModal.value = false
}
</script>

<template>
  <div class="space-y-5">
    <ConfirmModal
      :open="showDeleteModal"
      title="Excluir produto"
      description="O histórico de movimentações desse produto também será removido. Esta ação não pode ser desfeita."
      confirm-label="Sim, excluir"
      :loading="deleting"
      @confirm="doDelete"
      @cancel="showDeleteModal = false"
    />

    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Estoque</h1>
        <p class="text-gray-500 text-sm mt-1">{{ store.products.length }} produto(s) cadastrado(s)</p>
      </div>
      <button
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
        @click="showNewForm = !showNewForm"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Novo Produto
      </button>
    </div>

    <!-- Formulário novo produto -->
    <div v-if="showNewForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
      <h2 class="text-base font-semibold text-gray-900">Novo produto</h2>
      <div v-if="createError" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg">{{ createError }}</div>
      <form class="grid grid-cols-1 md:grid-cols-3 gap-4" @submit.prevent="createProduct">
        <div class="md:col-span-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
          <input v-model="newProduct.name" type="text" required placeholder="Óleo 5W30"
            class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Custo de compra (unidade)</label>
          <input v-model="newProduct.unit_cost" type="number" step="0.01" min="0" required placeholder="20,00"
            class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Preço de venda (unidade)</label>
          <input v-model="newProduct.unit_price" type="number" step="0.01" min="0" required placeholder="50,00"
            class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>
        <div class="md:col-span-3 flex justify-end gap-2">
          <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800" @click="showNewForm = false">
            Cancelar
          </button>
          <button type="submit" :disabled="creating"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
            {{ creating ? 'Salvando...' : 'Salvar produto' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Lista de produtos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 3" :key="i" class="h-16 bg-gray-100 rounded animate-pulse" />
      </div>

      <div v-else-if="store.products.length === 0" class="p-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <p>Nenhum produto cadastrado.</p>
        <button class="text-blue-600 hover:underline text-sm mt-1 inline-block" @click="showNewForm = true">
          Cadastrar primeiro produto
        </button>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div
          v-for="product in store.products"
          :key="product.id"
          class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap"
        >
          <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900">{{ product.name }}</p>
            <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
              <span>Custo: {{ formatCurrency(product.unit_cost) }}</span>
              <span>Venda: {{ formatCurrency(product.unit_price) }}</span>
              <span class="font-medium" :class="product.quantity > 0 ? 'text-green-700' : 'text-red-600'">
                {{ product.quantity }} em estoque
              </span>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-shrink-0">
            <button
              type="button"
              class="text-xs font-medium px-3 py-1.5 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
              @click="openEntrada(product.id, product.name)"
            >
              + Compra
            </button>
            <button
              type="button"
              :disabled="product.quantity === 0"
              class="text-xs font-medium px-3 py-1.5 rounded-full bg-green-50 text-green-700 hover:bg-green-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
              @click="openSaida(product.id, product.name)"
            >
              Vender
            </button>
            <button
              type="button"
              class="text-red-400 hover:text-red-600"
              title="Excluir produto"
              @click="askDelete(product.id)"
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

    <!-- Modal de movimento (compra/venda) -->
    <div v-if="openMovement" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h2 class="text-base font-semibold text-gray-900">
          {{ openMovement.type === 'entrada' ? 'Registrar compra' : 'Registrar venda' }}
          <span class="text-gray-500 font-normal">— {{ openMovement.productName }}</span>
        </h2>

        <div v-if="movementError" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg">{{ movementError }}</div>

        <form class="space-y-4" @submit.prevent="saveMovement">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
            <input v-model="movementForm.quantity" type="number" min="1" step="1" required
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>

          <div v-if="openMovement.type === 'entrada'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Custo pago (unidade)</label>
            <input v-model="movementForm.unit_cost" type="number" step="0.01" min="0" required
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>

          <div v-else>
            <label class="block text-sm font-medium text-gray-700 mb-1">Preço de venda (unidade)</label>
            <input v-model="movementForm.unit_price" type="number" step="0.01" min="0" placeholder="Deixe em branco para usar o preço padrão"
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
            <input v-model="movementForm.movement_date" type="date" required
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações (opcional)</label>
            <input v-model="movementForm.notes" type="text" maxlength="255"
              class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800" @click="closeMovement">
              Cancelar
            </button>
            <button type="submit" :disabled="savingMovement"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
              {{ savingMovement ? 'Salvando...' : 'Confirmar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
