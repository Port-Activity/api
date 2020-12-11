<?php
namespace SMA\PAA\SERVICE;

use DateTimeImmutable;
use DateInterval;
use SMA\PAA\AuthenticationException;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\TOOL\EmailTools;
use SMA\PAA\TOOL\ImoTools;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\ORM\SlotReservationRepository;
use SMA\PAA\ORM\SlotReservationModel;
use SMA\PAA\ORM\SlotReservationStatusModel;
use SMA\PAA\ORM\NominationRepository;
use SMA\PAA\ORM\NominationModel;
use SMA\PAA\ORM\NominationStatusModel;
use SMA\PAA\ORM\NominationBerthRepository;
use SMA\PAA\ORM\BerthReservationRepository;
use SMA\PAA\ORM\BerthReservationModel;
use SMA\PAA\ORM\VesselRepository;
use SMA\PAA\ORM\VesselModel;
use SMA\PAA\ORM\TimestampRepository;
use SMA\PAA\ORM\TimestampModel;
use SMA\PAA\ORM\PortCallRepository;
use SMA\PAA\ORM\PortCallModel;
use SMA\PAA\ORM\SettingRepository;
use SMA\PAA\SERVICE\JwtService;
use SMA\PAA\SERVICE\EmailService;
use SMA\PAA\SERVICE\PortCallService;
use SMA\PAA\SERVICE\TimestampApiKeyWeightService;
use SMA\PAA\SERVICE\TranslationService;

class SlotReservationService implements ISlotReservationService
{
    private $portTimeZone;
    private $jitEtaFormUrl;
    private $travelDurationToBerth;
    private $rtaWindowDuration;
    private $laytimeBufferDuration;
    private $liveEtaAlertBufferDuration;
    private $liveEtaAlertDelayDuration;
    private $portOperatorEmails;

    public function __construct()
    {
        $this->portTimeZone = getenv("PORT_DEFAULT_TIME_ZONE") ?: "UTC";
        $this->jitEtaFormUrl = getenv("JIT_ETA_FORM_URL");

        $settingRepository = new SettingRepository();
        $this->travelDurationToBerth = $settingRepository->getSetting("queue_travel_duration_to_berth")->value;
        $this->rtaWindowDuration = $settingRepository->getSetting("queue_rta_window_duration")->value;
        $this->laytimeBufferDuration = $settingRepository->getSetting("queue_laytime_buffer_duration")->value;
        $this->liveEtaAlertBufferDuration =
            $settingRepository->getSetting("queue_live_eta_alert_buffer_duration")->value;
        $this->liveEtaAlertDelayDuration = $settingRepository->getSetting("queue_live_eta_alert_delay_duration")->value;

        $emailTools = new EmailTools();
        $emails = str_replace(",", " ", $settingRepository->getSetting("port_operator_emails")->value);
        if ($emailTools->parseAndValidate($emails)) {
            $this->portOperatorEmails = $emailTools->emailsStringToArray($emails);
        } else {
            $this->portOperatorEmails = [];
        }
    }

    private function createMailContents(
        SlotReservationModel $model,
        string $token,
        string $expiryDate
    ): array {
        $t = new TranslationService();

        $res = [];
        $subject = "";
        $textBody = "";
        $htmlBody = "";
        $formUrl = $this->jitEtaFormUrl;
        $link = $formUrl . "?token=" . $token;

        $subject = $t->t("[EMAIL SLOT REQUEST] Slot request") . " #" . $model->id . " ";
        if ($model->slot_reservation_status_id === SlotReservationStatusModel::id("offered")) {
            $subject .= $t->t("[EMAIL SLOT REQUEST] offer");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("accepted")) {
            $subject .= $t->t("[EMAIL SLOT REQUEST] confirmation");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("no_nomination")) {
            $subject .= $t->t("[EMAIL SLOT REQUEST] nomination not found");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("no_free_slot")) {
            $subject .= $t->t("[EMAIL SLOT REQUEST] free slot not available");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("updated")) {
            $subject .= $t->t("[EMAIL SLOT REQUEST] updated by port");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("cancelled_by_vessel")) {
            $subject .= $t->t("[EMAIL SLOT REQUEST] cancellation confirmation");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("cancelled_by_port")) {
            $subject .= $t->t("[EMAIL SLOT REQUEST] cancelled by port");
        }

        $heading = "";
        if ($model->slot_reservation_status_id === SlotReservationStatusModel::id("offered")) {
            $heading =
                $t->t("[EMAIL SLOT REQUEST] Please send your JIT ETA " .
                "to outer port area based on RTA window given by port.");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("accepted")) {
            $heading = $t->t("[EMAIL SLOT REQUEST] Your JIT ETA to outer port area has been accepted.");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("no_nomination")) {
            $heading = $t->t("[EMAIL SLOT REQUEST] Port is unable to find nomination for your slot request.");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("no_free_slot")) {
            $heading = $t->t("[EMAIL SLOT REQUEST] Port is unable to find free slot for your request.");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("updated")) {
            $heading = $t->t("[EMAIL SLOT REQUEST] Port has updated your JIT ETA.");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("cancelled_by_vessel")) {
            $heading = $t->t("[EMAIL SLOT REQUEST] Your slot request has been cancelled by your request.");
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("cancelled_by_port")) {
            $heading = $t->t("[EMAIL SLOT REQUEST] Your slot request has been cancelled by port.");
        }

        $paragraph = $t->t("[EMAIL SLOT REQUEST] To view and update your slot request please use the link below.");
        $expiry = $t->t("[EMAIL SLOT REQUEST] This link is valid until:") . " " . $expiryDate;

        $textBody =
            $heading . "\n\n" .
            $paragraph . "\n" .
            $expiry . "\n" .
            $link;

        $htmlBody =
            "<h2>" . $heading . "</h2>" .
            "<p>" . $paragraph . "</p>" .
            "<p>" . $expiry . "</p>" .
            "<a href=\"" . $link . "\">" .
            $t->t("[EMAIL SLOT REQUEST] Slot request") . " #" . $model->id .
            "</a>";

        $res["subject"] = $subject;
        $res["text_body"] = $textBody;
        $res["html_body"] = $htmlBody;
        return $res;
    }

