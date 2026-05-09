<?php

namespace App\Core;

class Permission
{
    public const ACTIONS = ['view', 'create', 'edit', 'move', 'change_status', 'delete'];

    public static function modules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'crm' => 'Pipeline/CRM',
            'estabelecimentos' => 'Estabelecimentos',
            'representantes' => 'Representantes',
            'usuarios' => 'Usuários',
            'chamados' => 'Chamados',
            'material' => 'Material de Apoio',
            'segmentos' => 'Segmentos',
            'produtos_dinamicos' => 'Produtos Dinâmicos',
            'campos_dinamicos' => 'Campos Dinâmicos',
            'billing' => 'Faturamento',
            'agenda' => 'Agenda',
            'whatsapp' => 'WhatsApp',
            'whatsapp_instances' => 'Instâncias WhatsApp',
            'email_marketing' => 'E-mail Marketing',
            'email_settings' => 'Configurações Email',
            'sistpay_settings' => 'API SistPay',
        ];
    }

    public static function actionLabels(): array
    {
        return [
            'view' => 'Ver',
            'create' => 'Criar',
            'edit' => 'Editar',
            'move' => 'Mover',
            'change_status' => 'Mudar Status',
            'delete' => 'Excluir',
        ];
    }

    public static function defaultMatrix(): array
    {
        $matrix = [];
        foreach (array_keys(self::modules()) as $module) {
            $matrix[$module] = self::defaultModulePermissions();
        }
        return $matrix;
    }

    public static function defaultModulePermissions(): array
    {
        return [
            'view' => false,
            'create' => false,
            'edit' => false,
            'move' => false,
            'change_status' => false,
            'delete' => false,
        ];
    }

    public static function normalizePostedPermissions(array $posted): array
    {
        $matrix = self::defaultMatrix();

        foreach ($matrix as $module => $actions) {
            foreach (self::ACTIONS as $action) {
                $matrix[$module][$action] = !empty($posted[$module][$action]);
            }
        }

        return $matrix;
    }

    public static function loadByUserId(int $userId): array
    {
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT module_key, can_view, can_create, can_edit, can_move, can_change_status, can_delete
                 FROM user_module_permissions
                 WHERE user_id = ?",
                [$userId]
            );

            if (empty($rows)) {
                return [];
            }

            $permissions = [];
            foreach ($rows as $row) {
                $permissions[$row['module_key']] = [
                    'view' => (bool) $row['can_view'],
                    'create' => (bool) $row['can_create'],
                    'edit' => (bool) $row['can_edit'],
                    'move' => (bool) $row['can_move'],
                    'change_status' => (bool) $row['can_change_status'],
                    'delete' => (bool) $row['can_delete'],
                ];
            }

            return $permissions;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function saveByUserId(int $userId, array $matrix): void
    {
        $db = Database::getInstance();

        $db->query("DELETE FROM user_module_permissions WHERE user_id = ?", [$userId]);

        $sql = "INSERT INTO user_module_permissions
            (user_id, module_key, can_view, can_create, can_edit, can_move, can_change_status, can_delete, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        foreach ($matrix as $module => $actions) {
            $db->query($sql, [
                $userId,
                $module,
                !empty($actions['view']) ? 1 : 0,
                !empty($actions['create']) ? 1 : 0,
                !empty($actions['edit']) ? 1 : 0,
                !empty($actions['move']) ? 1 : 0,
                !empty($actions['change_status']) ? 1 : 0,
                !empty($actions['delete']) ? 1 : 0,
            ]);
        }
    }

    public static function inferFromRequest(string $path, string $method): ?array
    {
        $method = strtoupper($method);

        $rules = [
            ['#^dashboard$#', 'dashboard', 'view'],
            ['#^crm$#', 'crm', 'view'],
            ['#^crm/pipelines#', 'crm', self::actionByMethod($method, 'edit')],
            ['#^crm/move-deal$#', 'crm', 'move'],
            ['#^crm/update-deal-order$#', 'crm', 'move'],
            ['#^crm/deals/create$#', 'crm', 'create'],
            ['#^crm/deals$#', 'crm', $method === 'POST' ? 'create' : 'view'],
            ['#^crm/deals/\d+/edit$#', 'crm', 'edit'],
            ['#^crm/deals/\d+$#', 'crm', self::actionByMethod($method, 'view')],

            ['#^estabelecimentos$#', 'estabelecimentos', self::actionByMethod($method, 'view')],
            ['#^estabelecimentos/create$#', 'estabelecimentos', 'create'],
            ['#^estabelecimentos/import$#', 'estabelecimentos', self::actionByMethod($method, 'create')],
            ['#^estabelecimentos/\d+/edit$#', 'estabelecimentos', 'edit'],
            ['#^estabelecimentos/\d+/(approve|reprove|migrate-sistpay)$#', 'estabelecimentos', 'change_status'],
            ['#^estabelecimentos/\d+$#', 'estabelecimentos', self::actionByMethod($method, 'view')],

            ['#^representantes$#', 'representantes', self::actionByMethod($method, 'view')],
            ['#^representantes/create$#', 'representantes', 'create'],
            ['#^representantes/\d+/edit$#', 'representantes', 'edit'],
            ['#^representantes/\d+/(toggle-status|reset-password)$#', 'representantes', 'change_status'],
            ['#^representantes/\d+$#', 'representantes', self::actionByMethod($method, 'view')],

            ['#^usuarios$#', 'usuarios', self::actionByMethod($method, 'view')],
            ['#^usuarios/create$#', 'usuarios', 'create'],
            ['#^usuarios/\d+/edit$#', 'usuarios', 'edit'],
            ['#^usuarios/\d+/(toggle-status|reset-password)$#', 'usuarios', 'change_status'],
            ['#^usuarios/\d+$#', 'usuarios', self::actionByMethod($method, 'view')],

            ['#^chamados$#', 'chamados', self::actionByMethod($method, 'view')],
            ['#^chamados/create$#', 'chamados', 'create'],
            ['#^chamados/\d+/(edit|responder)$#', 'chamados', 'edit'],
            ['#^chamados/\d+/fechar$#', 'chamados', 'change_status'],
            ['#^chamados/\d+$#', 'chamados', self::actionByMethod($method, 'view')],

            ['#^material#', 'material', self::actionByMethod($method, 'view')],
            ['#^segmentos#', 'segmentos', self::actionByMethod($method, 'view')],
            ['#^produtos-dinamicos#', 'produtos_dinamicos', self::actionByMethod($method, 'view')],
            ['#^campos-dinamicos#', 'campos_dinamicos', self::actionByMethod($method, 'view')],
            ['#^billing#', 'billing', self::actionByMethod($method, 'view')],
            ['#^agenda#', 'agenda', self::actionByMethod($method, 'view')],
            ['#^whatsapp/attendance#', 'whatsapp', self::actionByMethod($method, 'view')],
            ['#^whatsapp/instances#', 'whatsapp_instances', self::actionByMethod($method, 'view')],
            ['#^email-marketing#', 'email_marketing', self::actionByMethod($method, 'view')],
            ['#^email-settings#', 'email_settings', self::actionByMethod($method, 'view')],
            ['#^sistpay-settings#', 'sistpay_settings', self::actionByMethod($method, 'view')],
        ];

        foreach ($rules as [$regex, $module, $action]) {
            if (preg_match($regex, $path)) {
                return ['module' => $module, 'action' => $action];
            }
        }

        return null;
    }

    private static function actionByMethod(string $method, string $default = 'view'): string
    {
        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'edit',
            'DELETE' => 'delete',
            default => $default,
        };
    }
}
