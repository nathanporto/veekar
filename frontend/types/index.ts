export interface User {
  id: number
  name: string
  email: string
}

export interface Customer {
  id: number
  name: string
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

export interface ServiceHistory {
  id: number
  vehicle_id: number
  vehicle?: Vehicle
  service_date: string
  description: string
  mileage: number
  amount: number | null
  notes: string | null
  created_at: string
  updated_at: string
}

export interface ApiResponse<T> {
  data: T
  message?: string
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
