<script setup lang="ts">
import { useApi } from '~/composables/useApi'

const api = useApi()

interface ServiceItem {
  id: number
  vehicle_id: number
  description: string
  service_date: string | null
  estimated_delivery: string | null
  service_status: string
  tracking_token: string | null
  vehicle?: {
    plate: string
    brand: string
    model: string
    customer?: { name: string } | null
  }
}

type Status = 'recebido' | 'em_diagnostico' | 'aguardando_pecas' | 'em_servico' | 'pronto' | 'entregue'

const STAGES: { key: Status; label: string; color: string; bg: string; border: string; borderDrag: string; dot: string; textLight: string }[] = [
  { key: 'recebido',         label: 'Recebido',         color: 'text-gray-600',   bg: 'bg-gray-50',    border: 'border-gray-200',  borderDrag: 'border-gray-400',  dot: 'bg-gray-400',   textLight: 'text-gray-400' },
  { key: 'em_diagnostico',   label: 'Em diagnóstico',   color: 'text-blue-700',   bg: 'bg-blue-50',    border: 'border-blue-200',  borderDrag: 'border-blue-500',  dot: 'bg-blue-500',   textLight: 'text-blue-300' },
  { key: 'aguardando_pecas', label: 'Aguardando peças', color: 'text-amber-700',  bg: 'bg-amber-50',   border: 'border-amber-200', borderDrag: 'border-amber-500', dot: 'bg-amber-500',  textLight: 'text-amber-300' },
  { key: 'em_servico',       label: 'Em serviço',       color: 'text-indigo-700', bg: 'bg-indigo-50',  border: 'border-indigo-200',borderDrag: 'border-indigo-500',dot: 'bg-indigo-500', textLight: 'text-indigo-300' },
  { key: 'pronto',           label: 'Pronto',           color: 'text-green-700',  bg: 'bg-green-50',   border: 'border-green-200', borderDrag: 'border-green-500', dot: 'bg-green-500',  textLight: 'text-green-300' },
]

const ALL_STAGES_WITH_ENTREGUE: { key: Status; label: string }[] = [
  ...STAGES,
  { key: 'entregue', label: 'Entregue ✓' },
]

const services = ref<ServiceItem[]>([])
const loading = ref(true)
const updating = ref<number | null>(null)

// Stage picker popup
const pickingFor = ref<number | null>(null)

// Drag & drop
const draggedId = ref<number | null>(null)
const dragOverCol = ref<Status | null>(null)

const grouped = computed(() => {
  const map = new Map<Status, ServiceItem[]>()
  STAGES.forEach(s => map.set(s.key, []))
  services.value.forEach(s => {
    const key = (s.service_status ?? 'recebido') as Status
    const list = map.get(key)
    if (list) list.push(s)
  })
  return map
})

const totalActive = computed(() => services.value.length)

function formatDate(d: string | null | undefined) {
  if (!d) return null
  const dt = new Date(d.includes('T') ? d : d + 'T00:00:00')
  return isNaN(dt.getTime()) ? null : dt.toLocaleDateString('pt-BR')
}

async function moveStatus(service: ServiceItem, newStatus: Status) {
  if (newStatus === service.service_status) return
  updating.value = service.id
  pickingFor.value = null
  try {
    await api.patch(`/vehicles/${service.vehicle_id}/service-histories/${service.id}/status`, {
      service_status: newStatus,
    })
    if (newStatus === 'entregue') {
      services.value = services.value.filter(s => s.id !== service.id)
    } else {
      service.service_status = newStatus
    }
  } finally {
    updating.value = null
  }
}

// ── Drag & drop ──────────────────────────────────────────
function onDragStart(e: DragEvent, service: ServiceItem) {
  draggedId.value = service.id
  if (e.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
    e.dataTransfer.setData('text/plain', String(service.id))
  }
}

function onDragEnd() {
  draggedId.value = null
  dragOverCol.value = null
}

function onDragOver(e: DragEvent, stageKey: Status) {
  e.preventDefault()
  dragOverCol.value = stageKey
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'
}

function onDragLeave(e: DragEvent) {
  // só limpa se saiu do elemento e não entrou num filho
  const rel = e.relatedTarget as HTMLElement | null
  if (!rel || !(e.currentTarget as HTMLElement).contains(rel)) {
    dragOverCol.value = null
  }
}

async function onDrop(stageKey: Status) {
  dragOverCol.value = null
  if (draggedId.value === null) return
  const service = services.value.find(s => s.id === draggedId.value)
  draggedId.value = null
  if (!service || service.service_status === stageKey) return
  await moveStatus(service, stageKey)
}

