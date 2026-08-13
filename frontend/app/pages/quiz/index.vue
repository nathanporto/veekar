<script setup lang="ts">
import { useQuizStore } from '~/stores/quiz'

definePageMeta({ layout: 'auth' })

const store = useQuizStore()

type StepKey = 'business_type' | 'cars_per_month' | 'current_control' | 'main_pain'

const answers = reactive<Record<StepKey, string>>({
  business_type: '',
  cars_per_month: '',
  current_control: '',
  main_pain: '',
})

const steps: { key: StepKey; question: string; options: { value: string; label: string }[] }[] = [
  {
    key: 'business_type',
    question: 'Qual tipo de negócio você tem?',
    options: [
      { value: 'oficina', label: 'Oficina mecânica' },
      { value: 'funilaria', label: 'Funilaria' },
      { value: 'estetica', label: 'Estética automotiva' },
    ],
  },
  {
    key: 'cars_per_month',
    question: 'Quantos carros você atende por mês, em média?',
    options: [
      { value: 'até 10', label: 'Até 10' },
      { value: '11 a 30', label: '11 a 30' },
      { value: '31 a 60', label: '31 a 60' },
      { value: 'mais de 60', label: 'Mais de 60' },
    ],
  },
  {
    key: 'current_control',
    question: 'Como você controla hoje?',
    options: [
      { value: 'caderno', label: 'Caderno' },
      { value: 'planilha', label: 'Planilha' },
      { value: 'whatsapp', label: 'WhatsApp' },
      { value: 'outro sistema', label: 'Já uso outro sistema' },
    ],
  },
  {
    key: 'main_pain',
    question: 'Qual sua maior dor hoje?',
    options: [
      { value: 'perder historico', label: 'Perder histórico de cliente/veículo' },
      { value: 'nao saber quem deve', label: 'Não saber quem ainda deve pagar' },
      { value: 'comissao', label: 'Calcular comissão de funcionário' },
      { value: 'desorganizacao', label: 'Desorganização geral' },
    ],
  },
]

const painMessages: Record<string, string> = {
  'perder historico': 'nunca mais perder o histórico de um cliente ou veículo',
  'nao saber quem deve': 'saber na hora quem já pagou e quem ainda deve',
  comissao: 'calcular a comissão de cada funcionário automaticamente',
  desorganizacao: 'colocar ordem na desorganização do dia a dia',
}

const stage = ref<'quiz' | 'contact' | 'offer'>('quiz')
const currentStep = ref(0)
const progress = computed(() => Math.round(((currentStep.value) / steps.length) * 100))

function selectOption(value: string) {
  answers[steps[currentStep.value].key] = value
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  } else {
    stage.value = 'contact'
  }
}

function goBack() {
  if (currentStep.value > 0) currentStep.value--
}

// Contato
const contact = reactive({ name: '', email: '', phone: '' })
const acceptedTerms = ref(false)
const submittingContact = ref(false)
const contactError = ref('')
const leadId = ref<number | null>(null)

async function submitContact() {
  if (!acceptedTerms.value) {
    contactError.value = 'Você precisa aceitar os Termos de Uso e a Política de Privacidade'
    return
  }
  submittingContact.value = true
  contactError.value = ''
  try {
    const lead = await store.createLead({
      name: contact.name,
      email: contact.email,
      phone: contact.phone,
      business_type: answers.business_type,
      cars_per_month: answers.cars_per_month,
      current_control: answers.current_control,
      main_pain: answers.main_pain,
      accepted_terms: acceptedTerms.value,
    })
    leadId.value = lead.id
    stage.value = 'offer'
  } catch (e) {
    contactError.value = e instanceof Error ? e.message : 'Erro ao enviar. Tente novamente.'
  } finally {
    submittingContact.value = false
  }
}

// Oferta
const checkingOut = ref(false)
const checkoutError = ref('')

async function subscribeWithDiscount() {
  if (!leadId.value) return
  checkingOut.value = true
  checkoutError.value = ''
  try {
    const data = await store.checkout(leadId.value)
    window.location.href = data.url
  } catch (e) {
    checkoutError.value = e instanceof Error ? e.message : 'Erro ao iniciar pagamento.'
    checkingOut.value = false
  }
}

