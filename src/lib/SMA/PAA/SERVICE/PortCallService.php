<?php
namespace SMA\PAA\SERVICE;

use DateTime;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\VesselRepository;
use SMA\PAA\ORM\TimestampRepository;
use SMA\PAA\ORM\PortCallModel;
use SMA\PAA\ORM\PortCallRepository;
use SMA\PAA\ORM\VisRtzStateRepository;
use SMA\PAA\ORM\VisVesselRepository;
use SMA\PAA\ORM\VisVoyagePlanRepository;
use SMA\PAA\ORM\TimestampModel;
use SMA\PAA\ORM\TimestampPrettyModel;
use SMA\PAA\ORM\SlotReservationRepository;
use SMA\PAA\Session;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\TOOL\TimestampClusterTools;
use SMA\PAA\TOOL\OutboundVesselTools;

class PortCallService
{
    private $stateService;
    private $masterSource;
    private $masterStartBufferDuration;
    private $masterEndBufferDuration;

    public function __construct(
        ITimestampService $timestampService = null,
        IVesselService $vesselService = null,
        IStateService $stateService = null
    ) {
        if (!$timestampService) {
            $timestampService = new TimestampService();
        }
        $this->timestampService = $timestampService;
        if (!$vesselService) {
            $vesselService = new VesselService();
        }
        $this->vesselService = $vesselService;
        if (!$stateService) {
            $stateService = new StateService();
        }
        $this->stateService = $stateService;

        $masterSources = getenv("PORT_CALL_MASTER_SOURCE");

        if ($masterSources === false) {
            $this->masterSource = [];
        } else {
            $this->masterSource = explode(",", $masterSources);
        }

        $dateTools = new DateTools();

        $this->masterStartBufferDuration = getenv("PORT_CALL_MASTER_START_BUFFER_DURATION");
        if ($this->masterStartBufferDuration === false) {
            $this->masterStartBufferDuration = "PT0D";
        } elseif (!$dateTools->isValidIsoDuration($this->masterStartBufferDuration)) {
            $this->masterStartBufferDuration = "PT0D";
        }

        $this->masterEndBufferDuration = getenv("PORT_CALL_MASTER_END_BUFFER_DURATION");
        if ($this->masterEndBufferDuration === false) {
            $this->masterEndBufferDuration = "PT0D";
        } elseif (!$dateTools->isValidIsoDuration($this->masterEndBufferDuration)) {
            $this->masterEndBufferDuration = "PT0D";
        }
    }

    private function fillPortCallDatas(PortCallModel $model)
    {
        $ensureValues = [
            "id"
            ,"status"
            ,"vessel_name"
            ,"imo"
            ,"nationality"
            ,"from_port"
            ,"to_port"
            ,"loa"
            ,"beam"
            ,"draft"
            ,"first_eta"
            ,"current_eta"
            ,"rta"
            ,"first_etd"
            ,"current_etd"
            ,"planned_eta"
            ,"planned_etd"
            ,"ata"
            ,"atd"
            ,"inbound_piloting_status"
            ,"outbound_piloting_status"
            ,"cargo_operations_status"
            ,"berth"
            ,"berth_name"
        ];

        $out = array_reduce($ensureValues, function ($acc, $key) use ($model) {
            $acc[$key] = isset($model->$key) ? $model->$key : "";
            return $acc;
        }, []);
        $out["badges"] = [];
        if ($model->status) {
            $out["badges"][] = ["type" => "success", "value" => $model->status];
        }
        if ($model->berth_name) {
            $out["badges"][] = ["type" => "default", "value" => $model->berth_name];
        }
        $out["is_vis"] = $model->getIsVis();
        if ($model->getIsVis()) {
            $out["badges"][] = ["type" => "default", "value" => "stm"];
        }
        $service = new UnLocodeService();
        if ($model->from_port) {
            $out["from_port_code"] = $model->from_port;
            $out["from_port"] = $service->codeToCitySafe($model->from_port);
        }
        if ($model->to_port) {
            $out["to_port_code"] = $model->to_port;
            $out["to_port"] = $service->codeToCitySafe($model->to_port);
        }
        if ($model->next_port) {
            $out["next_port_code"] = $model->next_port;
            $out["next_port"] = $service->codeToCitySafe($model->next_port);
        }
        $out["eta_form_received"] = false;
        if ($model->eta_form_email) {
            $out["eta_form_received"] = true;
        }
        $dateTools = new DateTools();
        $out["next_event"] = [
            "title" => $model->next_event_title,
            "ts" => $model->next_event_ts ? $dateTools->isoDate($model->next_event_ts) : null
        ];

        // fill vis data
        if ($model->getIsVis()) {
            $repository = new VisVesselRepository();
            $voyagePlanRepository = new VisVoyagePlanRepository();
            $stateRepository = new VisRtzStateRepository();
            $visVesselModel = $repository->first(["imo" => $model->imo]);
            $voyagePlanModel = null;
            $toVoyagePlanModel = null;
            if ($visVesselModel) {
                $voyagePlanModel = $voyagePlanRepository->first(
                    ["from_service_id" => $visVesselModel->service_id],
                    "time DESC"
                );
                $toVoyagePlanModel = $voyagePlanRepository->first(
                    ["to_service_id" => $visVesselModel->service_id],
                    "time DESC"
                );
            }
            // also port communicates to vessel about voyage plans, eg. rta
            // TODO: we must keep track of RTAs separately?
            if ($toVoyagePlanModel && $toVoyagePlanModel->time > $voyagePlanModel->time) {
                $voyagePlanModel = $toVoyagePlanModel;
            }
            $out["vis_service_id"] = $visVesselModel ? $visVesselModel->service_id : null;

            //TODO: remove vis_status from port call in db since not used
            $out["vis_rtz_state"] =
                $voyagePlanModel && $voyagePlanModel->rtz_state
                ? $stateRepository->getStateNameWithStateId($voyagePlanModel->rtz_state)
                : null
            ;

            if ($out["vis_rtz_state"]) {
                $out["badges"][] = [
                    "type" => "default",
                    "value" => str_replace("_", " ", $out["vis_rtz_state"])
                ];
            }
        }

        return $out;
    }

