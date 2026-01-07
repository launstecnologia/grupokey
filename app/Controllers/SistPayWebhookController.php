<?php

namespace App\Controllers;

use App\Models\Establishment;

class SistPayWebhookController
{
    private $establishmentModel;
    
    public function __construct()
    {
        $this->establishmentModel = new Establishment();
    }
    
    /**
     * Recebe webhook do SistPay para atualizar status de estabelecimentos
     * 
     * Endpoint: POST /sistpay/webhook
     */
    public function handle()
    {
        // Log da requisição recebida
        $rawInput = file_get_contents('php://input');
        write_log('Webhook SistPay recebido: ' . $rawInput, 'sistpay-webhook.log');
        
        // Verificar se é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Decodificar JSON
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            write_log('Erro ao decodificar JSON do webhook: ' . json_last_error_msg(), 'sistpay-webhook.log');
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }
        
        // Validar dados obrigatórios
        if (!isset($data['event']) || !isset($data['establishment_id'])) {
            write_log('Webhook sem campos obrigatórios: ' . json_encode($data), 'sistpay-webhook.log');
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: event, establishment_id']);
            return;
        }
        
        $event = $data['event'];
        $sistpayEstablishmentId = $data['establishment_id'];
        
        write_log("Processando evento: {$event}, SistPay ID: {$sistpayEstablishmentId}", 'sistpay-webhook.log');
        
        try {
            // Processar diferentes tipos de eventos
            switch ($event) {
                case 'establishment.status_changed':
                    $this->handleStatusChanged($data);
                    break;
                    
                case 'establishment.created':
                    // Estabelecimento criado - apenas logar
                    write_log('Estabelecimento criado no SistPay: ' . json_encode($data), 'sistpay-webhook.log');
                    break;
                    
                default:
                    write_log("Evento desconhecido: {$event}", 'sistpay-webhook.log');
                    http_response_code(400);
                    echo json_encode(['error' => 'Unknown event type']);
                    return;
            }
            
            // Resposta de sucesso
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);
            
        } catch (\Exception $e) {
            write_log('Erro ao processar webhook: ' . $e->getMessage(), 'sistpay-webhook.log');
            write_log('Stack trace: ' . $e->getTraceAsString(), 'sistpay-webhook.log');
            
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Processa evento de mudança de status
     */
    private function handleStatusChanged($data)
    {
        $sistpayEstablishmentId = $data['establishment_id'];
        $newStatus = $data['new_status'] ?? null;
        $oldStatus = $data['old_status'] ?? null;
        
        if ($newStatus === null) {
            throw new \Exception('new_status não informado no webhook');
        }
        
        write_log("Status alterado: {$oldStatus} -> {$newStatus} (SistPay ID: {$sistpayEstablishmentId})", 'sistpay-webhook.log');
        
        // Buscar estabelecimento pelo sistpay_id
        $establishment = $this->establishmentModel->findBySistPayId($sistpayEstablishmentId);
        
        if (!$establishment) {
            // Tentar buscar pelo código (EST-{id}) se fornecido
            if (isset($data['code'])) {
                $establishment = $this->establishmentModel->findByCode($data['code']);
            }
            
            if (!$establishment) {
                write_log("Estabelecimento não encontrado para SistPay ID: {$sistpayEstablishmentId}", 'sistpay-webhook.log');
                throw new \Exception("Estabelecimento não encontrado para SistPay ID: {$sistpayEstablishmentId}");
            }
        }
        
        $establishmentId = $establishment['id'];
        
        // Atualizar status no banco de dados
        $result = $this->establishmentModel->updateStatusFromWebhook($establishmentId, $newStatus);
        
        if ($result) {
            write_log("Status atualizado com sucesso para estabelecimento ID: {$establishmentId} (SistPay ID: {$sistpayEstablishmentId})", 'sistpay-webhook.log');
        } else {
            throw new \Exception("Falha ao atualizar status do estabelecimento ID: {$establishmentId}");
        }
    }
}

