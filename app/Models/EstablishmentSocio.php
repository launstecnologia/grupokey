<?php

namespace App\Models;

use App\Core\Database;

class EstablishmentSocio
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    public function getByEstablishment($establishmentId): array
    {
        return $this->db->fetchAll(
            "SELECT id, establishment_id, sort_order, nome, cpf, data_nascimento, rg
             FROM establishment_socios
             WHERE establishment_id = ?
             ORDER BY sort_order ASC, id ASC",
            [(int) $establishmentId]
        );
    }

    public function replaceForEstablishment($establishmentId, array $socios): void
    {
        $establishmentId = (int) $establishmentId;
        if ($establishmentId <= 0) {
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "DELETE FROM establishment_socios WHERE establishment_id = ?",
                [$establishmentId]
            );

            $order = 0;
            foreach ($socios as $socio) {
                if (!is_array($socio)) {
                    continue;
                }
                $nome = trim((string) ($socio['nome'] ?? ''));
                $cpf = preg_replace('/\D/', '', (string) ($socio['cpf'] ?? ''));
                $rg = trim((string) ($socio['rg'] ?? ''));
                $birth = trim((string) ($socio['data_nascimento'] ?? ''));
                if ($nome === '' && $cpf === '' && $rg === '' && $birth === '') {
                    continue;
                }

                $this->db->query(
                    "INSERT INTO establishment_socios
                     (establishment_id, sort_order, nome, cpf, data_nascimento, rg, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $establishmentId,
                        $order,
                        $nome !== '' ? $nome : null,
                        $cpf !== '' ? $cpf : null,
                        $birth !== '' ? $birth : null,
                        $rg !== '' ? $rg : null,
                    ]
                );
                $order++;
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function ensureTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS establishment_socios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                establishment_id INT NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                nome VARCHAR(255) NULL,
                cpf VARCHAR(20) NULL,
                data_nascimento DATE NULL,
                rg VARCHAR(40) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_establishment_socios_establishment (establishment_id, sort_order)
            )"
        );
    }
}
