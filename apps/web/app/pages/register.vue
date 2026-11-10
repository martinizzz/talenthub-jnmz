<template>
  <div class="mx-auto max-w-lg rounded-xl bg-base-100 p-7 shadow-sm">
    <h1 class="mb-2 text-3xl font-bold">Crear cuenta</h1>
    <p class="mb-6 text-sm opacity-60">Regístrate como estudiante o representante de una empresa.</p>
    <form class="space-y-4" @submit.prevent="submit">
      <label class="block text-sm">Tipo de cuenta<select v-model="role" class="select select-bordered mt-1 w-full"><option value="STUDENT">Estudiante / egresado</option><option value="COMPANY">Empresa</option></select></label>
      <label class="block text-sm">Nombre completo<input v-model="name" class="input input-bordered mt-1 w-full" maxlength="100" required minlength="2" /></label>
      <label v-if="role === 'STUDENT'" class="block text-sm">Universidad<input v-model="university" class="input input-bordered mt-1 w-full" maxlength="120" required minlength="2" /></label>
      <label v-if="role === 'COMPANY'" class="block text-sm">Nombre de empresa<input v-model="companyName" class="input input-bordered mt-1 w-full" maxlength="120" required minlength="2" /></label>
      <label class="block text-sm">Correo electrónico<input v-model="email" class="input input-bordered mt-1 w-full" type="email" required /></label>
      <label class="block text-sm">Contraseña (6 caracteres mínimo)<input v-model="password" class="input input-bordered mt-1 w-full" type="password" minlength="6" required /></label>
      <p class="text-xs opacity-60">Proyecto de demostración. No compartas información personal sensible ni documentos reales.</p>
      <div v-if="error" role="alert" class="alert alert-error text-sm">{{ error }}</div>
      <button class="btn btn-primary w-full" :disabled="auth.busy">{{ auth.busy ? 'Creando cuenta...' : 'Crear cuenta gratuita' }}</button>
    </form>
  </div>
</template>
<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
const role = ref<'STUDENT' | 'COMPANY'>('STUDENT')
const name = ref('')
const companyName = ref('')
const university = ref('')
const email = ref('')
const password = ref('')
const error = ref('')
async function submit() {
  error.value = ''
  try {
    await auth.register({ email: email.value, password: password.value, name: name.value, role: role.value, companyName: companyName.value, university: university.value })
    await navigateTo(role.value === 'COMPANY' ? '/company' : '/applications')
  } catch (e) { error.value = (e as Error).message }
}
</script>
