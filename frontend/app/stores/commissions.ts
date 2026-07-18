import { defineStore } from 'pinia'

export interface CommissionServiceEntry {
  id: number
  vehicle_id: number
  description: string
  service_date: string
  amount: string
  commission_amount: string
}

export interface EmployeeCommission {
  employee_id: number
  employee_name: string
  commission_type: 'nenhuma' | 'percentual' | 'fixo'
  commission_value: string | null
  total_commission: number
  services: CommissionServiceEntry[]
}

export const useCommissionsStore = defineStore('commissions', () => {
  const api = useApi()
  const commissions = ref<EmployeeCommission[]>([])
  const loading = ref(false)

  async function fetchAll() {
    loading.value = true
    try {
      commissions.value = await api.get<EmployeeCommission[]>('/commissions')
    } finally {
      loading.value = false
    }
  }

  return { commissions, loading, fetchAll }
})