    public function portCallTimelineObject(int $id)
    {
        $repository = new PortCallRepository();
        $model = $repository->get($id);

        if ($model === null) {
            throw new InvalidParameterException("Invalid port call ID: " . $id);
        }

        $data = $this->buildPortCallTimelineObject($model);
        $out = [];
        foreach ($data["portcalls"][0]["events"] as $event) {
            $out = array_merge($out, $event["timestamps"]);
        };
        return [
            "ship" => $data["ship"],
            "timestamps" => $out
        ];
    }

    public function portCallTimelineObjectCsv(int $id, string $time_zone)
    {
        $dateTools = new DateTools();

        $ns = getenv("NAMESPACE");
        $lng = getenv("LANGUAGE");

        $csvHeader = '"IMO","Name","Nationality","From","To","Next","Berth","Event","Time"';

        $rawData = $this->portCallTimelineObject($id);
        $vesselName = empty($rawData["ship"]["vessel_name"]) ? "" : $rawData["ship"]["vessel_name"];
        $vesselImo = empty($rawData["ship"]["imo"]) ? "" : $rawData["ship"]["imo"];

        $commonCsv = "";
        $shipValues = ["imo", "vessel_name", "nationality", "from_port", "to_port", "next_port", "berth_name"];
        foreach ($shipValues as $shipValue) {
            $commonCsv .= empty($rawData["ship"][$shipValue]) ? '""' : '"' . $rawData["ship"][$shipValue] . '"';
            $commonCsv .= ",";
        }

        $csv = $csvHeader . PHP_EOL;
        foreach ($rawData["timestamps"] as $timestamp) {
            $csvLine = $commonCsv;

            $time = '""';
            if (!empty($timestamp["time"])) {
                $time = '"' . $dateTools->isoDate($timestamp["time"], $time_zone) . '"';
            }
            $timestampName = $timestamp["time_type"] . "_" . $timestamp["state"];
            $timestampName = str_replace("_", " ", $timestampName);

            $translationService = new TranslationService();
            $translated = $translationService->getValueFor($ns, $lng, $timestampName);
            if (!empty($translated)) {
                $timestampName = $translated;
            }
            $timestampName = '"' . str_replace('"', " ", $timestampName) . '"';

            $csvLine .= $timestampName . "," . $time;

            $csv .= $csvLine . PHP_EOL;
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'
            . $id . '-'
            . $vesselImo . '-'
            . urlencode($vesselName)
            . '.csv";');
        echo($csv);
        exit(0);
    }

    private function portCallModelToRtaTimestamp(PortCallModel $model)
    {
        if ($model->rta) {
            return [
                "time_type" => "Recommended",
                "state" => "Arrival_Vessel_PortArea",
                "time" => $model->rta,
                "created_at" => $model->rta("updated_at"),
                "created_by" => $model->rta("updated_by"),
                "payload" => [],
                "weight" => 1
            ];
        }
        return null;
    }

    private function portCallModelToLiveEtaTimestamp(PortCallModel $model)
    {
        if ($model->liveEta("updated_by")) {
            return [
                "time_type" => "LiveEstimated",
                "state" => "Arrival_Vessel_PortArea",
                "time" => $model->live_eta,
                "created_at" => $model->liveEta("updated_at"),
                "created_by" => $model->liveEta("updated_by"),
                "payload" => [],
                "weight" => 1
            ];
        }
        return null;
    }

    private function buildPortCallTimelineObject(PortCallModel $model)
    {
        $client = new ClientService();
        $data = $client->getTimestampsByPortCall($model->id);

        $service = new PortCallTemplateService();
        $template = $service->get(getenv("NAMESPACE"));

        $factory = new PortCallFactory();
        $liveEtaTimestamp = $this->portCallModelToLiveEtaTimestamp($model);
        if ($liveEtaTimestamp) {
            $data["timestamps"][] = $liveEtaTimestamp;
        }
        $rtaTimestamp = $this->portCallModelToRtaTimestamp($model);
        if ($rtaTimestamp) {
            $data["timestamps"][] = $rtaTimestamp;
        }

        $weightMap = [];
        $weightMap[] = [];
        $factoryTimestamps = [];
        foreach ($data["timestamps"] as $timestamp) {
            $oldWeight = -1;
            $newWeight = $timestamp["weight"];
            $timeType = $timestamp["time_type"];
            $state = $timestamp["state"];
            if (isset($weightMap[$timeType][$state])) {
                $oldWeight = $weightMap[$timeType][$state];
            } else {
                $weightMap[$timeType][$state] = $newWeight;
            }
            $heavier = false;
            if ($newWeight >= $oldWeight) {
                $weightMap[$timeType][$state] = $newWeight;
                $heavier = true;
            }

            if ($heavier) {
                $factoryTimestamps[] = $timestamp;
            }
        }

        return
        [
            "ship" => $this->fillPortCallDatas($model), // note: index 'ship' is used still in ui
            "portcalls" => [$factory->timestampsToPortCall($template, $factoryTimestamps)]
        ];
    }