    private function sendMailToVessel(SlotReservationModel $model)
    {
        $emailTools = new EmailTools();
        $emailsToArray = $emailTools->parseAndValidate($model->email);
        if (!$emailsToArray) {
            throw new InvalidParameterException("Invalid email address: " . $model->email);
        }

        $privateKey = json_decode(getenv("PRIVATE_KEY_JSON"));
        $publicKey = json_decode(getenv("PUBLIC_KEY_JSON"));

        $jwtService = new JwtService($privateKey, $publicKey);
        // Generate token
        $expiresIn = 30*24*60*60;
        $token = $jwtService->encode(
            ["slot_request_id" => $model->id],
            $expiresIn
        );

        $expiryDate = date(\DateTime::ATOM, time() + $expiresIn);
        $emailService = new EmailService();
        $contents = $this->createMailContents($model, $token, $expiryDate);

        try {
            $emailService->sendEmail(
                $model->email,
                $contents["subject"],
                $contents["text_body"],
                $contents["html_body"]
            );
        } catch (\Exception $e) {
            error_log('Email sending exception: '. $e->getMessage());
            return ["result" => "ERROR"];
        }
    }

    private function addTimestamps(SlotReservationModel $model, bool $addFirstEta = false)
    {
        if ($model->slot_reservation_status_id !== SlotReservationStatusModel::id("offered") &&
           $model->slot_reservation_status_id !== SlotReservationStatusModel::id("updated") &&
           $model->slot_reservation_status_id !== SlotReservationStatusModel::id("accepted")) {
            return;
        }

        // Update vessel repository
        $vesselModel = new VesselModel();
        $vesselModel->set($model->imo, $model->vessel_name);
        $vesselRepository = new VesselRepository();
        $vesselRepository->save($vesselModel);

        $dateTools = new DateTools();

        $payload = [];
        $payload["source"] = "jit_eta_form";
        $payload["external_id"] = "slot_reservation_id" . $model->id;
        $payload["slot_reservation_id"] = $model->id;
        $payload["slot_reservation_status"] = SlotReservationStatusModel::name($model->slot_reservation_status_id);
        $payload["rta_window_start"] = $model->rta_window_start;
        $payload["rta_window_end"] = $model->rta_window_end;
        $payload["laytime"] = $model->laytime;
        $payload["loa"] = $model->loa;
        $payload["beam"] = $model->beam;
        $payload["draft"] = $model->draft;
        // We need unique dummy payload in order to pass duplicate timestamps
        $payload["dummy"] = $dateTools->now();

        $timestampModels = [];

        if ($addFirstEta) {
            $timestampModel = new TimestampModel();
            $timestampModel->set(
                $model->imo,
                $model->vessel_name,
                "Estimated",
                "Arrival_Vessel_PortArea",
                $model->eta,
                $payload
            );
            $timestampModels[] = $timestampModel;

            // We have first ETA and laytime from vessel, so we can store ETD also
            $etd = $dateTools->addIsoDuration($model->eta, $this->travelDurationToBerth);
            $etd = $dateTools->addIsoDuration($etd, $model->laytime);
            $timestampModel = new TimestampModel();
            $timestampModel->set(
                $model->imo,
                $model->vessel_name,
                "Estimated",
                "Departure_Vessel_Berth",
                $etd,
                $payload
            );
            $timestampModels[] = $timestampModel;
        }

        if ($model->slot_reservation_status_id === SlotReservationStatusModel::id("offered") ||
           $model->slot_reservation_status_id === SlotReservationStatusModel::id("updated")) {
            $timestampModel = new TimestampModel();
            $timestampModel->set(
                $model->imo,
                $model->vessel_name,
                "Recommended",
                "Arrival_Vessel_PortArea",
                $model->rta_window_start,
                $payload
            );
            $timestampModels[] = $timestampModel;
        } elseif ($model->slot_reservation_status_id === SlotReservationStatusModel::id("accepted")) {
            $timestampModel = new TimestampModel();
            $timestampModel->set(
                $model->imo,
                $model->vessel_name,
                "Planned",
                "Arrival_Vessel_PortArea",
                $model->jit_eta,
                $payload
            );
            $timestampModels[] = $timestampModel;

            // We have JIT ETA and laytime from vessel, so we can store PTD also
            $ptd = $dateTools->addIsoDuration($model->jit_eta, $this->travelDurationToBerth);
            $ptd = $dateTools->addIsoDuration($ptd, $model->laytime);
            $timestampModel = new TimestampModel();
            $timestampModel->set(
                $model->imo,
                $model->vessel_name,
                "Planned",
                "Departure_Vessel_Berth",
                $ptd,
                $payload
            );
            $timestampModels[] = $timestampModel;
        }

        $timestampRepository = new TimestampRepository();
        $timestampApiKeyWeightService = new TimestampApiKeyWeightService();

        foreach ($timestampModels as $timestampModel) {
            if (!$timestampRepository->isDuplicate($timestampModel)) {
                if (!$timestampApiKeyWeightService->checkApiKeyPermission($timestampModel)) {
                    throw new AuthenticationException("No permission to post given timestamp data");
                }
                $timestampRepository->save($timestampModel);
                $portCallService = new PortCallService();
                $portCallService->parseMasterData($timestampModel);
                $portCallService->timestampsToPortCalls($timestampModel->imo, true);
            }
        }
    }

