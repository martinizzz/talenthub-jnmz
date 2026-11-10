# TALENTHUB GTO — SESIÓN 03/04

## Registro, login, roles y publicación de vacantes

**Duración de clase:** 90 minutos (el proyecto completo incluye trabajo complementario). **Resultado:** Habilitar registro/login de estudiantes y empresas, y permitir que las empresas publiquen sus vacantes con autorizaciones.

## 1. Retomar exactamente la sesión 02

1. Abre el MISMO repositorio `talenthub-gto/`. Los archivos de las sesiones 01 y 02 siguen presentes.
2. Comprueba que Firebase Spark tiene habilitado Email/Password, reglas Firestore publicadas y que `apps/web/.env` y `apps/api/config/local.php` contienen los valores correctos.
3. Crea la rama:

```bash
git switch develop
git switch -c feature/sesion-03
```

4. **No edites ninguno de los `package.json`.** Esta sesión solo modifica 3 archivos y agrega 4 archivos (ver tabla 3).
5. Al editar un archivo marcado como **REEMPLAZAR**, elimina el contenido antiguo y pega el bloque nuevo COMPLETO. No combines dos versiones del archivo.

## 2. Árbol del CÓDIGO FUENTE acumulado al terminar esta sesión

```text
talenthub-gto/
├── apps/
│   ├── api/
│   │   ├── config/
│   │   │   └── local.php.example
│   │   ├── public/
│   │   │   ├── .htaccess
│   │   │   └── index.php
│   │   ├── src/
│   │   │   ├── ApiException.php
│   │   │   ├── Application.php
│   │   │   ├── Auth.php
│   │   │   ├── bootstrap.php
│   │   │   ├── Config.php
│   │   │   ├── Firestore.php
│   │   │   ├── Http.php
│   │   │   ├── Remote.php
│   │   │   └── Validator.php
│   │   ├── tests/
│   │   │   ├── requirements.php
│   │   │   └── smoke.php
│   │   └── router.php
│   └── web/
│       ├── app/
│       │   ├── assets/
│       │   │   └── css/
│       │   │       └── main.css
│       │   ├── components/
│       │   │   └── AppHeader.vue
│       │   ├── composables/
│       │   │   └── useApi.ts
│       │   ├── pages/
│       │   │   ├── jobs/
│       │   │   │   ├── [id].vue
│       │   │   │   └── new.vue
│       │   │   ├── [...slug].vue
│       │   │   ├── company.vue
│       │   │   ├── index.vue
│       │   │   ├── login.vue
│       │   │   └── register.vue
│       │   ├── plugins/
│       │   │   └── firebase.client.ts
│       │   ├── stores/
│       │   │   └── auth.ts
│       │   ├── types/
│       │   │   └── index.ts
│       │   └── app.vue
│       ├── .env.example
│       ├── nuxt.config.ts
│       └── package.json
├── firebase/
│   ├── firestore.indexes.json
│   └── firestore.rules
├── .gitignore
├── firebase.json
├── netlify.toml
└── package.json
```

Este árbol muestra los **archivos de código y configuración de la aplicación**, sin las guías Markdown, `README` ni el material de respaldo del ZIP. Las carpetas `node_modules/`, `.nuxt/` y `.output/` son generadas por npm/Nuxt y tampoco aparecen. Los archivos locales `.env` y `local.php` son exclusivos de cada equipo.

## 3. Lista de archivos: ¿qué creo y qué reemplazo?

| Orden | Acción | Ruta exacta | ¿Para qué sirve? |
|---:|---|---|---|
| 01 | **REEMPLAZAR COMPLETO** | `apps/api/src/Application.php` | Enrutador y casos de uso REST; evoluciona en cada sesión. |
| 02 | **REEMPLAZAR COMPLETO** | `apps/web/app/components/AppHeader.vue` | Barra de navegación; cambia al añadir áreas privadas. |
| 03 | **REEMPLAZAR COMPLETO** | `apps/web/app/app.vue` | Diseño raíz de Nuxt. |
| 04 | CREAR | `apps/web/app/pages/register.vue` | Formulario de registro de estudiante o empresa. |
| 05 | CREAR | `apps/web/app/pages/login.vue` | Formulario para iniciar sesión. |
| 06 | CREAR | `apps/web/app/pages/company.vue` | Panel y listado de vacantes propias de la empresa. |
| 07 | CREAR | `apps/web/app/pages/jobs/new.vue` | Formulario de publicación de vacantes. |

> **Ruta:** todas las rutas son relativas a la carpeta `talenthub-gto/`. Un nombre como `apps/web/package.json` significa que debes crear el archivo **dentro de** `apps/web/`, no en la raíz. **Cada bloque de código es el archivo completo**; no copies el encabezado Markdown dentro del archivo. Las rutas con `[id].vue` conservan literalmente los corchetes.

## 4. CÓDIGO COMPLETO — copiar en este orden

### Paso 01 — `apps/api/src/Application.php` · REEMPLAZAR EL ARCHIVO COMPLETO

Enrutador y casos de uso REST; evoluciona en cada sesión.

