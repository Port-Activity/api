<?php
namespace SMA\PAA\ORM;

use Exception;
use SMA\PAA\DB\Connection;
use SMA\PAA\DB\IConnection;
use SMA\PAA\Session;

const KEY = __CLASS__ . "_fake_db";
class OrmRepository
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $modelClassName;
    /**
     * @var IConnection
     */
    private $db;
    /**
     * @var string
     */
    public $table;
    public function __construct(String $className)
    {
        if (preg_match("/(.*)Repository$/", $className, $matches)) {
            $tokens = explode("\\", $matches[1]);
            $this->className = $className;
            $this->modelClassName = str_replace("Repository", "Model", $className);
            $this->table = "public." . strtolower(preg_replace("/\B([A-Z])/", '_$1', array_pop($tokens)));
        }
    }
    public static function injectFakeDb(IConnection $db): void
    {
        $GLOBALS[KEY] = $db;
    }
    public function setDb(IConnection $db): void
    {
        $this->db = $db;
    }
    public function getDb(): IConnection
    {
        if (isset($GLOBALS[KEY])) {
            $this->db = $GLOBALS[KEY];
        }
        if (isset($GLOBALS[$this->className."_fake_db"])) {
            $this->db = $GLOBALS[$this->className."_fake_db"];
        }
        if (!$this->db) {
            $this->db = Connection::get();
        }
        return $this->db;
    }
    private function storeCrud(OrmModel $model = null, string $sql = "", array $values = [])
    {
        $crudLogRepository = new CrudLogRepository();
        if ($model) {
            $crudLogModel = new CrudLogModel();
            $crudLogModel->table_name = $this->table;
            $crudLogModel->data = json_encode(
                $model->buildValues(
                    $model->buildLoggableFields()
                )
            );
            $crudLogRepository->save($crudLogModel, true);
        }
        if ($sql) {
            $crudLogModel = new CrudLogModel();
            $crudLogModel->table_name = $this->table;
            $crudLogModel->data = json_encode(["sql" => $sql, "values" => $values]);
            $crudLogRepository->save($crudLogModel, true);
        }
    }
    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        $session = new Session();
        $dbts = gmdate("Y-m-d\TH:i:s\Z");
        $model->modified_by = $session->userId();
        $model->modified_at = $dbts;
        if (!$model->id) {
            $model->created_by = $session->userId();
            $model->created_at = $dbts;
        }

        $fields = $model->buildFieldsExludingId();
        $values = $model->BuildValues($fields);
        $db = $this->getDb();
        if ($model->id) {
            $placeHolders = implode(",", array_map(function ($v) {
                return "$v = ?";
            }, $fields));
            $statement = $db->query(
                "UPDATE $this->table SET $placeHolders WHERE id=?",
                ...(array_values(array_merge($values, array($model->id))))
            );
        } else {
            $placeHolders = substr(str_repeat("?,", sizeof($values)), 0, -1);
            $statement = $db->query(
                "INSERT INTO $this->table (" . implode(",", $fields) . ") VALUES ($placeHolders)",
                ...(array_values($values))
            );
        }
        if ($statement) {
            $model->id = $model->id ?: $db->getLastInsertId();
            if (!$skipCrudLog) {
                $this->storeCrud($model);
            }
            return $model->id;
        }
        throw new \Exception("Save failed");
    }
    private function buildObject(?array $values): ?OrmModel
    {
        if (!$values) {
            return null;
        }
        $object = new $this->modelClassName;
        foreach ($values as $k => $v) {
            $method = "set".str_replace(' ', '', ucwords(str_replace('_', ' ', $k)));
            if (method_exists($object, $method)) {
                $object->$method($v);
            } else {
                $object->$k = $v;
            }
        }
        return $object;
    }
    private function buildObjects(?array $values)
    {
        if (!$values) {
            return [];
        }
        $parent = $this;
        return array_map(function ($a) use ($parent) {
            return $parent->buildObject($a);
        }, $values);
    }
    public function get(int $id): ?OrmModel
    {
        $db = $this->getDb();
        return $this->buildObject($db->queryOne("SELECT * FROM $this->table WHERE id=?", $id));
    }
    protected function getWithQuery(string $query, ...$args): ?OrmModel
    {
        $db = $this->getDb();
        return $this->buildObject($db->queryOne($query, ...$args));
    }
    protected function getMultipleWithQuery(string $query, ...$args): array
    {
        $db = $this->getDb();
        return $this->buildObjects($db->queryAll($query, ...$args));
    }
    private function listObjectsOrCount(
        array $query,
        int $start,
        int $count,
        string $orderBy = "id",
        bool $onlyCount = false
    ): ?array {
        $object = new $this->modelClassName;
        $fields = [];
        if (!empty($query["complex_select"])) {
            if (preg_match('/^(.*?) FROM/', $query["complex_select"], $match) === 1) {
                $selectFields = explode(",", $match[1]);
                foreach ($selectFields as $selectField) {
                    if (strpos($selectField, " AS ") === false) {
                        $plainSelectFields = explode(".", $selectField);
                        if (count($plainSelectFields) === 3) {
                            if (preg_match('/^[a-z_]+$/', $plainSelectFields[2], $match) === 1) {
                                $fields[] = $plainSelectFields[2];
                            }
                        }
                    } else {
                        $plainSelectFields = explode(" AS ", $selectField);
                        if (count($plainSelectFields) === 2) {
                            if (preg_match('/^[a-z_]+$/', $plainSelectFields[1], $match) === 1) {
                                $fields[] = $plainSelectFields[1];
                            }
                        }
                    }
                }
            }
        } else {
            $fields = $object->buildFields();
        }
        if ($onlyCount) {
            // don't check orderBy
        } else {
            $fieldsWithLower = array_map(function ($f) {
                return "lower($f)";
            }, $fields);
            $fieldsWithDesc = array_map(function ($f) {
                return "$f DESC";
            }, $fields);
            $fieldsWithLowerDesc = array_map(function ($f) {
                return "lower($f) DESC";
            }, $fields);
            $tokens = explode(",", $orderBy);
            foreach ($tokens as $token) {
                if (!in_array($token, $fields)
                    && !in_array($token, $fieldsWithLower)
                    && !in_array($token, $fieldsWithDesc)
                    && !in_array($token, $fieldsWithLowerDesc)
                    ) {
                        throw new \Exception("Invalid orderBy: " . $token);
                }
            }
        }

        $db = $this->getDb();

        $constructedQuery = "";
        $whereStr = "";
        $values = [];
        if (!empty($query["complex_query"])) {
            $neededArgs = substr_count($query["complex_query"], "?");
            if (empty($query["complex_args"]) || count($query["complex_args"]) !== $neededArgs) {
                throw new \Exception("Invalid or missing arguments for complex query");
            }

            $whereStr = " WHERE " . $query["complex_query"];
            $values = $query["complex_args"];
        } else {
            $wheres = [];

            $map = [
                "gt" => '>',
                "lt" => '<',
                "gte" => '>=',
                "lte" => '<=',
                "ilike" => "ILIKE",
                "neq" => "<>",
                "in" => "in",
                "nin" => "not in"
            ];

            foreach ($query as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $innerK => $innerV) {
                        if (array_key_exists($innerK, $map)) {
                            $operator = $map[$innerK];
                            if ($innerK === "in" || $innerK === "nin") {
                                if (is_array($innerV)) {
                                    $holders = implode(",", array_fill(0, count($innerV), "?"));
                                } else {
                                    $holders = "?";
                                }
                                $wheres[] = "$k $operator ($holders)";
                            } else {
                                $wheres[] = "$k $operator ?";
                            }
                            if (is_array($innerV)) {
                                $values = array_merge($values, array_values($innerV));
                            } else {
                                $values[] = $innerV;
                            }
                        } elseif ($innerK === "LOWER") {
                            $wheres[] = "LOWER($k)=LOWER(?)";
                            $values[] = $innerV;
                        } else {
                            throw new \Exception("Invalid operator: " . $innerK);
                        }
                    }
                } elseif ($k !== "complex_select") {
                    if ($v === null) {
                        $wheres[] = "$k is null";
                    } elseif ($v === "NOT NULL") {
                        $wheres[] = "$k is not null";
                    } else {
                        $wheres[] = "$k=?";
                        $values[] = $v;
                    }
                }
            }

            $whereStr = $wheres ? " WHERE " . implode(" AND ", $wheres) : "";
        }

        $onlyCount ? null : array_push($values, $count);
        $onlyCount ? null : array_push($values, $start);

        if (!empty($query["complex_select"])) {
            $constructedQuery = "SELECT "
            . ($onlyCount ? "count(c.id) FROM (SELECT ".$query["complex_select"] : $query["complex_select"])
            . $whereStr
            . ($onlyCount ? ") AS c" : " ORDER BY " . $orderBy)
            . ($onlyCount ? "" : " LIMIT ? OFFSET ?");
        } else {
            $constructedQuery = "SELECT "
            . ($onlyCount ? " count(id)" : implode(",", $fields))
            . " FROM $this->table"
            . $whereStr
            . ($onlyCount ? "" : " ORDER BY " . $orderBy)
            . ($onlyCount ? "" : " LIMIT ? OFFSET ?");
        }

        return $db->queryAll(
            $constructedQuery,
            ...array_values($values)
        );
    }
    public function listPaginated(
        array $query,
        int $start,
        int $count,
        string $orderBy = "id"
    ): ?array {
        return [
            "data" => $this->list($query, $start, $count, $orderBy),
            "pagination" => [
                "start" => $start,
                "limit" => $count,
                "total" => $this->count($query)
            ]
        ];
    }
    public function list(array $query, int $start, int $count, string $orderBy = "id"): ?array
    {
        return $this->buildObjects(
            $this->listObjectsOrCount($query, $start, $count, $orderBy)
        );
    }
    public function count(array $query): int
    {
        $data = $this->listObjectsOrCount($query, 0, 0, "", true);
        return $data[0]["count"];
    }
    public function listNoLimit(array $query, int $start, string $orderBy = "id"): ?array
    {
        $values = $query;
        array_push($values, $start);
        $wheres = [];
        foreach ($query as $k => $v) {
            $wheres[] = "$k=?";
        }
        $db = $this->getDb();
        $object = new $this->modelClassName;
        return $this->buildObjects(
            $db->queryAll(
                "SELECT "
                . implode(",", $object->buildFields())
                . " FROM $this->table"
                . ($wheres ? " WHERE " . implode(" AND ", $wheres) : "")
                . " ORDER BY " . $orderBy
                . " OFFSET ?",
                ...array_values($values)
            )
        );
    }
    public function listAll(string $orderBy = "id"): ?array
    {
        $db = $this->getDb();
        $object = new $this->modelClassName;
        return $this->buildObjects(
            $db->queryAll(
                "SELECT * FROM $this->table"
                . " ORDER BY " . $orderBy
            )
        );
    }
    public function first(array $query, string $orderBy = "id"): ?OrmModel
    {
        $data = $this->list($query, 0, 1, $orderBy);
        return $data && sizeof($data) > 0 ? $data[0] : null;
    }
    public function delete(array $ids, bool $skipCrudLog = false)
    {
        $db = $this->getDb();
        $in = join(',', array_fill(0, count($ids), '?'));
        $out = $db->query("DELETE FROM $this->table WHERE id IN ($in)", ...array_values($ids));
        if (!$skipCrudLog) {
            foreach ($ids as $id) {
                $model = new $this->modelClassName;
                $model->id = $id;
                $this->storeCrud($model);
            }
        }
        return $out;
    }
    public function deleteAll(array $query)
    {
        $values = $query;
        $wheres = [];
        foreach ($query as $k => $v) {
            $wheres[] = "$k=?";
        }
        $db = $this->getDb();
        $sql =
        "DELETE "
        . "FROM $this->table "
        . ($wheres ? " WHERE " . implode(" AND ", $wheres) : "")
        ;
        $out = $db->query($sql, ...array_values($values));
        $this->storeCrud(null, $sql, $values);
        return $out;
    }
    public function update($sql, ...$args)
    {
        $db = $this->getDb();
        $out = $db->query($sql, ...$args);
        $this->storeCrud(null, $sql, $args);
        return $out;
    }
    public function updateWithoutCrudHistoryStoring($sql, ...$args)
    {
        $db = $this->getDb();
        $out = $db->query($sql, ...$args);
        return $out;
    }
    public function buildJoinSelect(array $joinParameters)
    {
        $res = "";

        $table = $this->table;
        $model = new $this->modelClassName();

        $fields = $model->buildFields();
        $selectFields = "";
        foreach ($fields as $field) {
            $selectFields .= $table . "." . $field . ",";
        }

        $res = $selectFields;

        foreach ($joinParameters as $k => $v) {
            $repository = new OrmRepository($k);
            $foreignTable = $repository->table;

            foreach ($v["values"] as $innerK => $innerV) {
                if (!empty($innerK)) {
                    $res .= $foreignTable . "." . $innerK . " AS " . $innerV .  ",";
                } else {
                    $res .= $foreignTable . "." . $innerV . ",";
                }
            }
        }
        $res = rtrim($res, ",");

        $res .= " FROM " . $table;

        foreach ($joinParameters as $k => $v) {
            $repository = new OrmRepository($k);
            $foreignTable = $repository->table;

            if (count($v["join"]) > 1) {
                throw new \Exception("Multiple ON conditions not supported!");
            }

            foreach ($v["join"] as $innerK => $innerV) {
                $res .= " LEFT JOIN " . $foreignTable
                . " ON (" . $table . "." . $innerK . "=" . $foreignTable . "." . $innerV . ")";
            }
        }

        return $res;
    }
    public function setIntervalStyleToISO8601()
    {
        $db = $this->getDb();
        return $db->queryOne("SET intervalstyle = 'iso_8601';");
    }
}