    private function removeTimestamps(SlotReservationModel $model)
    {
        $timestampRepository = new TimestampRepository();

        $deleteTimestampIds = [];
        $nullPortCallTimestampIds = [];
        $parseMasterTimestampModels = [];

        // Find timestamps to delete and orphan
        if ($model->port_call_id !== null) {
            $query = [];
            $query["imo"] = $model->imo;
            $query["port_call_id"] = $model->port_call_id;

            $timestampModels = $timestampRepository->list($query, 0, 10000);

            foreach ($timestampModels as $timestampModel) {
                $deleted = false;
                if (isset($timestampModel->payload)) {
                    $payload = json_decode($timestampModel->payload, true);
                    if (isset($payload["slot_reservation_id"])) {
                        if ($payload["slot_reservation_id"] === $model->id) {
                            $deleteTimestampIds[] = $timestampModel->id;
                            $deleted = true;
                        }
                    }
                }

                if (!$deleted) {
                    $nullPortCallTimestampIds[] = $timestampModel->id;
                    $parseMasterTimestampModels[] = $timestampModel;
                }
            }
        }

        // Find rest of timestamps to delete
        $query = [];
        $query["imo"] = $model->imo;
        $query["port_call_id"] = null;
        $query["payload->>'slot_reservation_id'"] = $model->id;
        $timestampModels = $timestampRepository->list($query, 0, 10000);
        foreach ($timestampModels as $timestampModel) {
            $deleteTimestampIds[] = $timestampModel->id;
        }

        if (!empty($deleteTimestampIds)) {
            $timestampRepository->delete($deleteTimestampIds);
        }

        $timestampRepository->nullPortCallsByIds($nullPortCallTimestampIds);

        // Find port calls to delete
        $portCallRepository = new PortCallRepository();
        $portCallModel = null;
        if ($model->port_call_id !== null) {
            $portCallModel = $portCallRepository->get($model->port_call_id);
        } else {
            $query = [];
            $query["imo"] = $model->imo;
            $query["master_id"] = "slot_reservation_id" . $model->id;

            $portCallModel = $portCallRepository->first($query);
        }
        if ($portCallModel !== null) {
            $portCallRepository->deletePortCallById($portCallModel->id);
        }

        // Parse orphanized timestamps to open port calls with normal master data
        $portCallService = new PortCallService();
        foreach ($parseMasterTimestampModels as $parseMasterTimestampModel) {
            $portCallService->parseMasterData($parseMasterTimestampModel);
        }

        // Attach orphan timestamps to port calls
        $portCallService->timestampsToPortCalls($model->imo, false);
    }

    private function validateInputs(
        string $email = null,
        int $imo = null,
        string $laytime = null,
        string $eta = null,
        int $id = null,
        string $jitEta = null,
        string $rtaWindowStart = null
    ) {
        if ($email !== null) {
            $emailTools = new EmailTools();
            $emailsToArray = $emailTools->parseAndValidate($email);
            if (!$emailsToArray) {
                throw new InvalidParameterException("Given email address is not valid: " . $email);
            }
        }

        if ($imo !== null) {
            $imoTools = new ImoTools();
            try {
                $imoTools->isValidImo($imo);
            } catch (\Exception $e) {
                throw new InvalidParameterException("Given IMO is not valid: " . $imo);
            }
        }

        if ($laytime !== null) {
            $dateTools = new DateTools();
            if (!$dateTools->isValidIsoDuration($laytime)) {
                throw new InvalidParameterException("Given laytime is not in ISO format: " . $laytime);
            }
        }

        if ($eta !== null) {
            $dateTools = new DateTools();
            if (!$dateTools->isValidIsoDateTime($eta)) {
                throw new InvalidParameterException("Given ETA is not in ISO format: " . $eta);
            }
        }

        $slotReservationModel = null;
        if ($id !== null) {
            $slotReservationRepository = new SlotReservationRepository();
            $slotReservationModel = $slotReservationRepository->get($id);
            if ($slotReservationModel === null) {
                throw new InvalidParameterException("Cannot find slot reservation for given id: " . $id);
            }
        }

        if ($jitEta !== null && $slotReservationModel === null) {
            throw new InvalidParameterException("Cannot find slot reservation for given id: " . $id);
        }

        if ($rtaWindowStart !== null) {
            $dateTools = new DateTools();
            if (!$dateTools->isValidIsoDateTime($rtaWindowStart)) {
                throw new InvalidParameterException("Given RTA window start is not in ISO format: " . $rtaWindowStart);
            }

            $rtaWindowStart = new DateTimeImmutable($rtaWindowStart);
        }

        if ($jitEta !== null) {
            $dateTools = new DateTools();
            if (!$dateTools->isValidIsoDateTime($jitEta)) {
                throw new InvalidParameterException("Given JIT ETA is not in ISO format: " . $jitEta);
            }

            if ($rtaWindowStart === null) {
                $rtaWindowStart = new DateTimeImmutable($slotReservationModel->rta_window_start);
            }

            $rtaWindowEnd = $rtaWindowStart->add(new DateInterval($this->rtaWindowDuration));

            $jitEtaDateTime = new DateTimeImmutable($jitEta);

            if ($jitEtaDateTime < $rtaWindowStart || $jitEtaDateTime > $rtaWindowEnd) {
                throw new InvalidParameterException(
                    "Given JIT ETA is not within RTA window: " . $jitEta
                    . ", RTA window: " . $rtaWindowStart->format("Y-m-d\TH:i:sP")
                    . " - " . $rtaWindowEnd->format("Y-m-d\TH:i:sP")
                );
            }
        }
    }

