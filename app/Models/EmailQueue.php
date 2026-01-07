<?php

namespace App\Models;

use App\Core\Database;

class EmailQueue
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Adiciona e-mails à fila de disparo
     */
    public function addToQueue($campaignId, $recipientIds, $scheduledAt = null)
    {
        if (empty($recipientIds)) {
            return 0;
        }
        
        $scheduledAt = $scheduledAt ?: date('Y-m-d H:i:s');
        $count = 0;
        
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO email_queue (campaign_id, recipient_id, scheduled_at, priority) 
                    VALUES (?, ?, ?, ?)";
            
            foreach ($recipientIds as $recipientId) {
                $this->db->query($sql, [$campaignId, $recipientId, $scheduledAt, 0]);
                $count++;
            }
            
            $this->db->commit();
            return $count;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Processa a fila de e-mails com controle de taxa (rate limiting)
     * 
     * @param int $batchSize Número de e-mails a processar por vez
     * @param int $delaySeconds Delay entre cada envio (em segundos)
     * @return array Estatísticas do processamento
     */
    public function processQueue($batchSize = 10, $delaySeconds = 2)
    {
        $stats = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0
        ];
        
        // Buscar itens pendentes
        $items = $this->getPendingItems($batchSize);
        
        if (empty($items)) {
            return $stats;
        }
        
        foreach ($items as $item) {
            try {
                // Marcar como processando
                $this->updateStatus($item['id'], 'PROCESSING');
                
                // Processar envio (será feito pelo EmailMarketingController)
                $stats['processed']++;
                
                // Delay entre envios para não sobrecarregar o servidor
                if ($delaySeconds > 0 && $stats['processed'] < count($items)) {
                    sleep($delaySeconds);
                }
                
            } catch (\Exception $e) {
                write_log('Erro ao processar item da fila: ' . $e->getMessage(), 'email-queue.log');
                $this->markAsFailed($item['id'], $e->getMessage());
                $stats['failed']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Busca itens pendentes da fila
     */
    public function getPendingItems($limit = 10)
    {
        $sql = "SELECT q.*, r.email, r.name, r.recipient_type, c.subject, c.body, c.signature
                FROM email_queue q
                INNER JOIN email_campaign_recipients r ON q.recipient_id = r.id
                INNER JOIN email_campaigns c ON q.campaign_id = c.id
                WHERE q.status = 'PENDING' 
                AND q.scheduled_at <= NOW()
                AND c.status IN ('SCHEDULED', 'SENDING')
                ORDER BY q.priority DESC, q.scheduled_at ASC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Atualiza status de um item da fila
     */
    public function updateStatus($queueId, $status, $errorMessage = null)
    {
        $sql = "UPDATE email_queue SET status = ?, processed_at = NOW()";
        
        if ($errorMessage) {
            $sql .= ", error_message = ?";
            $params = [$status, $errorMessage, $queueId];
        } else {
            $params = [$status, $queueId];
        }
        
        $sql .= " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Marca item como enviado
     */
    public function markAsSent($queueId)
    {
        return $this->updateStatus($queueId, 'SENT');
    }
    
    /**
     * Marca item como falhou
     */
    public function markAsFailed($queueId, $errorMessage = null)
    {
        $item = $this->getById($queueId);
        
        if ($item && $item['attempts'] < $item['max_attempts']) {
            // Reagendar para nova tentativa (exponencial backoff)
            $delay = pow(2, $item['attempts']) * 60; // 1min, 2min, 4min...
            $newScheduledAt = date('Y-m-d H:i:s', strtotime("+{$delay} seconds"));
            
            $sql = "UPDATE email_queue SET 
                    status = 'PENDING', 
                    attempts = attempts + 1,
                    scheduled_at = ?,
                    error_message = ?
                    WHERE id = ?";
            
            return $this->db->query($sql, [$newScheduledAt, $errorMessage, $queueId]);
        } else {
            // Esgotou tentativas, marcar como falhou definitivamente
            return $this->updateStatus($queueId, 'FAILED', $errorMessage);
        }
    }
    
    /**
     * Busca item da fila por ID
     */
    public function getById($queueId)
    {
        $sql = "SELECT * FROM email_queue WHERE id = ?";
        return $this->db->fetch($sql, [$queueId]);
    }
    
    /**
     * Retorna estatísticas da fila
     */
    public function getStats($campaignId = null)
    {
        $where = $campaignId ? "WHERE campaign_id = ?" : "";
        $params = $campaignId ? [$campaignId] : [];
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'SENT' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed
                FROM email_queue {$where}";
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Limpa itens antigos da fila (mais de 30 dias)
     */
    public function cleanOldItems($days = 30)
    {
        $sql = "DELETE FROM email_queue 
                WHERE status IN ('SENT', 'FAILED') 
                AND processed_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $this->db->query($sql, [$days]);
    }
}

