<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class TimestampStateTypeRepositoryTest extends TestCase
{
    public function testGetStateTypeMappingsSqlQuery(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "type1"], ["id" => 2, "name" => "type2"]]);
        $repository = new TimestampStateTypeRepository();
        $repository->setDb($db);
        $repository->getStateTypeMappings();
        $this->assertEquals(
            '["SELECT * FROM public.timestamp_state_type"]',
            json_encode($db->lastQuery())
        );
    }

    public function testGetStateTypeMappingsReturnValue(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "type1"], ["id" => 2, "name" => "type2"]]);
        $repository = new TimestampStateTypeRepository();
        $repository->setDb($db);
        $result = $repository->getStateTypeMappings();
        $this->assertEquals(
            $result,
            ["type1" => 1, "type2" => 2]
        );
    }
}
