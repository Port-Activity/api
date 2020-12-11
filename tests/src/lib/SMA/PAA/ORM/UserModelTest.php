<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new UserModel();
        $this->assertEquals(
            '["id","created_at","created_by","modified_at","modified_by",'
            . '"email","password_hash","first_name","last_name","role_id",'
            . '"last_login_time","last_login_data","last_session_time","registration_code_id","status","time_zone",'
            . '"failed_logins","suspend_start","locked","registration_type"]',
            json_encode($model->buildFields())
        );
    }
    public function testBuildingValues(): void
    {
        $model = new UserModel();
        $model->id = 1;
        $model->last_name = "Foo";
        $model->first_name = "Bar";
        $this->assertEquals(
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,"modified_by":null,'
            . '"email":null,"password_hash":null,"first_name":"Bar","last_name":"Foo","role_id":null,'
            . '"last_login_time":null,"last_login_data":null,'
            . '"last_session_time":null,"registration_code_id":null,"status":"active","time_zone":null,'
            . '"failed_logins":0,"suspend_start":null,"locked":"f","registration_type":""}',
            json_encode($model->buildValues($model->buildFields()))
        );
    }
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Model field can't be object
     */
    public function testSaveFailsIfObjectFieldValueIsObject()
    {
        $model = new UserModel();
        $model->id = 1;
        $model->last_name = new UserModel();
        $model->buildValues($model->buildFields());
    }
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Model field can't be array
     */
    public function testSaveFailsIfObjectFieldValueIsArray()
    {
        $model = new UserModel();
        $model->id = 1;
        $model->last_name = ["foo"];
        $model->buildValues($model->buildFields());
    }
    public function testBuildingLoggableFields()
    {
        $model = new UserModel();
        $this->assertEquals(
            '["id","created_at","created_by","modified_at","modified_by","email",'
            . '"first_name","last_name","role_id","last_session_time","registration_code_id","status","time_zone",'
            . '"failed_logins","suspend_start","locked","registration_type"]',
            json_encode($model->buildLoggableFields())
        );
    }
}
