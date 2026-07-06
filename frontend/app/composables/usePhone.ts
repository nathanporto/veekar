export function usePhone() {
  function maskLandline(digits: string): string {
    return digits
      .replace(/^(\d{2})(\d)/, '($1) $2')
      .replace(/(\d{4})(\d{1,4})$/, '$1-$2')
  }

  function maskMobile(digits: string): string {
    return digits
      .replace(/^(\d{2})(\d)/, '($1) $2')
      .replace(/(\d{5})(\d{1,4})$/, '$1-$2')
  }

  function applyMask(raw: string): string {
    const digits = raw.replace(/\D/g, '').slice(0, 11)
    return digits.length <= 10 ? maskLandline(digits) : maskMobile(digits)
  }

  return { applyMask }
}
