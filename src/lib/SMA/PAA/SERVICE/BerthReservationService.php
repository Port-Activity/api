<?php
namespace SMA\PAA\SERVICE;

use DateTime;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\ORM\BerthReservationRepository;
use SMA\PAA\ORM\BerthReservationModel;
use SMA\PAA\ORM\BerthReservationTypeModel;
use SMA\PAA\ORM\BerthRepository;
use SMA\PAA\ORM\BerthModel;
use SMA\PAA\SERVICE\SlotReservationService;

class BerthReservationService implements IBerthReservationService
{
    private function validateInputs(
        int $berthId,
        string $reservationStart,
        string $reservationEnd,
        string $berthReservationType = null,
        int $slotReservationId = null,
        int $updateId = null
    ) {
        if ($berthId !== null) {
            $berthRepository = new BerthRepository();
            $berthModel = $berthRepository->get($berthId);
    
            if ($berthModel === null) {
                throw new InvalidParameterException("Given berth ID is invalid: " . $berthId);
            }

            if (!$berthModel->getIsNominatable()) {
                throw new InvalidParameterException("Given berth ID cannot be nominated: " . $berthId);
            }
        }

        $dateTools = new DateTools();
        if (!$dateTools->isValidIsoDateTime($reservationStart)) {
            throw new InvalidParameterException("Given reservation start is not in ISO format: " . $reservationStart);
        }

        if (!$dateTools->isValidIsoDateTime($reservationEnd)) {
            throw new InvalidParameterException("Given reservation end is not in ISO format: " . $reservationEnd);
        }

        $startTime = new DateTime($reservationStart);
        $endTime = new DateTime($reservationEnd);

        if ($startTime > $endTime) {
            throw new InvalidParameterException("Reservation start must be before reservation end");
        }

        if ($berthReservationType !== null) {
            if (BerthReservationTypeModel::id($berthReservationType) === null) {
                throw new InvalidParameterException(
                    "Given berth reservation type is invalid: " . $berthReservationType
                );
            }
        }

        if ($slotReservationId !== null) {
            $slotReservationRepository = new SlotReservationRepository();
            $slotReservationModel = $slotReservationRepository->get($slotReservationId);
    
            if ($slotReservationModel === null) {
                throw new InvalidParameterException("Given slot reservation ID is invalid: " . $slotReservationId);
            }
        }

        $berthReservationRepository = new BerthReservationRepository();
        if (!$berthReservationRepository->checkIfFree($berthId, $reservationStart, $reservationEnd, $updateId)) {
            throw new InvalidParameterException(
                "Given time slot is not free: " . $reservationStart . " - " . $reservationEnd
            );
        }
    }

    public function add(
        int $berth_id,
        string $berth_reservation_type,
        string $reservation_start,
        string $reservation_end,
        int $slot_reservation_id = null
    ): array {
        $this->validateInputs(
            $berth_id,
            $reservation_start,
            $reservation_end,
            $berth_reservation_type,
            $slot_reservation_id
        );

        $repository = new BerthReservationRepository();
        $model = new BerthReservationModel();
        $type = BerthReservationTypeModel::id($berth_reservation_type);

        $dateTools = new DateTools();

        $model->set(
            $berth_id,
            $type,
            $dateTools->isoDate($reservation_start),
            $dateTools->isoDate($reservation_end),
            $slot_reservation_id
        );
        $repository->save($model);
        $slotReservationService = new SlotReservationService();
        $slotReservationService->resolve();

        return ["result" => "OK"];
    }

    public function list(
        int $berth_id,
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array {
        $repository = new BerthReservationRepository();

        $query = [];
        $query["public.berth_reservation.berth_id"] = $berth_id;

        $joins = [];
        $joins["BerthReservationTypeRepository"] = [
            "values" => ["readable_name" => "readable_berth_reservation_type"],
            "join" => ["berth_reservation_type_id" => "id"]
        ];
        $joins["SlotReservationRepository"] = [
            "values" => ["vessel_name" => "vessel_name"],
            "join" => ["slot_reservation_id" => "id"]
        ];

        $query["complex_select"] = $repository->buildJoinSelect($joins);

        if (!empty($search)) {
            if (preg_match("/^\^/", $search)) {
                $query["vessel_name"] = ["ilike" => substr($search, 1) . "%"];
            } else {
                $query["vessel_name"] = ["ilike" => "%" . $search . "%"];
            }
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "reservation_start";

        return $repository->listPaginated($query, $offset, $limit, $sort);
    }

    public function update(
        int $id,
        string $reservation_start,
        string $reservation_end
    ): array {
        $repository = new BerthReservationRepository();
        $model = $repository->get($id);
        if ($model === null) {
            throw new InvalidParameterException("Given berth reservation ID is invalid: " . $id);
        }

        if ($model->berth_reservation_type_id !== BerthReservationTypeModel::id("port_blocked")) {
            throw new InvalidParameterException("Only port block reservations can be updated");
        }

        $this->validateInputs($model->berth_id, $reservation_start, $reservation_end, null, null, $id);

        $dateTools = new DateTools();

        $model->set(
            $model->berth_id,
            $model->berth_reservation_type_id,
            $dateTools->isoDate($reservation_start),
            $dateTools->isoDate($reservation_end),
            $model->slot_reservation_id
        );

        $repository->save($model);
        $slotReservationService = new SlotReservationService();
        $slotReservationService->resolve();

        return ["result" => "OK"];
    }

    public function delete(int $id): array
    {
        $repository = new BerthReservationRepository();
        $model = $repository->get($id);
        if ($model === null) {
            throw new InvalidParameterException("Given berth reservation ID is invalid: " . $id);
        }

        if ($model->berth_reservation_type_id !== BerthReservationTypeModel::id("port_blocked")) {
            throw new InvalidParameterException("Only port block reservations can be deleted");
        }

        $repository->delete([$model->id]);
        $slotReservationService = new SlotReservationService();
        $slotReservationService->resolve();

        return ["result" => "OK"];
    }

    public function get(int $id): ?BerthReservationModel
    {
        $repository = new BerthReservationRepository();

        $query = [];
        $query["public.berth_reservation.id"] = $id;

        $joins = [];
        $joins["BerthReservationTypeRepository"] = [
            "values" => ["readable_name" => "readable_berth_reservation_type"],
            "join" => ["berth_reservation_type_id" => "id"]
        ];
        $joins["SlotReservationRepository"] = [
            "values" => ["vessel_name" => "vessel_name"],
            "join" => ["slot_reservation_id" => "id"]
        ];

        $query["complex_select"] = $repository->buildJoinSelect($joins);

        $res = $repository->list($query, 0, 1);

        return empty($res) ? null : $res[0];
    }
}
