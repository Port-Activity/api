<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class UserRepositoryTest extends TestCase
{
    public function testSavingObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new UserRepository();
        $repository->setDb($db);
        $model = new UserModel();
        $model->id = 123;
        $model->first_name = "Jack";
        $model->last_name = "White";
        $model->email = "jack.white@acme.com";
        $repository->save($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.user SET created_at = ?,created_by = ?,modified_at = ?,modified_by = ?'
            . ',email = ?,password_hash = ?,first_name = ?,last_name = ?,role_id = ?'
            . ',last_login_time = ?,last_login_data = ?,last_session_time = ?,registration_code_id = ?'
            . ',status = ?,time_zone = ?,failed_logins = ?,suspend_start = ?,locked = ?,registration_type = ? '
            . 'WHERE id=?"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",null,"<date>",0,"jack.white@acme.com",null,"Jack","White",'
            . 'null,null,null,null,null,"active",null,0,null,"f","",123]',
            json_encode($last)
        );
    }
    public function testSavingNewObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new UserRepository();
        $repository->setDb($db);
        $model = new UserModel();
        $model->first_name = "Jack";
        $model->last_name = "White";
        $model->email = "jack.white@acme.com";
        $repository->save($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"INSERT INTO public.user '
            . '(created_at,created_by,modified_at,modified_by,email,password_hash,first_name,last_name,role_id,'
            . 'last_login_time,last_login_data,last_session_time,registration_code_id,status,time_zone,'
            . 'failed_logins,suspend_start,locked,registration_type) '
            . 'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",0,"<date>",0,"jack.white@acme.com",null,"Jack","White",null,null,null,null,null,"active",null,'
            . '0,null,"f",""]',
            json_encode($last)
        );
    }
    public function testDeletingObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new UserRepository();
        $repository->setDb($db);
        $model = new UserModel();
        $model->id = 234;
        $model->first_name = "Jack";
        $model->last_name = "White";
        $model->email = "jack.white@acme.com";
        $repository->delete([$model->id], true);
        $this->assertEquals(
            '["DELETE FROM public.user WHERE id IN (?)",234]',
            json_encode($db->lastQuery())
        );
    }
    public function testGetObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new UserRepository();
        $repository->setDb($db);
        $repository->get(123);
        $this->assertEquals(
            '["SELECT * FROM public.user WHERE id=?",123]',
            json_encode($db->lastQuery())
        );
    }
    public function testListObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection([["id" => 111, "foo" => "bar"], ["id" => 112, "foo" => "baz"]]);
        $repository = new UserRepository();
        $repository->setDb($db);
        $repository->list(["first_name" => "Foo"], 0, 10);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,'
            . 'email,password_hash,first_name,last_name,role_id,'
            . 'last_login_time,last_login_data,last_session_time,registration_code_id,status,time_zone,'
            . 'failed_logins,suspend_start,locked,registration_type '
            . 'FROM public.user WHERE first_name=? '
            . 'ORDER BY id LIMIT ? OFFSET ?","Foo",10,0]',
            json_encode($db->lastQuery())
        );
    }
    public function testListObjectWithoutSearchCriteriaGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection([["id" => 111, "foo" => "bar"], ["id" => 112, "foo" => "baz"]]);
        $repository = new UserRepository();
        $repository->setDb($db);
        $repository->list([], 0, 10);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,'
            . 'modified_by,email,password_hash,first_name,last_name,role_id,'
            . 'last_login_time,last_login_data,last_session_time,registration_code_id,status,time_zone,'
            . 'failed_logins,suspend_start,locked,registration_type '
            . 'FROM public.user ORDER BY id LIMIT ? OFFSET ?",10,0]',
            json_encode($db->lastQuery())
        );
    }
}
