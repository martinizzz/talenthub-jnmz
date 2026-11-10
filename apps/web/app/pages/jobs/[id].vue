<template>
  <div class="mx-auto max-w-3xl">
    <NuxtLink to="/" class="link link-primary text-sm">← Volver a vacantes</NuxtLink>
    <div v-if="loading" class="my-10 text-center"><span class="loading loading-spinner" /></div>
    <div v-else-if="error" role="alert" class="alert alert-error mt-4">{{ error }}</div>
    <article v-else-if="job" class="mt-5 rounded-2xl bg-base-100 p-7 shadow-sm">
      <div class="badge badge-secondary badge-outline mb-3">{{ job.modality }}</div>
      <h1 class="text-3xl font-bold">{{ job.title }}</h1>
      <p class="mt-3 text-lg font-medium text-primary">{{ job.companyName }}</p>
      <p class="mt-1 text-sm opacity-60">{{ job.location }}</p>
      <div class="divider" />
      <h2 class="text-xl font-semibold">Descripción de la oportunidad</h2>
      <p class="mt-3 whitespace-pre-line leading-7">{{ job.description }}</p>
      <div class="divider" />
      <p v-if="success" class="alert alert-success mb-4">Postulación registrada correctamente.</p>
      <p v-if="applyError" role="alert" class="alert alert-error mb-4">{{ applyError }}</p>
      <button v-if="auth.profile?.role === 'STUDENT' && job.status === 'OPEN'" class="btn btn-primary" :disabled="sending || !!success" @click="apply">
        {{ sending ? 'Enviando...' : 'Postularme a esta vacante' }}
      </button>
      <NuxtLink v-else-if="!auth.user" to="/login" class="btn btn-primary">Inicia sesión para postularte</NuxtLink>
      <p v-else-if="auth.profile?.role === 'COMPANY'" class="text-sm opacity-60">Cuenta de empresa: puedes publicar vacantes desde tu panel.</p>
    </article>
  </div>
</template>
<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import type { Job, Application } from '~/types'
const auth = useAuthStore()
const { request } = useApi()
const route = useRoute()
const job = ref<Job | null>(null)
const loading = ref(true)
const error = ref('')
const applyError = ref('')
const success = ref(false)
const sending = ref(false)
onMounted(async () => {
  try { job.value = await request<Job>(`/jobs/${route.params.id}`) }
  catch (e) { error.value = (e as Error).message }
  finally { loading.value = false }
})
async function apply() {
  if (!job.value) return
  sending.value = true
  applyError.value = ''
  try { await request<Application>('/applications', { method: 'POST', body: { jobId: job.value.id } }); success.value = true }
  catch (e) { applyError.value = (e as Error).message }
  finally { sending.value = false }
}
</script>