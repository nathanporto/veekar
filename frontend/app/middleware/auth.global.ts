const publicRoutes = ['/', '/login', '/register', '/esqueci-senha', '/redefinir-senha', '/assinatura/sucesso']

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('veekar_token')

  if (!token.value && !publicRoutes.includes(to.path)) {
    return navigateTo('/login')
  }

  if (token.value && ['/login', '/register'].includes(to.path)) {
    return navigateTo('/dashboard')
  }
})
