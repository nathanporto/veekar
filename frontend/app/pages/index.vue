<script setup lang="ts">
definePageMeta({ layout: 'landing' })

const mobileMenuOpen = ref(false)

const features = [
  {
    icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
    title: 'Busca instantânea por placa',
    description: 'Digite a placa e veja em segundos todo o histórico do veículo: proprietário, serviços, quilometragem e observações.',
  },
  {
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    title: 'Histórico completo de atendimentos',
    description: 'Registre cada serviço com descrição, peças trocadas, valores e km. Nunca mais perca o histórico de um carro.',
  },
  {
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    title: 'Cadastro de clientes e veículos',
    description: 'Organize sua carteira de clientes com nome, telefone, CPF e todos os veículos vinculados a cada um.',
  },
  {
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    title: 'Dashboard com visão geral',
    description: 'Acompanhe em tempo real quantos clientes, veículos e atendimentos sua oficina já acumulou.',
  },
]

const steps = [
  { number: '01', title: 'Cadastre sua oficina', description: 'Crie sua conta gratuitamente em menos de 1 minuto. Nenhum cartão necessário para começar.' },
  { number: '02', title: 'Adicione seus clientes e veículos', description: 'Importe ou cadastre seus clientes e os veículos de cada um. Tudo organizado e de fácil acesso.' },
  { number: '03', title: 'Registre os atendimentos', description: 'A cada serviço, registre o que foi feito, as peças usadas e o valor cobrado. Em segundos.' },
]

const faqs = [
  {
    question: 'O trial é realmente gratuito? Preciso de cartão?',
    answer: 'Sim, totalmente gratuito e sem cartão de crédito. Você cria a conta e já começa a usar. Só é necessário assinar se quiser ir além de 3 clientes e 3 veículos.',
  },
  {
    question: 'O que acontece com meus dados se eu cancelar?',
    answer: 'Seus dados ficam preservados. Você continua podendo visualizar tudo, mas não consegue adicionar novos clientes, veículos ou atendimentos até reativar a assinatura.',
  },
  {
    question: 'Posso cancelar quando quiser?',
    answer: 'Sim, sem multa e sem burocracia. O cancelamento é feito diretamente pelo sistema e tem efeito imediato.',
  },
  {
    question: 'O Veekar funciona no celular?',
    answer: 'Sim. O sistema é responsivo e funciona em qualquer navegador — celular, tablet ou computador. Não precisa instalar nenhum app.',
  },
  {
    question: 'Consigo buscar um veículo que outra oficina cadastrou?',
    answer: 'Não. Cada oficina tem sua própria base de dados, totalmente separada e privada. Você só vê os veículos e clientes cadastrados pela sua conta.',
  },
  {
    question: 'Quantos usuários posso ter por conta?',
    answer: 'Atualmente cada conta é individual (uma oficina, um acesso). Acesso multi-usuário está no nosso roadmap.',
  },
]

const openFaq = ref<number | null>(null)

function toggleFaq(index: number) {
  openFaq.value = openFaq.value === index ? null : index
}
</script>

