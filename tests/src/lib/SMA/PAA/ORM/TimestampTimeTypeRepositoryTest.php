<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class TimestampTimeTypeRepositoryTest extends TestCase
{
    public function testListObjectWithoutSearchCriteriaGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "type1"], ["id" => 2, "name" => "type2"]]);
        $repository = new TimestampTimeTypeRepository();
        $repository->setDb($db);
        $repository->list([], 0, 10);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,'
            . 'modified_by,name FROM public.timestamp_time_type '
            . 'ORDER BY id LIMIT ? OFFSET ?",10,0]',
            json_encode($db->lastQuery())
        );
    }
    public function testGetTimeTypeMappingsSqlQuery(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "type1"], ["id" => 2, "name" => "type2"]]);
        $repository = new TimestampTimeTypeRepository();
        $repository->setDb($db);
        $repository->getTimeTypeMappings();
        $this->assertEquals(
            '["SELECT * FROM public.timestamp_time_type"]',
            json_encode($db->lastQuery())
        );
    }

    public function testGetTimeTypeMappingsReturnValue(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "type1"], ["id" => 2, "name" => "type2"]]);
        $repository = new TimestampTimeTypeRepository();
        $repository->setDb($db);
        $result = $repository->getTimeTypeMappings();
        $this->assertEquals(
            $result,
            ["type1" => 1, "type2" => 2]
        );
    }
}
