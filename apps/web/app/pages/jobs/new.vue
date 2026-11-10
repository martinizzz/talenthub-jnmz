<template>
  <div class="mx-auto max-w-2xl rounded-xl bg-base-100 p-7 shadow-sm">
    <NuxtLink to="/company" class="link link-primary text-sm">← Panel de empresa</NuxtLink>
    <h1 class="my-5 text-3xl font-bold">Publicar nueva vacante</h1>
    <div v-if="auth.ready && auth.profile?.role !== 'COMPANY'" class="alert alert-warning">Necesitas una cuenta de empresa para publicar.</div>
    <form v-else class="space-y-4" @submit.prevent="publish">
      <label class="block text-sm">Título del puesto<input v-model="title" class="input input-bordered mt-1 w-full" required minlength="5" maxlength="120" /></label>
      <label class="block text-sm">Ciudad o ubicación<input v-model="location" class="input input-bordered mt-1 w-full" required minlength="2" maxlength="120" /></label>
      <label class="block text-sm">Modalidad<select v-model="modality" class="select select-bordered mt-1 w-full"><option value="PRESENCIAL">Presencial</option><option value="HIBRIDO">Híbrido</option><option value="REMOTO">Remoto</option></select></label>
      <label class="block text-sm">Descripción y requisitos<textarea v-model="description" class="textarea textarea-bordered mt-1 h-40 w-full" required minlength="20" maxlength="2500" /></label>
      <div v-if="error" role="alert" class="alert alert-error">{{ error }}</div>
      <button class="btn btn-primary" :disabled="sending">{{ sending ? 'Publicando...' : 'Publicar vacante' }}</button>
    </form>
  </div>
</template>
<script setup lang="ts">
import type { Job, Modality } from '~/types'
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
const { request } = useApi()
const title = ref('')
const location = ref('')
const modality = ref<Modality>('PRESENCIAL')
const description = ref('')
const error = ref('')
const sending = ref(false)
async function publish() {
  sending.value = true
  error.value = ''
  try {
    await request<Job>('/jobs', { method: 'POST', body: { title: title.value, location: location.value, modality: modality.value, description: description.value } })
    await navigateTo('/company')
  } catch (e) { error.value = (e as Error).message }
  finally { sending.value = false }
}
</script>
