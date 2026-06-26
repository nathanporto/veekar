const publicRoutes = ['/', '/login', '/register', '/esqueci-senha', '/redefinir-senha', '/assinatura/sucesso', '/verificar-email/pendente']

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('veekar_token')

  const isPublic = publicRoutes.includes(to.path)
    || to.path.startsWith('/orcamento/')
    || to.path.startsWith('/verificar-email')

  if (!token.value && !isPublic) {
    return navigateTo('/login')
  }

  if (token.value && ['/login', '/register'].includes(to.path)) {
    return navigateTo('/dashboard')
  }
})
