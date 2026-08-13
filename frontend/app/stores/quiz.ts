import { defineStore } from 'pinia'

export const useQuizStore = defineStore('quiz', () => {
  const api = useApi()

  async function createLead(payload: {
    name: string
    email: string
    phone: string
    business_type?: string
    cars_per_month?: string
    current_control?: string
    main_pain?: string
    accepted_terms: boolean
  }): Promise<{ id: number }> {
    return api.post<{ id: number }>('/quiz/leads', payload)
  }

  async function checkout(leadId: number): Promise<{ url: string }> {
    return api.post<{ url: string }>('/quiz/checkout', { lead_id: leadId })
  }

  return { createLead, checkout }
})
