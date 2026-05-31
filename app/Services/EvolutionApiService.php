<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EvolutionApiService
 *
 * Cliente PHP para Evolution API V2.
 * 
 * Una sola instalación de Evolution API maneja múltiples "instancias"
 * (1 por tenant = 1 número de WhatsApp conectado vía QR).
 * 
 * Configuración global desde .env:
 *   EVOLUTION_API_BASE_URL  → https://evolutionapi.nexacore.com.mx
 *   EVOLUTION_API_KEY       → api key maestra
 * 
 * La "instance" es lo único que varía por tenant y se guarda en
 * tenants.configuracion.evolution_api.instance
 */
class EvolutionApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('EVOLUTION_API_BASE_URL', ''), '/');
        $this->apiKey = env('EVOLUTION_API_KEY', '');
    }

    /**
     * Verificar que el servicio está configurado.
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey);
    }

    /**
     * Verificar conectividad con Evolution API (health check).
     */
    public function ping(): array
    {
        return $this->get('/');
    }

    // ==================== GESTIÓN DE INSTANCIAS ====================

    /**
     * Crear una nueva instancia de WhatsApp para un tenant.
     */
    public function createInstance(string $instanceName): array
    {
        return $this->post('/instance/create', [
            'instanceName' => $instanceName,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
        ]);
    }

    /**
     * Listar todas las instancias existentes.
     */
    public function listInstances(): array
    {
        return $this->get('/instance/fetchInstances');
    }

    /**
     * Conectar instancia = obtener QR para escanear.
     */
    public function connectInstance(string $instance): array
    {
        return $this->post('/instance/connect', [
            'instanceName' => $instance,
        ]);
    }

    /**
     * Reconectar instancia desconectada (genera nuevo QR).
     */
    public function reconnectInstance(string $instanceName): array
    {
        return $this->post('/instance/connect', [
            'instanceName' => $instanceName,
        ]);
    }

    /**
     * Crear instancia con generacion explicita de QR (query param).
     */
    public function createInstanceWithQr(string $instanceName): array
    {
        return $this->post('/instance/create?qrcode=true', [
            'instanceName' => $instanceName,
        ]);
    }

    /**
     * Obtener QR code de una instancia (endpoint alternativo).
     */
    public function getQrCode(string $instance): array
    {
        return $this->get("/instance/qrcode/{$instance}");
    }

    /**
     * Obtener estado de conexión de una instancia.
     * Retorna: { state: "open"|"connecting"|"close", ... }
     */
    public function getConnectionState(string $instance): array
    {
        return $this->get("/instance/connectionState/{$instance}");
    }

    /**
     * Cerrar sesión de WhatsApp (logout) de una instancia.
     */
    public function logout(string $instance): array
    {
        return $this->delete("/instance/logout/{$instance}");
    }

    /**
     * Eliminar una instancia permanentemente.
     */
    public function deleteInstance(string $instance): array
    {
        return $this->delete("/instance/delete/{$instance}");
    }

    /**
     * Enviar mensaje de texto vía la instancia del tenant.
     * 
     * @param string $instance  Nombre de la instancia (ej. "tenant_3")
     * @param string $number    Número destino o grupo (ej. "528991610219" o "521234567890@g.us")
     * @param string $text      Mensaje de texto (soporta markdown de WhatsApp: *negrita*, _cursiva_, etc.)
     */
    public function sendText(string $instance, string $number, string $text): array
    {
        return $this->post("/message/sendText/{$instance}", [
            'number' => $number,
            'text' => $text,
            'delay' => 1200,
        ]);
    }

    // ==================== GRUPOS ====================

    /**
     * Obtener todos los grupos de WhatsApp de una instancia.
     * 
     * @return array [{ id, subject, participants }]
     */
    public function fetchGroups(string $instance, bool $withParticipants = true): array
    {
        $query = $withParticipants ? '?getParticipants=true' : '';
        return $this->get("/group/fetchAll/{$instance}{$query}");
    }

    /**
     * Obtener todos los contactos de una instancia.
     * 
     * @return array [{ id, pushName, profilePictureUrl }]
     */
    public function fetchContacts(string $instance): array
    {
        return $this->post("/chat/findContacts/{$instance}", [
            'where' => new \stdClass(),
        ]);
    }

    /**
     * Obtener contactos directamente del chat list.
     */
    public function fetchChats(string $instance): array
    {
        return $this->post("/chat/findChats/{$instance}", [
            'where' => new \stdClass(),
        ]);
    }

    // ==================== HELPERS HTTP ====================

    protected function get(string $endpoint): array
    {
        return $this->request('get', $endpoint);
    }

    protected function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, $data);
    }

    protected function delete(string $endpoint): array
    {
        return $this->request('delete', $endpoint);
    }

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = "{$this->baseUrl}{$endpoint}";

        try {
            $http = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15);

            /** @var Response $response */
            $response = match ($method) {
                'get' => $http->get($url),
                'post' => $http->post($url, $data),
                'delete' => $http->delete($url, $data),
                default => throw new Exception("Método HTTP no soportado: {$method}"),
            };

            Log::info("[EvolutionAPI] {$method} {$endpoint}", [
                'url' => $url,
                'status' => $response->status(),
                'request_body' => $data,
                'response_body' => $response->json(),
            ]);

            $body = $response->json() ?? [];

            if (!$response->successful()) {
                Log::warning("[EvolutionAPI] Error en {$method} {$endpoint}", [
                    'status' => $response->status(),
                    'body' => $body,
                ]);
            }

            return array_merge(['_status' => $response->status()], $body);

        } catch (Exception $e) {
            Log::error("[EvolutionAPI] Excepción en {$method} {$endpoint}", [
                'error' => $e->getMessage(),
            ]);

            return [
                '_status' => 0,
                '_error' => $e->getMessage(),
            ];
        }
    }
}
