<template>
  <div class="min-h-screen bg-base-200 text-base-content">
    <AppHeader />
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <NuxtPage />
    </main>
    <footer class="border-t border-base-300 py-6 text-center text-xs opacity-65">
      TalentHub GTO · Proyecto demostrativo académico · No institucional
    </footer>
  </div>
</template>
<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
onMounted(() => { void auth.initialize() })
</script>