// Fecha o picker ao clicar fora
onMounted(async () => {
  document.addEventListener('click', () => { pickingFor.value = null })

  try {
    services.value = await api.get<ServiceItem[]>('/dashboard/kanban')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Etapas do Serviço</h1>
        <p class="text-sm text-gray-500 mt-0.5">
          {{ totalActive }} serviço{{ totalActive !== 1 ? 's' : '' }} em andamento · arraste ou clique para mover
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
      <div v-for="i in 5" :key="i" class="bg-white rounded-xl p-4 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-24 mb-4" />
        <div class="space-y-3">
          <div v-for="j in 2" :key="j" class="h-20 bg-gray-100 rounded-lg" />
        </div>
      </div>
    </div>

    <!-- Board -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start">
      <div
        v-for="stage in STAGES"
        :key="stage.key"
        class="rounded-xl border-2 p-3 space-y-3 min-h-36 transition-colors duration-150"
        :class="[
          stage.bg,
          dragOverCol === stage.key ? stage.borderDrag : stage.border,
          dragOverCol === stage.key ? 'scale-[1.01]' : '',
        ]"
        @dragover="onDragOver($event, stage.key)"
        @dragleave="onDragLeave($event)"
        @drop="onDrop(stage.key)"
      >
        <!-- Header coluna -->
        <div class="flex items-center justify-between px-1">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full flex-shrink-0" :class="stage.dot" />
            <span class="text-xs font-semibold uppercase tracking-wide" :class="stage.color">
              {{ stage.label }}
            </span>
          </div>
          <span class="text-xs font-bold px-1.5 py-0.5 rounded-full bg-white/80" :class="stage.color">
            {{ grouped.get(stage.key)?.length ?? 0 }}
          </span>
        </div>

        <!-- Cards -->
        <div
          v-for="service in grouped.get(stage.key)"
          :key="service.id"
          draggable="true"
          class="bg-white rounded-lg border border-gray-100 p-3 shadow-sm space-y-2 cursor-grab active:cursor-grabbing select-none transition-opacity"
          :class="draggedId === service.id ? 'opacity-40' : 'opacity-100'"
          @dragstart="onDragStart($event, service)"
          @dragend="onDragEnd"
          @click.stop
        >
          <!-- Placa + share -->
          <div class="flex items-center justify-between gap-2">
            <NuxtLink
              :to="`/veiculos/${service.vehicle_id}`"
              class="font-mono text-sm font-bold text-blue-700 hover:underline"
              @click.stop
            >
              {{ service.vehicle?.plate }}
            </NuxtLink>
            <SharePopup v-if="service.tracking_token" :token="service.tracking_token" />
          </div>

          <!-- Veículo + cliente -->
          <div>
            <p class="text-xs text-gray-500">{{ service.vehicle?.brand }} {{ service.vehicle?.model }}</p>
            <p v-if="service.vehicle?.customer" class="text-xs text-gray-400 truncate">
              {{ service.vehicle.customer.name }}
            </p>
          </div>

          <!-- Descrição -->
          <p class="text-xs text-gray-700 line-clamp-2">{{ service.description }}</p>

          <!-- Entrega prevista -->
          <p v-if="service.estimated_delivery" class="text-xs text-gray-400">
            Entrega: <span class="font-medium text-gray-600">{{ formatDate(service.estimated_delivery) }}</span>
          </p>

          <!-- Ações -->
          <div class="flex items-center gap-1 pt-1.5 border-t border-gray-100">
            <!-- Spinner -->
            <div v-if="updating === service.id" class="w-3 h-3 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto" />

            <template v-else>
              <!-- Botão Mover para (picker) -->
              <div class="relative flex-1">
                <button
                  class="w-full text-xs py-1.5 px-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-1"
                  :class="[stage.color, 'hover:bg-black/5 border border-current/20']"
                  @click.stop="pickingFor = pickingFor === service.id ? null : service.id"
                >
                  Mover para
                  <svg class="w-3 h-3 transition-transform" :class="pickingFor === service.id ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <!-- Picker dropdown -->
                <Transition
                  enter-active-class="transition duration-100 ease-out"
                  enter-from-class="opacity-0 scale-95 -translate-y-1"
                  enter-to-class="opacity-100 scale-100 translate-y-0"
                  leave-active-class="transition duration-75 ease-in"
                  leave-from-class="opacity-100 scale-100 translate-y-0"
                  leave-to-class="opacity-0 scale-95 -translate-y-1"
                >
                  <div
                    v-if="pickingFor === service.id"
                    class="absolute bottom-full mb-1.5 left-0 right-0 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50"
                    @click.stop
                  >
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide px-3 pt-2.5 pb-1">
                      Selecionar etapa
                    </p>
                    <button
                      v-for="opt in ALL_STAGES_WITH_ENTREGUE"
                      :key="opt.key"
                      class="w-full text-left text-sm px-3 py-2 transition-colors flex items-center gap-2 disabled:opacity-40"
                      :class="opt.key === service.service_status
                        ? 'bg-blue-50 text-blue-700 font-semibold cursor-default'
                        : opt.key === 'entregue'
                          ? 'text-green-700 hover:bg-green-50'
                          : 'text-gray-700 hover:bg-gray-50'"
                      :disabled="opt.key === service.service_status"
                      @click="moveStatus(service, opt.key)"
                    >
                      <span
                        class="w-2 h-2 rounded-full flex-shrink-0"
                        :class="{
                          'bg-gray-400': opt.key === 'recebido',
                          'bg-blue-500': opt.key === 'em_diagnostico',
                          'bg-amber-500': opt.key === 'aguardando_pecas',
                          'bg-indigo-500': opt.key === 'em_servico',
                          'bg-green-500': opt.key === 'pronto',
                          'bg-green-700': opt.key === 'entregue',
                        }"
                      />
                      {{ opt.label }}
                      <svg v-if="opt.key === service.service_status" class="w-3.5 h-3.5 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </div>
                </Transition>
              </div>
            </template>
          </div>
        </div>

        <!-- Empty state coluna -->
        <div
          v-if="!grouped.get(stage.key)?.length"
          class="flex items-center justify-center py-6 rounded-lg border-2 border-dashed transition-colors"
          :class="dragOverCol === stage.key ? stage.borderDrag : 'border-transparent'"
        >
          <p class="text-xs opacity-30" :class="stage.color">Nenhum</p>
        </div>
      </div>
    </div>

    <!-- Empty geral -->
    <div v-if="!loading && totalActive === 0" class="text-center py-20">
      <svg class="w-14 h-14 text-gray-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <p class="text-gray-400 mb-2">Nenhum serviço em andamento.</p>
      <NuxtLink to="/veiculos" class="text-blue-600 hover:underline text-sm">
        Ir para Veículos
      </NuxtLink>
    </div>
  </div>
</template>
