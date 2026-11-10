<template>
  <div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div><h1 class="text-3xl font-black">Panel de empresa</h1><p class="opacity-65">{{ auth.profile?.companyName }}</p></div>
      <NuxtLink v-if="auth.profile?.role === 'COMPANY'" to="/jobs/new" class="btn btn-primary">+ Publicar vacante</NuxtLink>
    </div>
    <div v-if="auth.ready && auth.profile?.role !== 'COMPANY'" class="alert alert-warning">Solo las cuentas de empresa pueden acceder a este panel. <NuxtLink to="/login" class="link">Ingresar</NuxtLink></div>
    <template v-else>
      <p v-if="error" role="alert" class="alert alert-error">{{ error }}</p>
      <div v-if="loading" class="loading loading-spinner" />
      <p v-else-if="!jobs.length" class="rounded-xl bg-base-100 p-8 text-center">No has publicado vacantes todavía.</p>
      <div v-else class="grid gap-4 md:grid-cols-2">
        <article v-for="job in jobs" :key="job.id" class="card border border-base-300 bg-base-100">
          <div class="card-body">
            <div class="flex items-start justify-between gap-2"><h2 class="card-title">{{ job.title }}</h2><span class="badge" :class="job.status === 'OPEN' ? 'badge-success' : 'badge-neutral'">{{ job.status }}</span></div>
            <p class="text-sm opacity-65">{{ job.location }} · {{ job.modality }}</p>
            <div class="card-actions mt-3">
              <NuxtLink :to="`/company-applications?job=${job.id}`" class="btn btn-outline btn-sm">Ver candidatos</NuxtLink>
              <NuxtLink :to="`/jobs/${job.id}`" class="btn btn-ghost btn-sm">Ver</NuxtLink>
              <button v-if="job.status === 'OPEN'" class="btn btn-warning btn-sm" @click="closeJob(job)">Cerrar vacante</button>
            </div>
          </div>
        </article>
      </div>
    </template>
  </div>
</template>
<script setup lang="ts">
import type { Job } from '~/types'
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
const { request } = useApi()
const jobs = ref<Job[]>([])
const loading = ref(true)
const error = ref('')
async function load() {
  if (auth.profile?.role !== 'COMPANY') { loading.value = false; return }
  loading.value = true
  try { jobs.value = await request<Job[]>('/company/jobs') }
  catch (e) { error.value = (e as Error).message }
  finally { loading.value = false }
}
watch(() => auth.profile?.role, () => { void load() }, { immediate: true })
async function closeJob(job: Job) {
  if (!confirm(`¿Cerrar la vacante "${job.title}"?`)) return
  try { await request<Job>(`/jobs/${job.id}`, { method: 'PATCH', body: { status: 'CLOSED' } }); await load() }
  catch (e) { error.value = (e as Error).message }
}
</script>
