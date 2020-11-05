<?php
namespace SMA\PAA\DB;

use Exception;
use PDO;
use PDOStatement;

class Connection implements IConnection
{
    private static $key = 'PAA_DB_CONNECTION';
    public static $SINGLETON_WARNING_PASS = "dontcallme";

    public function __construct($donCallDirectly, $hostname, $port, $database, $username, $password)
    {
        if ($donCallDirectly !== self::$SINGLETON_WARNING_PASS) {
            throw new Exception("Please use method get to get db connection");
        }
        $connectionString = "pgsql:host=$hostname;port=$port;dbname=$database";
        $this->pdo = new PDO($connectionString, $username, $password);
    }
    public static function get() : IConnection
    {
        if (!array_key_exists(self::$key, $GLOBALS)) {
            $hostname = getenv('PAA_DB_HOSTNAME');
            $database = getenv('PAA_DB_DATABASE');
            $port = getenv('PAA_DB_PORT');
            $username = getenv('PAA_DB_USERNAME');
            $password = getenv('PAA_DB_PASSWORD');
            $connection = new self(
                self::$SINGLETON_WARNING_PASS,
                $hostname,
                $port,
                $database,
                $username,
                $password
            );
            $GLOBALS[self::$key] = $connection;
        }
        return $GLOBALS[self::$key];
    }
    public function query(string $sql, ...$args): IStatement
    {
        return new Statement($this->theQuery($sql, ...$args));
    }
    private function theQuery(string $sql, ...$args): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $ret = $statement->execute($args);
        if (!$ret) {
            throw new Exception(
                "Bad query: " . implode(", ", $statement->errorInfo()) .
                ", SQL: " . $sql . ", ARGS: " . implode(", ", $args)
            );
        }
        return $statement;
    }
    public function queryAll(string $sql, ...$args): array
    {
        $statement = $this->theQuery($sql, ...$args);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public function queryOne(string $sql, ...$args): ?array
    {
        $statement = $this->theQuery($sql, ...$args);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function getLastInsertId(): int
    {
        return intval($this->pdo->lastInsertId());
    }
}