    private function findOpenNomination(
        int $imo,
        string $startTime,
        string $endTime
    ): ?NominationModel {
        $nominationRepository = new NominationRepository();
        return $nominationRepository->findOpen($imo, $startTime, $endTime);
    }

    private function reserveBerth(
        NominationModel $nominationModel,
        string $startTime,
        string $laytime
    ): ?BerthReservationModel {
        $nominationBerthRepository = new NominationBerthRepository();
        $berthIds = $nominationBerthRepository->getBerthIds($nominationModel->id);

        $berthReservationRepository = new BerthReservationRepository();
        return $berthReservationRepository->reserveForSlotReservation(
            $startTime,
            $laytime,
            $nominationModel->window_end,
            $berthIds
        );
    }

    private function updateOtherRepositories(
        int $slotReservationId,
        BerthReservationModel $berthReservationModel = null,
        NominationModel $nominationModel = null
    ) {
        if ($berthReservationModel !== null) {
            $berthReservationRepository = new BerthReservationRepository();
            $berthReservationModel->slot_reservation_id = $slotReservationId;
            $berthReservationRepository->save($berthReservationModel);
        }

        if ($nominationModel !== null) {
            $nominationRepository = new NominationRepository();
            $nominationModel->nomination_status_id = NominationStatusModel::id("reserved");
            $nominationRepository->save($nominationModel);
        }
    }

    private function resolveNoNomination(int $imo = null)
    {
        $repository = new SlotReservationRepository();
        $repository->setIntervalStyleToISO8601();
        $query = [];
        if ($imo !== null) {
            $query["imo"] = $imo;
        }
        $query["slot_reservation_status_id"] = SlotReservationStatusModel::id("no_nomination");
        $models = $repository->list($query, 0, 1000);

        foreach ($models as $model) {
            $nominationId = null;
            $berthId = null;
            $rtaWindowStart = null;
            $rtaWindowEnd = null;
            $slotReservationStatusId = $model->slot_reservation_status_id;

            $dateTools = new DateTools();
            $startTime = $dateTools->isoDate($model->eta);
            $endTime = $dateTools->addIsoDuration($startTime, $model->laytime);

            $nominationModel = $this->findOpenNomination($model->imo, $startTime, $endTime);

            $berthReservationModel = null;
            if ($nominationModel === null) {
                $slotReservationStatusId = SlotReservationStatusModel::id("no_nomination");
            } else {
                $nominationId = $nominationModel->id;
                $berthReservationModel = $this->reserveBerth(
                    $nominationModel,
                    $startTime,
                    $model->laytime
                );
            }

            if ($berthReservationModel === null) {
                if ($nominationModel !== null) {
                    $slotReservationStatusId = SlotReservationStatusModel::id("no_free_slot");
                }
            } else {
                $slotReservationStatusId = SlotReservationStatusModel::id("offered");
                $berthId = $berthReservationModel->berth_id;

                $reservationStart = new DateTimeImmutable($berthReservationModel->reservation_start);
                // Berth reservation is to the berth and we need to give RTA to synchronisation point
                // So we subtract travel time from reservation start
                $reservationStart = $reservationStart->sub(new DateInterval($this->travelDurationToBerth));
                $rtaEnd = $reservationStart->add(new DateInterval($this->rtaWindowDuration));
                $rtaWindowStart = $reservationStart->format("Y-m-d\TH:i:sP");
                $rtaWindowEnd = $rtaEnd->format("Y-m-d\TH:i:sP");
            }

            $model->nomination_id = $nominationId;
            $model->berth_id = $berthId;
            $model->rta_window_start = $rtaWindowStart;
            $model->rta_window_end = $rtaWindowEnd;
            $model->slot_reservation_status_id = $slotReservationStatusId;
            $repository->save($model);

            $this->updateOtherRepositories($model->id, $berthReservationModel, $nominationModel);

            // Send mail only if status has changed
            if ($model->slot_reservation_status_id !== SlotReservationStatusModel::id("no_nomination")) {
                $this->sendMailToVessel($model);
                $this->addTimestamps($model, true);
            }
        }
    }

