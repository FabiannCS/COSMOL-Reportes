<?php

namespace App\Core;

use PDO;

abstract class Model
{
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
