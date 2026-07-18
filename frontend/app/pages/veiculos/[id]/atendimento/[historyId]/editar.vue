<script setup lang="ts">
import { useServiceHistoryStore } from '~/stores/serviceHistory'
import { useVehiclesStore } from '~/stores/vehicles'
import { useEmployeesStore } from '~/stores/employees'
import type { Vehicle } from '~/types'

const historyStore = useServiceHistoryStore()
const vehiclesStore = useVehiclesStore()
const employeesStore = useEmployeesStore()
const router = useRouter()
const route = useRoute()
const vehicleId = Number(route.params.id)
const historyId = Number(route.params.historyId)

const vehicle = ref<Vehicle | null>(null)
const loading = ref(false)
const loadingData = ref(true)
const error = ref('')
const employeeId = ref<number | null>(null)

const selectedEmployeeCommission = computed(() => {
  const employee = employeesStore.employees.find(e => e.id === employeeId.value)
  if (!employee || employee.commission_type === 'nenhuma') return null
  return employee.commission_type === 'percentual'
    ? `${Number(employee.commission_value)}% de comissão por atendimento`
    : `${new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(employee.commission_value))} de comissão fixa por atendimento`
})

const form = reactive({
  service_date: '',
  description: '',
  mileage: 0,
  notes: '',
  estimated_delivery: '',
  return_date: '',
  return_reason: '',
  insurer: '',
  claim_number: '',
  insurance_status: '' as '' | 'aguardando' | 'aprovado' | 'recusado',
})

const hasReturn = computed(() => !!form.return_date)
const hasInsurance = computed(() => !!(form.insurer || form.claim_number || form.insurance_status))

const useReturn = ref(false)
const useInsurance = ref(false)

const today = new Date().toISOString().split('T')[0]

