import { defineStore } from 'pinia'
import type { Customer, PaginatedResponse } from '~/types'

export const useCustomersStore = defineStore('customers', () => {
  const api = useApi()
  const customers = ref<Customer[]>([])
  const total = ref(0)
  const loading = ref(false)

  async function fetchAll(search = '') {
    loading.value = true
    const query = search ? `?search=${encodeURIComponent(search)}` : ''
    const data = await api.get<PaginatedResponse<Customer>>(`/customers${query}`)
    customers.value = data.data
    total.value = data.total
    loading.value = false
  }

  async function fetchOne(id: number): Promise<Customer> {
    return api.get<Customer>(`/customers/${id}`)
  }

  async function create(payload: Omit<Customer, 'id' | 'created_at' | 'updated_at'>): Promise<Customer> {
    return api.post<Customer>('/customers', payload)
  }

  async function update(id: number, payload: Partial<Customer>): Promise<Customer> {
    return api.put<Customer>(`/customers/${id}`, payload)
  }

  async function remove(id: number): Promise<void> {
    await api.delete(`/customers/${id}`)
    customers.value = customers.value.filter(c => c.id !== id)
  }

  return { customers, total, loading, fetchAll, fetchOne, create, update, remove }
})
