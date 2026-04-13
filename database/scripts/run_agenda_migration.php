<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Core/AutoConfig.php';
App\Core\AutoConfig::init();
$db = App\Core\Database::getInstance();
$sql = file_get_contents(__DIR__ . '/../migrations/032_create_agenda_contacts_table.sql');
$queries = array_filter(array_map('trim', explode(';', $sql)), function ($q) {
    return !empty($q) && !preg_match('/^--/', $q);
});
foreach ($queries as $q) {
    if (trim($q)) $db->query($q);
}
echo "OK - Tabela agenda_contacts criada.\n";
