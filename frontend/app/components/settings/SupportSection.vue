<script setup lang="ts">
const faqs = [
  {
    question: 'Como busco um veículo pela placa?',
    answer: 'Use a barra de busca no topo do sistema — basta digitar a placa (com ou sem traço) e pressionar Enter. O histórico completo do veículo aparece na tela.',
  },
  {
    question: 'Como registro um novo atendimento?',
    answer: 'Acesse o menu Veículos, clique no veículo desejado e depois em "Novo Atendimento". Preencha a data, descrição, km e peças/serviços realizados.',
  },
  {
    question: 'Como faço upgrade do trial para o plano Premium?',
    answer: 'Acesse a aba "Plano" em Configurações e clique em "Assinar agora". Você será redirecionado para o checkout seguro via Stripe.',
  },
  {
    question: 'O que acontece com meus dados se eu cancelar?',
    answer: 'Seus dados ficam preservados. Você continua podendo visualizar clientes, veículos e histórico, mas não consegue adicionar novos registros até reativar a assinatura.',
  },
  {
    question: 'Como altero os dados de um cliente ou veículo?',
    answer: 'Acesse a listagem de Clientes ou Veículos, clique no registro desejado e use o botão "Editar". As alterações são salvas imediatamente.',
  },
  {
    question: 'Posso ter mais de um usuário na mesma conta?',
    answer: 'Atualmente cada conta é individual. Suporte a múltiplos usuários por empresa está previsto em breve.',
  },
]

const openFaq = ref<number | null>(null)

function toggleFaq(index: number) {
  openFaq.value = openFaq.value === index ? null : index
}
</script>

<template>
  <div class="space-y-8">
    <!-- Contato -->
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5">
      <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      <div class="flex-1">
        <p class="font-semibold text-gray-900">Fale com o suporte</p>
        <p class="text-sm text-gray-500 mt-0.5">Resposta em até 24 horas nos dias úteis.</p>
        <a
          href="mailto:veekarofc@gmail.com"
          class="inline-flex items-center gap-1.5 mt-3 text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors"
        >
          veekarofc@gmail.com
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </a>
      </div>
    </div>

    <!-- FAQ -->
    <div>
      <h2 class="text-base font-semibold text-gray-900 mb-4">Perguntas frequentes</h2>

      <div class="space-y-2">
        <div
          v-for="(faq, i) in faqs"
          :key="i"
          class="bg-white border border-gray-200 rounded-xl overflow-hidden"
        >
          <button
            class="w-full text-left px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors"
            @click="toggleFaq(i)"
          >
            <span class="text-sm font-medium text-gray-900">{{ faq.question }}</span>
            <svg
              class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-200"
              :class="openFaq === i ? 'rotate-180' : ''"
              fill="none" viewBox="0 0 24 24" stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div v-if="openFaq === i" class="px-5 pb-4 text-sm text-gray-500 leading-relaxed border-t border-gray-100 pt-3">
            {{ faq.answer }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
