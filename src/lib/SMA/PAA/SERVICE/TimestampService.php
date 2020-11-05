<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\PortCallRepository;
use SMA\PAA\ORM\TimestampRepository;
use SMA\PAA\ORM\TimestampTimeTypeRepository;
use SMA\PAA\ORM\TimestampStateRepository;
use SMA\PAA\ORM\TimestampDefinitionRepository;
use SMA\PAA\SERVICE\PortCallService;

class TimestampService implements ITimestampService
{
    public function unfinishedPortCallImos(): array
    {
        $timeTypeRepository = new TimestampTimeTypeRepository();
        $type = $timeTypeRepository->first(["name" => "Estimated"]);
        $stateRepository = new TimestampStateRepository();
        $state = $stateRepository->first(["name" => "Arrival_Vessel_PortArea"]);
        $repository = new TimestampRepository();
        return $repository->unfinishedPortCallImos($type, $state);
    }
    public function portCallTimestamps(int $imo): array
    {
        $repository = new TimestampRepository();
        return array_reverse($repository->getTimestampsPretty($imo));
    }

    public function getTimestampDefinitions(): array
    {
        $res = [];

        $timestampDefinitionRepository = new TimestampDefinitionRepository();
        $res = $timestampDefinitionRepository->getAllTimestampDefinitionsPretty();

        return $res;
    }

    public function delete(int $id)
    {
        $repository = new TimestampRepository();
        $model = $repository->get($id);
        $portCallId = $model->port_call_id;

        $repository->delete([$id]);

        if (!empty($portCallId)) {
            $portCallService = new PortCallService();
            $portCallService->scanPortCall($portCallId, true, true);
        }

        return true;
    }

    public function updatePortCallId(int $id, int $portCallId)
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($portCallId);
        $repository = new TimestampRepository();
        $model = $repository->get($id);
        if ($portCallModel->imo === $model->imo) {
            $oldPortCallId = $model->port_call_id;
            $model->port_call_id = $portCallId;
            $model->setIsTrash(false);
            $repository->save($model);

            $portCallService = new PortCallService();
            $portCallService->scanPortCall($portCallId, true, true);
            if (!empty($oldPortCallId)) {
                $portCallService->scanPortCall($oldPortCallId, true, true);
            }

            return true;
        }
        throw new \Exception("Port call imo must be same as timestamp imo.");
    }
    public function unsetPortCallId(int $id)
    {
        $repository = new TimestampRepository();
        $model = $repository->get($id);
        $oldPortCallId = $model->port_call_id;
        $model->port_call_id = null;
        $model->setIsTrash(true);
        $repository->save($model);

        $portCallService = new PortCallService();
        if (!empty($oldPortCallId)) {
            $portCallService->scanPortCall($oldPortCallId, true, true);
        }

        return true;
    }
    public function untrashById(int $id)
    {
        $repository = new TimestampRepository();
        $model = $repository->get($id);
        $model->setIsTrash(false);
        $repository->save($model);

        return true;
    }
}
