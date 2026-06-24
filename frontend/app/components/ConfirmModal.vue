<script setup lang="ts">
defineProps<{
  open: boolean
  title: string
  description: string
  confirmLabel?: string
  confirmClass?: string
  loading?: boolean
  error?: string
}>()

defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/40 backdrop-blur-sm"
          @click="$emit('cancel')"
        />

        <!-- Modal -->
        <Transition
          enter-active-class="transition duration-150"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-100"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="open"
            class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4"
          >
            <div class="space-y-1.5">
              <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
              <p class="text-sm text-gray-500 leading-relaxed">{{ description }}</p>
            </div>

            <p v-if="error" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ error }}</p>

            <div class="flex gap-3 justify-end pt-1">
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                :disabled="loading"
                @click="$emit('cancel')"
              >
                Cancelar
              </button>
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                :class="confirmClass ?? 'bg-red-600 hover:bg-red-700 text-white'"
                :disabled="loading"
                @click="$emit('confirm')"
              >
                <span v-if="loading" class="flex items-center gap-2">
                  <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  Aguarde...
                </span>
                <span v-else>{{ confirmLabel ?? 'Confirmar' }}</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
