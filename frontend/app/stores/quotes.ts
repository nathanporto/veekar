import { defineStore } from 'pinia'

export interface QuoteItem {
  id?: number
  description: string
  quantity: number
  unit_price: number
  subtotal?: number
}

export interface Quote {
  id: number
  token: string
  status: 'pending' | 'approved' | 'rejected'
  notes: string | null
  expires_at: string | null
  total: number
  customer: { id: number; name: string } | null
  vehicle: { id: number; plate: string; brand: string; model: string } | null
  items: QuoteItem[]
  created_at: string
}

export interface PublicQuote {
  token: string
  status: 'pending' | 'approved' | 'rejected'
  notes: string | null
  expires_at: string | null
  mechanic: { name: string; company_name: string | null }
  customer: { name: string } | null
  vehicle: { plate: string; brand: string; model: string; year: number } | null
  items: (QuoteItem & { subtotal: number })[]
  total: number
}

export const useQuotesStore = defineStore('quotes', () => {
  const api = useApi()
  const quotes = ref<Quote[]>([])
  const loading = ref(false)

  async function fetchQuotes() {
    loading.value = true
    try {
      quotes.value = await api.get<Quote[]>('/quotes')
    } finally {
      loading.value = false
    }
  }

  async function createQuote(payload: {
    customer_id?: number | null
    vehicle_id?: number | null
    notes?: string
    expires_at?: string
    items: { description: string; quantity: number; unit_price: number }[]
  }): Promise<Quote> {
    const created = await api.post<Quote>('/quotes', payload)
    quotes.value.unshift(created)
    return created
  }

  async function removeQuote(id: number) {
    await api.delete(`/quotes/${id}`)
    quotes.value = quotes.value.filter(q => q.id !== id)
  }

  const pendingCount = computed(() => quotes.value.filter(q => q.status === 'pending').length)

  return { quotes, loading, pendingCount, fetchQuotes, createQuote, removeQuote }
})
