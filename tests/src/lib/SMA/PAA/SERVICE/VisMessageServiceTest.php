<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;
use SMA\PAA\ORM\FakeConnection;
use SMA\PAA\ORM\OrmRepository;
use SMA\PAA\ORM\VisRtzStateModel;
use SMA\PAA\ORM\VisVoyagePlanModel;

final class VisMessageServiceTest extends TestCase
{
    public function testNonProblematicVisRtzStatusReturnNullMessage(): void
    {
        OrmRepository::injectFakeDb(new FakeConnection([["vessel_name" => "dummy", "name" => "dummy"]]));
        $model = new VisVoyagePlanModel();
        $model->from_service_id = "fromdummy";
        $model->to_service_id = "todummy";
        $model->rtz_state = VisRtzStateModel::SYNC_WITH_ETA_FOUND;
        $service = new VisMessageService();
        $this->assertEquals(null, $service->automaticMessageForVisStatusIfAny($model));
    }
    public function testVisRtzStatusReturnMessageCorreclyFormattedMessageWhenNoVoygePlan(): void
    {
        $service = new VisMessageService();
        $this->assertEquals(
            'Share your voyage plan with Port of Gävle (SEGVX). '
            . 'Voyage plan should have calculated schedule and waypoint near pilot boarding area.',
            $service->automaticMessageForNoVoyagePlan()
        );
    }
    public function testVisRtzStatusReturnMessageWhenStatusCalcualtedSchduleNotFound(): void
    {
        OrmRepository::injectFakeDb(new FakeConnection([["vessel_name" => "dummy", "name" => "dummy"]]));
        $model = new VisVoyagePlanModel();
        $model->from_service_id = "fromdummy";
        $model->to_service_id = "todummy";
        $model->rtz_state = VisRtzStateModel::CALCULATED_SCHEDULE_NOT_FOUND;
        $model->rtz_parse_results = json_encode(["route_name" => "This is a Route Name"]);
        $service = new VisMessageService();
        $this->assertEquals(
            'The voyage plan (This is a Route Name) does not have a valid schedule. '
            . 'Please send an updated voyage plan with a valid ETA for waypoint near pilot boarding area.',
            $service->automaticMessageForVisStatusIfAny($model)
        );
    }
    public function testVisRtzStatusReturnMessageWhenStatusSyncNotFoundAndCantBeAdded(): void
    {
        OrmRepository::injectFakeDb(new FakeConnection([["vessel_name" => "dummy", "name" => "dummy"]]));
        $model = new VisVoyagePlanModel();
        $model->from_service_id = "fromdummy";
        $model->to_service_id = "todummy";
        $model->rtz_state = VisRtzStateModel::CALCULATED_SCHEDULE_NOT_FOUND;
        $model->rtz_parse_results = json_encode(["route_name" => "This is a Route Name"]);
        $service = new VisMessageService();
        $this->assertEquals(
            'The voyage plan (This is a Route Name) does not have a valid schedule. '
            . 'Please send an updated voyage plan with a valid ETA for waypoint near pilot boarding area.',
            $service->automaticMessageForVisStatusIfAny($model)
        );
    }
    public function testVisRtzStatusReturnMessageWhenStatusSyncNotFoundAndCantBeAddedWithoutRouteName(): void
    {
        OrmRepository::injectFakeDb(new FakeConnection([["vessel_name" => "dummy", "name" => "dummy"]]));
        $model = new VisVoyagePlanModel();
        $model->from_service_id = "fromdummy";
        $model->to_service_id = "todummy";
        $model->rtz_state = VisRtzStateModel::CALCULATED_SCHEDULE_NOT_FOUND;
        $service = new VisMessageService();
        $this->assertEquals(
            'The voyage plan (UNKNOWN) does not have a valid schedule. '
            . 'Please send an updated voyage plan with a valid ETA for waypoint near pilot boarding area.',
            $service->automaticMessageForVisStatusIfAny($model)
        );
    }
    public function testGetSyncPointAreaString(): void
    {
        $service = new VisMessageService();
        $this->assertEquals(
            '<Circle><position lat="12.345" lon="67.89"/><radius>1234</radius></Circle>',
            $service->getSyncPointAreaString()
        );
    }
}
