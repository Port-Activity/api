<?php
namespace SMA\PAA\ORM;

use ReflectionClass;
use ReflectionProperty;
use SMA\PAA\TOOL\DateTools;

class OrmModel
{
    public $id;
    public $created_at;
    public $created_by;
    public $modified_at;
    public $modified_by;
    protected $className;
    public function __construct(string $className)
    {
        $this->className = $className;
    }
    public function buildFields(): array
    {
        $reflectSelf = new ReflectionClass(__CLASS__);
        $reflect = new ReflectionClass($this->className);
        $fields = array_unique(array_merge(
            $reflectSelf->getProperties(ReflectionProperty::IS_PUBLIC),
            $reflect->getProperties(ReflectionProperty::IS_PUBLIC)
        ));
        return array_map(function ($v) {
            return $v->getName();
        }, $fields);
    }
    public function dontLogFields()
    {
        return array();
    }
    public function buildLoggableFields(): array
    {
        if ($this->dontLogFields()) {
            return array_values(array_diff($this->buildFields(), $this->dontLogFields()));
        }
        return $this->buildFields();
    }
    public function buildFieldsExludingId(): array
    {
        return array_filter($this->buildFields(), function ($v) {
            return $v !== "id";
        });
    }
    private function isTimeField($field)
    {
        return in_array($field, ["time", "created_at", "modified_at"]);
    }
    public function buildValues(array $fields): array
    {
        $dateTools = new DateTools();
        $data = [];
        foreach ($fields as $field) {
            $value = $this->$field;
            if (is_object($value)) {
                throw new \Exception("Model field can't be object");
            }
            if (is_array($value)) {
                throw new \Exception("Model field can't be array");
            }
            $data[$field] = $this->isTimeField($field) && $value
                ? $dateTools->isoDate($value)
                : $value;
        }
        return $data;
    }
    public function filter(array $whitelist)
    {
        $objectVars = get_object_vars($this);
        foreach ($objectVars as $k => $v) {
            if (!in_array($k, $whitelist)) {
                unset($this->$k);
            }
        }

        return $this;
    }
}
