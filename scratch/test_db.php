<?php
require_once __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query('SELECT current_database(), current_user');
print_r($stmt->fetch(PDO::FETCH_ASSOC));

$tables = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
