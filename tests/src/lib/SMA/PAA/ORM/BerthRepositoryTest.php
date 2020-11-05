<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class BerthRepositoryTest extends TestCase
{
    public function testUpdateGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection([["id" => 11, "name" => "first_user"], ["id" => 12, "name" => "user"]]);
        $repository = new BerthRepository();
        $repository->setDb($db);
        $model = new BerthModel();
        $model->id = 123;
        $model->code = "B1";
        $model->name = "Berth 1";
        $repository->save($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.berth SET '
            .'created_at = ?,created_by = ?,modified_at = ?,modified_by = ?,code = ?,name = ?,nominatable = ? '
            .'WHERE id=?"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",null,"<date>",0,"B1","Berth 1","f",123]',
            json_encode($last)
        );
    }
    public function testDeletingObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new BerthRepository();
        $repository->setDb($db);
        $model = new BerthModel();
        $model->id = 234;
        $model->code = "B1";
        $model->name = "Berth 1";
        $repository->delete([$model->id], true);
        $this->assertEquals(
            '["DELETE FROM public.berth WHERE id IN (?)",234]',
            json_encode($db->lastQuery())
        );
    }
    public function testGetObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new BerthRepository();
        $repository->setDb($db);
        $repository->get(123);
        $this->assertEquals(
            '["SELECT * FROM public.berth WHERE id=?",123]',
            json_encode($db->lastQuery())
        );
    }
}
