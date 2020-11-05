<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;
use DateTimeImmutable;
use DateInterval;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\TOOL\EmailTools;
use SMA\PAA\ORM\SettingRepository;
use SMA\PAA\ORM\BerthRepository;
use SMA\PAA\SERVICE\EmailService;
use SMA\PAA\SERVICE\SlotReservationService;

class BerthReservationRepository extends OrmRepository
{
    private $portTimeZone;
    private $travelDurationToBerth;
    private $rtaWindowDuration;
    private $laytimeBufferDuration;
    private $portOperatorEmails;

    public function __construct()
    {
        $this->portTimeZone = getenv("PORT_DEFAULT_TIME_ZONE") ?: "UTC";

        $settingRepository = new SettingRepository();
        $this->travelDurationToBerth = $settingRepository->getSetting("queue_travel_duration_to_berth")->value;
        $this->rtaWindowDuration = $settingRepository->getSetting("queue_rta_window_duration")->value;
        $this->laytimeBufferDuration = $settingRepository->getSetting("queue_laytime_buffer_duration")->value;

        $emailTools = new EmailTools();
        $emails = str_replace(",", " ", $settingRepository->getSetting("port_operator_emails")->value);
        if ($emailTools->parseAndValidate($emails)) {
            $this->portOperatorEmails = $emailTools->emailsStringToArray($emails);
        } else {
            $this->portOperatorEmails = [];
        }

        parent::__construct(__CLASS__);
    }

