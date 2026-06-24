import { defineStore } from 'pinia'
import type { SubscriptionStatus } from '~/types'

export const useSubscriptionStore = defineStore('subscription', () => {
  const api = useApi()

  const status = ref<SubscriptionStatus | null>(null)
  const loading = ref(false)

  const isTrial = computed(() => status.value?.status === 'trial')
  const isActive = computed(() => status.value?.status === 'active')
  const isExpired = computed(() => ['canceled', 'past_due'].includes(status.value?.status ?? ''))

  const atCustomerLimit = computed(() =>
    isTrial.value && (status.value?.customers_count ?? 0) >= (status.value?.customers_limit ?? 3),
  )

  const atVehicleLimit = computed(() =>
    isTrial.value && (status.value?.vehicles_count ?? 0) >= (status.value?.vehicles_limit ?? 3),
  )

  const trialCustomersLeft = computed(() =>
    Math.max(0, (status.value?.customers_limit ?? 3) - (status.value?.customers_count ?? 0)),
  )

  const trialVehiclesLeft = computed(() =>
    Math.max(0, (status.value?.vehicles_limit ?? 3) - (status.value?.vehicles_count ?? 0)),
  )

  async function fetchStatus() {
    loading.value = true
    try {
      status.value = await api.get<SubscriptionStatus>('/subscription/status')
    } finally {
      loading.value = false
    }
  }

  async function cancelSubscription() {
    await api.post('/subscription/cancel', {})
    await fetchStatus()
  }

  return {
    status,
    loading,
    isTrial,
    isActive,
    isExpired,
    atCustomerLimit,
    atVehicleLimit,
    trialCustomersLeft,
    trialVehiclesLeft,
    fetchStatus,
    cancelSubscription,
  }
})
