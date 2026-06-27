export function useApi() {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('veekar_token')

  async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }

    if (token.value) {
      headers['Authorization'] = `Bearer ${token.value}`
    }

    const response = await fetch(`${config.public.apiBase}${path}`, {
      ...options,
      headers: { ...headers, ...(options.headers as Record<string, string> ?? {}) },
    })

    if (response.status === 401) {
      token.value = null
      await navigateTo('/login')
      throw new Error('Não autorizado')
    }

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Erro inesperado' }))
      // Se vier erros de validação por campo, usa a primeira mensagem de campo
      if (error.errors) {
        const firstField = Object.keys(error.errors)[0]
        const firstMsg = error.errors[firstField]?.[0]
        if (firstMsg && !firstMsg.startsWith('validation.')) {
          throw new Error(firstMsg)
        }
        // Fallback para mensagem amigável por campo
        const fieldLabels: Record<string, string> = {
          email: 'E-mail',
          document: 'CPF/CNPJ',
          name: 'Nome',
          password: 'Senha',
        }
        const label = fieldLabels[firstField] ?? firstField
        if (firstMsg?.startsWith('validation.unique')) throw new Error(`${label} já está em uso.`)
      }
      throw new Error(error.message && !error.message.startsWith('validation.') ? error.message : 'Erro de validação. Verifique os dados.')
    }

    if (response.status === 204) return undefined as T
    return response.json() as Promise<T>
  }

  return {
    get: <T>(path: string) => request<T>(path),
    post: <T>(path: string, body: unknown) =>
      request<T>(path, { method: 'POST', body: JSON.stringify(body) }),
    put: <T>(path: string, body: unknown) =>
      request<T>(path, { method: 'PUT', body: JSON.stringify(body) }),
    patch: <T>(path: string, body: unknown) =>
      request<T>(path, { method: 'PATCH', body: JSON.stringify(body) }),
    delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
  }
}
