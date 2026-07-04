declare global {
  interface Window {
    fbq: ((...args: any[]) => void) & { queue?: any[] }
    _fbq: unknown
  }
}

export default defineNuxtPlugin(() => {
  const { metaPixelId } = useRuntimeConfig().public

  if (!metaPixelId) return

  ;(function (f: Window, b: Document, e: string, v: string) {
    if (f.fbq) return
    const n: any = (f.fbq = function (...args: unknown[]) {
      n.callMethod ? n.callMethod(...args) : n.queue.push(args)
    })
    if (!f._fbq) f._fbq = n
    n.push = n
    n.loaded = true
    n.version = '2.0'
    n.queue = []
    const t = b.createElement(e) as HTMLScriptElement
    t.async = true
    t.src = v
    const s = b.getElementsByTagName(e)[0]
    s.parentNode?.insertBefore(t, s)
  })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js')

  window.fbq('init', metaPixelId)
  window.fbq('track', 'PageView')

  useRouter().afterEach(() => {
    window.fbq('track', 'PageView')
  })
})