onMounted(async () => {
  const [v] = await Promise.all([
    vehiclesStore.fetchOne(vehicleId),
    historyStore.fetchByVehicle(vehicleId),
    employeesStore.fetchAll(),
  ])
  vehicle.value = v

  const history = historyStore.histories.find(h => h.id === historyId)
  if (!history) {
    await router.push(`/veiculos/${vehicleId}`)
    return
  }

  form.service_date = (history as any).service_date?.slice(0, 10) ?? ''
  form.description = (history as any).description ?? ''
  form.mileage = (history as any).mileage ?? 0
  form.notes = (history as any).notes ?? ''
  employeeId.value = (history as any).employee_id ?? null
  form.estimated_delivery = (history as any).estimated_delivery?.slice(0, 10) ?? ''
  form.return_date = (history as any).return_date?.slice(0, 10) ?? ''
  form.return_reason = (history as any).return_reason ?? ''
  form.insurer = (history as any).insurer ?? ''
  form.claim_number = (history as any).claim_number ?? ''
  form.insurance_status = (history as any).insurance_status ?? ''

  useReturn.value = !!form.return_date
  useInsurance.value = hasInsurance.value

  loadingData.value = false
})

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await historyStore.update(vehicleId, historyId, {
      service_date: form.service_date,
      description: form.description,
      mileage: Number(form.mileage),
      notes: form.notes || null,
      employee_id: employeeId.value || null,
      estimated_delivery: form.estimated_delivery || null,
      return_date: useReturn.value && form.return_date ? form.return_date : null,
      return_reason: useReturn.value && form.return_reason ? form.return_reason : null,
      insurer: useInsurance.value && form.insurer ? form.insurer : null,
      claim_number: useInsurance.value && form.claim_number ? form.claim_number : null,
      insurance_status: useInsurance.value && form.insurance_status ? form.insurance_status : null,
    } as any)
    await router.push(`/veiculos/${vehicleId}`)
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Erro ao salvar'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl space-y-5">
    <div class="flex items-center gap-3">
      <NuxtLink :to="`/veiculos/${vehicleId}`" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </NuxtLink>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Editar Atendimento</h1>
        <p v-if="vehicle" class="text-sm text-gray-500">
          {{ vehicle.plate }} — {{ vehicle.brand }} {{ vehicle.model }}
        </p>
      </div>
    </div>

    <div v-if="loadingData" class="space-y-4">
      <div v-for="i in 3" :key="i" class="bg-white rounded-xl p-6 shadow-sm animate-pulse h-32" />
    </div>

    <form v-else class="space-y-4" @submit.prevent="submit">
      <!-- Dados gerais -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Dados do Atendimento</h2>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Data *</label>
            <input
              v-model="form.service_date"
              type="date"
              required
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quilometragem *</label>
            <input
              v-model.number="form.mileage"
              type="number"
              required
              min="0"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Funcionário responsável
            <span class="text-gray-400 font-normal">(opcional)</span>
          </label>
          <select
            v-model="employeeId"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option :value="null">Nenhum</option>
            <option v-for="e in employeesStore.employees" :key="e.id" :value="e.id">{{ e.name }}</option>
          </select>
          <p v-if="selectedEmployeeCommission" class="text-xs text-green-600 mt-1">{{ selectedEmployeeCommission }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Descrição do Serviço *</label>
          <textarea
            v-model="form.description"
            rows="2"
            required
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Previsão de Entrega <span class="text-gray-400 font-normal">(opcional)</span>
          </label>
          <input
            v-model="form.estimated_delivery"
            type="date"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p class="text-xs text-gray-400 mt-1">Quando o veículo estará pronto para retirada</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Observações <span class="text-gray-400 font-normal">(opcional)</span>
          </label>
          <textarea
            v-model="form.notes"
            rows="2"
            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          />
        </div>
      </div>

      <!-- Seguro -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Seguro</h2>
            <p class="text-xs text-gray-400 mt-0.5">Seguradora, sinistro e status da aprovação</p>
          </div>
          <label class="flex items-center gap-2 cursor-pointer">
            <div
              class="relative w-10 h-5 rounded-full transition-colors"
              :class="useInsurance ? 'bg-blue-600' : 'bg-gray-300'"
              @click="useInsurance = !useInsurance"
            >
              <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"
                :class="useInsurance ? 'translate-x-5' : 'translate-x-0.5'" />
            </div>
            <span class="text-sm text-gray-600">Preencher</span>
          </label>
        </div>

        <template v-if="useInsurance">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Seguradora</label>
              <input
                v-model="form.insurer"
                type="text"
                placeholder="Ex: Porto Seguro, Bradesco..."
                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Nº do Sinistro</label>
              <input
                v-model="form.claim_number"
                type="text"
                placeholder="Ex: 2024/123456"
                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status do Seguro</label>
            <div class="flex gap-2">
              <button
                v-for="opt in [{ value: 'aguardando', label: 'Aguardando aprovação' }, { value: 'aprovado', label: 'Seguro aprovado' }, { value: 'recusado', label: 'Seguro recusado' }]"
                :key="opt.value"
                type="button"
                class="flex-1 py-2 text-sm font-medium rounded-lg border transition-colors"
                :class="form.insurance_status === opt.value
                  ? opt.value === 'aguardando' ? 'bg-yellow-500 text-white border-yellow-500'
                    : opt.value === 'aprovado' ? 'bg-green-600 text-white border-green-600'
                    : 'bg-red-500 text-white border-red-500'
                  : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                @click="form.insurance_status = opt.value as typeof form.insurance_status"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>
        </template>

        <p v-else class="text-sm text-gray-400">Ative para registrar informações de seguro.</p>
      </div>

      <!-- Agendamento de retorno -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Agendamento de Retorno</h2>
            <p class="text-xs text-gray-400 mt-0.5">Ex: próxima troca de óleo, revisão dos freios...</p>
          </div>
          <label class="flex items-center gap-2 cursor-pointer">
            <div
              class="relative w-10 h-5 rounded-full transition-colors"
              :class="useReturn ? 'bg-blue-600' : 'bg-gray-300'"
              @click="useReturn = !useReturn"
            >
              <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"
                :class="useReturn ? 'translate-x-5' : 'translate-x-0.5'" />
            </div>
            <span class="text-sm text-gray-600">Agendar</span>
          </label>
        </div>

        <template v-if="useReturn">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Data do retorno</label>
              <input
                v-model="form.return_date"
                type="date"
                :min="today"
                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Motivo</label>
              <input
                v-model="form.return_reason"
                type="text"
                placeholder="Ex: Troca de óleo, Revisão..."
                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </template>

        <p v-else class="text-sm text-gray-400">Ative para agendar quando o veículo deve retornar.</p>
      </div>

      <div v-if="error" class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-lg">{{ error }}</div>

      <div class="flex gap-3">
        <button
          type="submit"
          class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60"
          :disabled="loading"
        >
          {{ loading ? 'Salvando...' : 'Salvar Alterações' }}
        </button>
        <NuxtLink
          :to="`/veiculos/${vehicleId}`"
          class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
        >
          Cancelar
        </NuxtLink>
      </div>
    </form>
  </div>
</template>
