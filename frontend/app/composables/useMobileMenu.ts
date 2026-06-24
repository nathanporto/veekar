const isOpen = ref(false)

export function useMobileMenu() {
  function close() { isOpen.value = false }
  function toggle() { isOpen.value = !isOpen.value }
  return { isOpen, close, toggle }
}
