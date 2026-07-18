import { defineStore } from 'pinia'
import type { Employee } from '~/types'

export const useEmployeesStore = defineStore('employees', () => {
  const api = useApi()
  const employees = ref<Employee[]>([])
  const loading = ref(false)

  async function fetchAll() {
    loading.value = true
    try {
      employees.value = await api.get<Employee[]>('/employees')
    } finally {
      loading.value = false
    }
  }

  async function create(payload: { name: string; commission_type: string; commission_value: number | null }): Promise<Employee> {
    const data = await api.post<Employee>('/employees', payload)
    employees.value.push(data)
    return data
  }

  async function update(id: number, payload: { name: string; commission_type: string; commission_value: number | null }): Promise<Employee> {
    const data = await api.put<Employee>(`/employees/${id}`, payload)
    const idx = employees.value.findIndex(e => e.id === id)
    if (idx !== -1) employees.value[idx] = data
    return data
  }

  async function remove(id: number): Promise<void> {
    await api.delete(`/employees/${id}`)
    employees.value = employees.value.filter(e => e.id !== id)
  }

  return { employees, loading, fetchAll, create, update, remove }
})
