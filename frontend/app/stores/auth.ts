import { defineStore } from 'pinia'
import type { User } from '~/types'

export const useAuthStore = defineStore('auth', () => {
  const api = useApi()
  const user = ref<User | null>(null)
  const token = useCookie<string | null>('veekar_token', { maxAge: 86400 })

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string) {
    const data = await api.post<{ token: string; user: User; code?: string }>('/auth/login', { email, password })
    token.value = data.token
    user.value = data.user
    await navigateTo('/dashboard')
  }

  async function register(name: string, companyName: string, document: string, email: string, password: string, passwordConfirmation: string, acceptedTerms: boolean) {
    await api.post<{ message: string }>('/auth/register', {
      name,
      company_name: companyName,
      document,
      email,
      password,
      password_confirmation: passwordConfirmation,
      accepted_terms: acceptedTerms,
    })
    await navigateTo(`/verificar-email/pendente?email=${encodeURIComponent(email)}`)
  }

  async function resendVerification(email: string) {
    return api.post<{ message: string }>('/auth/resend-verification', { email })
  }

  async function logout() {
    await api.post('/auth/logout', {}).catch(() => {})
    token.value = null
    user.value = null
    await navigateTo('/login')
  }

  async function fetchUser() {
    if (!token.value) return
    user.value = await api.get<User>('/auth/me')
  }

  async function acceptTerms() {
    user.value = await api.post<User>('/auth/accept-terms', {})
  }

  return { user, token, isAuthenticated, login, register, logout, fetchUser, acceptTerms, resendVerification }
})
