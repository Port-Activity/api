<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class TimestampStateRepositoryTest extends TestCase
{
    public function testGetStateMappingsSqlQuery(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "state1"], ["id" => 2, "name" => "state2"]]);
        $repository = new TimestampStateRepository();
        $repository->setDb($db);
        $repository->getStateMappings();
        $this->assertEquals(
            '["SELECT * FROM public.timestamp_state"]',
            json_encode($db->lastQuery())
        );
    }

    public function testGetStateMappingsReturnValue(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "state1"], ["id" => 2, "name" => "state2"]]);
        $repository = new TimestampStateRepository();
        $repository->setDb($db);
        $result = $repository->getStateMappings();
        $this->assertEquals(
            $result,
            ["state1" => 1, "state2" => 2]
        );
    }
}
