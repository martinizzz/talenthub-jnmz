<template>
  <div class="border-b border-base-300 bg-base-100">
    <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
      <NuxtLink to="/" class="text-xl font-black tracking-tight text-primary">TalentHub <span class="text-base-content">GTO</span></NuxtLink>
      <div class="flex flex-wrap items-center gap-2 text-sm">
        <NuxtLink to="/" class="btn btn-ghost btn-sm">Vacantes</NuxtLink>
        <NuxtLink v-if="auth.profile?.role === 'STUDENT'" to="/applications" class="btn btn-ghost btn-sm">Mis postulaciones</NuxtLink>
        <NuxtLink v-if="auth.profile?.role === 'COMPANY'" to="/company" class="btn btn-ghost btn-sm">Mi empresa</NuxtLink>
        <template v-if="auth.user">
          <span class="hidden text-xs opacity-65 sm:inline">{{ auth.profile?.name || auth.user.email }}</span>
          <button class="btn btn-outline btn-sm" @click="auth.logout()">Salir</button>
        </template>
        <template v-else>
          <NuxtLink to="/login" class="btn btn-ghost btn-sm">Ingresar</NuxtLink>
          <NuxtLink to="/register" class="btn btn-primary btn-sm">Registrarme</NuxtLink>
        </template>
      </div>
    </nav>
  </div>
</template>
<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
</script>