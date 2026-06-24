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

  async function remove(vehicleId: number, historyId: number): Promise<void> {
    await api.delete(`/vehicles/${vehicleId}/service-histories/${historyId}`)
    histories.value = histories.value.filter(h => h.id !== historyId)
  }

  return { histories, loading, fetchByVehicle, create, remove }
})
