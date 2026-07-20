declare global {
  interface Window {
    pixelId: string
  }
}

export default defineNuxtPlugin(() => {
  window.pixelId = '6a5e646d1c32eb58e8024ec3'

  const script = document.createElement('script')
  script.setAttribute('async', '')
  script.setAttribute('defer', '')
  script.setAttribute('src', 'https://cdn.utmify.com.br/scripts/pixel/pixel.js')
  document.head.appendChild(script)
})