**Ubicación en VS Code:** `talenthub-gto/apps/api/src/Application.php`.

```php
<?php
declare(strict_types=1);
namespace TalentHub;

final class Application
{
    public static function handle(): void
    {
        try {
            Http::cors();
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');
            $segments = explode('/', $path);
            if (array_slice($segments, 0, 2) !== ['api', 'v1']) {
                throw new ApiException('Ruta no encontrada.', 404);
            }
            $part = array_slice($segments, 2);
            $token = Http::token();
            $db = null;
            if ($method === 'GET' && $part === ['health']) {
                Http::json(['ok' => true, 'service' => 'talenthub-php', 'version' => '1.0.0']);
            }
            $db = new Firestore();
            if ($method === 'POST' && $part === ['profiles']) {
                $user = Auth::identity($token);
                $body = Http::body();
                $role = Validator::oneOf($body, 'role', ['STUDENT', 'COMPANY']);
                $document = [
                    'uid' => $user['uid'],
                    'email' => $user['email'],
                    'name' => Validator::text($body, 'name', 100, 2),
                    'role' => $role,
                    'companyName' => $role === 'COMPANY' ? Validator::text($body, 'companyName', 120, 2) : '',
                    'university' => $role === 'STUDENT' ? Validator::text($body, 'university', 120, 2) : '',
                    'createdAt' => gmdate('c'),
                ];
                Http::json(['data' => $db->create('users', $user['uid'], $document, $token)], 201);
            }
            if ($method === 'GET' && $part === ['me']) {
                Http::json(['data' => Auth::profile((string) $token)]);
            }
            if ($method === 'GET' && $part === ['jobs']) {
                Http::json(['data' => $db->query('jobs', 'status', 'OPEN', $token)]);
            }
            if ($method === 'GET' && $part === ['company', 'jobs']) {
                $profile = Auth::role((string) $token, 'COMPANY');
                Http::json(['data' => $db->query('jobs', 'ownerId', (string) $profile['uid'], $token)]);
            }
            if ($method === 'POST' && $part === ['jobs']) {
                $profile = Auth::role((string) $token, 'COMPANY');
                $body = Http::body();
                $document = [
                    'ownerId' => $profile['uid'],
                    'companyName' => $profile['companyName'],
                    'title' => Validator::text($body, 'title', 120, 5),
                    'location' => Validator::text($body, 'location', 120, 2),
                    'modality' => Validator::oneOf($body, 'modality', ['PRESENCIAL', 'HIBRIDO', 'REMOTO']),
                    'description' => Validator::text($body, 'description', 2500, 20),
                    'status' => 'OPEN',
                    'createdAt' => gmdate('c'),
                ];
                Http::json(['data' => $db->create('jobs', bin2hex(random_bytes(12)), $document, (string) $token)], 201);
            }
            if (count($part) === 2 && $part[0] === 'jobs' && $method === 'GET') {
                $job = $db->get('jobs', $part[1], $token);
                if (!$job) throw new ApiException('Vacante no encontrada.', 404);
                Http::json(['data' => $job]);
            }
            if (count($part) === 2 && $part[0] === 'jobs' && $method === 'PATCH') {
                $profile = Auth::role((string) $token, 'COMPANY');
                $job = $db->get('jobs', $part[1], $token);
                if (!$job || $job['ownerId'] !== $profile['uid']) throw new ApiException('Vacante no disponible.', 403);
                $body = Http::body();
                $changes = [];
                foreach (['title' => 120, 'location' => 120, 'description' => 2500] as $field => $max) {
                    if (array_key_exists($field, $body)) {
                        $changes[$field] = Validator::text($body, $field, $max, $field === 'description' ? 20 : 2);
                    }
                }
                if (isset($body['modality'])) $changes['modality'] = Validator::oneOf($body, 'modality', ['PRESENCIAL', 'HIBRIDO', 'REMOTO']);
                if (isset($body['status'])) $changes['status'] = Validator::oneOf($body, 'status', ['OPEN', 'CLOSED']);
                if (!$changes) throw new ApiException('No hay campos para actualizar.', 422);
                Http::json(['data' => $db->update('jobs', $part[1], $changes, (string) $token)]);
            }
            throw new ApiException('Ruta no encontrada.', 404);
        } catch (ApiException $e) {
            Http::json(['error' => $e->getMessage()], $e->httpStatus);
        } catch (\Throwable $e) {
            error_log('TalentHub API: ' . $e->getMessage());
            Http::json(['error' => 'Error interno del servidor.'], 500);
        }
    }
}
```

### Paso 02 — `apps/web/app/components/AppHeader.vue` · REEMPLAZAR EL ARCHIVO COMPLETO

Barra de navegación; cambia al añadir áreas privadas.

**Ubicación en VS Code:** `talenthub-gto/apps/web/app/components/AppHeader.vue`.

```vue
<template>
  <div class="border-b border-base-300 bg-base-100">
    <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
      <NuxtLink to="/" class="text-xl font-black tracking-tight text-primary">TalentHub <span class="text-base-content">GTO</span></NuxtLink>
      <div class="flex flex-wrap items-center gap-2 text-sm">
        <NuxtLink to="/" class="btn btn-ghost btn-sm">Vacantes</NuxtLink>
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
```

