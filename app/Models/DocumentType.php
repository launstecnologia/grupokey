<?php

namespace App\Models;

use App\Core\Database;

class DocumentType
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
        $this->ensureProductLinkTable();
        $this->ensureSeed();
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT * FROM document_types WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (label LIKE ? OR code LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND is_active = ?";
            $params[] = (int) $filters['is_active'];
        }

        $sql .= " ORDER BY sort_order ASC, label ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getActive()
    {
        return $this->getAll(['is_active' => 1]);
    }

    public function getProductLinksByTypeId(int $typeId): array
    {
        return $this->db->fetchAll(
            "SELECT product_key
             FROM document_type_products
             WHERE document_type_id = ?
             ORDER BY product_key ASC",
            [$typeId]
        );
    }

    public function getProductMapForActiveTypes(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT dtp.product_key, dt.code
             FROM document_type_products dtp
             INNER JOIN document_types dt ON dt.id = dtp.document_type_id
             WHERE dt.is_active = 1
             ORDER BY dt.sort_order ASC, dt.label ASC"
        );

        $map = [];
        foreach ($rows as $row) {
            $productKey = strtoupper(trim((string) ($row['product_key'] ?? '')));
            $code = strtoupper(trim((string) ($row['code'] ?? '')));
            if ($productKey === '' || $code === '') {
                continue;
            }
            if (!isset($map[$productKey])) {
                $map[$productKey] = [];
            }
            if (!in_array($code, $map[$productKey], true)) {
                $map[$productKey][] = $code;
            }
        }

        return $map;
    }

    public function findById($id)
    {
        return $this->db->fetch("SELECT * FROM document_types WHERE id = ? LIMIT 1", [(int) $id]);
    }

    public function findByCode($code)
    {
        return $this->db->fetch("SELECT * FROM document_types WHERE code = ? LIMIT 1", [strtoupper(trim((string) $code))]);
    }

    public function create(array $data)
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $label = trim((string) ($data['label'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $isActive = (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0;

        if ($code === '' || $label === '') {
            throw new \InvalidArgumentException('Código e nome são obrigatórios.');
        }

        if ($this->findByCode($code)) {
            throw new \RuntimeException('Já existe um tipo de documento com este código.');
        }

        $this->ensureDocumentsEnumValue($code);

        $this->db->query(
            "INSERT INTO document_types (code, label, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$code, $label, $sortOrder, $isActive]
        );

        $id = (int) $this->db->lastInsertId();
        $this->syncProductLinks($id, $data['product_keys'] ?? []);
        return $id;
    }

    public function update($id, array $data)
    {
        $id = (int) $id;
        $row = $this->findById($id);
        if (!$row) {
            throw new \RuntimeException('Tipo de documento não encontrado.');
        }

        $code = strtoupper(trim((string) ($data['code'] ?? $row['code'] ?? '')));
        $label = trim((string) ($data['label'] ?? $row['label'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? $row['sort_order'] ?? 0);
        $isActive = (int) ($data['is_active'] ?? $row['is_active'] ?? 1) === 1 ? 1 : 0;

        if ($code === '' || $label === '') {
            throw new \InvalidArgumentException('Código e nome são obrigatórios.');
        }

        $existing = $this->findByCode($code);
        if ($existing && (int) $existing['id'] !== $id) {
            throw new \RuntimeException('Já existe um tipo de documento com este código.');
        }

        $this->ensureDocumentsEnumValue($code);

        $oldCode = (string) ($row['code'] ?? '');
        if ($oldCode !== '' && $oldCode !== $code) {
            $this->db->query("UPDATE documents SET document_type = ? WHERE document_type = ?", [$code, $oldCode]);
        }

        $this->db->query(
            "UPDATE document_types
             SET code = ?, label = ?, sort_order = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?",
            [$code, $label, $sortOrder, $isActive, $id]
        );

        $this->syncProductLinks($id, $data['product_keys'] ?? []);

        return true;
    }

    public function delete($id)
    {
        $row = $this->findById($id);
        if (!$row) {
            throw new \RuntimeException('Tipo de documento não encontrado.');
        }

        $code = (string) ($row['code'] ?? '');
        if ($code === '') {
            throw new \RuntimeException('Código inválido.');
        }

        $links = $this->countDocumentsByCode($code);
        if ($links > 0) {
            throw new \RuntimeException('Não é possível excluir. Este tipo está vinculado a ' . $links . ' documento(s).');
        }

        $this->db->query("DELETE FROM document_type_products WHERE document_type_id = ?", [(int) $id]);
        $this->db->query("DELETE FROM document_types WHERE id = ?", [(int) $id]);
        return true;
    }

    public function countDocumentsByCode(string $code): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) AS total FROM documents WHERE document_type = ?",
            [strtoupper(trim($code))]
        );
        return (int) ($result['total'] ?? 0);
    }

    private function ensureTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS document_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(64) NOT NULL UNIQUE,
                label VARCHAR(255) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_document_types_active (is_active),
                INDEX idx_document_types_sort (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function ensureProductLinkTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS document_type_products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                document_type_id INT NOT NULL,
                product_key VARCHAR(64) NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_document_type_product (document_type_id, product_key),
                INDEX idx_document_type_products_key (product_key),
                CONSTRAINT fk_document_type_products_type
                    FOREIGN KEY (document_type_id)
                    REFERENCES document_types(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function syncProductLinks(int $typeId, array $productKeys): void
    {
        $normalized = [];
        foreach ($productKeys as $key) {
            $productKey = strtoupper(trim((string) $key));
            if ($productKey === '') {
                continue;
            }
            if (!preg_match('/^[A-Z0-9_]+$/', $productKey)) {
                continue;
            }
            if (!in_array($productKey, $normalized, true)) {
                $normalized[] = $productKey;
            }
        }

        $this->db->query("DELETE FROM document_type_products WHERE document_type_id = ?", [$typeId]);
        foreach ($normalized as $productKey) {
            $this->db->query(
                "INSERT INTO document_type_products (document_type_id, product_key, created_at)
                 VALUES (?, ?, NOW())",
                [$typeId, $productKey]
            );
        }
    }

    private function ensureSeed(): void
    {
        $countRow = $this->db->fetch("SELECT COUNT(*) AS total FROM document_types");
        $total = (int) ($countRow['total'] ?? 0);
        if ($total > 0) {
            return;
        }

        $seed = [
            ['CONTRATO_SOCIAL', 'CONTRATO SOCIAL / REQUERIMENTO DE EMPRESÁRIO / CCMEI'],
            ['DOCUMENTO_FOTO_FRENTE', 'DOCUMENTO SÓCIO TITULAR FRENTE'],
            ['DOCUMENTO_FOTO_VERSO', 'DOCUMENTO SÓCIO TITULAR VERSO'],
            ['COMPROVANTE_BANCARIO', 'COMPROVANTE BANCÁRIO (Constando banco/agência/conta/cnpj ou razão social)'],
            ['FOTO_FACHADA', 'FOTO FACHADA (Solicite uma foto boa)'],
            ['COMPROVANTE_ENDERECO', 'COMPROVANTE DE ENDEREÇO COMERCIAL (Da loja)'],
        ];

        $sort = 1;
        foreach ($seed as $item) {
            [$code, $label] = $item;
            $this->ensureDocumentsEnumValue($code);
            $this->db->query(
                "INSERT INTO document_types (code, label, sort_order, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, 1, NOW(), NOW())",
                [$code, $label, $sort++]
            );
        }
    }

    private function ensureDocumentsEnumValue(string $code): void
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return;
        }

        $column = $this->db->fetch("SHOW COLUMNS FROM documents WHERE Field = 'document_type'");
        if (!$column || empty($column['Type'])) {
            return;
        }

        $typeDef = (string) $column['Type'];
        if (!preg_match("/^enum\((.*)\)$/i", $typeDef, $matches)) {
            return;
        }

        $enumValues = [];
        foreach (explode(',', (string) ($matches[1] ?? '')) as $raw) {
            $value = trim($raw, "'\" ");
            if ($value !== '') {
                $enumValues[] = $value;
            }
        }

        if (in_array($code, $enumValues, true)) {
            return;
        }

        $enumValues[] = $code;
        $quoted = array_map(function ($v) {
            return "'" . str_replace("'", "\\'", $v) . "'";
        }, $enumValues);

        $default = $column['Default'] ?? null;
        $defaultSql = '';
        if (is_string($default) && $default !== '') {
            $defaultSql = " DEFAULT '" . str_replace("'", "\\'", $default) . "'";
        }

        $nullSql = strtoupper((string) ($column['Null'] ?? 'YES')) === 'NO' ? ' NOT NULL' : ' NULL';

        $sql = "ALTER TABLE documents MODIFY COLUMN document_type ENUM(" . implode(', ', $quoted) . ")" . $nullSql . $defaultSql;
        $this->db->query($sql);
    }
}
