export interface User {
  id: number
  name: string
  company_name: string | null
  document: string | null
  email: string
}

export interface Customer {
  id: number
  name: string
  cpf: string | null
  phone: string
  email: string | null
  notes: string | null
  created_at: string
  updated_at: string
}

export interface Vehicle {
  id: number
  customer_id: number
  customer?: Customer
  brand: string
  model: string
  year: number
  color: string
  plate: string
  mileage: number
  created_at: string
  updated_at: string
}

export interface ServiceItem {
  id: number
  service_history_id: number
  description: string
  quantity: string
  unit_price: string
  created_at: string
  updated_at: string
}

export interface ServiceHistory {
  id: number
  vehicle_id: number
  vehicle?: Vehicle
  service_date: string
  description: string
  mileage: number
  amount: string | null
  notes: string | null
  items?: ServiceItem[]
  created_at: string
  updated_at: string
}

export interface SubscriptionStatus {
  status: 'trial' | 'active' | 'canceled' | 'past_due'
  current_period_end: string | null
  customers_count?: number
  vehicles_count?: number
  customers_limit?: number
  vehicles_limit?: number
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