    private function resolveNoFreeSlot(int $imo = null)
    {
        $repository = new SlotReservationRepository();
        $repository->setIntervalStyleToISO8601();
        $query = [];
        if ($imo !== null) {
            $query["imo"] = $imo;
        }
        $query["slot_reservation_status_id"] = SlotReservationStatusModel::id("no_free_slot");
        $models = $repository->list($query, 0, 1000);

        foreach ($models as $model) {
            $berthId = null;
            $rtaWindowStart = null;
            $rtaWindowEnd = null;
            $slotReservationStatusId = $model->slot_reservation_status_id;

            $dateTools = new DateTools();
            $startTime = $dateTools->isoDate($model->eta);

            $nominationRepository = new NominationRepository();
            $nominationModel = $nominationRepository->get($model->nomination_id);

            $berthReservationModel = $this->reserveBerth(
                $nominationModel,
                $startTime,
                $model->laytime
            );

            if ($berthReservationModel === null) {
                $slotReservationStatusId = SlotReservationStatusModel::id("no_free_slot");
            } else {
                $slotReservationStatusId = SlotReservationStatusModel::id("offered");
                $berthId = $berthReservationModel->berth_id;

                $reservationStart = new DateTimeImmutable($berthReservationModel->reservation_start);
                // Berth reservation is to the berth and we need to give RTA to synchronisation point
                // So we subtract travel time from reservation start
                $reservationStart = $reservationStart->sub(new DateInterval($this->travelDurationToBerth));
                $rtaEnd = $reservationStart->add(new DateInterval($this->rtaWindowDuration));
                $rtaWindowStart = $reservationStart->format("Y-m-d\TH:i:sP");
                $rtaWindowEnd = $rtaEnd->format("Y-m-d\TH:i:sP");
            }

            $model->berth_id = $berthId;
            $model->rta_window_start = $rtaWindowStart;
            $model->rta_window_end = $rtaWindowEnd;
            $model->slot_reservation_status_id = $slotReservationStatusId;
            $repository->save($model);

            $this->updateOtherRepositories($model->id, $berthReservationModel, null);

            // Send mail only if status has changed
            if ($model->slot_reservation_status_id !== SlotReservationStatusModel::id("no_free_slot")) {
                $this->sendMailToVessel($model);
                $this->addTimestamps($model, true);
            }
        }
    }

    private function completeSlotReservations()
    {
        // Depending on status we use different column for determining when to automatically complete slot reservation
        // Cancellations are deliberately left out since cancelled reservations are already completed
        $statusTimeMap = [
            "requested" => "eta",
            "no_nomination" => "eta",
            "no_free_slot" => "eta",
            "offered" => "rta_window_start",
            "accepted" => "rta_window_start",
            "updated" => "rta_window_start"
        ];

        $allModels = [];
        $dateTools = new DateTools();
        $now = $dateTools->now();
        $nowTime = new DateTimeImmutable($now);
        $repository = new SlotReservationRepository();
        $repository->setIntervalStyleToISO8601();

        foreach ($statusTimeMap as $k => $v) {
            $query = [];
            $query["slot_reservation_status_id"] = SlotReservationStatusModel::id($k);
            $query[$v] = ["lt" => $now];
            $allModels[] = $repository->list($query, 0, 1000);
        }

        foreach ($allModels as $models) {
            foreach ($models as $model) {
                if ($model->rta_window_start !== null) {
                    $startTime = new DateTimeImmutable($model->rta_window_start);
                } else {
                    $startTime = new DateTimeImmutable($model->eta);
                }
                $endTime = $startTime->add(new DateInterval($model->laytime));
                $endTime = $endTime->add(new DateInterval($this->rtaWindowDuration));
                $endTime = $endTime->add(new DateInterval($this->travelDurationToBerth));
                $endTime = $endTime->add(new DateInterval($this->laytimeBufferDuration));

                if ($nowTime > $endTime) {
                    $model->slot_reservation_status_id = SlotReservationStatusModel::id("completed");
                    $repository->save($model);
                }
            }
        }
    }

    private function sendJitEtaAlertMail(SlotReservationModel $model)
    {
        $t = new TranslationService();

        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->get($model->port_call_id);

        $dateTools = new DateTools();

        $liveEta = "unknown";
        if ($portCallModel !== null) {
            if ($portCallModel->live_eta !== null) {
                $liveEta = $dateTools->isoDate($portCallModel->live_eta, $this->portTimeZone);
            }
        }

        $jitEta = "unknown";
        if ($model->jit_eta !== null) {
            $jitEta = $dateTools->isoDate($model->jit_eta, $this->portTimeZone);
        }

        $subject = $t->t("[EMAIL LIVE ETA ALERT] Live ETA alert for") . " " . $model->vessel_name;
        $heading =
            $t->t("[EMAIL LIVE ETA ALERT] Ship") . " " .
            $model->vessel_name . " (IMO: " . $model->imo . ") " .
            $t->t("[EMAIL LIVE ETA ALERT] JIT ETA differs from Live ETA");
        $paragraph1 = $t->t("[EMAIL LIVE ETA ALERT] JIT ETA:") . " " . $jitEta;
        $paragraph2 = $t->t("[EMAIL LIVE ETA ALERT] Live ETA:") . " " . $liveEta;

        $textBody =
            $heading . "\n\n" .
            $paragraph1 . "\n" .
            $paragraph2 . "\n";

        $htmlBody =
            "<h2>" . $heading . "</h2>" .
            "<p>" . $paragraph1 . "</p>" .
            "<p>" . $paragraph2 . "</p>";

        if ($model->nomination_id !== null) {
            $nominationRepository = new NominationRepository();
            $nominationModel = $nominationRepository->get($model->nomination_id);

            $emailTools = new EmailTools();
            if ($nominationModel !== null) {
                if ($emailTools->parseAndValidate($nominationModel->email)) {
                    $this->portOperatorEmails[] = $nominationModel->email;
                }
            }
        }

        $emailService = new EmailService();
        foreach ($this->portOperatorEmails as $portOperatorEmail) {
            try {
                $emailService->sendEmail(
                    $portOperatorEmail,
                    $subject,
                    $textBody,
                    $htmlBody
                );
            } catch (\Exception $e) {
                error_log('Email sending exception: '. $e->getMessage());
            }
        }
    }

