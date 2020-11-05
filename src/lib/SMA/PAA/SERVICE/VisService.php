<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\ORM\PortCallModel;
use SMA\PAA\ORM\PortCallRepository;
use SMA\PAA\ORM\VisVesselRepository;
use SMA\PAA\Session;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\TOOL\VisTools;

class VisService
{
    public function getTextMessage(
        string $from_service_id = null,
        string $to_service_id = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $visTools = new VisTools(new CurlRequest());

        $res = $visTools->getTextMessage($from_service_id, $to_service_id, $offset, $limit, $sort);

        return $res;
    }

    public function getNotification(
        string $from_service_id = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $visTools = new VisTools(new CurlRequest());

        $res = $visTools->getNotification($from_service_id, $offset, $limit, $sort);

        return $res;
    }

    public function getService(
        int $imo = null,
        string $service_id = null
    ): array {
        $visTools = new VisTools(new CurlRequest());

        $res = $visTools->getService($imo, $service_id);

        // VIS agent posts the results directly to VIS vessel database, so we always return OK
        return ["result" => "OK"];
    }

    public function getVessel(
        int $imo = null,
        string $vessel_name = null,
        string $service_id = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $visTools = new VisTools(new CurlRequest());

        $res = $visTools->getVessel($imo, $vessel_name, $service_id, $offset, $limit, $sort);

        return $res;
    }

    public function getVoyagePlan(
        string $from_service_id = null,
        string $to_service_id = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $visTools = new VisTools(new CurlRequest());

        $res = $visTools->getVoyagePlan($from_service_id, $to_service_id, $offset, $limit, $sort);

        return $res;
    }

    public function sendTextMessageWithImo(
        int $imo,
        string $subject,
        string $body
    ) {
        $repository = new VisVesselRepository();
        $model = $repository->first(["imo" => $imo]);
        if (!$model) {
            throw new \Exception("Can't send message since no service_id known");
        }
        $session = new Session();
        $user = $session->user();
        return $this->sendTextMessage(
            $model->service_id,
            $user->first_name . " " . $user->last_name,
            $subject,
            $body
        );
    }

    public function sendTextMessage(
        string $vis_service_id,
        string $author,
        string $subject,
        string $body,
        string $informationObjectReferenceId = null,
        string $informationObjectReferenceType = null,
        string $area = null
    ): array {
        $res = [];

        $visTools = new VisTools(new CurlRequest());

        $visTools->uploadTextMessage(
            $vis_service_id,
            $author,
            $subject,
            $body,
            $informationObjectReferenceId,
            $informationObjectReferenceType,
            $area
        );

        return ["result" => "OK"];
    }

    public function sendRta(
        string $vis_service_id,
        string $rta,
        string $eta_min,
        string $eta_max,
        int $port_call_id = null
    ): array {
        if ($port_call_id) {
            $repository = new PortCallRepository();
            $model = $repository->get($port_call_id);
            if ($model && $model->status !== PortCallModel::STATUS_ARRIVING) {
                throw new \Exception("Port call is not at arriving status");
            }
        }

        $visTools = new VisTools(new CurlRequest());

        $visTools->sendRta($vis_service_id, $rta, $eta_min, $eta_max);

        $session = new Session();
        $tools  = new DateTools();

        if ($model) {
            $repository = new PortCallRepository();
            $repository->saveRta(
                $port_call_id,
                $rta,
                [
                    "eta_min" => $eta_min,
                    "eta_max" => $eta_max,
                    "updated_at" => $tools->now(),
                    "updated_by" => $session->userId()
                ]
            );
        }

        return ["result" => "OK"];
    }

    public function pollVisService(): array
    {
        $res = [];

        $visTools = new VisTools(new CurlRequest());

        $visTools->pollVisService();

        return ["result" => "OK"];
    }

    public function getVisServiceConfiguration(): array
    {
        $res = [];

        $visTools = new VisTools(new CurlRequest());

        $res = json_decode($visTools->getVisServiceConfiguration(), true);

        return $res;
    }
}
