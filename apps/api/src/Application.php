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
?>