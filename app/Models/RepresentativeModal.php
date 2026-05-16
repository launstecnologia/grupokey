<?php

namespace App\Models;

use App\Core\Database;

class RepresentativeModal
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT * FROM representative_modals WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR message LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = ?";
            $params[] = (int) $filters['is_active'];
        }

        $sql .= " ORDER BY id DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function findById(int $id)
    {
        return $this->db->fetch("SELECT * FROM representative_modals WHERE id = ? LIMIT 1", [$id]);
    }

    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO representative_modals
             (title, message, image_path, image_url, link_type, external_link, internal_target_type, internal_target_id,
              trigger_type, trigger_date, trigger_month_day, anniversary_years, milestone_establishments,
              audience_type, selected_representative_ids_json, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['title'] ?? null,
                $data['message'] ?? null,
                $data['image_path'] ?? null,
                $data['image_url'] ?? null,
                $data['link_type'] ?? 'none',
                $data['external_link'] ?? null,
                $data['internal_target_type'] ?? null,
                $data['internal_target_id'] ?? null,
                $data['trigger_type'] ?? 'custom_date',
                $data['trigger_date'] ?? null,
                $data['trigger_month_day'] ?? null,
                $data['anniversary_years'] ?? null,
                $data['milestone_establishments'] ?? null,
                $data['audience_type'] ?? 'all',
                $data['selected_representative_ids_json'] ?? null,
                (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query(
            "UPDATE representative_modals SET
             title = ?, message = ?, image_path = ?, image_url = ?, link_type = ?, external_link = ?,
             internal_target_type = ?, internal_target_id = ?, trigger_type = ?, trigger_date = ?, trigger_month_day = ?,
             anniversary_years = ?, milestone_establishments = ?, audience_type = ?, selected_representative_ids_json = ?,
             is_active = ?, updated_at = NOW()
             WHERE id = ?",
            [
                $data['title'] ?? null,
                $data['message'] ?? null,
                $data['image_path'] ?? null,
                $data['image_url'] ?? null,
                $data['link_type'] ?? 'none',
                $data['external_link'] ?? null,
                $data['internal_target_type'] ?? null,
                $data['internal_target_id'] ?? null,
                $data['trigger_type'] ?? 'custom_date',
                $data['trigger_date'] ?? null,
                $data['trigger_month_day'] ?? null,
                $data['anniversary_years'] ?? null,
                $data['milestone_establishments'] ?? null,
                $data['audience_type'] ?? 'all',
                $data['selected_representative_ids_json'] ?? null,
                (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
                $id,
            ]
        );
        return true;
    }

    public function delete(int $id): bool
    {
        $row = $this->findById($id);
        if ($row && !empty($row['image_path']) && file_exists($row['image_path'])) {
            @unlink($row['image_path']);
        }
        $this->db->query("DELETE FROM representative_modal_deliveries WHERE modal_id = ?", [$id]);
        $this->db->query("DELETE FROM representative_modals WHERE id = ?", [$id]);
        return true;
    }

    public function getPendingForRepresentative(int $representativeId)
    {
        $this->generateDeliveriesForRepresentative($representativeId);

        return $this->db->fetch(
            "SELECT d.id as delivery_id, d.event_key, d.scheduled_at, m.*
             FROM representative_modal_deliveries d
             INNER JOIN representative_modals m ON m.id = d.modal_id
             WHERE d.representative_id = ? AND d.status = 'PENDING' AND m.is_active = 1
             ORDER BY d.scheduled_at ASC, d.id ASC
             LIMIT 1",
            [$representativeId]
        );
    }

    public function acknowledgeDelivery(int $deliveryId, int $representativeId): bool
    {
        $this->db->query(
            "UPDATE representative_modal_deliveries
             SET status = 'VIEWED', viewed_at = NOW(), updated_at = NOW()
             WHERE id = ? AND representative_id = ?",
            [$deliveryId, $representativeId]
        );
        return true;
    }

    private function generateDeliveriesForRepresentative(int $representativeId): void
    {
        $representative = $this->db->fetch("SELECT id, created_at, birthday_date FROM representatives WHERE id = ? LIMIT 1", [$representativeId]);
        if (!$representative) {
            return;
        }

        $today = new \DateTimeImmutable(date('Y-m-d'));
        $year = (int) $today->format('Y');
        $establishmentCountRow = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM establishments WHERE created_by_representative_id = ?",
            [$representativeId]
        );
        $establishmentCount = (int) ($establishmentCountRow['total'] ?? 0);

        $campaigns = $this->db->fetchAll("SELECT * FROM representative_modals WHERE is_active = 1");
        foreach ($campaigns as $campaign) {
            if (!$this->isRepresentativeTargeted($campaign, $representativeId)) {
                continue;
            }

            $trigger = (string) ($campaign['trigger_type'] ?? 'custom_date');
            $eventKey = null;
            $shouldCreate = false;

            if ($trigger === 'custom_date') {
                $date = (string) ($campaign['trigger_date'] ?? '');
                if ($date !== '' && $today >= new \DateTimeImmutable($date)) {
                    $eventKey = 'DATE:' . $date;
                    $shouldCreate = true;
                }
            } elseif ($trigger === 'commemorative_date') {
                $md = (string) ($campaign['trigger_month_day'] ?? '');
                if (preg_match('/^\d{2}-\d{2}$/', $md)) {
                    $eventDate = \DateTimeImmutable::createFromFormat('Y-m-d', $year . '-' . $md);
                    if ($eventDate && $today >= $eventDate) {
                        $eventKey = 'COMMEM:' . $year . ':' . $md;
                        $shouldCreate = true;
                    }
                }
            } elseif ($trigger === 'birthday') {
                $birth = (string) ($representative['birthday_date'] ?? '');
                if ($birth !== '') {
                    $md = date('m-d', strtotime($birth));
                    $eventDate = \DateTimeImmutable::createFromFormat('Y-m-d', $year . '-' . $md);
                    if ($eventDate && $today >= $eventDate) {
                        $eventKey = 'BIRTHDAY:' . $year;
                        $shouldCreate = true;
                    }
                }
            } elseif ($trigger === 'platform_anniversary') {
                $years = (int) ($campaign['anniversary_years'] ?? 0);
                if ($years > 0 && !empty($representative['created_at'])) {
                    $base = new \DateTimeImmutable(date('Y-m-d', strtotime((string) $representative['created_at'])));
                    $eventDate = $base->modify('+' . $years . ' years');
                    if ($eventDate && $today >= $eventDate) {
                        $eventKey = 'PLATFORM:' . $years;
                        $shouldCreate = true;
                    }
                }
            } elseif ($trigger === 'establishment_milestone') {
                $milestone = (int) ($campaign['milestone_establishments'] ?? 0);
                if ($milestone > 0 && $establishmentCount >= $milestone) {
                    $eventKey = 'MILESTONE:' . $milestone;
                    $shouldCreate = true;
                }
            }

            if (!$shouldCreate || $eventKey === null) {
                continue;
            }

            $exists = $this->db->fetch(
                "SELECT id FROM representative_modal_deliveries
                 WHERE modal_id = ? AND representative_id = ? AND event_key = ?
                 LIMIT 1",
                [(int) $campaign['id'], $representativeId, $eventKey]
            );
            if ($exists) {
                continue;
            }

            $this->db->query(
                "INSERT INTO representative_modal_deliveries
                 (modal_id, representative_id, event_key, status, scheduled_at, created_at, updated_at)
                 VALUES (?, ?, ?, 'PENDING', NOW(), NOW(), NOW())",
                [(int) $campaign['id'], $representativeId, $eventKey]
            );
        }
    }

    private function isRepresentativeTargeted(array $campaign, int $representativeId): bool
    {
        $audienceType = (string) ($campaign['audience_type'] ?? 'all');
        if ($audienceType !== 'selected') {
            return true;
        }

        $json = (string) ($campaign['selected_representative_ids_json'] ?? '[]');
        $ids = json_decode($json, true);
        if (!is_array($ids)) {
            return false;
        }
        $ids = array_map('intval', $ids);
        return in_array($representativeId, $ids, true);
    }

    private function ensureSchema(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS representative_modals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NULL,
                message TEXT NOT NULL,
                image_path VARCHAR(500) NULL,
                image_url VARCHAR(1000) NULL,
                link_type ENUM('none','external','internal') NOT NULL DEFAULT 'none',
                external_link VARCHAR(1000) NULL,
                internal_target_type VARCHAR(50) NULL,
                internal_target_id VARCHAR(50) NULL,
                trigger_type ENUM('birthday','commemorative_date','platform_anniversary','establishment_milestone','custom_date') NOT NULL DEFAULT 'custom_date',
                trigger_date DATE NULL,
                trigger_month_day CHAR(5) NULL,
                anniversary_years INT NULL,
                milestone_establishments INT NULL,
                audience_type ENUM('all','selected') NOT NULL DEFAULT 'all',
                selected_representative_ids_json TEXT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_rep_modals_active (is_active),
                INDEX idx_rep_modals_trigger (trigger_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS representative_modal_deliveries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                modal_id INT NOT NULL,
                representative_id INT NOT NULL,
                event_key VARCHAR(120) NOT NULL,
                status ENUM('PENDING','VIEWED') NOT NULL DEFAULT 'PENDING',
                scheduled_at DATETIME NOT NULL,
                viewed_at DATETIME NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_rep_modal_delivery (modal_id, representative_id, event_key),
                INDEX idx_rep_modal_delivery_pending (representative_id, status),
                CONSTRAINT fk_rep_modal_delivery_modal FOREIGN KEY (modal_id) REFERENCES representative_modals(id) ON DELETE CASCADE,
                CONSTRAINT fk_rep_modal_delivery_rep FOREIGN KEY (representative_id) REFERENCES representatives(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $hasBirthdayColumn = $this->db->fetch("SHOW COLUMNS FROM representatives LIKE 'birthday_date'");
        if (!$hasBirthdayColumn) {
            $this->db->query("ALTER TABLE representatives ADD COLUMN birthday_date DATE NULL");
        }
    }
}
