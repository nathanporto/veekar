import { defineStore } from 'pinia'
import type { Vehicle, PaginatedResponse } from '~/types'

export const useVehiclesStore = defineStore('vehicles', () => {
  const api = useApi()
  const vehicles = ref<Vehicle[]>([])
  const total = ref(0)
  const loading = ref(false)

  async function fetchAll(search = '') {
    loading.value = true
    const query = search ? `?search=${encodeURIComponent(search)}` : ''
    const data = await api.get<PaginatedResponse<Vehicle>>(`/vehicles${query}`)
    vehicles.value = data.data
    total.value = data.total
    loading.value = false
  }

  async function fetchOne(id: number): Promise<Vehicle> {
    return api.get<Vehicle>(`/vehicles/${id}`)
  }

  async function searchByPlate(plate: string): Promise<Vehicle | null> {
    return api.get<Vehicle>(`/vehicles/search?plate=${encodeURIComponent(plate)}`).catch(() => null)
  }

  async function create(payload: Omit<Vehicle, 'id' | 'customer' | 'created_at' | 'updated_at'>): Promise<Vehicle> {
    return api.post<Vehicle>('/vehicles', payload)
  }

  async function update(id: number, payload: Partial<Vehicle>): Promise<Vehicle> {
    return api.put<Vehicle>(`/vehicles/${id}`, payload)
  }

  async function remove(id: number): Promise<void> {
    await api.delete(`/vehicles/${id}`)
    vehicles.value = vehicles.value.filter(v => v.id !== id)
  }

  return { vehicles, total, loading, fetchAll, fetchOne, searchByPlate, create, update, remove }
})
