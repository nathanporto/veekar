<script setup lang="ts">
import { useQuizStore } from '~/stores/quiz'

definePageMeta({ layout: 'quiz' })

const store = useQuizStore()

type StepKey = 'business_type' | 'cars_per_month' | 'current_control' | 'main_pain'

const answers = reactive<Record<StepKey, string>>({
  business_type: '',
  cars_per_month: '',
  current_control: '',
  main_pain: '',
})

const steps: { key: StepKey; question: string; options: { value: string; label: string; icon: string }[] }[] = [
  {
    key: 'business_type',
    question: 'Qual tipo de negócio você tem?',
    options: [
      { value: 'oficina', label: 'Oficina mecânica', icon: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3m0 0h-3m0 0v4' },
      { value: 'funilaria', label: 'Funilaria', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
      { value: 'estetica', label: 'Estética automotiva', icon: 'M5 13l4 4L19 7' },
      { value: 'outro', label: 'Outro', icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6' },
    ],
  },
  {
    key: 'cars_per_month',
    question: 'Quantos carros você atende por mês, em média?',
    options: [
      { value: 'até 10', label: 'Até 10', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
      { value: '11 a 30', label: '11 a 30', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
      { value: '31 a 60', label: '31 a 60', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
      { value: 'mais de 60', label: 'Mais de 60', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
      { value: 'outro', label: 'Outro', icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6' },
    ],
  },
  {
    key: 'current_control',
    question: 'Como você controla hoje?',
    options: [
      { value: 'caderno', label: 'Caderno', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
      { value: 'planilha', label: 'Planilha', icon: 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2' },
      { value: 'whatsapp', label: 'WhatsApp', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
      { value: 'outro sistema', label: 'Já uso outro sistema', icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
      { value: 'outro', label: 'Outro', icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6' },
    ],
  },
  {
    key: 'main_pain',
    question: 'Qual sua maior dor hoje?',
    options: [
      { value: 'perder historico', label: 'Perder histórico de cliente/veículo', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
      { value: 'nao saber quem deve', label: 'Não saber quem ainda deve pagar', icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
      { value: 'comissao', label: 'Calcular comissão de funcionário', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1' },
      { value: 'desorganizacao', label: 'Desorganização geral', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
      { value: 'outro', label: 'Outro', icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6' },
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
const direction = ref<'forward' | 'back'>('forward')

const otherTexts = reactive<Record<StepKey, string>>({
  business_type: '',
  cars_per_month: '',
  current_control: '',
  main_pain: '',
})

const isOtherSelected = computed(() => answers[steps[currentStep.value].key] === 'outro')

function advanceStep() {
  direction.value = 'forward'
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  } else {
    stage.value = 'contact'
  }
}

function selectOption(value: string) {
  answers[steps[currentStep.value].key] = value
  if (value !== 'outro') {
    advanceStep()
  }
}

function goBack() {
  if (currentStep.value > 0) {
    direction.value = 'back'
    currentStep.value--
  }
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
    const finalAnswer = (key: StepKey) =>
      answers[key] === 'outro' && otherTexts[key].trim() ? otherTexts[key].trim() : answers[key]

    const lead = await store.createLead({
      name: contact.name,
      email: contact.email,
      phone: contact.phone,
      business_type: finalAnswer('business_type'),
      cars_per_month: finalAnswer('cars_per_month'),
      current_control: finalAnswer('current_control'),
      main_pain: finalAnswer('main_pain'),
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

    <div class="bg-white rounded-3xl shadow-2xl shadow-black/40 p-8 ring-1 ring-white/10">
      <!-- Quiz -->
      <div v-if="stage === 'quiz'">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-blue-600">Pergunta {{ currentStep + 1 }} de {{ steps.length }}</span>
          <button
            v-if="currentStep > 0"
            class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1"
            @click="goBack"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Voltar
          </button>
        </div>

        <div class="h-1.5 bg-gray-100 rounded-full mb-6 overflow-hidden">
          <div class="h-full bg-blue-600 rounded-full transition-all duration-500 ease-out" :style="{ width: progress + '%' }" />
        </div>

        <Transition :name="direction === 'forward' ? 'slide-left' : 'slide-right'" mode="out-in">
          <div :key="currentStep">
            <h1 class="text-xl font-bold text-gray-900 mb-5 leading-snug">{{ steps[currentStep].question }}</h1>

            <div class="space-y-2.5">
              <button
                v-for="opt in steps[currentStep].options"
                :key="opt.value"
                class="w-full flex items-center gap-3 text-left px-4 py-3.5 border-2 rounded-2xl hover:border-blue-500 hover:bg-blue-50 hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 text-sm font-medium text-gray-700 group"
                :class="answers[steps[currentStep].key] === opt.value ? 'border-blue-500 bg-blue-50' : 'border-gray-100'"
                @click="selectOption(opt.value)"
              >
                <span
                  class="flex-shrink-0 w-9 h-9 rounded-xl group-hover:bg-blue-600 flex items-center justify-center transition-colors"
                  :class="answers[steps[currentStep].key] === opt.value ? 'bg-blue-600' : 'bg-gray-100'"
                >
                  <svg
                    class="w-4.5 h-4.5 group-hover:text-white transition-colors"
                    :class="answers[steps[currentStep].key] === opt.value ? 'text-white' : 'text-gray-500'"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="opt.icon" />
                  </svg>
                </span>
                {{ opt.label }}
              </button>
            </div>

            <div v-if="isOtherSelected" class="mt-3 space-y-3">
              <input
                v-model="otherTexts[steps[currentStep].key]"
                type="text"
                placeholder="Qual? (opcional)"
                class="w-full px-4 py-3 text-sm border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                @keyup.enter="advanceStep"
              />
              <button
                type="button"
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-blue-600/30 hover:-translate-y-0.5"
                @click="advanceStep"
              >
                Continuar
              </button>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Contato -->
      <div v-else-if="stage === 'contact'">
        <div class="text-center mb-5">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-50 rounded-2xl mb-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h1 class="text-xl font-bold text-gray-900">Quase lá!</h1>
          <p class="text-sm text-gray-500 mt-1">Pra onde a gente manda o resultado?</p>
        </div>

        <form class="space-y-3" @submit.prevent="submitContact">
          <input
            v-model="contact.name"
            type="text"
            required
            placeholder="Seu nome"
            class="w-full px-4 py-3 text-sm border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
          <input
            v-model="contact.email"
            type="email"
            required
            placeholder="Seu melhor e-mail"
            class="w-full px-4 py-3 text-sm border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
          <input
            v-model="contact.phone"
            type="tel"
            required
            placeholder="WhatsApp (com DDD)"
            class="w-full px-4 py-3 text-sm border-2 border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
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
            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all disabled:opacity-60 shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-0.5"
          >
            {{ submittingContact ? 'Enviando...' : 'Ver meu resultado' }}
          </button>
        </form>
      </div>

      <!-- Oferta -->
      <div v-else class="space-y-5">
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-50 rounded-2xl mb-3 animate-bounce-in">
            <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 class="text-lg font-bold text-gray-900">
            Pelo que você me contou, o Veekar vai te ajudar a
            {{ painMessages[answers.main_pain] ?? 'organizar sua oficina' }}.
          </h1>
        </div>

        <div class="bg-gradient-to-br from-blue-600 via-blue-600 to-indigo-700 rounded-2xl p-5 text-center text-white relative overflow-hidden shadow-xl shadow-blue-600/30">
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl" />
          <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1 relative">Oferta exclusiva pra você</p>
          <p class="text-3xl font-bold relative">20% OFF</p>
          <p class="text-sm text-blue-100 mt-1 relative">nos primeiros 3 meses</p>
          <p class="text-xs text-blue-200 mt-2 relative">
            de <span class="line-through">R$ 49,90</span> por <strong>R$ 39,92</strong>/mês
          </p>
        </div>

        <div v-if="checkoutError" class="bg-red-50 text-red-600 text-sm px-3 py-2 rounded-lg">{{ checkoutError }}</div>

        <button
          :disabled="checkingOut"
          class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all disabled:opacity-60 shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-0.5"
          @click="subscribeWithDiscount"
        >
          {{ checkingOut ? 'Redirecionando...' : 'Quero o desconto' }}
        </button>

        <button
          class="w-full py-3 border-2 border-gray-100 hover:bg-gray-50 text-gray-600 font-medium rounded-xl transition-colors"
          @click="startTrial"
        >
          Prefiro testar grátis primeiro
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.25s ease;
}
.slide-left-enter-from {
  opacity: 0;
  transform: translateX(24px);
}
.slide-left-leave-to {
  opacity: 0;
  transform: translateX(-24px);
}
.slide-right-enter-from {
  opacity: 0;
  transform: translateX(-24px);
}
.slide-right-leave-to {
  opacity: 0;
  transform: translateX(24px);
}

@keyframes bounce-in {
  0% { transform: scale(0.5); opacity: 0; }
  60% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); }
}
.animate-bounce-in {
  animation: bounce-in 0.5s ease-out;
}
</style>