    private function checkJitEtaDiscrepancy(SlotReservationModel $model)
    {
        if ($model->jit_eta === null || $model->jit_eta_discrepancy_time === null) {
            return;
        }

        $now = new DateTimeImmutable();
        $discrepancyTime = new DateTimeImmutable($model->jit_eta_discrepancy_time);
        $triggerTime = $discrepancyTime->add(new DateInterval($this->liveEtaAlertDelayDuration));

        if ($now > $triggerTime) {
            $repository = new SlotReservationRepository();
            $model->jit_eta_discrepancy_time = null;
            $model->jit_eta_alert_state = "red";
            $repository->save($model);

            $this->sendJitEtaAlertMail($model);
        }
    }

    public function resolve(int $imo = null)
    {
        $this->validateInputs(null, $imo);
        $this->resolveNoNomination($imo);
        $this->resolveNoFreeSlot($imo);
    }

    public function forceRtaShift(int $slotReservationId, string $newRtaStart)
    {
        $repository = new SlotReservationRepository();
        $model = $repository->get($slotReservationId);

        if ($model === null) {
            throw new InvalidParameterException("Cannot find slot reservation for given id: " . $slotReservationId);
        }

        $dateTools = new DateTools();
        $model->slot_reservation_status_id = SlotReservationStatusModel::id("updated");
        $model->rta_window_start = $newRtaStart;
        $model->rta_window_end = $dateTools->addIsoDuration($newRtaStart, $this->rtaWindowDuration);

        // JIT ETA cannot be earlier than new RTA
        if ($model->jit_eta !== null) {
            $jitEtaDateTime = new DateTimeImmutable($model->jit_eta);
            $rtaStartDateTime = new DateTimeImmutable($model->rta_window_start);

            if ($rtaStartDateTime > $jitEtaDateTime) {
                $model->jit_eta = $model->rta_window_start;
            }
        }

        $repository->save($model);
        $this->sendMailToVessel($model);
        $this->addTimestamps($model);
    }

    public function checkLiveEta(int $id, string $live_eta)
    {
        $dateTools = new DateTools();
        $repository = new SlotReservationRepository();
        $model = $repository->get($id);

        if ($model === null) {
            error_log("Invalid slot reservation ID: " . $id);
            return;
        }

        if ($model->jit_eta === null) {
            error_log("Slot reservation ID has no JIT ETA: " . $id);
            return;
        }

        $liveEta = null;
        try {
            $liveEta = new DateTimeImmutable($live_eta);
        } catch (\Exception $e) {
            error_log("Invalid live ETA: " . $live_eta);
            return;
        }

        $currentJitEta = new DateTimeImmutable($model->jit_eta);
        $maxLiveEta = $currentJitEta->add(new DateInterval($this->liveEtaAlertBufferDuration));

        $saveModel = false;
        if ($liveEta > $maxLiveEta) {
            if ($model->jit_eta_alert_state === "green") {
                $model->jit_eta_alert_state = "orange";
                $saveModel = true;
            }
            if ($model->jit_eta_discrepancy_time === null) {
                $model->jit_eta_discrepancy_time = $dateTools->now();
                $saveModel = true;
            }
        } else {
            if ($model->jit_eta_alert_state !== "green") {
                $model->jit_eta_alert_state = "green";
                $saveModel = true;
            }
            if ($model->jit_eta_discrepancy_time !== null) {
                $model->jit_eta_discrepancy_time = null;
                $saveModel = true;
            }
        }

        if ($saveModel) {
            $repository->save($model);
        }

        $this->checkJitEtaDiscrepancy($model);
    }

