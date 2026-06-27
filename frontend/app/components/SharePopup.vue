<script setup lang="ts">
const props = defineProps<{
  token: string
  title?: string
  description?: string
}>()

const show = ref(false)
const copied = ref(false)
const buttonRef = ref<HTMLElement | null>(null)
const popupRef = ref<HTMLElement | null>(null)

const trackingUrl = computed(() => {
  if (typeof window === 'undefined') return ''
  return `${window.location.origin}/acompanhar/${props.token}`
})

const whatsappText = computed(() =>
  encodeURIComponent(`Olá! Segue o link para acompanhar o serviço do seu veículo:\n${trackingUrl.value}`)
)

async function toggle() {
  // Tenta Web Share API nativa — se falhar, mostra popup próprio
  if (navigator?.share) {
    try {
      await navigator.share({
        title: props.title ?? 'Acompanhe seu serviço',
        text: props.description ?? 'Clique no link para acompanhar o andamento do seu veículo.',
        url: trackingUrl.value,
      })
      return
    } catch {
      // Falhou (cancelado pelo usuário ou não suportado) — abre popup
    }
  }
  show.value = !show.value
}

function copyLink() {
  navigator.clipboard.writeText(trackingUrl.value)
  copied.value = true
  setTimeout(() => { copied.value = false; show.value = false }, 1800)
}

function openWhatsApp() {
  window.open(`https://wa.me/?text=${whatsappText.value}`, '_blank')
  show.value = false
}

function onDocClick(e: MouseEvent) {
  if (!popupRef.value?.contains(e.target as Node) &&
      !buttonRef.value?.contains(e.target as Node)) {
    show.value = false
  }
}

onMounted(() => document.addEventListener('click', onDocClick))
onUnmounted(() => document.removeEventListener('click', onDocClick))
</script>

<template>
  <div class="relative">
    <button
      ref="buttonRef"
      type="button"
      class="text-gray-400 hover:text-green-600 transition-colors"
      title="Compartilhar com cliente"
      @click.stop="toggle"
    >
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
      </svg>
    </button>

    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 scale-95 translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-1"
    >
      <div
        v-if="show"
        ref="popupRef"
        class="absolute right-0 top-7 z-50 w-52 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden"
      >
        <div class="px-3 py-2 border-b border-gray-100">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Compartilhar com cliente</p>
        </div>

        <button
          class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors"
          @click.stop="openWhatsApp"
        >
          <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
          </svg>
          WhatsApp
        </button>

        <button
          class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
          @click.stop="copyLink"
        >
          <svg v-if="!copied" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <svg v-else class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          {{ copied ? 'Link copiado!' : 'Copiar link' }}
        </button>
      </div>
    </Transition>
  </div>
</template>
