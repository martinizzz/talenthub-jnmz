<template>
  <div class="mx-auto max-w-md rounded-xl bg-base-100 p-7 shadow-sm">
    <h1 class="mb-2 text-3xl font-bold">Iniciar sesión</h1>
    <p class="mb-6 text-sm opacity-60">Accede a tus vacantes y postulaciones.</p>
    <form class="space-y-4" @submit.prevent="submit">
      <label class="form-control block"><span class="label-text">Correo electrónico</span><input v-model="email" class="input input-bordered mt-1 w-full" type="email" required /></label>
      <label class="form-control block"><span class="label-text">Contraseña</span><input v-model="password" class="input input-bordered mt-1 w-full" type="password" required /></label>
      <div v-if="error" role="alert" class="alert alert-error text-sm">{{ error }}</div>
      <button class="btn btn-primary w-full" :disabled="auth.busy">{{ auth.busy ? 'Ingresando...' : 'Ingresar' }}</button>
    </form>
    <p class="mt-5 text-center text-sm">¿No tienes cuenta? <NuxtLink to="/register" class="link link-primary">Regístrate</NuxtLink></p>
  </div>
</template>
<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
const email = ref('')
const password = ref('')
const error = ref('')
async function submit() {
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    await navigateTo(auth.profile?.role === 'COMPANY' ? '/company' : auth.profile?.role === 'STUDENT' ? '/applications' : '/')
  } catch (e) { error.value = (e as Error).message }
}
</script>
