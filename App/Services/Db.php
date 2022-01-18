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

    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $className
     * @return array|false|null
     */
    public function query($sql, $params = [], $className = 'stdClass')
    {
        try {
            $sth = $this->pdo->prepare($sql);
            $result = $sth->execute($params);

            if (false === $result) {
                return null;
            }
            if (preg_match('/^(DELETE)(.+?)$/u',$sql)) {
                return $sth->rowCount();
            }
            $sth->setFetchMode(PDO::FETCH_CLASS, $className);
            return $sth->fetchAll();
        } catch (PDOException $PDOException) {
            if ($PDOException->getCode() !== "HY000")
                echo $PDOException->getCode();
        }
        return null;
    }
}