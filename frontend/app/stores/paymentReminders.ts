import { defineStore } from 'pinia'
import type { PaymentReminder } from '~/types'

export const usePaymentRemindersStore = defineStore('paymentReminders', () => {
  const api = useApi()
  const reminders = ref<PaymentReminder[]>([])
  const loading = ref(false)

  async function fetchAll() {
    loading.value = true
    try {
      reminders.value = await api.get<PaymentReminder[]>('/payment-reminders')
    } finally {
      loading.value = false
    }
  }

  async function create(payload: { description: string; amount: number | null; due_date: string }): Promise<PaymentReminder> {
    const data = await api.post<PaymentReminder>('/payment-reminders', payload)
    reminders.value.push(data)
    return data
  }

  async function markPaid(id: number, paid: boolean): Promise<PaymentReminder> {
    const data = await api.patch<PaymentReminder>(`/payment-reminders/${id}`, { paid })
    const idx = reminders.value.findIndex(r => r.id === id)
    if (idx !== -1) reminders.value[idx] = data
    return data
  }

  async function remove(id: number): Promise<void> {
    await api.delete(`/payment-reminders/${id}`)
    reminders.value = reminders.value.filter(r => r.id !== id)
  }

  return { reminders, loading, fetchAll, create, markPaid, remove }
})
