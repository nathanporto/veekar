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