    public function add(
        string $email,
        int $imo,
        string $vessel_name,
        float $loa,
        float $beam,
        float $draft,
        string $laytime,
        string $eta
    ): array {
        $this->validateInputs(
            $email,
            $imo,
            $laytime,
            $eta
        );

        $nominationId = null;
        $berthId = null;
        $rtaWindowStart = null;
        $rtaWindowEnd = null;
        $jitEta = null;
        $slotReservationStatusId = SlotReservationStatusModel::id("requested");

        $dateTools = new DateTools();
        $startTime = $dateTools->isoDate($eta);
        $endTime = $dateTools->addIsoDuration($startTime, $laytime);

        $nominationModel = $this->findOpenNomination($imo, $startTime, $endTime);

        // Find free slot from berths
        $berthReservationModel = null;
        if ($nominationModel === null) {
            $slotReservationStatusId = SlotReservationStatusModel::id("no_nomination");
        } else {
            $nominationId = $nominationModel->id;
            $berthReservationModel = $this->reserveBerth(
                $nominationModel,
                $startTime,
                $laytime
            );
        }

        if ($berthReservationModel === null) {
            if ($nominationModel !== null) {
                $slotReservationStatusId = SlotReservationStatusModel::id("no_free_slot");
            }
        } else {
            $slotReservationStatusId = SlotReservationStatusModel::id("offered");
            $berthId = $berthReservationModel->berth_id;

            $reservationStart = new DateTimeImmutable($berthReservationModel->reservation_start);
            // Berth reservation is to the berth and we need to give RTA to synchronisation point
            // So we subtract travel time from reservation start
            $reservationStart = $reservationStart->sub(new DateInterval($this->travelDurationToBerth));
            $rtaEnd = $reservationStart->add(new DateInterval($this->rtaWindowDuration));
            $rtaWindowStart = $reservationStart->format("Y-m-d\TH:i:sP");
            $rtaWindowEnd = $rtaEnd->format("Y-m-d\TH:i:sP");
        }

        $repository = new SlotReservationRepository();
        $model = new SlotReservationModel();
        $model->set(
            $nominationId,
            $berthId,
            $email,
            $imo,
            $vessel_name,
            $loa,
            $beam,
            $draft,
            $laytime,
            $dateTools->isoDate($eta),
            $rtaWindowStart,
            $rtaWindowEnd,
            $jitEta,
            $slotReservationStatusId
        );
        $modelId = $repository->save($model);

        $this->updateOtherRepositories($modelId, $berthReservationModel, $nominationModel);
        $this->sendMailToVessel($model);
        $this->addTimestamps($model, true);

        return ["result" => "OK"];
    }

    public function update(
        int $id,
        string $laytime,
        string $jit_eta,
        string $rta_window_start = null
    ): array {
        $this->validateInputs(null, null, $laytime, null, $id, $jit_eta, $rta_window_start);

        $repository = new SlotReservationRepository();
        $repository->setIntervalStyleToISO8601();
        $model = $repository->get($id);

        if (!($model->slot_reservation_status_id === SlotReservationStatusModel::id("offered") ||
             $model->slot_reservation_status_id === SlotReservationStatusModel::id("accepted") ||
             $model->slot_reservation_status_id === SlotReservationStatusModel::id("updated"))) {
            throw new InvalidParameterException(
                "Invalid current slot reservation status: "
                . SlotReservationStatusModel::name($model->slot_reservation_status_id)
            );
        }

        $nominationRepository = new NominationRepository();
        $nominationModel = $nominationRepository->first(["id" => $model->nomination_id]);

        $berthReservationRepository = new BerthReservationRepository();

        $dateTools = new DateTools();
        // RTA window start can only be updated by port
        if ($rta_window_start !== null) {
            $oldStart = new DateTimeImmutable($model->rta_window_start);
            $newStart = new DateTimeImmutable($rta_window_start);
            // If RTA window start is updated we need more strict checking than with just laytime update
            if ($oldStart <> $newStart) {
                $newBerthStart = new DateTimeImmutable($rta_window_start);
                $newBerthStart = $newBerthStart->add(new DateInterval($this->travelDurationToBerth));
                $newBerthEnd = $newBerthStart->add(new DateInterval($laytime));
                $newBerthEnd = $newBerthEnd->add(new DateInterval($this->rtaWindowDuration));
                // Check that new parameters fit within assigned nomination
                $nominationStart = new DateTimeImmutable($nominationModel->window_start);
                $nominationEnd = new DateTimeImmutable($nominationModel->window_end);
                if ($newBerthStart < $nominationStart || $newBerthEnd > $nominationEnd) {
                    throw new InvalidParameterException(
                        "Reserved nomination not valid with given RTA window start: "
                        . $rta_window_start . " and laytime: " . $laytime
                    );
                }
                // Check that new parameters fit to berth and change berth reservation
                if ($berthReservationRepository->updateFromSlotReservation($model->id, $laytime, $rta_window_start)) {
                    $model->laytime = $laytime;
                    $model->jit_eta = $jit_eta;
                    $model->rta_window_start = $rta_window_start;
                    $model->rta_window_end = $dateTools->addIsoDuration($rta_window_start, $this->rtaWindowDuration);
                    $model->slot_reservation_status_id = SlotReservationStatusModel::id("updated");

                    $repository->save($model);
                    $this->sendMailToVessel($model);
                    $this->addTimestamps($model);

                    return ["result" => "OK"];
                } else {
                    throw new InvalidParameterException(
                        "Berth not free with given RTA window start: "
                        . $rta_window_start . " and laytime: " . $laytime
                    );
                }
            }
        }

        $dateInterval = $berthReservationRepository->maximumFreeIntervalForSlotReservation($model->id);

        if ($dateInterval !== null) {
            $rtaStart = new DateTimeImmutable($model->rta_window_start);
            $maxEnd = $rtaStart->add($dateInterval);
            $newEnd = $rtaStart->add(new DateInterval($laytime));
            if ($newEnd > $maxEnd) {
                $berthReservationRepository = new BerthReservationRepository();
                $delta = $maxEnd->diff($newEnd);
                $berthReservationRepository->pushStackForwards($model->id, $delta);
            }
        }

        $berthReservationRepository = new BerthReservationRepository();
        if (!$berthReservationRepository->updateFromSlotReservation(
            $model->id,
            $laytime
        )) {
            throw new InvalidParameterException("Given laytime not possible: " . $laytime);
        }

        $model->laytime = $laytime;
        $model->jit_eta = $jit_eta;

        // RTA window start can only be updated by port
        if ($rta_window_start !== null) {
            $model->slot_reservation_status_id = SlotReservationStatusModel::id("updated");
        } else {
            $model->slot_reservation_status_id = SlotReservationStatusModel::id("accepted");
        }

        $repository->save($model);
        $this->sendMailToVessel($model);
        $this->addTimestamps($model);

        return ["result" => "OK"];
    }