    private function sendBerthBlockShiftEmail(BerthReservationModel $model)
    {
        $berthRepository = new BerthRepository();
        $berthModel = $berthRepository->get($model->berth_id);

        $berthCode = "";
        $berthName = "";
        if ($berthModel !== null) {
            $berthCode = $berthModel->code;
            $berthName = $berthModel->name;
        }

        $dateTools = new DateTools();
        $subject = "Berth " . $berthCode . " block #" . $model->id . " shifted";
        $heading1 = "Berth " . $berthName . " block #" . $model->id . " has been shifted due to laytime change";
        $heading2 = "New berth block time";
        $paragraph = $dateTools->isoDate($model->reservation_start, $this->portTimeZone)
                     . " - "
                     . $dateTools->isoDate($model->reservation_end, $this->portTimeZone);

        $textBody =
            $heading1 . "\n\n" .
            $heading2 . "\n" .
            $paragraph . "\n";

        $htmlBody =
            "<h2>" . $heading1 . "</h2>" .
            "<h3>" . $heading2 . "</h3>" .
            "<p>" . $paragraph . "</p>";

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

    public function checkIfFree(
        int $berthId,
        string $reservationStart,
        string $reservationEnd,
        int $skipId = null
    ): bool {
        $query = [];
        $query["complex_query"] = "berth_id = ? AND ("
        . "(? >= reservation_start AND ? <= reservation_end)"
        . " OR (? >= reservation_start AND ? <= reservation_end)"
        . " OR (reservation_start >= ? AND reservation_start <= ?)"
        . " OR (reservation_end >= ? AND reservation_end <= ?))";

        $query["complex_args"] = [
            $berthId,
            $reservationStart, $reservationStart,
            $reservationEnd, $reservationEnd,
            $reservationStart, $reservationEnd,
            $reservationStart, $reservationEnd,
        ];

        if ($skipId !== null) {
            $skipQuery = "id != ? AND " . $query["complex_query"];
            $query["complex_query"] = $skipQuery;
            array_unshift($query["complex_args"], $skipId);
        }

        if ($this->count($query) !== 0) {
            return false;
        }

        return true;
    }

    public function maximumFreeIntervalForSlotReservation(
        int $slotReservationId
    ): ?DateInterval {
        $query = [];
        $query["slot_reservation_id"] = $slotReservationId;
        $model1 = $this->first($query);

        if ($model1 === null) {
            throw new InvalidArgumentException("Invalid slot reservation ID: " . $slotReservationId);
        }

        $query = [];
        $query["berth_id"] = $model1->berth_id;
        $query["reservation_start"] = ["gt" => $model1->reservation_start];
        $model2 = $this->first($query, "reservation_start");

        if ($model2 === null) {
            return null;
        }

        $end = new DateTimeImmutable($model2->reservation_start);
        $start = new DateTimeImmutable($model1->reservation_start);

        // Subtract buffers and additional 1 minute to avoid overlap
        $end = $end->sub(new DateInterval($this->rtaWindowDuration));
        $end = $end->sub(new DateInterval($this->laytimeBufferDuration));
        $end = $end->sub(new DateInterval("PT1M"));
        return $start->diff($end);
    }

    public function reserveForSlotReservation(
        string $startTime,
        string $laytime,
        string $maxTime,
        array $berthIds
    ): ?BerthReservationModel {
        $nominatableBerthIds = [];

        // Start time is the ETA to synchronisation point
        // We need to add the travel time to berth since we are reserving berths
        $start = new DateTimeImmutable($startTime);
        $start = $start->add(new DateInterval($this->travelDurationToBerth));
        $max = new DateTimeImmutable($maxTime);
        $lay = new DateInterval($laytime);
        $layBuffer = new DateInterval($this->laytimeBufferDuration);

        $startTime = $start->format("Y-m-d\TH:i:sP");
        $maxTime = $max->format("Y-m-d\TH:i:sP");

        // Filter to include only nominatable berths
        foreach ($berthIds as $berthId) {
            $berthRepository = new BerthRepository();
            $berthModel = $berthRepository->get($berthId);
            if ($berthModel !== null) {
                if ($berthModel->getIsNominatable()) {
                    $nominatableBerthIds[] = $berthModel->id;
                }
            }
        }

        // Find gaps from nominatable berths
        $firstAvailables = [];
        foreach ($nominatableBerthIds as $nominatableBerthId) {
            // Query existing reservations that fall within requested start and max times
            $query = [];
            $query["complex_query"] = "berth_id = ? AND ("
            . "(reservation_start >= ? AND reservation_start <= ?)"
            . " OR (reservation_end >= ? AND reservation_end <= ?))";

            $query["complex_args"] = [
                $nominatableBerthId,
                $startTime, $maxTime,
                $startTime, $maxTime,
            ];

            $models = $this->list($query, 0, 100000, "reservation_start");

            // Find gaps using start - previous end
            // PHP cannot compare date intervals so we use absolute dates
            // Default is start -1 minute since we need to add 1 minute to found start
            // to avoid overlapping with existing starts
            $prevEnd = $start->sub(new DateInterval("PT1M"));

            foreach ($models as $model) {
                $resStart = new DateTimeImmutable($model->reservation_start);
                $neededStart = $prevEnd->add($lay);
                $neededStart = $neededStart->add($layBuffer);
                // There has to be 2 minute additional buffer so we don't overlap
                $neededStart = $neededStart->add(new DateInterval($this->rtaWindowDuration));
                $neededStart = $neededStart->add(new DateInterval("PT2M"));
                // If gap is large enough then store it and break out
                if ($resStart >= $neededStart) {
                    $firstAvailables[$nominatableBerthId] = $prevEnd->add(new DateInterval("PT1M"));
                    break;
                }
                $prevEnd = new DateTimeImmutable($model->reservation_end);
            }
            // If gap still not found then final check from max time to previous end
            // This will also find the result when we have no existing reservations
            // In that case we return the given startTime which is optimal case
            if (!isset($firstAvailables[$nominatableBerthId])) {
                $neededStart = $prevEnd->add($lay);
                $neededStart = $neededStart->add($layBuffer);
                $neededStart = $neededStart->add(new DateInterval($this->rtaWindowDuration));
                $neededStart = $neededStart->add(new DateInterval("PT2M"));
                if ($max >= $neededStart) {
                    $firstAvailables[$nominatableBerthId] = $prevEnd->add(new DateInterval("PT1M"));
                }
            }
        }

        // Can't find free slot
        if (empty($firstAvailables)) {
            return null;
        }

        $model = null;
        asort($firstAvailables);
        // We still loop through all berths just in case there has been race condition
        foreach ($firstAvailables as $k => $v) {
            $newStart = $v;
            $newEnd = $newStart->add($lay);
            $newEnd = $newEnd->add($layBuffer);
            $newEnd = $newEnd->add(new DateInterval($this->rtaWindowDuration));

            if ($this->checkIfFree($k, $newStart->format("Y-m-d\TH:i:sP"), $newEnd->format("Y-m-d\TH:i:sP"))) {
                $model = new BerthReservationModel();
                $type = BerthReservationTypeModel::id("vessel_reserved");

                $model->set(
                    $k,
                    $type,
                    $newStart->format("Y-m-d\TH:i:sP"),
                    $newEnd->format("Y-m-d\TH:i:sP"),
                );
                $this->save($model);

                break;
            }
        }

        return $model;
    }

    public function updateFromSlotReservation(
        int $slotReservationId,
        string $laytime,
        string $newRtaWindowStart = null
    ): bool {
        $query = [];
        $query["slot_reservation_id"] = $slotReservationId;
        $model = $this->first($query);

        if ($model === null) {
            throw new InvalidArgumentException("Invalid slot reservation ID: " . $slotReservationId);
        }

        $dateTools = new DateTools;

        $newStart = null;
        if ($newRtaWindowStart === null) {
            $newStart = $model->reservation_start;
        } else {
            $newStart = $dateTools->addIsoDuration($newRtaWindowStart, $this->travelDurationToBerth);
        }

        $newEnd = $dateTools->addIsoDuration($newStart, $laytime);
        $newEnd = $dateTools->addIsoDuration($newEnd, $this->laytimeBufferDuration);
        $newEnd = $dateTools->addIsoDuration($newEnd, $this->rtaWindowDuration);

        if (!$this->checkIfFree($model->berth_id, $newStart, $newEnd, $model->id)) {
            return false;
        }

        $model->reservation_start = $newStart;
        $model->reservation_end = $newEnd;
        $this->save($model);

        return true;
    }

    public function pushStackForwards(
        int $slotReservationId,
        DateInterval $delta,
        string $newRtaWindowStart = null
    ) {
        $dateTools = new DateTools();

        $query = [];
        $query["slot_reservation_id"] = $slotReservationId;
        $model = $this->first($query);

        if ($model === null) {
            throw new InvalidArgumentException("Invalid slot reservation ID: " . $slotReservationId);
        }

        $start = null;
        if ($newRtaWindowStart === null) {
            $start = $model->reservation_start;
        } else {
            $start = $dateTools->addIsoDuration($newRtaWindowStart, $this->travelDurationToBerth);
        }

        $cumulativeDelta = new DateInterval("P0D");

        $query = [];
        $query["berth_id"] = $model->berth_id;
        $query["reservation_start"] = ["gt" => $start];
        $query["id"] = ["neq" => $model->id];

        $models = $this->list($query, 0, 100000, "reservation_start");

        $affectedModels = [];
        $prevEnd = null;
        foreach ($models as $model) {
            if ($prevEnd !== null) {
                $neededStart = $prevEnd->add($delta);
                $resStart = new DateTimeImmutable($model->reservation_start);
                $cumulativeDelta = $dateTools->addDateInterval($cumulativeDelta, $prevEnd->diff($resStart));
                $cumulativeDelta = $dateTools->subDateInterval($cumulativeDelta, new DateInterval("PT1M"));
                if ($dateTools->compareDateInterval($cumulativeDelta, $delta) >= 0) {
                    break;
                }
            }
            $prevEnd = new DateTimeImmutable($model->reservation_end);
            $innerModel = [];
            $innerModel["model"] = $model;
            $innerModel["delta"] = $dateTools->subDateInterval($delta, $cumulativeDelta);
            $affectedModels[] = $innerModel;
        }

        $affectedModels = array_reverse($affectedModels);

        $slotReservationService = new SlotReservationService();
        foreach ($affectedModels as $affectedModel) {
            $oldStart = new DateTimeImmutable($affectedModel["model"]->reservation_start);
            $oldEnd = new DateTimeImmutable($affectedModel["model"]->reservation_end);
            $newStart = $oldStart->add($affectedModel["delta"]);
            $newEnd = $oldEnd->add($affectedModel["delta"]);
            $affectedModel["model"]->reservation_start = $newStart->format("Y-m-d\TH:i:sP");
            $affectedModel["model"]->reservation_end = $newEnd->format("Y-m-d\TH:i:sP");
            $this->save($affectedModel["model"]);

            if ($affectedModel["model"]->berth_reservation_type_id ===
                BerthReservationTypeModel::id("vessel_reserved")) {
                $newSlotStart = $dateTools->subIsoDuration(
                    $affectedModel["model"]->reservation_start,
                    $this->travelDurationToBerth
                );
                $slotReservationService->forceRtaShift(
                    $affectedModel["model"]->slot_reservation_id,
                    $newSlotStart
                );
            } elseif ($affectedModel["model"]->berth_reservation_type_id ===
                      BerthReservationTypeModel::id("port_blocked")) {
                $this->sendBerthBlockShiftEmail($affectedModel["model"]);
            }
        }
    }
}
