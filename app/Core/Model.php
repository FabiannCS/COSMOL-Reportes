<?php

namespace App\Core;

use PDO;

abstract class Model
{
    public function __construct()
    {
    }

    /**
     * Retorna la instancia de conexión PDO compartida
     *
     * @return PDO
     */
    protected function db()
    {
        return Database::getInstance();
    }
}
