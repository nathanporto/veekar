import { defineStore } from 'pinia'
import type { ServiceHistory } from '~/types'

export const useServiceHistoryStore = defineStore('serviceHistory', () => {
  const api = useApi()
  const histories = ref<ServiceHistory[]>([])
  const loading = ref(false)

  async function fetchByVehicle(vehicleId: number) {
    loading.value = true
    const data = await api.get<ServiceHistory[]>(`/vehicles/${vehicleId}/service-histories`)
    histories.value = data
    loading.value = false
  }

  async function create(
    vehicleId: number,
    payload: Omit<ServiceHistory, 'id' | 'vehicle_id' | 'vehicle' | 'created_at' | 'updated_at'>,
  ): Promise<ServiceHistory> {
    return api.post<ServiceHistory>(`/vehicles/${vehicleId}/service-histories`, payload)
  }

  async function update(vehicleId: number, historyId: number, payload: Partial<ServiceHistory>): Promise<ServiceHistory> {
    const data = await api.put<ServiceHistory>(`/vehicles/${vehicleId}/service-histories/${historyId}`, payload)
    const idx = histories.value.findIndex(h => h.id === historyId)
    if (idx !== -1) histories.value[idx] = data
    return data
  }

  async function remove(vehicleId: number, historyId: number): Promise<void> {
    await api.delete(`/vehicles/${vehicleId}/service-histories/${historyId}`)
    histories.value = histories.value.filter(h => h.id !== historyId)
  }

  async function updatePaymentStatus(
    vehicleId: number,
    historyId: number,
    paymentStatus: 'pendente' | 'parcial' | 'pago',
  ): Promise<ServiceHistory> {
    const data = await api.patch<ServiceHistory>(
      `/vehicles/${vehicleId}/service-histories/${historyId}/payment-status`,
      { payment_status: paymentStatus },
    )
    const idx = histories.value.findIndex(h => h.id === historyId)
    if (idx !== -1) histories.value[idx] = data
    return data
  }

  return { histories, loading, fetchByVehicle, create, update, remove, updatePaymentStatus }
})
