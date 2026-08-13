<script setup lang="ts">
definePageMeta({ layout: 'auth' })
</script>

<template>
  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md text-center">
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
      class="inline-block w-full text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
    >
      Já defini minha senha, ir para o login
    </NuxtLink>
  </div>
</template>
