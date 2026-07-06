import { defineStore } from 'pinia'
import type { Product } from '~/types'

export const useProductsStore = defineStore('products', () => {
  const api = useApi()
  const products = ref<Product[]>([])
  const loading = ref(false)

  async function fetchAll() {
    loading.value = true
    try {
      products.value = await api.get<Product[]>('/products')
    } finally {
      loading.value = false
    }
  }

  async function create(payload: { name: string; unit_cost: number; unit_price: number }): Promise<Product> {
    const data = await api.post<Product>('/products', payload)
    products.value.push(data)
    return data
  }

  async function remove(productId: number): Promise<void> {
    await api.delete(`/products/${productId}`)
    products.value = products.value.filter(p => p.id !== productId)
  }

  async function registerEntrada(
    productId: number,
    payload: { quantity: number; unit_cost: number; movement_date: string; notes?: string },
  ): Promise<Product> {
    const data = await api.post<Product>(`/products/${productId}/entrada`, payload)
    const idx = products.value.findIndex(p => p.id === productId)
    if (idx !== -1) products.value[idx] = data
    return data
  }

  async function registerSaida(
    productId: number,
    payload: { quantity: number; unit_price?: number; movement_date: string; notes?: string },
  ): Promise<Product> {
    const data = await api.post<Product>(`/products/${productId}/saida`, payload)
    const idx = products.value.findIndex(p => p.id === productId)
    if (idx !== -1) products.value[idx] = data
    return data
  }

  return { products, loading, fetchAll, create, remove, registerEntrada, registerSaida }
})
