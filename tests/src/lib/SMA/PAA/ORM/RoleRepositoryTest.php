<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class RoleRepositoryTest extends TestCase
{
    public function testUpdateGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection([["id" => 11, "name" => "first_user"], ["id" => 12, "name" => "user"]]);
        $repository = new RoleRepository();
        $repository->setDb($db);
        $model = new RoleModel();
        $model->id = 123;
        $model->name = "user";
        $repository->save($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.role SET '
            .'created_at = ?,created_by = ?,modified_at = ?,modified_by = ?,name = ? '
            .'WHERE id=?"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",null,"<date>",0,"user",123]',
            json_encode($last)
        );
    }
    public function testDeletingObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new RoleRepository();
        $repository->setDb($db);
        $model = new RoleModel();
        $model->id = 234;
        $model->name = "admin";
        $model->user_id = 111;
        $repository->delete([$model->id], true);
        $this->assertEquals(
            '["DELETE FROM public.role WHERE id IN (?)",234]',
            json_encode($db->lastQuery())
        );
    }
    public function testGetObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new RoleRepository();
        $repository->setDb($db);
        $repository->get(123);
        $this->assertEquals(
            '["SELECT * FROM public.role WHERE id=?",123]',
            json_encode($db->lastQuery())
        );
    }
}