<template>
  <div class="font-sans antialiased">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gray-100">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-4.5 h-4.5 text-white w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
            </svg>
          </div>
          <span class="text-gray-900 font-bold text-xl tracking-tight">Veekar</span>
        </div>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
          <a href="#funcionalidades" class="hover:text-gray-900 transition-colors">Funcionalidades</a>
          <a href="#como-funciona" class="hover:text-gray-900 transition-colors">Como funciona</a>
          <a href="#planos" class="hover:text-gray-900 transition-colors">Planos</a>
          <a href="#faq" class="hover:text-gray-900 transition-colors">FAQ</a>
          <a href="#suporte" class="hover:text-gray-900 transition-colors">Suporte</a>
        </nav>

        <div class="hidden md:flex items-center gap-3">
          <NuxtLink to="/login" class="text-sm font-medium text-gray-700 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
            Entrar
          </NuxtLink>
          <NuxtLink to="/register" class="text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition-colors">
            Começar grátis
          </NuxtLink>
        </div>

        <!-- Mobile menu button -->
        <button class="md:hidden p-2 rounded-lg hover:bg-gray-100" @click="mobileMenuOpen = !mobileMenuOpen">
          <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile menu -->
      <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white px-4 py-4 space-y-3">
        <a href="#funcionalidades" class="block text-sm font-medium text-gray-700 py-2" @click="mobileMenuOpen = false">Funcionalidades</a>
        <a href="#como-funciona" class="block text-sm font-medium text-gray-700 py-2" @click="mobileMenuOpen = false">Como funciona</a>
        <a href="#planos" class="block text-sm font-medium text-gray-700 py-2" @click="mobileMenuOpen = false">Planos</a>
        <a href="#faq" class="block text-sm font-medium text-gray-700 py-2" @click="mobileMenuOpen = false">FAQ</a>
        <a href="#suporte" class="block text-sm font-medium text-gray-700 py-2" @click="mobileMenuOpen = false">Suporte</a>
        <div class="pt-2 flex flex-col gap-2">
          <NuxtLink to="/login" class="text-center text-sm font-medium text-gray-700 py-2.5 border border-gray-200 rounded-lg">Entrar</NuxtLink>
          <NuxtLink to="/register" class="text-center text-sm font-semibold bg-blue-600 text-white py-2.5 rounded-lg">Começar grátis</NuxtLink>
        </div>
      </div>
    </header>

    <!-- HERO -->
    <section class="relative bg-gradient-to-b from-slate-950 to-slate-900 text-white overflow-hidden">
      <!-- Grid background -->
      <div class="absolute inset-0 opacity-[0.04]"
        style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(to right, #fff 1px, transparent 1px); background-size: 48px 48px;" />

      <!-- Glow -->
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-blue-600/20 rounded-full blur-3xl pointer-events-none" />

      <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-24 pb-28 text-center">
        <div class="inline-flex items-center gap-2 bg-blue-600/20 border border-blue-500/30 text-blue-300 text-xs font-semibold px-3.5 py-1.5 rounded-full mb-6">
          <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse" />
          Teste grátis — sem cartão de crédito
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
          O histórico da oficina
          <span class="block text-blue-400">na palma da mão</span>
        </h1>

        <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
          Busque qualquer veículo pela placa e veja em segundos todo o histórico de serviços, peças trocadas e dados do proprietário.
          Simples, rápido e feito para oficinas reais.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row gap-3 justify-center">
          <NuxtLink
            to="/register"
            class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-8 py-4 rounded-xl text-base transition-all shadow-lg shadow-blue-600/30 hover:shadow-blue-500/40"
          >
            Criar conta grátis
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </NuxtLink>
          <a
            href="#como-funciona"
            class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/15 border border-white/10 text-white font-medium px-8 py-4 rounded-xl text-base transition-colors"
          >
            Ver como funciona
          </a>
        </div>

        <!-- Social proof -->
        <p class="mt-8 text-slate-500 text-sm">
          Teste com até 3 clientes e 3 veículos — gratuito, sem prazo.
        </p>
      </div>
    </section>

    <!-- FEATURES -->
    <section id="funcionalidades" class="py-24 bg-gray-50">
      <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Tudo que sua oficina precisa</h2>
          <p class="mt-4 text-gray-500 max-w-xl mx-auto">Sem complicação. Cada funcionalidade foi pensada para o dia a dia de quem trabalha com veículos.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div
            v-for="f in features"
            :key="f.title"
            class="bg-white rounded-2xl border border-gray-100 p-7 hover:shadow-md hover:border-blue-100 transition-all group"
          >
            <div class="w-11 h-11 bg-blue-50 group-hover:bg-blue-100 rounded-xl flex items-center justify-center mb-4 transition-colors">
              <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="f.icon" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">{{ f.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed">{{ f.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="como-funciona" class="py-24 bg-white">
      <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Como funciona</h2>
          <p class="mt-4 text-gray-500 max-w-xl mx-auto">Comece a usar em minutos. Sem treinamento, sem complexidade.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(step, i) in steps" :key="step.number" class="relative text-center">
            <!-- Connector line -->
            <div v-if="i < steps.length - 1" class="hidden md:block absolute top-8 left-[calc(50%+2.5rem)] right-[calc(-50%+2.5rem)] h-px bg-gradient-to-r from-blue-200 to-gray-200" />

            <div class="relative inline-flex items-center justify-center w-16 h-16 bg-blue-600 text-white font-bold text-xl rounded-2xl mb-5 shadow-lg shadow-blue-600/20">
              {{ step.number }}
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">{{ step.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed max-w-xs mx-auto">{{ step.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- PRICING -->
    <section id="planos" class="py-24 bg-slate-950 text-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
          <h2 class="text-3xl sm:text-4xl font-bold">Simples assim: um plano só</h2>
          <p class="mt-4 text-slate-400">Sem taxas escondidas. Sem planos confusos. Um valor, acesso completo.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
          <!-- Trial card -->
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-7">
            <p class="text-xs font-bold uppercase tracking-widest text-amber-400 mb-3">Trial gratuito</p>
            <div class="flex items-end gap-1 mb-1">
              <span class="text-4xl font-extrabold">Grátis</span>
            </div>
            <p class="text-slate-400 text-sm mb-6">Para testar sem compromisso</p>
            <ul class="space-y-3 text-sm text-slate-300 mb-8">
              <li class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Até 3 clientes
              </li>
              <li class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Até 3 veículos
              </li>
              <li class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Atendimentos ilimitados
              </li>
              <li class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Sem prazo de expiração
              </li>
            </ul>
            <NuxtLink to="/register" class="block text-center py-3 border border-slate-700 hover:border-amber-500 hover:text-amber-400 text-white font-medium rounded-xl transition-colors text-sm">
              Criar conta grátis
            </NuxtLink>
          </div>

          <!-- Premium card -->
          <div class="relative bg-blue-600 rounded-2xl p-7 overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/40 rounded-full -translate-y-16 translate-x-16" />
            <div class="relative">
              <p class="text-xs font-bold uppercase tracking-widest text-blue-200 mb-3">Premium</p>
              <div class="flex items-end gap-1 mb-1">
                <span class="text-4xl font-extrabold">R$ 49</span>
                <span class="text-blue-200 pb-1 text-lg">,90<span class="text-sm">/mês</span></span>
              </div>
              <p class="text-blue-200 text-sm mb-6">Acesso ilimitado a tudo</p>
              <ul class="space-y-3 text-sm text-white mb-8">
                <li class="flex items-center gap-2.5">
                  <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  Clientes e veículos ilimitados
                </li>
                <li class="flex items-center gap-2.5">
                  <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  Histórico completo de serviços
                </li>
                <li class="flex items-center gap-2.5">
                  <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  Controle de peças e valores
                </li>
                <li class="flex items-center gap-2.5">
                  <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  Dashboard e relatórios
                </li>
                <li class="flex items-center gap-2.5">
                  <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  Suporte prioritário
                </li>
                <li class="flex items-center gap-2.5">
                  <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  Cancele quando quiser
                </li>
              </ul>
              <NuxtLink to="/register" class="block text-center py-3 bg-white hover:bg-blue-50 text-blue-600 font-semibold rounded-xl transition-colors text-sm shadow-lg">
                Assinar agora
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-24 bg-gray-50">
      <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Perguntas frequentes</h2>
          <p class="mt-4 text-gray-500">Tire suas dúvidas antes de começar.</p>
        </div>

        <div class="space-y-3">
          <div
            v-for="(faq, i) in faqs"
            :key="i"
            class="bg-white border border-gray-200 rounded-xl overflow-hidden"
          >
            <button
              class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors"
              @click="toggleFaq(i)"
            >
              <span class="font-medium text-gray-900 text-sm sm:text-base">{{ faq.question }}</span>
              <svg
                class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform duration-200"
                :class="openFaq === i ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="openFaq === i" class="px-6 pb-5 text-sm text-gray-500 leading-relaxed border-t border-gray-100 pt-4">
              {{ faq.answer }}
            </div>
          </div>
        </div>

        <p class="text-center text-sm text-gray-400 mt-8">
          Não encontrou o que procurava?
          <a href="#suporte" class="text-blue-600 hover:underline font-medium">Fale com o suporte</a>
        </p>
      </div>
    </section>

    <!-- SUPORTE -->
    <section id="suporte" class="py-24 bg-white">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-50 rounded-2xl mb-6">
          <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Precisa de ajuda?</h2>
        <p class="mt-4 text-gray-500 max-w-md mx-auto">
          Nossa equipe está pronta para te ajudar com qualquer dúvida sobre o Veekar.
          Resposta em até 24 horas.
        </p>

        <a
          href="mailto:veekarofc@gmail.com"
          class="mt-8 inline-flex items-center gap-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg shadow-blue-600/20"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          veekarofc@gmail.com
        </a>

        <p class="mt-4 text-xs text-gray-400">Clique para abrir seu cliente de e-mail</p>
      </div>
    </section>

    <!-- FINAL CTA -->
    <section class="py-24 bg-white">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Pronto para organizar sua oficina?</h2>
        <p class="mt-4 text-gray-500">Crie sua conta agora e comece a usar de graça. Sem cartão, sem burocracia.</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
          <NuxtLink
            to="/register"
            class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-xl text-base transition-colors shadow-lg shadow-blue-600/20"
          >
            Criar conta grátis
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </NuxtLink>
          <NuxtLink to="/login" class="inline-flex items-center justify-center text-gray-700 font-medium px-8 py-4 rounded-xl border border-gray-200 hover:bg-gray-50 text-base transition-colors">
            Já tenho conta
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-gray-100 py-10 bg-gray-50">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-blue-600 rounded-md flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
            </svg>
          </div>
          <span class="font-bold text-gray-800">Veekar</span>
        </div>
        <p class="text-sm text-gray-400">© {{ new Date().getFullYear() }} Veekar. Todos os direitos reservados.</p>
        <div class="flex gap-5 text-sm text-gray-500">
          <NuxtLink to="/login" class="hover:text-gray-800 transition-colors">Entrar</NuxtLink>
          <NuxtLink to="/register" class="hover:text-gray-800 transition-colors">Criar conta</NuxtLink>
          <a href="mailto:veekarofc@gmail.com" class="hover:text-gray-800 transition-colors">Suporte</a>
        </div>
      </div>
    </footer>

  </div>
</template>
