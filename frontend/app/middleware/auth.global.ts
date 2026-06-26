const publicRoutes = ['/', '/login', '/register', '/esqueci-senha', '/redefinir-senha', '/assinatura/sucesso']

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('veekar_token')

  const isPublic = publicRoutes.includes(to.path)
    || to.path.startsWith('/orcamento/')

  if (!token.value && !isPublic) {
    return navigateTo('/login')
  }

  if (token.value && ['/login', '/register'].includes(to.path)) {
    return navigateTo('/dashboard')
  }
})
