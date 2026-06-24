<script setup lang="ts">
import { useSubscriptionStore } from '~/stores/subscription'

const subscriptionStore = useSubscriptionStore()
const checkingOut = ref(false)
const canceling = ref(false)
const showCancelModal = ref(false)
const error = ref('')

onMounted(() => subscriptionStore.fetchStatus())

async function subscribe() {
  checkingOut.value = true
  error.value = ''
  try {
    const api = useApi()
    const data = await api.post<{ url: string }>('/subscription/checkout', {})
    window.location.href = data.url
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao iniciar pagamento'
    checkingOut.value = false
  }
}

async function confirmCancel() {
  canceling.value = true
  error.value = ''
  try {
    await subscriptionStore.cancelSubscription()
    showCancelModal.value = false
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro desconhecido ao cancelar'
  } finally {
    canceling.value = false
  }
}

function formatDate(dateStr: string | null) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('pt-BR')
}
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-6">
  <ConfirmModal
    :open="showCancelModal"
    title="Cancelar assinatura"
    description="Tem certeza? Após o cancelamento os novos cadastros serão bloqueados."
    confirm-label="Sim, cancelar"
    :loading="canceling"
    :error="error"
    @confirm="confirmCancel"
    @cancel="showCancelModal = false; error = ''"
  />
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Plano</h1>
      <p class="text-gray-500 text-sm mt-1">Gerencie sua assinatura do Veekar</p>
    </div>

    <!-- Status atual -->
    <div
      class="rounded-xl border p-5"
      :class="{
        'bg-amber-50 border-amber-200': subscriptionStore.isTrial,
        'bg-green-50 border-green-200': subscriptionStore.isActive,
        'bg-red-50 border-red-200': subscriptionStore.isExpired,
      }"
    >
      <div class="flex items-center gap-3 mb-1">
        <span
          class="text-xs font-bold uppercase tracking-wide px-2.5 py-1 rounded-full"
          :class="{
            'bg-amber-200 text-amber-800': subscriptionStore.isTrial,
            'bg-green-200 text-green-800': subscriptionStore.isActive,
            'bg-red-200 text-red-800': subscriptionStore.isExpired,
          }"
        >
          <template v-if="subscriptionStore.isTrial">Trial gratuito</template>
          <template v-else-if="subscriptionStore.isActive">Ativo</template>
          <template v-else>Expirado</template>
        </span>
      </div>

      <template v-if="subscriptionStore.isTrial">
        <p class="text-sm text-amber-900 mt-2">
          Você está no período gratuito. Pode cadastrar até <strong>3 clientes</strong> e <strong>3 veículos</strong>.
        </p>
        <div class="mt-3 grid grid-cols-2 gap-3">
          <div class="bg-white rounded-lg p-3 text-center border border-amber-200">
            <p class="text-2xl font-bold text-gray-900">
              {{ subscriptionStore.status?.customers_count ?? 0 }}/{{ subscriptionStore.status?.customers_limit ?? 3 }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Clientes</p>
          </div>
          <div class="bg-white rounded-lg p-3 text-center border border-amber-200">
            <p class="text-2xl font-bold text-gray-900">
              {{ subscriptionStore.status?.vehicles_count ?? 0 }}/{{ subscriptionStore.status?.vehicles_limit ?? 3 }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Veículos</p>
          </div>
        </div>
      </template>

      <template v-else-if="subscriptionStore.isActive">
        <p class="text-sm text-green-900 mt-2">
          Sua assinatura está ativa. Acesso ilimitado a todos os recursos.
        </p>
        <p v-if="subscriptionStore.status?.current_period_end" class="text-xs text-green-700 mt-1">
          Próxima cobrança: {{ formatDate(subscriptionStore.status.current_period_end) }}
        </p>

        <div class="mt-4 pt-4 border-t border-green-200">
          <button
            class="text-sm text-red-500 hover:text-red-700 underline"
            @click="showCancelModal = true"
          >
            Cancelar assinatura
          </button>
          <p class="text-xs text-gray-400 mt-1">
            Você mantém o acesso até o fim do período pago.
          </p>
        </div>
      </template>

      <template v-else>
        <p class="text-sm text-red-900 mt-2">
          Sua assinatura expirou. Seus dados estão preservados, mas você não pode adicionar novos registros.
        </p>
      </template>
    </div>

    <!-- Plano Premium -->
    <div v-if="!subscriptionStore.isActive" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
        <div class="flex items-end gap-2">
          <span class="text-4xl font-bold">R$ 49</span>
          <span class="text-blue-200 pb-1">,90/mês</span>
        </div>
        <p class="text-blue-100 text-sm mt-1">Veekar Premium — acesso completo</p>
      </div>

      <div class="p-6 space-y-3">
        <ul class="space-y-2.5 text-sm text-gray-700">
          <li class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Clientes e veículos ilimitados
          </li>
          <li class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Histórico completo de atendimentos
          </li>
          <li class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Controle detalhado de peças e serviços
          </li>
          <li class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Dashboard e relatórios financeiros
          </li>
          <li class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Suporte prioritário
          </li>
        </ul>

        <div v-if="error" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ error }}</div>

        <button
          class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60 mt-2"
          :disabled="checkingOut"
          @click="subscribe"
        >
          {{ checkingOut ? 'Redirecionando para o pagamento...' : 'Assinar agora' }}
        </button>

        <p class="text-xs text-gray-400 text-center">
          Pagamento seguro via Stripe. Cancele quando quiser.
        </p>
      </div>
    </div>
  </div>
</template>
