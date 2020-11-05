<?php
namespace SMA\PAA\SERVICE;

use InvalidArgumentException;
use DateTime;
use DateTimeZone;
use SMA\PAA\Session;
use SMA\PAA\AuthenticationException;
use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\ORM\TimestampTimeTypeRepository;
use SMA\PAA\ORM\TimestampStateRepository;
use SMA\PAA\ORM\TimestampRepository;
use SMA\PAA\ORM\TimestampModel;
use SMA\PAA\ORM\VesselRepository;
use SMA\PAA\ORM\VesselModel;
use SMA\PAA\ORM\LogisticsTimestampRepository;
use SMA\PAA\ORM\LogisticsTimestampModel;
use SMA\PAA\ORM\PortCallRepository;
use SMA\PAA\ORM\VisNotificationRepository;
use SMA\PAA\ORM\VisNotificationModel;
use SMA\PAA\ORM\VisMessageRepository;
use SMA\PAA\ORM\VisMessageModel;
use SMA\PAA\ORM\VisRtzStateRepository;
use SMA\PAA\ORM\VisVoyagePlanRepository;
use SMA\PAA\ORM\VisVoyagePlanModel;
use SMA\PAA\ORM\VisVesselRepository;
use SMA\PAA\ORM\VisVesselModel;
use SMA\PAA\ORM\SlotReservationModel;
use SMA\PAA\TOOL\ImoTools;
use SMA\PAA\TOOL\VisTools;
use SMA\PAA\TOOL\ResendTools;
use SMA\PAA\TOOL\AinoTools;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\SERVICE\InboundVesselService;
use SMA\PAA\SERVICE\SlotReservationService;
use SMA\PAA\SERVICE\TimestampApiKeyWeightService;

class AgentService
{
    private function sendAinoRemoteServiceTransaction(
        int $userId,
        bool $success,
        array $result,
        int $imo = null,
        array $meta = null
    ) {
        $ainoTools = new AinoTools();
        $ainoTools->sendAinoTransactionTimestampFromRemoteService(
            $userId,
            $success,
            isset($imo) ? ["imo" => $imo] : [],
            isset($meta) ? $meta : [],
            $ainoTools->resultChecksum($result)
        );
    }

    private function sendAinoSaveTransaction(bool $success, string $payload, array $result, int $imo = null)
    {
        $ainoTools = new AinoTools();
        $ainoTools->sendAinoTransactionSaveTimestamp(
            $success,
            $payload,
            isset($imo) ? ["imo" => $imo] : [],
            [],
            $ainoTools->resultChecksum($result)
        );
    }

    public function verifyActualIsNotInFuture(string $time_type, string $time, string $now): bool
    {
        $FUTURE_TOLERANCE_IN_MINUTES = 10;
        if ($time_type === "Actual") {
            $tools = new DateTools();
            if ($tools->differenceSeconds($time, $now) < -1 * $FUTURE_TOLERANCE_IN_MINUTES  * 60) {
                throw new InvalidArgumentException(
                    "Time type is actual, but time is in future."
                    . " Now: " . $now
                    . ", Time: " . $time
                );
            }
        }

        return true;
    }

