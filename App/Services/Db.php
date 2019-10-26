<?php

namespace App\Services;

use PDO;
use PDOException;

class Db
{
    private $pdo;
    private static $instance;

    private function __construct()
    {
        try {
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $dbOptions = (require __DIR__ . '/../settings.php')['db'];

            $this->pdo = new PDO(
                'mysql:host=' . $dbOptions['host'] . ';dbname=' . $dbOptions['dbname'],
                $dbOptions['user'],
                $dbOptions['password'],
                $opt
            );
            $this->pdo->exec('SET NAMES UTF8');
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function query(string $sql, array $params = [], string $className = 'stdClass'): ?array
    {
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute($params);

        if (false === $result) {
            return null;
        }

        return $sth->fetchAll(PDO::FETCH_CLASS, $className);
    }
}