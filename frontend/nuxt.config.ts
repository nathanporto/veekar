                 export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE ?? 'http://localhost:8000/api',
      metaPixelId: process.env.NUXT_PUBLIC_META_PIXEL_ID ?? '',
    },
  },

  typescript: {
    strict: true,
  },

  routeRules: {
    '/**': {
      headers: {
        'X-Frame-Options': 'SAMEORIGIN',
        'X-Content-Type-Options': 'nosniff',
        'Referrer-Policy': 'strict-origin-when-cross-origin',
        'Permissions-Policy': 'camera=(), microphone=(), geolocation=()',
        'Content-Security-Policy': [
          "default-src 'self'",
          "script-src 'self' 'unsafe-inline' https://connect.facebook.net",
          "style-src 'self' 'unsafe-inline'",
          "img-src 'self' data: https://www.facebook.com",
          "font-src 'self' data:",
          `connect-src 'self' https://veekar-production.up.railway.app https://www.facebook.com https://connect.facebook.net${process.env.NODE_ENV !== 'production' ? ' http://localhost:8000' : ''}`,
          "frame-ancestors 'self'",
          "base-uri 'self'",
          "form-action 'self'",
        ].join('; '),
      },
    },
  },

  app: {
    head: {
      title: 'Veekar',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Sistema de histórico automotivo' },
      ],
    },
  },

  tailwindcss: {
    configPath: '~/tailwind.config.ts',
  },
})