    private function filterHiddenImosAndLocodes(array $portCallModels): array
    {
        $res = [];
        $vesselRepository = new VesselRepository();
        $hiddenImos = $vesselRepository->getImosWithVisibility(false);

        $visibleLocodes = explode(",", getenv("VISIBLE_UNLOCODES"));
        # Add empty string to view unknown to_port port calls also
        $visibleLocodes[] = "";

        foreach ($portCallModels as $portCallModel) {
            if (!in_array($portCallModel->imo, $hiddenImos)
                && in_array($portCallModel->to_port, $visibleLocodes)) {
                $res[] = $portCallModel;
            }
        }

        return $res;
    }

    private function ongoingPortCallModels()
    {
        $repository = new PortCallRepository();
        return $this->filterHiddenImosAndLocodes($repository->ongoingPortCalls());
    }

    public function ongoingPortCallImoByStatusAndEta()
    {
        $repository = new PortCallRepository();
        $models = $this->filterHiddenImosAndLocodes($repository->ongoingPortCalls());
        return array_map(function (PortCallModel $model) {
            return [
                "imo" => $model->imo,
                "state" => $model->status,
                "current_eta" => $model->current_eta
            ];
        }, $models);
    }

    public function list($limit, $offset, $sort = "next_event_ts", $search = "")
    {
        $query = [];
        if ($search) {
            if (ctype_digit($search) && preg_match("/[0-9]{7,}/", $search)) {
                $query = ["imo" => $search];
            } elseif (preg_match("/^\^/", $search)) {
                $query = ["vessel_name" => ["ilike" => substr($search, 1) . "%"]];
            } else {
                $query = ["vessel_name" => ["ilike" => "%" . $search . "%"]];
            }
        }
        $repository = new PortCallRepository();
        $list = $repository->listPaginated($query, $offset, $limit, $sort);
        foreach ($list['data'] as $k => $model) {
            $list['data'][$k] = $this->fillPortCallDatas($model);
        }
        return $list;
    }

    public function timestamps($port_call_id, $limit, $offset, $sort)
    {
        $repository = new TimestampRepository();
        $data = $repository->listPaginated(["port_call_id" => $port_call_id], $offset, $limit, $sort);
        $data["data"] = $repository->pretty($data["data"]);
        return $data;
    }

    public function timestampsWithoutPortCall($imo, $limit, $offset, $sort)
    {
        $repository = new TimestampRepository();
        $data = $repository->listPaginated(["imo" => $imo, "port_call_id" => null], $offset, $limit, $sort);
        $data["data"] = $repository->pretty($data["data"]);
        return $data;
    }
    private function timeFromData($data)
    {
        return array_key_exists("ship", $data) && array_key_exists("next_event", $data["ship"])
            ? strtotime($data["ship"]["next_event"]["ts"])
            : 0
        ;
    }
    public function portCallsOngoing()
    {
        $stateService = $this->stateService;
        // $stateService->triggerPortCalls(); // TODO: create dev flag for this?
        $portCalls = $stateService->getSet(StateService::LATEST_PORT_CALLS, function () use ($stateService) {
            $this->markPortCallsAsDoneIfDeparted();
            $portCallModels = $this->ongoingPortCallModels();
            $data = array_map(function (PortCallModel $portCallModel) {
                return $this->buildPortCallTimelineObject($portCallModel);
            }, $portCallModels);
            usort($data, function ($a, $b) {
                $ts1 = $this->timeFromData($a);
                $ts2 = $this->timeFromData($b);
                return $ts1 > $ts2;
            });
            return $data;
        });
        $session = new Session();
        $user = $session->user();
        $pinnedVessels = [];
        if ($user) {
            $pinnedVessels = $stateService->getSet(StateService::PINNED_VESSELS . "." . $user->id, function () {
                $pinnedVesselService = new PinnedVesselService();
                $res = $pinnedVesselService->getVesselIds();
                return $res;
            });
        }
        $res["portcalls"] = $portCalls;
        $res["pinned_vessels"] = $pinnedVessels;

        return $res;
    }

    private function returnIfExists($key, array $array)
    {
        return array_key_exists($key, $array) ? $array[$key] : null;
    }

    public function markPortCallsAsDoneIfDeparted(int $offsetMinutes = 180)
    {
        $repository = new PortCallRepository();
        $models = $repository->list(["status" => PortCallModel::STATUS_DEPARTED], 0, 100);
        $tools = new DateTools();
        $changes = [];
        foreach ($models as $model) {
            if ($tools->differenceSeconds($model->atd, $tools->now()) >= 60 * $offsetMinutes) {
                $model->status = PortCallModel::STATUS_DONE;
                $repository->save($model);
                $changes[] = ["port_call_id" => $model->id, "imo" => $model->imo ];
            }
        }
        return ["count" => sizeof($changes), "status_changed" => $changes];
    }

    private function closePortCallIfDeparted(int $portCallId, int $offsetMinutes = 180, bool $forceClose = false)
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($portCallId);

