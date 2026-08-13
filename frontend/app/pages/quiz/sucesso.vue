<script setup lang="ts">
definePageMeta({ layout: 'quiz' })
</script>

<template>
  <div class="w-full max-w-md">
    <div class="flex items-center justify-center gap-2.5 mb-6">
      <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30">
        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
        </svg>
      </div>
      <span class="text-white font-bold text-xl tracking-tight">Veekar</span>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl shadow-black/40 p-8 ring-1 ring-white/10 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-green-50 rounded-2xl mb-6">
        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h1 class="text-xl font-bold text-gray-900 mb-2">Pagamento confirmado!</h1>
      <p class="text-gray-500 text-sm leading-relaxed mb-6">
        Enviamos um e-mail pra você definir sua senha e acessar o Veekar.
        Confira sua caixa de entrada (e a pasta de spam, só por garantia).
      </p>
      <NuxtLink
        to="/login"
        class="inline-block w-full text-center py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-blue-600/30 hover:-translate-y-0.5"
      >
        Já defini minha senha, ir para o login
      </NuxtLink>
    </div>
  </div>
</template>
