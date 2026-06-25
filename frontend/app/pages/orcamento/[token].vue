<script setup lang="ts">
import type { PublicQuote } from '~/stores/quotes'

definePageMeta({ layout: 'public' })

const route = useRoute()
const config = useRuntimeConfig()
const token = route.params.token as string

const quote = ref<PublicQuote | null>(null)
const loading = ref(true)
const notFound = ref(false)
const responded = ref(false)
const responding = ref(false)
const responseError = ref('')

onMounted(async () => {
  try {
    const res = await fetch(`${config.public.apiBase}/public/quotes/${token}`)
    if (!res.ok) {
      notFound.value = true
      return
    }
    quote.value = await res.json()
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})

async function respond(action: 'approved' | 'rejected') {
  responding.value = true
  responseError.value = ''
  try {
    const res = await fetch(`${config.public.apiBase}/public/quotes/${token}/respond`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ action }),
    })
    if (!res.ok) {
      const err = await res.json().catch(() => ({}))
      responseError.value = err.message ?? 'Erro ao responder.'
      return
    }
    const data = await res.json()
    if (quote.value) quote.value.status = data.status
    responded.value = true
  } finally {
    responding.value = false
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
  <div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-lg mx-auto">

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="animate-pulse space-y-4">
          <div class="h-6 bg-gray-200 rounded w-48 mx-auto" />
          <div class="h-4 bg-gray-100 rounded w-32 mx-auto" />
        </div>
      </div>

      <!-- Não encontrado -->
      <div v-else-if="notFound" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <p class="text-gray-500 text-lg mb-1">Orçamento não encontrado</p>
        <p class="text-gray-400 text-sm">O link pode ter expirado ou ser inválido.</p>
      </div>

      <div v-else-if="quote">
        <!-- Cabeçalho -->
        <div class="text-center mb-6">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 rounded-xl mb-3">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h1 class="text-xl font-bold text-gray-900">Orçamento</h1>
          <p class="text-gray-500 text-sm mt-1">
            {{ quote.mechanic.company_name || quote.mechanic.name }}
          </p>
        </div>

        <!-- Card principal -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <!-- Info do veículo/cliente -->
          <div v-if="quote.customer || quote.vehicle" class="px-6 py-4 bg-gray-50 border-b border-gray-100">
            <div v-if="quote.customer" class="text-sm font-medium text-gray-900">{{ quote.customer.name }}</div>
            <div v-if="quote.vehicle" class="text-sm text-gray-500 mt-0.5">
              <span class="font-mono font-semibold text-blue-700">{{ quote.vehicle.plate }}</span>
              · {{ quote.vehicle.brand }} {{ quote.vehicle.model }} {{ quote.vehicle.year }}
            </div>
          </div>

          <!-- Itens -->
          <div class="divide-y divide-gray-50">
            <div
              v-for="(item, i) in quote.items"
              :key="i"
              class="px-6 py-3 flex items-center justify-between gap-4"
            >
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ item.description }}</p>
                <p class="text-xs text-gray-400">{{ item.quantity }}× {{ formatCurrency(item.unit_price) }}</p>
              </div>
              <p class="text-sm font-semibold text-gray-900 whitespace-nowrap">{{ formatCurrency(item.subtotal) }}</p>
            </div>
          </div>

          <!-- Total -->
          <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50">
            <span class="text-sm font-semibold text-gray-700">Total</span>
            <span class="text-xl font-bold text-gray-900">{{ formatCurrency(quote.total) }}</span>
          </div>

          <!-- Observações -->
          <div v-if="quote.notes" class="px-6 py-4 border-t border-gray-100">
            <p class="text-xs font-medium text-gray-500 mb-1">Observações</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ quote.notes }}</p>
          </div>

          <!-- Validade -->
          <div v-if="quote.expires_at" class="px-6 py-3 border-t border-gray-100">
            <p class="text-xs text-gray-400">
              Válido até <span class="font-medium text-gray-600">{{ formatDate(quote.expires_at) }}</span>
            </p>
          </div>
        </div>

        <!-- Resposta já dada -->
        <div
          v-if="quote.status !== 'pending'"
          class="mt-4 rounded-xl p-5 text-center"
          :class="quote.status === 'approved' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'"
        >
          <p
            class="font-semibold text-lg"
            :class="quote.status === 'approved' ? 'text-green-700' : 'text-red-600'"
          >
            {{ quote.status === 'approved' ? '✓ Orçamento aprovado' : '✗ Orçamento recusado' }}
          </p>
          <p class="text-sm mt-1" :class="quote.status === 'approved' ? 'text-green-600' : 'text-red-500'">
            {{ responded ? 'Sua resposta foi registrada com sucesso.' : 'Este orçamento já foi respondido.' }}
          </p>
        </div>

        <!-- Botões de resposta -->
        <div v-else class="mt-4 space-y-3">
          <p class="text-center text-sm text-gray-500">Deseja aprovar ou recusar este orçamento?</p>
          <div class="grid grid-cols-2 gap-3">
            <button
              :disabled="responding"
              class="py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
              @click="respond('approved')"
            >
              ✓ Aprovar
            </button>
            <button
              :disabled="responding"
              class="py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
              @click="respond('rejected')"
            >
              ✗ Recusar
            </button>
          </div>
          <p v-if="responseError" class="text-center text-sm text-red-500">{{ responseError }}</p>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">
          Gerado por <span class="font-medium text-gray-500">Veekar</span> · Sistema de Gestão Automotiva
        </p>
      </div>
    </div>
  </div>
</template>
