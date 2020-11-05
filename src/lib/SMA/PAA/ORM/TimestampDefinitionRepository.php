<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\TimestampDefinitionPrettyModel;

class TimestampDefinitionRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getAllTimestampDefinitionsPretty(): array
    {
        $rawResults = $this->listAll();

        $res = [];
        foreach ($rawResults as $rawResult) {
            $prettyResult = new TimestampDefinitionPrettyModel();
            $prettyResult->setFromTimestampDefinition($rawResult);
            $res[] = $prettyResult;
        }

        return $res;
    }
}
