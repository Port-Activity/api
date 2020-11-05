<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class PushNotificationTokenRepositoryTest extends TestCase
{
    public function testRemovingTokenGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection(["id" => 11, "user_id" => 111, "installation_id" => "abc",
            "platform" => "test", "token" => "xxx_yyy"]);
        $repository = new PushNotificationTokenRepository();
        $repository->setDb($db);
        $model = new PushNotificationTokenModel();
        $model->installation_id = "abc";
        $model->platform = "test";
        $model->token = "";
        $model->user_id = 111;
        $repository->save($model, true);
        $this->assertEquals(
            '["DELETE FROM public.push_notification_token WHERE id IN (?)",11]',
            json_encode($db->lastQuery())
        );
    }
    public function testDeletingObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new PushNotificationTokenRepository();
        $repository->setDb($db);
        $model = new PushNotificationTokenModel();
        $model->id = 234;
        $model->name = "admin";
        $model->user_id = 111;
        $repository->delete([$model->id], true);
        $this->assertEquals(
            '["DELETE FROM public.push_notification_token WHERE id IN (?)",234]',
            json_encode($db->lastQuery())
        );
    }
}