    public function delete(int $id, bool $cancel_only = false, bool $by_vessel = false): array
    {
        $this->validateInputs(null, null, null, null, $id);

        $repository = new SlotReservationRepository();
        $model = $repository->get($id);

        if ($by_vessel) {
            $model->slot_reservation_status_id = SlotReservationStatusModel::id("cancelled_by_vessel");
        } else {
            $model->slot_reservation_status_id = SlotReservationStatusModel::id("cancelled_by_port");
        }

        // Mark nomination as free again
        if ($model->nomination_id !== null) {
            $nominationRepository = new NominationRepository();
            $nominationModel = $nominationRepository->first(["id" => $model->nomination_id]);

            if ($nominationModel !== null) {
                $nominationModel->nomination_status_id = NominationStatusModel::id("open");
                $nominationRepository->save($nominationModel);
            }
        }

        // Remove berth reservation
        $berthReservationRepository = new BerthReservationRepository();
        $berthReservationModel = $berthReservationRepository->first(["slot_reservation_id" => $model->id]);
        if ($berthReservationModel !== null) {
            $berthReservationRepository->delete([$berthReservationModel->id]);
        }

        $model->berth_id === null;
        $model->nomination_id = null;
        $model->rta_window_start = null;
        $model->rta_window_end = null;
        $model->jit_eta = null;

        $repository->save($model);

        $this->sendMailToVessel($model);
        $this->removeTimestamps($model);

        if (!$cancel_only) {
            // TODO: Delete slot reservation model
        }

        return ["result" => "OK"];
    }

    public function get(int $id): ?SlotReservationModel
    {
        $this->completeSlotReservations();

        $repository = new SlotReservationRepository();
        $repository->setIntervalStyleToISO8601();

        $query = [];
        $query["public.slot_reservation.id"] = $id;

        $joins = [];
        $joins["SlotReservationStatusRepository"] = [
            "values" => ["name" => "slot_reservation_status"],
            "join" => ["slot_reservation_status_id" => "id"]
        ];

        $query["complex_select"] = $repository->buildJoinSelect($joins);

        $res = $repository->list($query, 0, 1);

        if (empty($res)) {
            return null;
        }

        $dateTools = new DateTools();

        $model = $res[0];
        $model->laytime = $dateTools->dateIntervalToHHMM(new DateInterval($model->laytime));
        $model->max_laytime = null;

        if ($model->berth_id !== null && $model->nomination_id !== null) {
            $nominationRepository = new NominationRepository();
            $nominationModel = $nominationRepository->first(["id" => $model->nomination_id]);

            $berthReservationRepository = new BerthReservationRepository();
            $dateInterval = $berthReservationRepository->maximumFreeIntervalForSlotReservation(
                $model->id
            );

            if ($dateInterval !== null) {
                $model->max_laytime = $dateTools->dateIntervalToHHMM($dateInterval);
            } else {
                $model->max_laytime = null;
            }
        }

        return $model;
    }

    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null,
        bool $isDashboard = false
    ): array {
        $this->completeSlotReservations();

        $repository = new SlotReservationRepository();
        $repository->setIntervalStyleToISO8601();

        $query = [];

        $joins = [];
        $joins["SlotReservationStatusRepository"] = [
            "values" => ["readable_name" => "readable_slot_reservation_status",
                         "name" => "slot_reservation_status"],
            "join" => ["slot_reservation_status_id" => "id"]
        ];
        $joins["BerthRepository"] = [
            "values" => ["name" => "berth_name"],
            "join" => ["berth_id" => "id"]
        ];

        $query["complex_select"] = $repository->buildJoinSelect($joins);

        if ($isDashboard) {
            $query["complex_query"] = "slot_reservation_status_id in (?, ?, ?)";

            $query["complex_args"] = [
                SlotReservationStatusModel::id("offered"),
                SlotReservationStatusModel::id("accepted"),
                SlotReservationStatusModel::id("updated")
            ];
        }

        if (!empty($search)) {
            if (preg_match("/^\^/", $search)) {
                $query["vessel_name"] = ["ilike" => substr($search, 1) . "%"];
            } else {
                $query["vessel_name"] = ["ilike" => "%" . $search . "%"];
            }
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "id";

        return $repository->listPaginated($query, $offset, $limit, $sort);
    }

    public function dashboardList(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array {
        $res = [];
        $res["data"] = [];
        $res["pagination"] = [];

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "rta_window_start";

        $rawResults = $this->list($limit, $offset, $sort, $search, true);

        // Decorate results from port call data
        foreach ($rawResults["data"] as $result) {
            $result->live_eta = null;
            $result->ptd = null;

            if ($result->port_call_id !== null) {
                $portCallRepository = new PortCallRepository();
                $portCallModel = $portCallRepository->get($result->port_call_id);

                if ($portCallModel !== null) {
                    $result->live_eta = $portCallModel->live_eta;
                    $result->ptd = $portCallModel->current_ptd;
                }
            }

            $res["data"][] = $result;
        }

        $res["pagination"] = $rawResults["pagination"];

        return $res;
    }
}
