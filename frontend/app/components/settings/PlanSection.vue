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
  return new Date(dateStr).toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' })
}

function daysRemaining(dateStr: string | null) {
  if (!dateStr) return 0
  const diff = new Date(dateStr).getTime() - Date.now()
  return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)))
}

function daysProgress(dateStr: string | null) {
  if (!dateStr) return 0
  const end = new Date(dateStr).getTime()
  const now = Date.now()
  const periodMs = 30 * 24 * 60 * 60 * 1000
  const used = periodMs - (end - now)
  return Math.min(100, Math.max(0, Math.round((used / periodMs) * 100)))
}

const features = [
  { icon: '👥', label: 'Clientes ilimitados' },
  { icon: '🚗', label: 'Veículos ilimitados' },
  { icon: '📋', label: 'Orçamentos digitais' },
  { icon: '📊', label: 'Relatórios financeiros' },
  { icon: '🔧', label: 'Controle de etapas' },
  { icon: '🎧', label: 'Suporte prioritário' },
]
</script>

<template>
  <div class="space-y-6">
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

    <!-- ATIVO -->
    <template v-if="subscriptionStore.isActive">
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest">Plano atual</p>
              <p class="text-2xl font-bold mt-0.5">Veekar Premium</p>
            </div>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-full">
              ✓ ATIVO
            </span>
          </div>
        </div>

        <div class="px-6 py-5 space-y-5">
          <!-- Dias restantes -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Tempo restante</span>
              <span class="text-sm font-bold text-blue-600">
                {{ subscriptionStore.status?.current_period_end ? daysRemaining(subscriptionStore.status.current_period_end) + ' dias' : 'Ativo' }}
              </span>
            </div>
            <div v-if="subscriptionStore.status?.current_period_end" class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full bg-blue-500 rounded-full transition-all"
                :style="{ width: (100 - daysProgress(subscriptionStore.status.current_period_end)) + '%' }"
              />
            </div>
            <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ subscriptionStore.status?.current_period_end ? 'Renova em ' + formatDate(subscriptionStore.status.current_period_end) : 'Renovação automática pelo Stripe' }}
            </p>
          </div>

          <!-- Recursos incluídos -->
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Incluído no seu plano</p>
            <div class="grid grid-cols-2 gap-2">
              <div
                v-for="f in features"
                :key="f.label"
                class="flex items-center gap-2.5 bg-gray-50 rounded-xl px-3 py-2.5"
              >
                <span class="text-base">{{ f.icon }}</span>
                <span class="text-sm text-gray-700 font-medium">{{ f.label }}</span>
              </div>
            </div>
          </div>

          <!-- Cancelar -->
          <div class="pt-1 border-t border-gray-100 flex items-center justify-between gap-4">
            <p class="text-xs text-gray-400">
              Você mantém o acesso até o fim do período pago.
            </p>
            <button
              class="shrink-0 text-sm font-medium text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 hover:border-red-300 transition-colors px-4 py-2 rounded-xl"
              @click="showCancelModal = true"
            >
              Cancelar plano
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- TRIAL -->
    <template v-else-if="subscriptionStore.isTrial">
      <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-3">
          <span class="text-xs font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-amber-200 text-amber-800">
            Trial gratuito
          </span>
        </div>
        <p class="text-sm text-amber-900">
          Você está no período gratuito. Pode cadastrar até <strong>3 clientes</strong> e <strong>3 veículos</strong>.
        </p>
        <div class="mt-3 grid grid-cols-2 gap-3">
          <div class="bg-white rounded-xl p-3 text-center border border-amber-200">
            <p class="text-2xl font-bold text-gray-900">
              {{ subscriptionStore.status?.customers_count ?? 0 }}/{{ subscriptionStore.status?.customers_limit ?? 3 }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Clientes</p>
          </div>
          <div class="bg-white rounded-xl p-3 text-center border border-amber-200">
            <p class="text-2xl font-bold text-gray-900">
              {{ subscriptionStore.status?.vehicles_count ?? 0 }}/{{ subscriptionStore.status?.vehicles_limit ?? 3 }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Veículos</p>
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 text-white">
          <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest">Veekar Premium</p>
          <div class="flex items-end gap-1 mt-1">
            <span class="text-3xl font-bold">R$ 49,90</span>
            <span class="text-blue-200 text-sm pb-0.5">/mês</span>
          </div>
          <p class="text-xs text-blue-200 mt-1">Cancele quando quiser</p>
        </div>

        <div class="px-6 py-5 space-y-4">
          <div class="grid grid-cols-2 gap-2">
            <div v-for="f in features" :key="f.label" class="flex items-center gap-2.5 bg-gray-50 rounded-xl px-3 py-2.5">
              <span class="text-base">{{ f.icon }}</span>
              <span class="text-sm text-gray-700 font-medium">{{ f.label }}</span>
            </div>
          </div>

          <div v-if="error" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-xl">{{ error }}</div>

          <button
            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
            :disabled="checkingOut"
            @click="subscribe"
          >
            {{ checkingOut ? 'Redirecionando...' : 'Assinar agora — R$ 49,90/mês' }}
          </button>

          <p class="text-xs text-gray-400 text-center">
            Pagamento seguro via Stripe.
          </p>
        </div>
      </div>
    </template>

    <!-- EXPIRADO -->
    <template v-else>
      <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
        <span class="text-xs font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-red-200 text-red-800">
          Expirado
        </span>
        <p class="text-sm text-red-900 mt-3">
          Sua assinatura expirou. Seus dados estão preservados, mas você não pode adicionar novos registros.
        </p>
      </div>

      <div v-if="error" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-xl">{{ error }}</div>

      <button
        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
        :disabled="checkingOut"
        @click="subscribe"
      >
        {{ checkingOut ? 'Redirecionando...' : 'Reativar — R$ 49,90/mês' }}
      </button>
    </template>
  </div>
</template>