    public function timestamp(
        int $imo,
        string $vessel_name,
        string $time_type,
        string $state,
        string $time,
        array $payload
    ) {
        // Note: get_defined_vars has to be the very first function call,
        // so we get only function parameters as resend data
        $resendData = get_defined_vars();

        $session = new Session();
        if ($session->getApiRole() === "master") {
            $resendTools = new ResendTools();
            $userId = $session->userId();
            $resendTools->resend($userId, $resendData);
        }

        $convertedTime = null;
        $verification = false;

        try {
            $tools = new DateTools();
            $convertedTime = $this->verifyAndConvertTime($time);
            $verification =
                $this->verifyData($imo, $vessel_name, $time_type, $state, $payload)
                && $this->verifyActualIsNotInFuture($time_type, $convertedTime, $tools->now());
        } catch (\Exception $e) {
            // Aino failure if we get timestamp from remote service calling API directly
            if ($session->getApiRole() === "master") {
                $meta = ["message" => json_encode($resendData)];
                $meta["exception"] = json_encode($e->getMessage());
                $this->sendAinoRemoteServiceTransaction($session->userId(), false, $resendData, $imo, $meta);
            }

            throw $e;
        }

        if (isset($convertedTime) and $verification) {
            // Aino success if we get timestamp from remote service calling API directly
            if ($session->getApiRole() === "master") {
                $this->sendAinoRemoteServiceTransaction($session->userId(), true, $resendData, $imo);
            }

            $vesselModel = new VesselModel();
            $vesselModel->set($imo, $vessel_name);
            $vesselRepository = new VesselRepository();
            $vesselId = $vesselRepository->save($vesselModel);

            # Update fake IMO
            if ($imo === 0) {
                $imo = $vesselRepository->getImo($vessel_name);
            }

            $vesselModel = $vesselRepository->get($vesselId);

            $model = new TimestampModel();
            $model->set($imo, $vesselModel->vessel_name, $time_type, $state, $convertedTime, $payload);

            $repository = new TimestampRepository();

            // Check if API key can post received timestamp
            // Manually added timestamps are omitted from this check
            $timestampApiKeyWeightService = new TimestampApiKeyWeightService();
            if (!$timestampApiKeyWeightService->checkApiKeyPermission($model)) {
                throw new AuthenticationException("No permission to post given timestamp data");
            }

            if ($repository->isDuplicate($model)) {
                # Do not save duplicate. Also agent does not need to know it sent duplicate.
                return ["result" => "OK"];
            }

            try {
                $repository->save($model);
                $this->sendAinoSaveTransaction(true, "timestamp", $resendData, $imo);
            } catch (\Exception $e) {
                $this->sendAinoSaveTransaction(false, "timestamp", $resendData, $imo);

                throw $e;
            }

            # Attach all orphan timestamps for this imo to port calls
            $portCallService = new PortCallService();
            $portCallService->parseMasterData($model);
            $portCallService->timestampsToPortCalls($model->imo, true);

            return ["result" => "OK"];
        }

        // Aino failure if we get timestamp from remote service calling API directly
        if ($session->getApiRole() === "master") {
            $meta = ["message" => json_encode($resendData)];
            $this->sendAinoRemoteServiceTransaction($session->userId(), false, $resendData, $imo, $meta);
        }

        return ["result" => "ERROR"];
    }

