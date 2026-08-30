// Dispara eventos do Meta Pixel com segurança (só roda no client, só se o pixel existir).
export function useMetaPixel() {
  function track(event: string, params?: Record<string, unknown>) {
    if (typeof window === 'undefined' || !window.fbq) return
    window.fbq('track', event, params)
  }

  function trackCustom(event: string, params?: Record<string, unknown>) {
    if (typeof window === 'undefined' || !window.fbq) return
    window.fbq('trackCustom', event, params)
  }

  return { track, trackCustom }
}
