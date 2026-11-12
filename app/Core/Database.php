<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    /** @var PDO|null */
    private static $instance = null;

    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct()
    {
    }

    /**
     * Retorna la instancia única de conexión PDO
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            $configPath = defined('BASE_PATH') ? BASE_PATH . '/app/Config/database.php' : __DIR__ . '/../Config/database.php';
            $config = file_exists($configPath) ? require $configPath : [];

            $host     = isset($config['host'])     ? $config['host']     : (getenv('DB_HOST')     ?: 'db');
            $port     = isset($config['port'])     ? $config['port']     : (getenv('DB_PORT')     ?: '5432');
            $dbname   = isset($config['dbname'])   ? $config['dbname']   : (getenv('DB_NAME')     ?: 'cosmol_reportes');
            $user     = isset($config['user'])     ? $config['user']     : (getenv('DB_USER')     ?: 'cosmol_user');
            $password = isset($config['password']) ? $config['password'] : (getenv('DB_PASSWORD') ?: 'cosmol_password');

            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instance = new PDO($dsn, $user, $password, $options);
        }

        return self::$instance;
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar una instancia de Database (Singleton).");
    }
}
