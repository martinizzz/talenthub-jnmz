<template>
  <div>
    <NuxtLink to="/company" class="link link-primary text-sm">← Panel de empresa</NuxtLink>
    <h1 class="my-4 text-3xl font-bold">Candidatos de la vacante</h1>
    <p v-if="error" class="alert alert-error">{{ error }}</p>
    <div v-if="loading" class="loading loading-spinner" />
    <p v-else-if="!applications.length" class="rounded-xl bg-base-100 p-8 text-center">Aún no hay postulaciones para esta vacante.</p>
    <div v-else class="space-y-3">
      <article v-for="item in applications" :key="item.id" class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-base-100 p-5">
        <div><h2 class="font-bold">{{ item.studentName }}</h2><p class="text-sm opacity-65">{{ item.studentEmail }}</p><p class="text-xs opacity-50">{{ new Date(item.createdAt).toLocaleDateString('es-MX') }}</p></div>
        <div class="flex flex-wrap items-center gap-2"><span class="badge badge-outline">{{ item.status }}</span><select class="select select-bordered select-sm" :value="item.status" aria-label="Actualizar postulación" @change="update(item, $event)"><option value="SUBMITTED" disabled>Enviada</option><option value="REVIEWING">En revisión</option><option value="ACCEPTED">Aceptada</option><option value="REJECTED">No seleccionada</option></select></div>
      </article>
    </div>
  </div>
</template>
<script setup lang="ts">
import type { Application, ApplicationStatus } from '~/types'
const route = useRoute()
const { request } = useApi()
const applications = ref<Application[]>([])
const loading = ref(true)
const error = ref('')
const jobId = computed(() => typeof route.query.job === 'string' ? route.query.job : '')
async function load() {
  if (!jobId.value) { error.value = 'Falta seleccionar una vacante.'; loading.value = false; return }
  loading.value = true
  try { applications.value = await request<Application[]>(`/jobs/${encodeURIComponent(jobId.value)}/applications`) }
  catch (e) { error.value = (e as Error).message }
  finally { loading.value = false }
}
onMounted(() => { void load() })
async function update(item: Application, event: Event) {
  const status = (event.target as HTMLSelectElement).value as ApplicationStatus
  error.value = ''
  try { await request<Application>(`/applications/${item.id}`, { method: 'PATCH', body: { status } }); await load() }
  catch (e) { error.value = (e as Error).message }
}
</script>