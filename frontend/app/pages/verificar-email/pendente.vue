<script setup lang="ts">
definePageMeta({ layout: 'auth' })
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 max-w-md w-full text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-2xl mb-6">
        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>

      <h1 class="text-2xl font-bold text-gray-900 mb-3">Confirme seu e-mail</h1>
      <p class="text-gray-500 text-sm leading-relaxed mb-6">
        Enviamos um link de confirmação para o seu e-mail.
        Clique no link para ativar sua conta e começar a usar o Veekar.
      </p>

      <p class="text-xs text-gray-400">
        Não recebeu? Verifique a pasta de spam ou
        <NuxtLink to="/register" class="text-blue-600 hover:underline">tente se cadastrar novamente.</NuxtLink>
      </p>
    </div>
  </div>
</template>
