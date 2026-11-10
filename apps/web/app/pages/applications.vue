<template>
  <div>
    <h1 class="text-3xl font-bold">Mis postulaciones</h1>
    <p class="mt-1 text-sm opacity-65">Sigue el estado de tus oportunidades de empleo.</p>
    <div v-if="auth.ready && auth.profile?.role !== 'STUDENT'" class="alert alert-warning mt-5">Necesitas una cuenta de estudiante para ver tus postulaciones.</div>
    <div v-else-if="loading" class="mt-8 loading loading-spinner" />
    <div v-else-if="error" role="alert" class="alert alert-error mt-5">{{ error }}</div>
    <div v-else-if="!applications.length" class="mt-6 rounded-xl bg-base-100 p-8 text-center">Todavía no te has postulado a ninguna vacante. <NuxtLink to="/" class="link link-primary">Explorar empleos</NuxtLink></div>
    <div v-else class="mt-6 space-y-3">
      <article v-for="item in applications" :key="item.id" class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-base-100 p-5">
        <div><h2 class="font-bold">{{ item.jobTitle }}</h2><p class="text-xs opacity-60">Fecha: {{ new Date(item.createdAt).toLocaleDateString('es-MX') }}</p></div>
        <div class="badge" :class="item.status === 'ACCEPTED' ? 'badge-success' : item.status === 'REJECTED' ? 'badge-error' : 'badge-info'">{{ labels[item.status] }}</div>
      </article>
    </div>
  </div>
</template>
<script setup lang="ts">
import type { Application, ApplicationStatus } from '~/types'
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
const { request } = useApi()
const applications = ref<Application[]>([])
const loading = ref(true)
const error = ref('')
const labels: Record<ApplicationStatus, string> = { SUBMITTED: 'Enviada', REVIEWING: 'En revisión', ACCEPTED: 'Aceptada', REJECTED: 'No seleccionada' }
async function load() {
  if (auth.profile?.role !== 'STUDENT') { loading.value = false; return }
  loading.value = true
  try { applications.value = await request<Application[]>('/applications/mine') }
  catch (e) { error.value = (e as Error).message }
  finally { loading.value = false }
}
watch(() => auth.profile?.role, () => { void load() }, { immediate: true })
</script>