### Paso 03 — `apps/web/app/app.vue` · REEMPLAZAR EL ARCHIVO COMPLETO

Diseño raíz de Nuxt.

**Ubicación en VS Code:** `talenthub-gto/apps/web/app/app.vue`.

```vue
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
```

### Paso 04 — `apps/web/app/pages/register.vue` · CREAR ARCHIVO NUEVO

Formulario de registro de estudiante o empresa.

**Ubicación en VS Code:** `talenthub-gto/apps/web/app/pages/register.vue`.

```vue
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
```

### Paso 05 — `apps/web/app/pages/login.vue` · CREAR ARCHIVO NUEVO

Formulario para iniciar sesión.

**Ubicación en VS Code:** `talenthub-gto/apps/web/app/pages/login.vue`.

```vue
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
```

### Paso 06 — `apps/web/app/pages/company.vue` · CREAR ARCHIVO NUEVO

Panel y listado de vacantes propias de la empresa.

**Ubicación en VS Code:** `talenthub-gto/apps/web/app/pages/company.vue`.

```vue
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
```

### Paso 07 — `apps/web/app/pages/jobs/new.vue` · CREAR ARCHIVO NUEVO

Formulario de publicación de vacantes.

**Ubicación en VS Code:** `talenthub-gto/apps/web/app/pages/jobs/new.vue`.

```vue
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
```

## 5. Ejecutar pruebas de registro, roles y vacantes

**Reinicia Nuxt si editaste `apps/web/.env`.** Inicia ambos servidores desde la raíz:

```bash
npm run check:php
npm run test:api
npm run dev:api
```

En otra terminal:

```bash
npm run dev:web
```

**Caso A — empresa:**

1. Abre `http://localhost:3000/register` y crea un usuario de pruebas con rol `COMPANY`.
2. Entra a `http://localhost:3000/company` y registra una vacante desde `/jobs/new`.
3. Verifica que la vacante aparece en `/` y en el panel de empresa.

**Caso B — estudiante:**

1. Cierra sesión; registra otro correo de pruebas con rol `STUDENT`.
2. Comprueba que puede leer vacantes, pero NO crear vacantes.
3. Comprueba que una llamada sin token válido a `/api/v1/me` devuelve HTTP 401.

**No se crea ninguna cuenta ADMIN desde el registro público.** Las reglas Firestore también limitan a `STUDENT` y `COMPANY`.

```bash
git add .
git commit -m "feat: sesion 03 usuarios y vacantes"
git switch develop
git merge --no-ff feature/sesion-03
```

**Evidencia:** registro como empresa, empresa publica, estudiante visualiza, protección por roles.

## 6. REFERENCIA: ¿dónde están los `package.json`? (NO MODIFICAR EN ESTA SESIÓN)

Estos dos archivos fueron creados en la **sesión 01** y permanecen en el repositorio. Se muestran completos aquí **solo para comprobar o recuperar sus contenidos**. No crees un tercer `package.json` dentro de `apps/api`, porque PHP no usa npm.

**Ruta 1: `talenthub-gto/package.json` (raíz)**

```json
{
  "name": "talenthub-gto-monorepo",
  "version": "1.0.0",
  "private": true,
  "workspaces": [
    "apps/web"
  ],
  "scripts": {
    "check:php": "php apps/api/tests/requirements.php",
    "dev:web": "npm run dev -w @talenthub/web",
    "build:web": "npm run generate -w @talenthub/web",
    "typecheck": "npm run typecheck -w @talenthub/web",
    "dev:api": "php -S 127.0.0.1:8000 -t apps/api/public apps/api/router.php",
    "test:api": "php apps/api/tests/smoke.php"
  },
  "engines": {
    "node": ">=24.11.0",
    "npm": ">=10.0.0"
  }
}
```

**Ruta 2: `talenthub-gto/apps/web/package.json` (frontend)**

```json
{
  "name": "@talenthub/web",
  "version": "1.0.0",
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "nuxt dev --host 0.0.0.0 --port 3000",
    "generate": "nuxt generate",
    "typecheck": "nuxt typecheck",
    "postinstall": "nuxt prepare"
  },
  "dependencies": {
    "nuxt": "^4.0.0",
    "vue": "^3.5.0",
    "firebase": "^12.0.0",
    "pinia": "^3.0.0",
    "@pinia/nuxt": "^0.11.0",
    "tailwindcss": "^4.0.0",
    "@tailwindcss/vite": "^4.0.0",
    "daisyui": "^5.0.0"
  },
  "devDependencies": {
    "typescript": "^5.8.0",
    "vue-tsc": "^3.1.0"
  },
  "engines": {
    "node": ">=24.11.0",
    "npm": ">=10.0.0"
  }
}
```

`npm install` se ejecuta **en la raíz**; no tienes que ejecutar `npm install` en `apps/web` ni instalar los paquetes uno por uno.
