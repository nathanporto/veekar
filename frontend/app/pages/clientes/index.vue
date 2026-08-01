<script setup lang="ts">
import { useCustomersStore } from '~/stores/customers'

const store = useCustomersStore()
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

// Importar planilha
const showImport = ref(false)
const importFile = ref<File | null>(null)
const importing = ref(false)
const importError = ref('')
const importResult = ref<{ imported: number; errors: { line: number; message: string }[] } | null>(null)

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  importFile.value = target.files?.[0] ?? null
  importResult.value = null
  importError.value = ''
}

async function submitImport() {
  if (!importFile.value) return
  importing.value = true
  importError.value = ''
  try {
    importResult.value = await store.importSheet(importFile.value)
    if (importResult.value.imported > 0) await store.fetchAll(search.value)
  } catch (e) {
    importError.value = e instanceof Error ? e.message : 'Erro ao importar planilha.'
  } finally {
    importing.value = false
  }
}

function closeImport() {
  showImport.value = false
  importFile.value = null
  importResult.value = null
  importError.value = ''
}
</script>

<template>
  <div class="space-y-5">
  <ConfirmModal
    :open="showDeleteModal"
    title="Excluir cliente"
    description="Os veículos e todo o histórico de atendimentos associados também serão removidos. Esta ação não pode ser desfeita."
    confirm-label="Sim, excluir"
    :loading="deleting"
    @confirm="doDelete"
    @cancel="showDeleteModal = false"
  />
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
        <p class="text-gray-500 text-sm mt-1">{{ store.total }} cliente(s) cadastrado(s)</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          class="flex items-center gap-2 px-4 py-2 border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors"
          @click="showImport = !showImport"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0l-4 4m4-4v12" />
          </svg>
          Importar planilha
        </button>
        <NuxtLink
          to="/clientes/novo"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Cliente
        </NuxtLink>
      </div>
    </div>

    <!-- Importar planilha -->
    <div v-if="showImport" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-sm font-semibold text-gray-900">Importar clientes de uma planilha</h2>
          <p class="text-xs text-gray-500 mt-1">
            Baixe o modelo ou envie sua própria planilha — reconhecemos colunas como Nome, Telefone/Celular, CPF e E-mail em qualquer ordem, e ignoramos as demais.
          </p>
        </div>
        <button class="text-gray-400 hover:text-gray-600" @click="closeImport">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <a
        href="/templates/clientes-modelo.xlsx"
        download
        class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:underline font-medium"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Baixar planilha modelo
      </a>

      <div class="flex items-center gap-3">
        <input
          type="file"
          accept=".xlsx,.xls,.csv"
          class="text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 file:text-sm file:font-medium hover:file:bg-gray-200"
          @change="onFileChange"
        />
        <button
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60 whitespace-nowrap"
          :disabled="!importFile || importing"
          @click="submitImport"
        >
          {{ importing ? 'Importando...' : 'Enviar planilha' }}
        </button>
      </div>

      <div v-if="importError" class="bg-red-50 text-red-600 text-sm px-4 py-2.5 rounded-lg">{{ importError }}</div>

      <div v-if="importResult" class="space-y-2">
        <p class="text-sm font-medium text-green-700 bg-green-50 px-4 py-2.5 rounded-lg">
          {{ importResult.imported }} cliente(s) importado(s) com sucesso.
        </p>
        <div v-if="importResult.errors.length > 0" class="bg-amber-50 text-amber-800 text-xs px-4 py-3 rounded-lg space-y-1">
          <p class="font-medium">{{ importResult.errors.length }} linha(s) com problema:</p>
          <p v-for="err in importResult.errors" :key="err.line">Linha {{ err.line }}: {{ err.message }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div class="px-6 py-4 border-b border-gray-100">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nome..."
          class="w-full max-w-xs px-3.5 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>

      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 5" :key="i" class="h-14 bg-gray-100 rounded animate-pulse" />
      </div>

      <div v-else-if="store.customers.length === 0" class="p-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <p>Nenhum cliente encontrado.</p>
        <p class="text-xs text-gray-400 mt-1">Cadastre seus clientes aqui pra depois vincular veículos e atendimentos a cada um.</p>
        <NuxtLink to="/clientes/novo" class="text-blue-600 hover:underline text-sm mt-1 inline-block">
          Cadastrar primeiro cliente
        </NuxtLink>
      </div>

      <div v-else>
        <!-- Tabela desktop -->
        <table class="w-full hidden md:table">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Nome</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">CPF</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Telefone</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">E-mail</th>
              <th class="text-left text-xs font-medium text-gray-500 px-6 py-3 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="customer in store.customers" :key="customer.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ customer.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ customer.cpf ?? '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ customer.phone }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ customer.email ?? '—' }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <NuxtLink :to="`/clientes/${customer.id}/editar`" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Editar</NuxtLink>
                  <span class="text-gray-300">|</span>
                  <button
                    class="text-red-500 hover:text-red-700 text-sm font-medium disabled:opacity-40"
                    :disabled="deleting && deleteTarget === customer.id"
                    @click="askDelete(customer.id)"
                  >Excluir</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Cards mobile -->
        <div class="divide-y divide-gray-100 md:hidden">
          <div
            v-for="customer in store.customers"
            :key="customer.id"
            class="px-4 py-4 flex items-start justify-between gap-3"
          >
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-gray-900 truncate">{{ customer.name }}</p>
              <p class="text-sm text-gray-500 mt-0.5">{{ customer.phone }}</p>
              <p v-if="customer.cpf" class="text-xs text-gray-400 font-mono mt-0.5">{{ customer.cpf }}</p>
              <p v-if="customer.email" class="text-xs text-gray-400 truncate mt-0.5">{{ customer.email }}</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0 pt-0.5">
              <NuxtLink :to="`/clientes/${customer.id}/editar`" class="text-blue-600 text-sm font-medium">Editar</NuxtLink>
              <button
                class="text-red-500 text-sm font-medium disabled:opacity-40"
                :disabled="deleting && deleteTarget === customer.id"
                @click="askDelete(customer.id)"
              >Excluir</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
