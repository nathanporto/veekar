<template>
  <div class="min-h-screen relative overflow-hidden bg-slate-950 flex items-center justify-center p-4">
    <!-- Fundo decorativo -->
    <div
      class="absolute -top-40 -left-40 w-[28rem] h-[28rem] bg-blue-600/30 rounded-full blur-3xl pointer-events-none animate-blob-a"
    />
    <div
      class="absolute -bottom-40 -right-40 w-[28rem] h-[28rem] bg-blue-500/20 rounded-full blur-3xl pointer-events-none animate-blob-b"
    />
    <div
      class="absolute top-1/3 right-0 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none animate-blob-c"
    />

    <div class="relative z-10 w-full flex flex-col items-center">
      <slot />
    </div>
  </div>
</template>

<style scoped>
@keyframes blob-a {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(40px, -30px) scale(1.15); }
}
@keyframes blob-b {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(-30px, 30px) scale(1.1); }
}
@keyframes blob-c {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(-20px, -20px) scale(1.2); }
}
.animate-blob-a { animation: blob-a 10s ease-in-out infinite; }
.animate-blob-b { animation: blob-b 12s ease-in-out infinite; }
.animate-blob-c { animation: blob-c 9s ease-in-out infinite; }
</style>