function startTrial() {
  navigateTo('/register')
}
</script>

<template>
  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
    <!-- Quiz -->
    <div v-if="stage === 'quiz'">
      <div class="h-1.5 bg-gray-100 rounded-full mb-6 overflow-hidden">
        <div class="h-full bg-blue-600 rounded-full transition-all" :style="{ width: progress + '%' }" />
      </div>

      <button
        v-if="currentStep > 0"
        class="text-xs text-gray-400 hover:text-gray-600 mb-3 flex items-center gap-1"
        @click="goBack"
      >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Voltar
      </button>

      <h1 class="text-xl font-bold text-gray-900 mb-5">{{ steps[currentStep].question }}</h1>

      <div class="space-y-2.5">
        <button
          v-for="opt in steps[currentStep].options"
          :key="opt.value"
          class="w-full text-left px-4 py-3 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-colors text-sm font-medium text-gray-700"
          @click="selectOption(opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Contato -->
    <div v-else-if="stage === 'contact'">
      <h1 class="text-xl font-bold text-gray-900 mb-1">Quase lá!</h1>
      <p class="text-sm text-gray-500 mb-5">Pra onde a gente manda o resultado?</p>

      <form class="space-y-3" @submit.prevent="submitContact">
        <input
          v-model="contact.name"
          type="text"
          required
          placeholder="Seu nome"
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          v-model="contact.email"
          type="email"
          required
          placeholder="Seu melhor e-mail"
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          v-model="contact.phone"
          type="tel"
          required
          placeholder="WhatsApp (com DDD)"
          class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <div class="flex items-start gap-2 pt-1">
          <input
            id="accept-terms-quiz"
            v-model="acceptedTerms"
            type="checkbox"
            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
          />
          <label for="accept-terms-quiz" class="text-xs text-gray-600 leading-relaxed cursor-pointer">
            Li e concordo com os
            <NuxtLink to="/termos" target="_blank" class="text-blue-600 hover:underline font-medium">Termos de Uso</NuxtLink>
            e a
            <NuxtLink to="/privacidade" target="_blank" class="text-blue-600 hover:underline font-medium">Política de Privacidade</NuxtLink>
            do Veekar, incluindo o tratamento de dados conforme a LGPD.
          </label>
        </div>

        <div v-if="contactError" class="bg-red-50 text-red-600 text-sm px-3 py-2 rounded-lg">{{ contactError }}</div>

        <button
          type="submit"
          :disabled="submittingContact"
          class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
        >
          {{ submittingContact ? 'Enviando...' : 'Ver meu resultado' }}
        </button>
      </form>
    </div>

    <!-- Oferta -->
    <div v-else class="space-y-5">
      <div class="text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-50 rounded-2xl mb-3">
          <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-lg font-bold text-gray-900">
          Pelo que você me contou, o Veekar vai te ajudar a
          {{ painMessages[answers.main_pain] ?? 'organizar sua oficina' }}.
        </h1>
      </div>

      <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-5 text-center text-white">
        <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Oferta exclusiva pra você</p>
        <p class="text-3xl font-bold">20% OFF</p>
        <p class="text-sm text-blue-100 mt-1">nos primeiros 3 meses</p>
        <p class="text-xs text-blue-200 mt-2">
          de <span class="line-through">R$ 49,90</span> por <strong>R$ 39,92</strong>/mês
        </p>
      </div>

      <div v-if="checkoutError" class="bg-red-50 text-red-600 text-sm px-3 py-2 rounded-lg">{{ checkoutError }}</div>

      <button
        :disabled="checkingOut"
        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
        @click="subscribeWithDiscount"
      >
        {{ checkingOut ? 'Redirecionando...' : 'Quero o desconto' }}
      </button>

      <button
        class="w-full py-3 border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium rounded-xl transition-colors"
        @click="startTrial"
      >
        Prefiro testar grátis primeiro
      </button>
    </div>
  </div>
</template>
