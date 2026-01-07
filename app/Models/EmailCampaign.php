<?php

namespace App\Models;

use App\Core\Database;

class EmailCampaign
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO email_campaigns (name, subject, body, signature, status, scheduled_at, created_by_user_id, created_by_representative_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['name'],
            $data['subject'],
            $data['body'],
            $data['signature'] ?? null,
            $data['status'] ?? 'DRAFT',
            $data['scheduled_at'] ?? null,
            $data['created_by_user_id'] ?? null,
            $data['created_by_representative_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data)
    {
        $sql = "UPDATE email_campaigns SET 
                name = ?, subject = ?, body = ?, signature = ?, status = ?, scheduled_at = ?, updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['subject'],
            $data['body'],
            $data['signature'] ?? null,
            $data['status'] ?? 'DRAFT',
            $data['scheduled_at'] ?? null,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function findById($id)
    {
        $sql = "SELECT c.*, 
                       u.name as created_by_user_name,
                       r.nome_completo as created_by_representative_name
                FROM email_campaigns c
                LEFT JOIN users u ON c.created_by_user_id = u.id
                LEFT JOIN representatives r ON c.created_by_representative_id = r.id
                WHERE c.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAll($filters = [])
    {
        $sql = "SELECT c.*, 
                       u.name as created_by_user_name,
                       r.nome_completo as created_by_representative_name
                FROM email_campaigns c
                LEFT JOIN users u ON c.created_by_user_id = u.id
                LEFT JOIN representatives r ON c.created_by_representative_id = r.id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR c.subject LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE email_campaigns SET status = ?, updated_at = NOW()";
        
        if ($status === 'SENDING' && !isset($data['started_at'])) {
            $sql .= ", started_at = NOW()";
        } elseif ($status === 'COMPLETED') {
            $sql .= ", completed_at = NOW()";
        }
        
        $sql .= " WHERE id = ?";
        
        return $this->db->query($sql, [$status, $id]);
    }
    
    public function updateCounts($id, $sent = 0, $failed = 0)
    {
        $sql = "UPDATE email_campaigns SET 
                sent_count = sent_count + ?, 
                failed_count = failed_count + ?,
                updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->query($sql, [$sent, $failed, $id]);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM email_campaigns WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function addAttachment($campaignId, $filePath, $fileName, $fileSize, $fileType)
    {
        $sql = "INSERT INTO email_campaign_attachments (campaign_id, file_path, file_name, file_size, file_type) 
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [$campaignId, $filePath, $fileName, $fileSize, $fileType]);
    }
    
    public function getAttachments($campaignId)
    {
        $sql = "SELECT * FROM email_campaign_attachments WHERE campaign_id = ? ORDER BY created_at ASC";
        return $this->db->fetchAll($sql, [$campaignId]);
    }
    
    public function deleteAttachment($attachmentId)
    {
        $sql = "SELECT file_path FROM email_campaign_attachments WHERE id = ?";
        $attachment = $this->db->fetch($sql, [$attachmentId]);
        
        if ($attachment && file_exists($attachment['file_path'])) {
            @unlink($attachment['file_path']);
        }
        
        $sql = "DELETE FROM email_campaign_attachments WHERE id = ?";
        return $this->db->query($sql, [$attachmentId]);
    }
    
    public function addRecipient($campaignId, $recipientType, $email, $name = null, $recipientId = null)
    {
        $sql = "INSERT INTO email_campaign_recipients (campaign_id, recipient_type, recipient_id, email, name) 
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [$campaignId, $recipientType, $recipientId, $email, $name]);
    }
    
    public function addRecipientsBatch($campaignId, $recipients)
    {
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO email_campaign_recipients (campaign_id, recipient_type, recipient_id, email, name) 
                    VALUES (?, ?, ?, ?, ?)";
            
            foreach ($recipients as $recipient) {
                $this->db->query($sql, [
                    $campaignId,
                    $recipient['type'],
                    $recipient['id'] ?? null,
                    $recipient['email'],
                    $recipient['name'] ?? null
                ]);
            }
            
            // Atualizar total de destinatários
            $countSql = "SELECT COUNT(*) as total FROM email_campaign_recipients WHERE campaign_id = ?";
            $result = $this->db->fetch($countSql, [$campaignId]);
            $total = $result['total'] ?? 0;
            
            $updateSql = "UPDATE email_campaigns SET total_recipients = ? WHERE id = ?";
            $this->db->query($updateSql, [$total, $campaignId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getRecipients($campaignId)
    {
        $sql = "SELECT * FROM email_campaign_recipients WHERE campaign_id = ? ORDER BY created_at ASC";
        return $this->db->fetchAll($sql, [$campaignId]);
    }
    
    public function getRecipientsByStatus($campaignId, $status)
    {
        $sql = "SELECT * FROM email_campaign_recipients 
                WHERE campaign_id = ? AND status = ? 
                ORDER BY created_at ASC";
        return $this->db->fetchAll($sql, [$campaignId, $status]);
    }
}

