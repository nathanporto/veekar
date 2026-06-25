<script setup lang="ts">
import { useQuotesStore } from '~/stores/quotes'

const store = useQuotesStore()
const config = useRuntimeConfig()

onMounted(() => store.fetchQuotes())

const statusLabel: Record<string, string> = {
  pending:  'Aguardando',
  approved: 'Aprovado',
  rejected: 'Recusado',
}

const statusClass: Record<string, string> = {
  pending:  'bg-amber-100 text-amber-700',
  approved: 'bg-green-100 text-green-700',
  rejected: 'bg-red-100 text-red-600',
}

function publicLink(token: string) {
  return `${window.location.origin}/orcamento/${token}`
}

async function copyLink(token: string) {
  await navigator.clipboard.writeText(publicLink(token))
  copiedToken.value = token
  setTimeout(() => (copiedToken.value = ''), 2000)
}

const copiedToken = ref('')

async function remove(id: number) {
  if (confirm('Excluir este orçamento?')) {
    await store.removeQuote(id)
  }
}

function formatCurrency(value: number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('pt-BR')
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Orçamentos</h1>
        <p class="text-gray-500 text-sm mt-1">Gere links de orçamento para seus clientes</p>
      </div>
      <NuxtLink
        to="/orcamentos/novo"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Novo Orçamento
      </NuxtLink>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="bg-white rounded-xl p-5 border border-gray-100 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-48 mb-3" />
        <div class="h-3 bg-gray-100 rounded w-32" />
      </div>
    </div>

    <!-- Vazio -->
    <div
      v-else-if="store.quotes.length === 0"
      class="bg-white rounded-xl border border-gray-100 p-16 text-center"
    >
      <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <p class="text-gray-500 mb-4">Nenhum orçamento criado ainda.</p>
      <NuxtLink
        to="/orcamentos/novo"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        Criar primeiro orçamento
      </NuxtLink>
    </div>

    <!-- Lista -->
    <div v-else class="space-y-3">
      <div
        v-for="q in store.quotes"
        :key="q.id"
        class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <span
                class="text-xs font-semibold px-2 py-0.5 rounded-full"
                :class="statusClass[q.status]"
              >
                {{ statusLabel[q.status] }}
              </span>
              <span v-if="q.customer" class="text-sm font-medium text-gray-900">{{ q.customer.name }}</span>
              <span v-if="q.vehicle" class="font-mono text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                {{ q.vehicle.plate }}
              </span>
            </div>
            <p class="text-xs text-gray-400">
              {{ q.items.length }} {{ q.items.length === 1 ? 'item' : 'itens' }} ·
              {{ formatCurrency(q.total) }} ·
              {{ formatDate(q.created_at) }}
            </p>
          </div>

          <div class="flex items-center gap-2 flex-shrink-0">
            <button
              class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              @click="copyLink(q.token)"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              {{ copiedToken === q.token ? 'Copiado!' : 'Copiar link' }}
            </button>
            <a
              :href="`/orcamento/${q.token}`"
              target="_blank"
              class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors"
              title="Abrir página pública"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
            </a>
            <button
              class="p-1.5 text-gray-400 hover:text-red-500 transition-colors"
              title="Excluir"
              @click="remove(q.id)"
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
