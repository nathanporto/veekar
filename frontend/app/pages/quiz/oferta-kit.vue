<script setup lang="ts">
definePageMeta({ layout: 'quiz' })

const CAKTO_CHECKOUT_URL = 'https://pay.cakto.com.br/f4yogd8_1053315'

const route = useRoute()
const source = computed(() => (route.query.ref === 'trial' ? 'trial' : 'checkout'))

const heading = computed(() =>
  source.value === 'trial'
    ? 'Antes de você continuar...'
    : 'Antes de você sair...'
)

const subheading = computed(() =>
  source.value === 'trial'
    ? 'Tudo bem se o sistema ainda não é pra agora. Você pode começar organizando sua oficina de um jeito mais simples.'
    : 'Talvez você ainda não esteja pronto(a) para assumir uma assinatura. Comece com uma opção mais simples.'
)

function goSecondary() {
  if (source.value === 'trial') {
    navigateTo('/register')
  } else {
    const leadId = route.query.lead_id
    // Volta direto pra tela de oferta (com o desconto), reaproveitando o lead já
    // criado — sem isso, /quiz reiniciaria o questionário do zero.
    navigateTo(leadId ? `/quiz?resume_lead_id=${leadId}` : '/quiz')
  }
}

const secondaryLabel = computed(() =>
  source.value === 'trial'
    ? 'Continuar com o teste grátis do Veekar'
    : 'Voltar para a oferta do Veekar'
)
</script>

<template>
  <div class="w-full max-w-md">
    <!-- Logo -->
    <div class="flex items-center justify-center gap-2.5 mb-6">
      <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30">
        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
        </svg>
      </div>
      <span class="text-white font-bold text-xl tracking-tight">Veekar</span>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl shadow-black/40 p-8 ring-1 ring-white/10 space-y-5">
      <div class="text-center">
        <h1 class="text-xl font-bold text-gray-900 leading-snug">{{ heading }}</h1>
        <p class="text-sm text-gray-500 mt-2">{{ subheading }}</p>
      </div>

      <div class="border-2 border-gray-100 rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-2">
          <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
            <svg class="w-4.5 h-4.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </span>
          <span class="text-sm font-bold text-gray-900">Kit Oficina Organizada</span>
        </div>
        <p class="text-sm text-gray-600 leading-relaxed">
          Planilhas, checklists, calculadora de orçamento e 20 mensagens prontas para você
          organizar sua oficina hoje mesmo — sem precisar de sistema nenhum.
        </p>
        <div class="mt-3 flex items-baseline gap-1.5">
          <span class="text-2xl font-bold text-gray-900">R$ 21,90</span>
          <span class="text-xs text-gray-400">pagamento único, Pix ou cartão</span>
        </div>
      </div>

      <a
        :href="CAKTO_CHECKOUT_URL"
        target="_blank"
        rel="noopener noreferrer"
        class="block w-full text-center py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-0.5"
      >
        Quero o Kit por R$ 21,90
      </a>

      <button
        class="w-full py-3 border-2 border-gray-100 hover:bg-gray-50 text-gray-600 font-medium rounded-xl transition-colors"
        @click="goSecondary"
      >
        {{ secondaryLabel }}
      </button>
    </div>
  </div>
</template>