        if ($portCallModel->status === PortCallModel::STATUS_DEPARTED) {
            $dateTools = new DateTools();

            $age = $dateTools->differenceSeconds($portCallModel->atd, $dateTools->now());
            if ($age >= $offsetMinutes * 60 || $forceClose) {
                $portCallModel->status = PortCallModel::STATUS_DONE;
                $portCallRepository->save($portCallModel);
            }
        }
    }

    private function departOldPortCall(int $portCallId, int $offsetDays = 30)
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($portCallId);

        if ($portCallModel->status !== PortCallModel::STATUS_DEPARTED) {
            $end = null;
            if (!empty($portCallModel->atd)) {
                $end = $portCallModel->atd;
            } elseif (!empty($portCallModel->current_ptd)) {
                $end = $portCallModel->current_ptd;
            } elseif (!empty($portCallModel->current_etd)) {
                $end = $portCallModel->current_etd;
            } elseif (!empty($portCallModel->ata)) {
                $end = $portCallModel->ata;
            } elseif (!empty($portCallModel->current_pta)) {
                $end = $portCallModel->current_pta;
            } elseif (!empty($portCallModel->current_eta)) {
                $end = $portCallModel->current_eta;
            }

            $dateTools = new DateTools();
            if ($end !== null) {
                $age = $dateTools->differenceSeconds($end, $dateTools->now());
                if ($age > $offsetDays * 24 * 60 * 60) {
                    $portCallModel->status = PortCallModel::STATUS_DEPARTED;
                    $portCallModel->atd = $end;
                    $portCallModel->next_event_title = PortCallModel::NEXT_EVENT_ATD;
                    $portCallModel->next_event_ts = $portCallModel->atd;
                    $portCallRepository->save($portCallModel);
                }
            }
        }
    }

    private function resolveIfVisAndRequestVoagePlan(PortCallModel $portCallModel, PortCallHelperModel $helperModel)
    {
        if ($portCallModel->getIsVis()) {
            // we got timestamp via vis for this port call already
        } else {
            $repository = new VisVesselRepository();
            $model = $repository->first(["imo" => $helperModel->imo()]);
            if ($model) {
                // known as vis vessel but not shared yet voyage plan since since no timestamp yet for is
                // TODO: is this safe to once we get eta?
                // TODO: or should it be requested later?
                $service = new VisService();
                $messageService = new VisMessageService();
                $service->sendTextMessage(
                    $model->service_id,
                    "Port Activity Application",
                    "Share voyage plan request",
                    $messageService->automaticMessageForNoVoyagePlan()
                );
            }
        }
    }

    public function scanPortCall(int $portCallId, bool $forceScan = false, bool $suppressExternalMessages = false): bool
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($portCallId);

        if (empty($portCallModel)) {
            return false;
        }

        $dateTools = new DateTools();

        if ($forceScan) {
            $portCallModel->clear();
        }

        $timestampRepository = new TimestampRepository();
        $timestamps = $timestampRepository->listNoLimit(["port_call_id" => $portCallId], 0);

        if (empty($timestamps)) {
            $portCallRepository->deletePortCallById($portCallId);
            return true;
        }

        $previousEtd = $portCallModel->current_etd;
        $first_eta = false;
        $fallback_atd = null;
        $at_berth = false;
        $departing = false;
        $departed = false;
        $weightMap = [];
        $weightMap[] = [];
        foreach ($timestamps as $timestamp) {
            $oldWeight = -1;
            $newWeight = $timestamp->weight;
            if (isset($weightMap[$timestamp->time_type_id][$timestamp->state_id])) {
                $oldWeight = $weightMap[$timestamp->time_type_id][$timestamp->state_id];
            } else {
                $weightMap[$timestamp->time_type_id][$timestamp->state_id] = $newWeight;
            }
            $heavier = false;
            if ($newWeight >= $oldWeight) {
                $weightMap[$timestamp->time_type_id][$timestamp->state_id] = $newWeight;
                $heavier = true;
            }

            $prettyTimestamp = new TimestampPrettyModel();
            $prettyTimestamp->setFromTimestamp($timestamp);
            $helperModel = PortCallHelperModel::fromTimeStampModel($prettyTimestamp);

            if (empty($portCallModel->vessel_name)) {
                $portCallModel->vessel_name = $helperModel->vesselName();
            }

            if ($heavier) {
                if ($helperModel->isEta()) {
                    if (!$first_eta) {
                        $first_eta = true;
                        $portCallModel->first_eta = $helperModel->time();
                    }
                    $portCallModel->current_eta = $helperModel->time();
                    if (!$suppressExternalMessages && $portCallModel->voyage_plan_request === null) {
                        $this->resolveIfVisAndRequestVoagePlan($portCallModel, $helperModel);
                        $portCallModel->voyage_plan_request = $dateTools->now();
                    }
                } elseif ($helperModel->isAta()) {
                    $portCallModel->ata = $helperModel->time();
                } elseif ($helperModel->isEtd()) {
                    if (!$portCallModel->first_etd) {
                        $portCallModel->first_etd = $helperModel->time();
                    }
                    $portCallModel->current_etd = $helperModel->time();
                } elseif ($helperModel->isAtd()) {
                    $portCallModel->atd = $helperModel->time();
                } elseif ($helperModel->isPtd()) {
                    $portCallModel->current_ptd = $helperModel->time();
                }

                if ($helperModel->isAtBerth()) {
                    $at_berth = true;
                } elseif ($helperModel->isDeparting()) {
                    $departing = true;
                } elseif ($helperModel->hasDeparted()) {
                    $departed = true;
                    if (empty($fallback_atd)) {
                        $fallback_atd = $helperModel->time();
                    }
                }
            }

            $payloadMapping = [
                "from_port" => "from_port",
                "to_port" => "to_port",
                "next_port" => "next_port",
                "mmsi" => "mmsi",
                "call_sign" => "call_sign",
                "vessel_loa" => "loa",
                "vessel_beam" => "beam",
                "vessel_draft" => "draft",
                "net_weight" => "net_weight",
                "gross_weight" => "gross_weight",
                "nationality" => "nationality",
                "berth" => "berth",
                "email" => "eta_form_email",
                "berth_name" => "berth_name",
                "slot_reservation_id" => "slot_reservation_id",
                "slot_reservation_status" => "slot_reservation_status",
                "rta_window_start" => "rta_window_start",
                "rta_window_end" => "rta_window_end",
                "laytime" => "laytime"
            ];

            foreach ($payloadMapping as $k => $v) {
                if ($heavier) {
                    $portCallModel->$v = $helperModel->payload($k) ?: $portCallModel->$v;
                } else {
                    if (empty($portCallModel->$v)) {
                        $portCallModel->$v = $helperModel->payload($k) ?: $portCallModel->$v;
                    }
                }
            }
            if ($helperModel->payload("source") === "vis") {
                $portCallModel->setIsVis(true);
                // poll service so it searched and stored to local db
                $service = new VisService();
                $service->getService($helperModel->imo());
            }
        }

        if ($departed) {
            if (empty($portCallModel->atd)) {
                if (empty($fallback_atd)) {
                    error_log("Cannot resolve proper ATD for departed port call: " . $portCallModel->id);
                    $portCallModel->atd = $dateTools->now();
                } else {
                    $portCallModel->atd = $fallback_atd;
                }
            }
            $portCallModel->status = PortCallModel::STATUS_DEPARTED;
            $portCallModel->next_event_title = PortCallModel::NEXT_EVENT_ATD;
            $portCallModel->next_event_ts = $portCallModel->atd;
        } elseif ($departing) {
            $portCallModel->status = PortCallModel::STATUS_DEPARTING;
            $portCallModel->next_event_title = PortCallModel::NEXT_EVENT_ETD;
            $portCallModel->next_event_ts = $portCallModel->current_etd;
        } elseif ($at_berth) {
            $portCallModel->status = PortCallModel::STATUS_AT_BERTH;
            $portCallModel->next_event_title = PortCallModel::NEXT_EVENT_ETD;
            $portCallModel->next_event_ts = $portCallModel->current_etd;
        } else {
            $portCallModel->status = PortCallModel::STATUS_ARRIVING;
            $portCallModel->next_event_title = PortCallModel::NEXT_EVENT_ETA;
            $portCallModel->next_event_ts = $portCallModel->current_eta;
        }

        // If port call does not use master ID, it is clustered port call
        // Update master start and end from current ETA and ETD
        if (empty($portCallModel->master_id)) {
            $portCallModel->master_start = $portCallModel->current_eta;
            $portCallModel->master_end = $portCallModel->current_etd;
        }

        $portCallRepository->save($portCallModel);

        // Update port call ID to slot reservation repository
        if ($portCallModel->slot_reservation_id !== null) {
            $slotReservationRepository = new SlotReservationRepository();
            $slotReservationRepository->updatePortCallId($portCallModel->slot_reservation_id, $portCallModel->id);
        }

        $this->departOldPortCall($portCallModel->id);
        $this->closePortCallIfDeparted($portCallModel->id);

        if (!$suppressExternalMessages && !empty($portCallModel->current_etd)) {
            if (!empty($portCallModel->next_port) && $portCallModel->current_etd !== $previousEtd) {
                # Send ETD to next port
                $outboundVesselTools = new OutboundVesselTools();
                try {
                    $outboundVesselTools->sendEtdToNextPort(
                        $portCallModel->imo,
                        $portCallModel->vessel_name,
                        $portCallModel->next_port,
                        $portCallModel->current_etd
                    );
                } catch (\Exception $e) {
                    error_log("Failed to send ETD to next port. Error: " . $e);
                }
            }
        }

        return true;
    }

    public function forceScanPortCall(int $port_call_id)
    {
        if (!$this->scanPortCall($port_call_id, true, true)) {
            return ["result" => "ERROR"];
        }

        return ["result" => "OK"];
    }

    public function rebuildPortCalls(int $imo)
    {
        $timestampClusterTools = new TimestampClusterTools(new CurlRequest());
        $timestampClusters = $timestampClusterTools->getTimestampClustersForImo($imo);

        if (empty($timestampClusters)) {
            return ["result" => "OK"];
        }

        $timestampRepository = new TimestampRepository();
        $timestampRepository->untrashByImo($imo);
        $timestampRepository->nullPortCallsByImo($imo);

        $portCallRepository = new PortCallRepository();
        $portCallRepository->deletePortCallsByImo($imo);

        $clusterId = -1;
        $portCallId = null;
        $portCallIds = [];
        foreach ($timestampClusters as $key => $value) {
            $timestamp = $timestampRepository->get($key);

            if ($clusterId !== $value) {
                $clusterId = $value;

                $portCallModel = new PortCallModel();
                $portCallModel->imo = $timestamp->imo;
                # Dummy values since these cannot be null, will be fixed later
                $portCallModel->status = PortCallModel::STATUS_ARRIVING;
                $portCallModel->first_eta = $timestamp->time;
                $portCallModel->current_eta = $timestamp->time;
                $portCallId = $portCallRepository->save($portCallModel);
                $portCallIds[] = $portCallId;
            }

            $timestamp->port_call_id = $portCallId;
            $timestampRepository->save($timestamp);
        }

        foreach ($portCallIds as $portCallId) {
            $this->scanPortCall($portCallId, false, true);
        }

        // Master source is in use
        // Scan database for first master ETA
        // and rebuild port calls again using master data
        if (!empty($this->masterSource)) {
            $query = [];
            $query["imo"] = $imo;
            $query["payload->>'source'"] = ["in" => $this->masterSource];
            $query["payload->>'external_id'"] = "NOT NULL";
            $query["time_type_id"] = TimestampPrettyModel::timeTypeId("Estimated");
            $query["state_id"] = TimestampPrettyModel::stateId("Arrival_Vessel_PortArea");

            $timestampModel = $timestampRepository->first($query, "time");

            if ($timestampModel === null) {
                return ["result" => "OK"];
            }

            // All timestamps after first master ETA are
            // attached to port calls using master data
            $query = [];
            $query["imo"] = $imo;
            $query["time"] = ["gte" => $start = $timestampModel->time];

            $timestampModels = $timestampRepository->list($query, 0, 10000);

            // Null clustered port call IDs
            $timestampIds = [];
            $portCallIds = [];
            foreach ($timestampModels as $timestampModel) {
                $timestampIds[] = $timestampModel->id;
                $portCallIds[] = $timestampModel->port_call_id;
            }
            $timestampRepository->nullPortCallsByIds($timestampIds);

            // Null port call IDs with same port call ID as the already nulled
            // This is needed because some timestamps might be before master start
            // but still have port call ID
            $deletePortCallIds = array_unique($portCallIds);
            foreach ($deletePortCallIds as $deletePortCallId) {
                $query = [];
                $query["port_call_id"] = $deletePortCallId;
                $timestampModels = $timestampRepository->list($query, 0, 10000);
                $timestampIds = [];
                foreach ($timestampModels as $timestampModel) {
                    $timestampIds[] = $timestampModel->id;
                }
                $timestampRepository->nullPortCallsByIds($timestampIds);
            }

            // Finally delete clustered port calls
            $portCallRepository->delete($deletePortCallIds);

            // Query the orphaned timestamps
            $query = [];
            $query["imo"] = $imo;
            $query["port_call_id"] = null;
            $orphanTimestamps = $timestampRepository->list($query, 0, 10000);

            // Open port calls by parsing master data from each orphan timestamp
            foreach ($orphanTimestamps as $orphanTimestamp) {
                $this->parseMasterData($orphanTimestamp);
            }

            // Attach orphan timestamps to port calls
            $this->timestampsToPortCalls($imo, false);
        }

        return ["result" => "OK"];
    }

    public function forceClosePortCall(int $port_call_id)
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($port_call_id);

        if (empty($portCallModel)) {
            return ["result" => "ERROR"];
        }

        $end = null;
        if (!empty($portCallModel->atd)) {
            $end = $portCallModel->atd;
        } elseif (!empty($portCallModel->current_ptd)) {
            $end = $portCallModel->current_ptd;
        } elseif (!empty($portCallModel->current_etd)) {
            $end = $portCallModel->current_etd;
        } elseif (!empty($portCallModel->ata)) {
            $end = $portCallModel->ata;
        } elseif (!empty($portCallModel->current_pta)) {
            $end = $portCallModel->current_pta;
        } elseif (!empty($portCallModel->current_eta)) {
            $end = $portCallModel->current_eta;
        } else {
            $dateTools = new DateTools();
            $end = $dateTools->now();
        }

        $dateTools = new DateTools();
        $age = $dateTools->differenceSeconds($end, $dateTools->now());
        if ($age < 0) {
            $end = $dateTools->now();
        }

        $portCallModel->status = PortCallModel::STATUS_DEPARTED;
        $portCallModel->atd = $end;
        $portCallModel->next_event_title = PortCallModel::NEXT_EVENT_ATD;
        $portCallModel->next_event_ts = $portCallModel->atd;
        $portCallRepository->save($portCallModel);

        $this->closePortCallIfDeparted($portCallModel->id, 0, true);

        return ["result" => "OK"];
    }

    private function timestampToPortCall(
        int $timestampId,
        int $portCallId,
        bool $isFromAgent = false,
        bool $ignoreStatus = false
    ): bool {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($portCallId);

        $timestampRepository = new TimestampRepository();
        $timestamp = $timestampRepository->get($timestampId);

        // Timestamp would alter closed port call
        // Trash timestamp
        if (!$ignoreStatus && $portCallModel->status === PortCallModel::STATUS_DONE) {
            $timestamp->setIsTrash(true);
            $timestampRepository->save($timestamp);
            return false;
        }

        // Save
        $timestamp->port_call_id = $portCallModel->id;
        $timestampRepository->save($timestamp);

        // Send notification if we received timestamp from Agent service
        // and there are no heavier timestamps
        if ($isFromAgent) {
            $query = [];
            $query["imo"] = $timestamp->imo;
            $query["time_type_id"] = $timestamp->time_type_id;
            $query["state_id"] = $timestamp->state_id;
            $query["port_call_id"] = $timestamp->port_call_id;
            $query["is_trash"] = "f";
            $query["weight"] = ["gt" => $timestamp->weight];

            if ($timestampRepository->first($query) === null) {
                $prettyTimestamp = new TimestampPrettyModel();
                $prettyTimestamp->setFromTimestamp($timestamp);
                $helperModel = PortCallHelperModel::fromTimeStampModel($prettyTimestamp);
                $pushService = new PushNotificationService();
                $pushService->sendVessel($helperModel);
            }
        }

        return true;
    }

    private function timestampsToPortCallsWithClustering(int $imo, bool $isFromAgent = false)
    {
        $timestampClusterTools = new TimestampClusterTools(new CurlRequest());
        $timestampClusters = $timestampClusterTools->getTimestampClustersForImo($imo);

        if (empty($timestampClusters)) {
            return ["result" => "ERROR"];
        }

        $timestampRepository = new TimestampRepository();
        $portCallRepository = new PortCallRepository();

        $clusterId = -1;
        $portCallId = null;
        $orphanTimestamps = [];
        $portCallsInClusters = [];
        foreach ($timestampClusters as $key => $value) {
            if ($clusterId !== $value) {
                $clusterId = $value;
            }

            $timestamp = $timestampRepository->get($key);

            // Timestamp has no associated port call ID
            // Add it to orphan list if it is not trash
            if ($timestamp->port_call_id === null && !$timestamp->getIsTrash()) {
                $orphanTimestamps[] = $key;
            }

            // Timestamp has associated port call ID
            // Add port call ID to timestamp cluster ID list
            if ($timestamp->port_call_id !== null) {
                $portCallsInClusters[$clusterId][] = $timestamp->port_call_id;
            }
        }

        // Remove duplicates from cluster ID -> port call ID mappings
        foreach ($portCallsInClusters as $key => $value) {
            $portCallsInClusters[$key] = array_unique($value);
        }

        $modifiedPortCallIds = [];
        foreach ($orphanTimestamps as $orphanTimestamp) {
            $clusterId = $timestampClusters[$orphanTimestamp];

            // Orphan cluster ID does not have associated port call ID
            // Open new port call
            if (!isset($portCallsInClusters[$clusterId])) {
                $timestamp = $timestampRepository->get($orphanTimestamp);

                $portCallModel = new PortCallModel();
                $portCallModel->imo = $timestamp->imo;
                # Dummy values since these cannot be null, will be fixed later
                $portCallModel->status = PortCallModel::STATUS_ARRIVING;
                $portCallModel->first_eta = $timestamp->time;
                $portCallModel->current_eta = $timestamp->time;

                $portCallId = $portCallRepository->save($portCallModel);
                $portCallsInClusters[$clusterId][] = $portCallId;
            }

            // Orphan cluster ID has only one associated port call ID
            // Attach timestamp to port call
            if (count($portCallsInClusters[$clusterId]) === 1) {
                $portCallId = $portCallsInClusters[$clusterId][0];
                if ($this->timestampToPortCall($orphanTimestamp, $portCallId, $isFromAgent)) {
                    $modifiedPortCallIds[] = $portCallId;
                }
            }

            // Orphan cluster ID has multiple associated port call IDs
            // This is erroneous case
            // Just attach the timestamp to last port call ID in list
            if (count($portCallsInClusters[$clusterId]) > 1) {
                error_log("Clustering mismatch for imo: " . $imo);

                $portCallId = end($portCallsInClusters[$clusterId]);
                if ($this->timestampToPortCall($orphanTimestamp, $portCallId, $isFromAgent)) {
                    $modifiedPortCallIds[] = $portCallId;
                }
            }
        }

        // Scan all modified port calls
        $modifiedPortCallIds = array_unique($modifiedPortCallIds);
        foreach ($modifiedPortCallIds as $modifiedPortCallId) {
            $this->scanPortCall($modifiedPortCallId, false, !$isFromAgent);
        }

        return ["result" => "OK"];
    }

    public function timestampsToPortCalls(int $imo, bool $isFromAgent = false, bool $ignoreStatus = false)
    {
        if (empty($this->masterSource)) {
            return $this->timestampsToPortCallsWithClustering($imo, $isFromAgent);
        }

        $timestampRepository = new TimestampRepository();
        $portCallRepository = new PortCallRepository();

        $orphanTimestamps = [];
        // Find orphan timestamps
        $query = [];
        $query["imo"] = $imo;
        $query["port_call_id"] = null;
        $orphanTimestamps = $timestampRepository->list($query, 0, 1000);

        $dateTools = new DateTools();

        $modifiedPortCallIds = [];
        foreach ($orphanTimestamps as $orphanTimestamp) {
            $query = [];
            $query["imo"] = $imo;
            $startTime = $dateTools->addIsoDuration($orphanTimestamp->time, $this->masterStartBufferDuration);
            $endTime = $dateTools->subIsoDuration($orphanTimestamp->time, $this->masterEndBufferDuration);
            $query["master_start"] = ["lte" => $startTime];
            $query["master_end"] = ["gte" => $endTime];
            $portCallModel = $portCallRepository->first($query, "master_end DESC");

            if ($portCallModel !== null) {
                $portCallId = $portCallModel->id;
                if ($this->timestampToPortCall($orphanTimestamp->id, $portCallId, $isFromAgent, $ignoreStatus)) {
                    $modifiedPortCallIds[] = $portCallId;
                }
            }
        }

        // Scan all modified port calls
        $modifiedPortCallIds = array_unique($modifiedPortCallIds);
        foreach ($modifiedPortCallIds as $modifiedPortCallId) {
            $this->scanPortCall($modifiedPortCallId, false, !$isFromAgent);
        }

        return ["result" => "OK"];
    }

    public function parseMasterData(TimestampModel $timestampModel)
    {
        if (empty($this->masterSource)) {
            return;
        }

        if (empty($timestampModel->payload)) {
            return;
        }
        $payload = json_decode($timestampModel->payload, true);

        if (empty($payload["source"])) {
            return;
        }

        if (empty($payload["external_id"])) {
            return;
        }

        $source = $payload["source"];
        if (!in_array($source, $this->masterSource)) {
            return;
        }

        $timestampPrettyModel = new TimestampPrettyModel();
        $timestampPrettyModel->setFromTimestamp($timestampModel);

        if ($timestampPrettyModel->time_type !== "Estimated") {
            return;
        }

        if ($timestampPrettyModel->state !== "Arrival_Vessel_PortArea" &&
            $timestampPrettyModel->state !== "Departure_Vessel_Berth") {
            return;
        }

        $masterId = $payload["external_id"];

        $portCallRepository = new PortCallRepository();

        $query = [];
        $query["master_id"] = $masterId;

        // Check if port call with matching master ID exists
        $portCallModel = $portCallRepository->first($query, "id DESC");

        // If no matching master ID, check if timestamp fits within existing masterless port call
        // If fits, do nothing, since timestamp can be associated to existing port call
        // This can happen if there are clustered port calls
        $dateTools = new DateTools();
        if ($portCallModel === null) {
            $query = [];
            $query["imo"] = $timestampModel->imo;
            $startTime = $dateTools->addIsoDuration($timestampModel->time, $this->masterStartBufferDuration);
            $endTime = $dateTools->subIsoDuration($timestampModel->time, $this->masterEndBufferDuration);
            $query["master_id"] = null;
            $query["master_start"] = ["lte" => $startTime];
            $query["master_end"] = ["gte" => $endTime];
            $portCallModel = $portCallRepository->first($query);

            if ($portCallModel !== null) {
                return;
            }
        }

        // Open new port call
        if ($portCallModel === null) {
            $portCallModel = new PortCallModel();
            $portCallModel->imo = $timestampPrettyModel->imo;
            $portCallModel->status = PortCallModel::STATUS_ARRIVING;
            $portCallModel->first_eta = $timestampPrettyModel->time;
            $portCallModel->current_eta = $timestampPrettyModel->time;
            $portCallModel->master_id = $masterId;
        }

        // Update master start or end times depending on state
        // if port call range is not manually changed
        if (!$portCallModel->getMasterManual()) {
            if ($timestampPrettyModel->state === "Arrival_Vessel_PortArea") {
                $portCallModel->master_start = $timestampPrettyModel->time;
            } elseif ($timestampPrettyModel->state === "Departure_Vessel_Berth") {
                $portCallModel->master_end = $timestampPrettyModel->time;
            }
        }

        $portCallRepository->save($portCallModel);
    }

    public function getPortCallRange(int $port_call_id)
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($port_call_id);

        if (empty($portCallModel)) {
            throw new InvalidParameterException("Invalid port call ID: " . $port_call_id);
        }

        $res = [];
        $res["port_call_id"] = $portCallModel->id;
        $res["start"] = $portCallModel->master_start;
        $res["end"] = $portCallModel->master_end;
        $res["master_id"] = $portCallModel->master_id;
        $res["master_manual"] = $portCallModel->getMasterManual();

        return $res;
    }

    public function setPortCallRange(int $port_call_id, string $start, string $end)
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($port_call_id);

        if (empty($portCallModel)) {
            throw new InvalidParameterException("Invalid port call ID: " . $port_call_id);
        }

        $dateTools = new DateTools();

        if (!$dateTools->isValidIsoDateTime($start)) {
            throw new InvalidParameterException("Invalid start time: " . $start);
        }

        if (!$dateTools->isValidIsoDateTime($end)) {
            throw new InvalidParameterException("Invalid end time: " . $end);
        }

        $startDateTime = new DateTime($start);
        $endDateTime = new DateTime($end);

        if ($startDateTime > $endDateTime) {
            throw new InvalidParameterException("Start time must be before end time");
        }

        $start = $dateTools->isoDate($start);
        $end = $dateTools->isoDate($end);

        // Update new range
        $portCallModel->master_start = $start;
        $portCallModel->master_end = $end;
        $portCallModel->setMasterManual(true);
        $portCallRepository->save($portCallModel);

        $modifiedPortCallIds = [];
        $modifiedPortCallIds[] = $port_call_id;

        $timestampRepository = new TimeStampRepository();

        // Null port call IDs from all current timestamps
        $query = [];
        $query["port_call_id"] = $portCallModel->id;
        $timestampModels = $timestampRepository->list($query, 0, 10000);
        $timestampIds = [];
        foreach ($timestampModels as $timestampModel) {
            $timestampIds[] = $timestampModel->id;
        }
        $timestampRepository->nullPortCallsByIds($timestampIds);

        // Null port call IDs of timestamps that fall within new range
        $timestampRepository = new TimeStampRepository();
        $query = [];
        $query["imo"] = $portCallModel->imo;
        $startTime = $dateTools->subIsoDuration($start, $this->masterStartBufferDuration);
        $endTime = $dateTools->addIsoDuration($end, $this->masterEndBufferDuration);
        $query["time"] = ["gte" => $startTime, "lte" => $endTime];
        $timestampModels = $timestampRepository->list($query, 0, 10000);
        $timestampIds = [];
        foreach ($timestampModels as $timestampModel) {
            if ($timestampModel->port_call_id !== null) {
                $modifiedPortCallIds[] = $timestampModel->port_call_id;
            }
            $timestampIds[] = $timestampModel->id;
        }
        $timestampRepository->nullPortCallsByIds($timestampIds);

        // Attach orphans without taking into account port call state
        // so the orphans are not all trashed
        $this->timestampsToPortCalls($portCallModel->imo, false, true);

        // Force scan all modified port call IDs
        // to update data and delete port calls without timestamps
        $scanPortCallIds = array_unique($modifiedPortCallIds);

        foreach ($scanPortCallIds as $scanPortCallId) {
            $this->scanPortCall($scanPortCallId, true, true);
        }
    }
}
