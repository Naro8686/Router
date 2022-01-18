<?php


namespace App\Models;

use App\Services\Db;
use Exception;
use PDOException;
use ReflectionObject;

abstract class ActiveRecordEntity implements Base
{
    public static $exists = true;
    protected $primaryKey = 'id';
    protected static $where = '';
    private static $params = ['sql' => [], 'columns' => []];

    /**
     * @param array $args
     */
    public function __construct($args = [])
    {
        self::$exists = $this->exists();
        foreach ($args as $name => $value) {
            $camelCaseName = $this->underscoreToCamelCase($name);
            $this->$camelCaseName = $value;
        }
    }

    public static function where($params = [])
    {
        list($where_column, $where_symbol, $where_value) = array_pad($params, 3, false);
        if ($where_column) {
            if (count($params) === 2) {
                $where_value = $where_symbol;
                $where_symbol = '=';
            }
            $field = ":$where_column";
            if (is_null($where_value)) {
                $field = 'NULL';
                if (in_array($where_symbol, ['!=', '<>'])) $where_symbol = 'IS NOT';
                else $where_symbol = 'IS';
            }
        } else {
            $where_column = 1;
            $where_symbol = '=';
            $where_value = 1;
            $field = ':where';
        }
        if (!is_null($where_value)) self::$params['columns'][$field] = $where_value;
        self::$params['sql'][$field] = "$where_column $where_symbol $field";
        if (!empty(self::$params['sql'])) self::$where =
            'WHERE ' . implode(' AND ', self::$params['sql']);

        $className = get_called_class();
        return new $className();
    }

    /** @var int */
    protected $id;

    /**
     * @param string $name
     */
    public function __set($name, $value)
    {
        $camelCaseName = $this->underscoreToCamelCase($name);
        $this->$camelCaseName = $value;
    }

    /**
     * @return int
     */
    public function getId()
    {
        $id = $this->underscoreToCamelCase($this->primaryKey);
        return $this->$id;
    }

    /**
     * @param string $source
     * @return string
     */
    private function underscoreToCamelCase($source)
    {
        return lcfirst(str_replace("\n", "", ucwords(str_replace("_", "\n", $source))));
    }

//    /**
//     * @param int $perPage
//     * @param string $pageName
//     * @return \Pagination|null
//     */
//    public static function pagination($perPage = 10, $pageName = 'current_page')
//    {
//        try {
//            $className = get_called_class();
//            $model = new $className();
//            $tableName = $model->getTableName();
//            $where = $model::$where;
//            $primaryKey = $model->primaryKey;
//            $sql = "SELECT * FROM {$tableName}
//                    {$where}
//                    ORDER BY {$tableName}.{$primaryKey} DESC
//                    LIMIT :limit, :per_page";
//
//            $columns = self::$params['columns'];
//
//            foreach ($columns as $field => $where_value) {
//                $columns[$field] = is_string($where_value)
//                    ? '\'' . $where_value . '\''
//                    : is_null($where_value) ? 'NULL' : $where_value;
//            }
//            $pagination = new \Pagination($perPage, strtr($where, $columns), false, $tableName, true);
//
//            $pagination->setCurrentPageLink($_SERVER['REQUEST_URI'], $pageName);
//            $columns = self::$params['columns'];
//            $columns[':limit'] = $pagination->starting_limit;
//            $columns[':per_page'] = $pagination->per_page;
//            $instance = Db::getInstance();
//            $items = $instance->query($sql, $columns, $className);
//            $pagination->setItems($items);
//            return $pagination;
//        } catch (\PDOException $pdoException) {
//            echo $pdoException->getMessage();
//        } catch (\Exception $exception) {
//            echo $exception->getMessage();
//        }
//        return null;
//    }

    /**
     * @return static[]
     */
    public static function findAll()
    {
        $db = Db::getInstance();
        return $db->query('SELECT * FROM ' . static::getTableName() . ';', [], get_called_class());
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function findOrNew($id)
    {
        $className = get_called_class();
        $model = new $className();
        $getById = self::getById($id);
        $model = $getById ?: (new $className([$model->primaryKey => $id]));
        self::$exists = !is_null($getById);
        return $model;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        $exists = false;
        try {
            $primaryKey = $this->primaryKey;
            $db = Db::getInstance();
            $query = $db->getPdo()->prepare("SELECT $primaryKey FROM " . static::getTableName() . " WHERE $primaryKey = ?;");
            $query->execute([$this->getId()]);
            $exists = $query->rowCount() > 0;
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
        return $exists;
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function getById($id)
    {
        $db = Db::getInstance();
        $className = get_called_class();
        $model = new $className();
        $tableName = $model->getTableName();
        $primaryKey = $model->primaryKey;
        $entities = $db->query(
            "SELECT * FROM $tableName WHERE {$primaryKey} = :{$primaryKey};",
            [":$primaryKey" => $id],
            get_called_class()
        );

        return $entities ? (is_array($entities) ? $entities[0] : $entities) : null;
    }

    public function save()
    {
        $mappedProperties = $this->mapPropertiesToDbFormat();
        if ($this->getId() !== null && $this::$exists) {
            $this->update($mappedProperties);
        } else {
            $this->insert($mappedProperties);
        }
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $deleted = false;
        $primaryKey = $this->primaryKey;
        try {
            if ($this->getId() !== null && $this::$exists) {
                $tableName = static::getTableName();
                $sql = "DELETE FROM $tableName WHERE $primaryKey = :$primaryKey";
                $db = Db::getInstance();
                $deleted = (bool)$db->query($sql, [":$primaryKey" => $this->$primaryKey], get_called_class());
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }


        return $deleted;
    }

    /**
     * @param array $mappedProperties
     */
    private function update($mappedProperties)
    {
        try {
            $primaryKey = $this->primaryKey;
            $columns2params = [];
            $params2values = [];
            $index = 1;
            foreach ($mappedProperties as $column => $value) {
                $param = ':param' . $index; // :param1
                $columns2params[] = $column . ' = ' . $param; // column1 = :param1
                $params2values[':param' . $index] = $value; // [:param1 => value1]
                $index++;
            }
            $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $columns2params) . ' WHERE ' . $primaryKey . ' = ' . $this->$primaryKey;
            $db = Db::getInstance();
            $db->query($sql, $params2values, get_called_class());
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * @param array $mappedProperties
     */
    private function insert($mappedProperties)
    {
        try {
            $columns = [];
            $params2values = [];
            $index = 1;
            foreach ($mappedProperties as $column => $value) {
                $this->$column = $value;
                $columns[] = $column;
                $params2values[] = $value;
                $index++;
            }
            $columnsStr = implode(', ', $columns);
            $paramsStr = trim(str_repeat('?, ', count($columns)), ', ');
            $tableName = static::getTableName();

            $sql = "INSERT INTO $tableName ($columnsStr) VALUES ($paramsStr);";
            $db = Db::getInstance();
            $db->query($sql, $params2values, get_called_class());
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * @return array
     */
    private function mapPropertiesToDbFormat()
    {
        $reflector = new ReflectionObject($this);
        $properties = $reflector->getProperties();

        $mappedProperties = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if ($propertyName === 'primaryKey') continue;
            if (!$property->isStatic()) {
                $propertyNameAsUnderscore = $this->camelCaseToUnderscore($propertyName);
                $mappedProperties[$propertyNameAsUnderscore] = $this->$propertyName;
            }
        }
        return $mappedProperties;
    }

    /**
     * @param string $source
     * @return string
     */
    private function camelCaseToUnderscore($source)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $source));
    }
}