<?php

namespace App\Models;

interface Base
{
    /**
     * @return string
     */
    static function getTableName();
}