    public function verifyAndConvertTime($timeIn): string
    {
        // TODO: improve date and time check => separate class for date handling
        $time = "";
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z$/', $timeIn)) {
            $datetime = DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $timeIn);
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})\.(\d{3})Z$/', $timeIn)) {
            $datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.v\Z", $timeIn);
        } elseif (preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(\+|\-)(\d{2}):(\d{2})$/',
            $timeIn
        )) {
            $datetime = DateTime::createFromFormat("Y-m-d\TH:i:sP", $timeIn);
        } elseif (preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})\.(\d{3})(\+|\-)(\d{2}):(\d{2})$/',
            $timeIn
        )) {
            $datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.vP", $timeIn);
        } elseif (preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(\+|\-)(\d{2}):(\d{2}.\d{3})$/',
            $timeIn
        )) {
            $datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.vP", $timeIn);
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(\+|\-)(\d{4})$/', $timeIn)) {
            $datetime = DateTime::createFromFormat("Y-m-d\TH:i:sO", $timeIn);
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})\.(\d{3})(\+|\-)(\d{4})$/', $timeIn)) {
            $datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.vO", $timeIn);
        } else {
            throw new InvalidArgumentException(
                "Invalid date format: " . $timeIn . ". "
                . "Use format YYYY-MM-DDThh:mm:ssZ, e.g. 2019-01-30T07:00:00Z' "
                . "or YYYY-MM-DDThh:mm:ss+hh:mm, e.g. 2019-01-30T07:00:00+00:00 "
                . "or YYYY-MM-DDThh:mm:ss+hhmm, e.g. 2019-01-30T07:00:00+0000."
                . "or YYYY-MM-DDThh:mm:ss.vvvZ, e.g. 2019-01-30T07:00:00.000Z' "
                . "or YYYY-MM-DDThh:mm:ss.vvv+hh:mm, e.g. 2019-01-30T07:00:00.000+00:00 "
                . "or YYYY-MM-DDThh:mm:ss.vvv+hhmm, e.g. 2019-01-30T07:00:00.000+0000."
            );
        }

        if ($datetime !== false) {
            #Convert to UTC
            $datetime->setTimeZone(new DateTimeZone("UTC"));
            $time = $datetime->format("Y-m-d\TH:i:sP");
        }

        return $time;
    }

    public function verifyAndConvertInterval($intervalIn): string
    {
        $parts = explode(":", $intervalIn);
        if (count($parts) === 2 &&
            is_numeric($parts[0]) &&
            is_numeric($parts[1]) &&
            $parts[0] >= 0 &&
            $parts[1] >= 0 &&
            $parts[1] < 60
            ) {
            return "PT" . $parts[0] . "H" . $parts[1]  . "M";
        } else {
            throw new InvalidArgumentException(
                "Invalid interval format: " . $intervalIn . ". "
                . "Use format hh:mm, e.g. 14:56"
            );
        }
    }

    public function verifyData(int $imo, string $vesselName, string $timeType, string $state, array $payload): bool
    {

        // Allow IMO to be not set
        if ($imo !== 0) {
            $imoTools = new ImoTools();
            $imoTools->isValidImo($imo);
        } else {
            if (empty($vesselName)) {
                throw new InvalidArgumentException(
                    "IMO is 0 and vessel name is empty."
                );
            }
        }

        #TODO: we must check later if we use codes from standars for timestamp types.

        #Time type must be valid
        $timeTypeRepository = new TimestampTimeTypeRepository();
        $timeTypes = $timeTypeRepository->getTimeTypeMappings();
        if (!isset($timeTypes[$timeType])) {
            throw new InvalidArgumentException(
                "Timestamp time type is not valid: " . $timeType . ". "
                . "Should be one of: " . implode(", ", array_keys($timeTypes))
            );
        }

        #State must be valid
        $stateRepository = new TimestampStateRepository();
        $states = $stateRepository->getStateMappings();
        if (!isset($states[$state])) {
            throw new InvalidArgumentException(
                "Timestamp state is not valid: " . $state . ". "
                . "Should be one of: " . implode(", ", array_keys($states))
            );
        }

        return true;
    }

    public function logisticsTimestamp(
        string $time,
        int $external_id,
        string $checkpoint,
        string $direction,
        array $front_license_plates,
        array $rear_license_plates,
        array $containers
    ) {
        // Note: get_defined_vars has to be the very first function call,
        // so we get only function parameters as resend data
        $resendData = get_defined_vars();

        $session = new Session();
        if ($session->getApiRole() === "master") {
            $resendTools = new ResendTools();
            $userId = $session->userId();
            $resendTools->resend($userId, $resendData);
        }

        $convertedTime = $this->verifyAndConvertTime($time);
        $verification = $this->verifyLogisticsData(
            $direction,
            $front_license_plates,
            $rear_license_plates,
            $containers
        );
        if (isset($convertedTime) and $verification) {
            $model = new LogisticsTimestampModel();

            $payload = [];
            $payload["external_id"] = $external_id;
            $payload["front_license_plates"] = $front_license_plates;
            $payload["rear_license_plates"] = $rear_license_plates;
            $payload["containers"] = $containers;
            $model->set($time, $checkpoint, $direction, $payload);

            $repository = new LogisticsTimestampRepository();

            if ($repository->isDuplicate($model)) {
                # Do not save duplicate. Also agent does not need to know it sent duplicate.
                return ["result" => "OK"];
            }

            try {
                $repository->save($model);
                $this->sendAinoSaveTransaction(true, "logistics-timestamp", $resendData);
            } catch (\Exception $e) {
                $this->sendAinoSaveTransaction(false, "logistics-timestamp", $resendData);

                throw $e;
            }

            return ["result" => "OK"];
        }

        return ["result" => "ERROR"];
    }

    public function verifyLogisticsData(
        string $direction,
        array $frontLicensePlates,
        array $rearLicensePlates,
        array $containers
    ): bool {
        if (!($direction === "In" || $direction === "Out")) {
            throw new InvalidArgumentException("Invalid direction value: ".$direction);
        }

        $validLicensePlateArray = ["number" => "", "nationality" => ""];

        foreach ($frontLicensePlates as $frontLicensePlate) {
            $diff = array_diff_key($validLicensePlateArray, $frontLicensePlate);
            if ($diff) {
                throw new InvalidArgumentException(
                    "Missing parameter(s) from front_license_plates: ".implode(", ", array_keys($diff))
                );
            }
            $diff = array_diff_key($frontLicensePlate, $validLicensePlateArray);
            if ($diff) {
                throw new InvalidArgumentException(
                    "Invalid parameter(s) in front_license_plates: ".implode(", ", array_keys($diff))
                );
            }
        }

        foreach ($rearLicensePlates as $rearLicensePlate) {
            $diff = array_diff_key($validLicensePlateArray, $rearLicensePlate);
            if ($diff) {
                throw new InvalidArgumentException(
                    "Missing parameter(s) from rear_license_plates: ".implode(", ", array_keys($diff))
                );
            }
            $diff = array_diff_key($rearLicensePlate, $validLicensePlateArray);
            if ($diff) {
                throw new InvalidArgumentException(
                    "Invalid parameter(s) in rear_license_plates: ".implode(", ", array_keys($diff))
                );
            }
        }

        $validContainerArray = ["identification" => ""];

        foreach ($containers as $container) {
            $diff = array_diff_key($validContainerArray, $container);
            if ($diff) {
                throw new InvalidArgumentException(
                    "Missing parameter(s) from containers: ".implode(", ", array_keys($diff))
                );
            }
            $diff = array_diff_key($container, $validContainerArray);
            if ($diff) {
                throw new InvalidArgumentException(
                    "Invalid parameter(s) in containers: ".implode(", ", array_keys($diff))
                );
            }
        }

        return true;
    }

    public function visNotifications(
        string $time,
        string $from_service_id,
        string $message_id,
        string $message_type,
        string $notification_type,
        string $subject,
        string $payload
    ) {
        $model = new VisNotificationModel();
        $model->set($time, $from_service_id, $message_id, $message_type, $notification_type, $subject, $payload);

        $repository = new VisNotificationRepository();
        $repository->save($model);

        $visTools = new VisTools(new CurlRequest());
        $visTools->visVesselDataFromServiceId($from_service_id);

        return ["result" => "OK"];
    }

    public function visMessages(
        string $time,
        string $from_service_id,
        string $to_service_id,
        string $message_id,
        string $message_type,
        string $payload
    ) {
        $model = new VisMessageModel();
        $model->set($time, $from_service_id, $to_service_id, $message_id, $message_type, $payload);

        $repository = new VisMessageRepository();
        $repository->save($model);

        $visTools = new VisTools(new CurlRequest());
        $visTools->visVesselDataFromServiceId($from_service_id);

        return ["result" => "OK"];
    }

    public function visVoyagePlans(
        string $time,
        string $from_service_id,
        string $to_service_id,
        string $message_id,
        string $message_type,
        string $rtz_state,
        string $rtz_parse_results,
        string $payload
    ) {

        $visRtzStateRepository = new VisRtzStateRepository();
        $rtzStates = $visRtzStateRepository->getStateMappings();
        if (!isset($rtzStates[$rtz_state])) {
            throw new InvalidArgumentException(
                "RTZ state is not valid: " . $rtz_state . ". "
                . "Should be one of: " . implode(", ", array_keys($rtzStates))
            );
        }

        $model = new VisVoyagePlanModel();
        $model->set(
            $time,
            $from_service_id,
            $to_service_id,
            $message_id,
            $message_type,
            $rtz_state,
            $rtz_parse_results,
            $payload
        );

        $repository = new VisVoyagePlanRepository();
        $repository->save($model);

        $visTools = new VisTools(new CurlRequest());
        $visTools->visVesselDataFromServiceId($from_service_id);

        $messageService = new VisMessageService();
        $message = $messageService->automaticMessageForVisStatusIfAny($model);
        if ($message) {
            $referenceId = $model->message_id;
            $referenceType = "RTZ";
            $area = $messageService->getSyncPointAreaString();
            $visService = new VisService();
            $visService->sendTextMessage(
                $from_service_id,
                "Port Activity Application",
                "Route inconsistent",
                $message,
                $referenceId,
                $referenceType,
                $area
            );
        }
        return ["result" => "OK"];
    }

    public function visVessels(
        int $imo,
        string $vessel_name,
        string $service_id,
        string $service_url
    ) {
        $model = new VisVesselModel();
        $model->set(
            $imo,
            $vessel_name,
            $service_id,
            $service_url
        );

        $repository = new VisVesselRepository();
        $repository->save($model);

        return ["result" => "OK"];
    }
    public function liveEta(
        int $imo,
        string $time,
        array $payload
    ) {
        $session = new Session();
        $repository = new PortCallRepository();
        $payload["updated_by"] = $session->userId();
        if ($repository->setLiveEta($imo, $time, $payload)) {
            return ["result" => "OK"];
        }
        throw new InvalidArgumentException(
            "Cannot set live eta for imo: " . $imo
        );
    }
    public function inboundVessel(
        string $time,
        int $imo,
        string $vessel_name,
        string $from_service_id
    ): array {
        $convertedTime =$this->verifyAndConvertTime($time);

        $service = new InboundVesselService();
        $service->add($time, $imo, $vessel_name, $from_service_id);

        // Everything above will throw exception on error, so we can always return OK
        return ["result" => "OK"];
    }
    public function addSlotReservation(
        string $email,
        int $imo,
        string $vessel_name,
        string $loa,
        string $beam,
        string $draft,
        string $laytime,
        string $eta
    ): array {
        $convertedLaytime =$this->verifyAndConvertInterval($laytime);
        $convertedEta =$this->verifyAndConvertTime($eta);

        $imoTools = new ImoTools();
        $imoTools->isValidImo($imo);

        $service = new SlotReservationService();
        return $service->add(
            $email,
            $imo,
            $vessel_name,
            $loa,
            $beam,
            $draft,
            $convertedLaytime,
            $convertedEta
        );
    }
    public function updateSlotReservation(
        int $id,
        string $laytime,
        string $jit_eta
    ): array {
        $convertedLaytime =$this->verifyAndConvertInterval($laytime);
        $convertedJitEta =$this->verifyAndConvertTime($jit_eta);

        $service = new SlotReservationService();
        return $service->update($id, $convertedLaytime, $convertedJitEta);
    }
    public function getSlotReservationById(int $id): ?SlotReservationModel
    {
        $service = new SlotReservationService();
        return $service->get($id);
    }
    public function cancelSlotReservation(int $id): array
    {
        $service = new SlotReservationService();
        return $service->delete($id, true, true);
    }
}
