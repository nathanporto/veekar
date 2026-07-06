export interface CashFlowMonth {
  label: string
  entradas: number
  saidas: number
  saldo: number
}

export interface FinancialReport {
  current_month: { total: number; count: number; avg: number }
  previous_month_total: number
  growth_percent: number | null
  chart: { label: string; amount: number }[]
  cash_flow: {
    current_month: CashFlowMonth
    chart: CashFlowMonth[]
  }
}

export const useReportsStore = defineStore('reports', () => {
  const api = useApi()

  const financial = ref<FinancialReport | null>(null)
  const loading = ref(false)

  async function fetchFinancial() {
    loading.value = true
    try {
      financial.value = await api.get<FinancialReport>('/reports/financial')
    } finally {
      loading.value = false
    }
  }

  return { financial, loading, fetchFinancial }
})
