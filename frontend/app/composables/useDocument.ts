export function useDocument() {
  function maskCpf(digits: string): string {
    return digits
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
  }

  function maskCnpj(digits: string): string {
    return digits
      .replace(/(\d{2})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1/$2')
      .replace(/(\d{4})(\d{1,2})$/, '$1-$2')
  }

  function applyMask(raw: string): string {
    const digits = raw.replace(/\D/g, '').slice(0, 14)
    if (digits.length <= 11) return maskCpf(digits)
    return maskCnpj(digits)
  }

  function validateCpf(value: string): boolean {
    const d = value.replace(/\D/g, '')
    if (d.length !== 11) return false
    if (/^(\d)\1{10}$/.test(d)) return false

    let sum = 0
    for (let i = 0; i < 9; i++) sum += parseInt(d[i]) * (10 - i)
    let check = (sum * 10) % 11
    if (check >= 10) check = 0
    if (check !== parseInt(d[9])) return false

    sum = 0
    for (let i = 0; i < 10; i++) sum += parseInt(d[i]) * (11 - i)
    check = (sum * 10) % 11
    if (check >= 10) check = 0
    return check === parseInt(d[10])
  }

  function validateCnpj(value: string): boolean {
    const d = value.replace(/\D/g, '')
    if (d.length !== 14) return false
    if (/^(\d)\1{13}$/.test(d)) return false

    const calc = (weights: number[]) => {
      const sum = weights.reduce((acc, w, i) => acc + parseInt(d[i]) * w, 0)
      const rest = sum % 11
      return rest < 2 ? 0 : 11 - rest
    }

    const first = calc([5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2])
    if (first !== parseInt(d[12])) return false

    const second = calc([6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2])
    return second === parseInt(d[13])
  }

  function validate(value: string): boolean {
    const digits = value.replace(/\D/g, '')
    if (digits.length === 11) return validateCpf(value)
    if (digits.length === 14) return validateCnpj(value)
    return false
  }

  function getType(value: string): 'CPF' | 'CNPJ' | null {
    const digits = value.replace(/\D/g, '')
    if (digits.length === 0) return null
    return digits.length <= 11 ? 'CPF' : 'CNPJ'
  }

  return { applyMask, validate, validateCpf, validateCnpj, getType }
}
