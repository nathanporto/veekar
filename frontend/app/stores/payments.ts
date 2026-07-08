import { defineStore } from 'pinia'
import type { ServiceHistory } from '~/types'

export const usePaymentsStore = defineStore('payments', () => {
  const api = useApi()
  const payments = ref<ServiceHistory[]>([])
  const loading = ref(false)

  async function fetchAll() {
    loading.value = true
    try {
      payments.value = await api.get<ServiceHistory[]>('/payments')
    } finally {
      loading.value = false
    }
  }

  async function updateStatus(
    vehicleId: number,
    historyId: number,
    status: 'pendente' | 'parcial' | 'pago',
    amountPaid?: number,
  ): Promise<ServiceHistory> {
    const data = await api.patch<ServiceHistory>(
      `/vehicles/${vehicleId}/service-histories/${historyId}/payment-status`,
      { payment_status: status, amount_paid: amountPaid },
    )
    const idx = payments.value.findIndex(p => p.id === historyId)
    if (idx !== -1) payments.value[idx] = data
    return data
  }

  return { payments, loading, fetchAll, updateStatus }
})
