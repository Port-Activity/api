<?php
namespace SMA\PAA\SERVICE;

use DateTime;
use DateInterval;
use SMA\PAA\Session;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\AuthenticationException;
use SMA\PAA\TOOL\EmailTools;
use SMA\PAA\TOOL\ImoTools;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\ORM\NominationRepository;
use SMA\PAA\ORM\NominationModel;
use SMA\PAA\ORM\NominationStatusModel;
use SMA\PAA\ORM\BerthRepository;
use SMA\PAA\ORM\BerthModel;
use SMA\PAA\ORM\NominationBerthRepository;
use SMA\PAA\ORM\NominationBerthModel;
use SMA\PAA\ORM\UserRepository;
use SMA\PAA\ORM\RoleRepository;
use SMA\PAA\SERVICE\SlotReservationService;

class NominationService implements INominationService
{
    private function getForCurrentUser($id): NominationModel
    {
        $session = new Session();
        $userId = $session->userId();

        $repository = new NominationRepository();
        $model = $repository->get($id);
        if ($model === null) {
            throw new InvalidParameterException(
                "Cannot find nomination instance with given id: " . $id
            );
        }
        if ($model->created_by !== $userId) {
            throw new AuthenticationException("No permission for given nomination");
        }

        return $model;
    }

    private function getForAnyUser($id): NominationModel
    {
        $repository = new NominationRepository();
        $model = $repository->get($id);
        if ($model === null) {
            throw new InvalidParameterException(
                "Cannot find nomination instance with given id: " . $id
            );
        }

        return $model;
    }

    private function validateInputs(
        string $email,
        string $imo,
        string $windowStart = null,
        string $windowEnd = null,
        array $berthIds = null,
        int $consigneeUserId = null
    ) {
        $skipWindowCheck = false;
        if ($windowStart === null || $windowEnd === null) {
            if ($windowStart !== $windowEnd) {
                throw new InvalidParameterException("Only one window date given");
            }

            $skipWindowCheck = true;
        }

        $skipBerthCheck = false;
        if ($berthIds === null) {
            $skipBerthCheck = true;
        }

        $emailTools = new EmailTools();
        $emailsToArray = $emailTools->parseAndValidate($email);
        if (!$emailsToArray) {
            throw new InvalidParameterException("Given email address is not valid: " . $email);
        }

        $imoTools = new ImoTools();
        try {
            $imoTools->isValidImo($imo);
        } catch (\Exception $e) {
            throw new InvalidParameterException("Given IMO is not valid: " . $imo);
        }

        if (!$skipWindowCheck) {
            $dateTools = new DateTools();
            if (!$dateTools->isValidIsoDateTime($windowStart)) {
                throw new InvalidParameterException("Given window start is not in ISO format: " . $windowStart);
            }

            if (!$dateTools->isValidIsoDateTime($windowEnd)) {
                throw new InvalidParameterException("Given window end is not in ISO format: " . $windowEnd);
            }

            $minTime = new DateTime();
            $minTime->setTime(0, 0)->sub(new DateInterval('P1D'));
            $startTime = new DateTime($windowStart);
            $endTime = new DateTime($windowEnd);

            if ($startTime > $endTime) {
                throw new InvalidParameterException("Window start must be before window end");
            }

            if ($minTime > $startTime) {
                throw new InvalidParameterException("Window start must be after current date");
            }

            $monthAheadDate = new DateTime();
            $monthAheadDate->setTime(23, 59, 59)->add(new DateInterval('P1M1D'));

            if ($endTime > $monthAheadDate) {
                throw new InvalidParameterException("Window end can be maximum one month in advance");
            }
        }

        if (!$skipBerthCheck) {
            $berthRepository = new BerthRepository();
            foreach ($berthIds as $berthId) {
                $berthModel = $berthRepository->get($berthId);

                if ($berthModel === null) {
                    throw new InvalidParameterException("Given berth ID is invalid: " . $berthId);
                }

                if (!$berthModel->getIsNominatable()) {
                    throw new InvalidParameterException("Given berth ID cannot be nominated: " . $berthId);
                }
            }
        }

        if ($consigneeUserId !== null) {
            $userRepository = new UserRepository();
            $userModel = $userRepository->get($consigneeUserId);

            if ($userModel === null) {
                throw new InvalidParameterException("Invalid user ID: " . $consigneeUserId);
            }

            $roleRepository = new RoleRepository();
            $consigneeRoleId = $roleRepository->mapToId("consignee");

            if ($userModel->role_id !== $consigneeRoleId) {
                throw new InvalidParameterException("Invalid user ID: " . $consigneeUserId);
            }
        }
    }

    private function addToNominationBerth(int $nominationId, array $berthIds)
    {
        $repository = new NominationBerthRepository();
        $berthPriority = 1;
        foreach ($berthIds as $berthId) {
            $model = new NominationBerthModel();
            $model->set($nominationId, $berthId, $berthPriority);
            $repository->save($model);
            $berthPriority += 1;
        }
    }

    private function attachBerths(NominationModel $nominationModel)
    {
        $berthIds = [];
        $berthNames = [];
        $nominationBerthRepository = new NominationBerthRepository();
        $nominationBerthModels = $nominationBerthRepository->listNoLimit(
            ["nomination_id" => $nominationModel->id],
            0,
            "berth_priority"
        );

        foreach ($nominationBerthModels as $nominationBerthModel) {
            $berthRepository = new BerthRepository();
            $berthModel = $berthRepository->get($nominationBerthModel->berth_id);
            if ($berthModel !== null) {
                $berthIds[] = $berthModel->id;
                $berthNames[] = $berthModel->name;
            }
        }

        $nominationModel->berth_ids = $berthIds;
        $nominationModel->berth_names = $berthNames;

        return $nominationModel;
    }

    private function commonAdd(
        string $companyName,
        string $email,
        int $imo,
        string $vesselName,
        string $windowStart,
        string $windowEnd,
        array $berthIds
    ): int {
            $nominationRepository = new NominationRepository();
            $nominationModel = new NominationModel();
            $nominationStatusId = NominationStatusModel::id("open");
            $dateTools = new DateTools();
            $nominationModel->set(
                $companyName,
                $email,
                $imo,
                $vesselName,
                $nominationStatusId,
                $dateTools->isoDate($windowStart),
                $dateTools->isoDate($windowEnd)
            );
            $nominationId = $nominationRepository->save($nominationModel);

            $this->addToNominationBerth($nominationId, $berthIds);
            $slotReservationService = new SlotReservationService();
            $slotReservationService->resolve($imo);

            return $nominationId;
    }

    private function commonUpdate(
        NominationModel $nominationModel,
        string $companyName,
        string $email,
        int $imo,
        string $vesselName,
        string $windowStart = null,
        string $windowEnd = null,
        array $berthIds = null
    ): int {
        if ($nominationModel->nomination_status_id !== NominationStatusModel::id("open")) {
            throw new InvalidParameterException("Only open nominations can be updated");
        }

        if ($windowStart === null) {
            $windowStart = $nominationModel->window_start;
        }
        if ($windowEnd === null) {
            $windowEnd = $nominationModel->window_end;
        }

        $dateTools = new DateTools();
        $nominationModel->set(
            $companyName,
            $email,
            $imo,
            $vesselName,
            $nominationModel->nomination_status_id,
            $dateTools->isoDate($windowStart),
            $dateTools->isoDate($windowEnd)
        );
        $nominationRepository = new NominationRepository();
        $nominationRepository->save($nominationModel);

        if ($berthIds !== null) {
            $nominationBerthRepository = new NominationBerthRepository();
            $nominationBerthModels = $nominationBerthRepository->listNoLimit(
                ["nomination_id" => $nominationModel->id],
                0
            );
            $nominationBerthIds = [];
            foreach ($nominationBerthModels as $nominationBerthModel) {
                $nominationBerthIds[] = $nominationBerthModel->id;
            }
            if (!empty($nominationBerthIds)) {
                $nominationBerthRepository->delete($nominationBerthIds);
            }

            $this->addToNominationBerth($nominationModel->id, $berthIds);
            $slotReservationService = new SlotReservationService();
            $slotReservationService->resolve($imo);
        }

        return $nominationModel->id;
    }

    private function commonList(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null,
        bool $ownOnly = false
    ): array {
        $this->expireNominations();

        $res = [];

        $session = new Session();
        $userId = $session->userId();

        $query = [];
        if ($ownOnly) {
            $query["public.nomination.created_by"] = $userId;
        }


        $repository = new NominationRepository();
        $joins = [];
        $joins["NominationStatusRepository"] = [
            "values" => ["readable_name" => "readable_nomination_status"],
            "join" => ["nomination_status_id" => "id"]
        ];
        $joins["UserRepository"] = [
            "values" => ["first_name" => "owner_first_name", "last_name" => "owner_last_name"],
            "join" => ["created_by" => "id"]
        ];
        $query["complex_select"] = $repository->buildJoinSelect($joins);

        if (!empty($search)) {
            if (ctype_digit($search) && preg_match("/[0-9]{7,}/", $search)) {
                $query["public.nomination.imo"] = $search;
            } elseif (preg_match("/^\^/", $search)) {
                $query["public.nomination.vessel_name"] = ["ilike" => substr($search, 1) . "%"];
            } else {
                $query["public.nomination.vessel_name"] = ["ilike" => "%" . $search . "%"];
            }
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "id";

        $rawRes = $repository->listPaginated($query, $offset, $limit, $sort);

        // TODO: Maybe this could be done with SQL?
        $res["data"] = [];
        foreach ($rawRes["data"] as $nominationModel) {
            $res["data"][] = $this->attachBerths($nominationModel);
        }
        $res["pagination"] = $rawRes["pagination"];

        return $res;
    }

    private function expireNominations()
    {
        $repository = new NominationRepository();
        $query = [];
        $query["nomination_status_id"] = ["neq" => NominationStatusModel::id("expired")];
        $dateTools = new DateTools();
        $query["window_end"] = ["lt" => $dateTools->now()];

        $models = $repository->list($query, 0, 1000);

        foreach ($models as $model) {
            $model->nomination_status_id = NominationStatusModel::id("expired");
            $repository->save($model);
        }
    }

    public function ownAdd(
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start,
        string $window_end,
        array $berth_ids
    ): array {
        $berth_ids = array_unique($berth_ids);

        $this->validateInputs($email, $imo, $window_start, $window_end, $berth_ids);

        $this->commonAdd(
            $company_name,
            $email,
            $imo,
            $vessel_name,
            $window_start,
            $window_end,
            $berth_ids
        );

        return ["result" => "OK"];
    }

    public function ownList(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array {
        return $this->commonList($limit, $offset, $sort, $search, true);
    }

    public function ownUpdate(
        int $id,
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start = null,
        string $window_end = null,
        array $berth_ids = null
    ): array {
        $nominationModel = $this->getForCurrentUser($id);

        if ($berth_ids !== null) {
            $berth_ids = array_unique($berth_ids);
        }

        $this->validateInputs($email, $imo, $window_start, $window_end, $berth_ids);

        $this->commonUpdate(
            $nominationModel,
            $company_name,
            $email,
            $imo,
            $vessel_name,
            $window_start,
            $window_end,
            $berth_ids
        );

        return ["result" => "OK"];
    }

    public function ownDelete(int $id): array
    {
        $repository = new NominationRepository();
        $nominationModel = $this->getForCurrentUser($id);

        if ($nominationModel->nomination_status_id !== NominationStatusModel::id("open")) {
            throw new InvalidParameterException("Only open nominations can be deleted");
        }

        $repository->delete([$nominationModel->id]);

        return ["result" => "OK"];
    }

    public function ownGet(int $id): NominationModel
    {
        $this->expireNominations();

        $nominationModel = $this->getForCurrentUser($id);
        return $this->attachBerths($nominationModel);
    }

    public function getAllNominatableBerths(): array
    {
        $berthRepository = new BerthRepository();
        return $berthRepository->listNoLimit(["nominatable" => true], 0, "lower(name)");
    }

    public function getConsigneeUsers(): array
    {
        $res = [];

        $roleRepository = new RoleRepository();
        $consigneeRoleId = $roleRepository->mapToId("consignee");

        $userRepository = new UserRepository();
        $rawResults = $userRepository->listNoLimit(["role_id" => $consigneeRoleId], 0, "lower(last_name)");

        foreach ($rawResults as $rawResult) {
            $innerRes = [];
            $innerRes["id"] = $rawResult->id;
            $innerRes["name"] = $rawResult->last_name . ", " . $rawResult->first_name;
            $res[] = $innerRes;
        }

        return $res;
    }

    public function addForConsignee(
        int $consignee_user_id,
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start,
        string $window_end,
        array $berth_ids
    ): array {
        $berth_ids = array_unique($berth_ids);

        $this->validateInputs($email, $imo, $window_start, $window_end, $berth_ids, $consignee_user_id);

        $nominationId = $this->commonAdd(
            $company_name,
            $email,
            $imo,
            $vessel_name,
            $window_start,
            $window_end,
            $berth_ids
        );
        // Nomination is created on behalf of some consignee
        $nominationRepository = new NominationRepository();
        $nominationModel = $nominationRepository->get($nominationId);
        $nominationModel->created_by = $consignee_user_id;
        $nominationId = $nominationRepository->save($nominationModel);

        return ["result" => "OK"];
    }

    public function update(
        int $id,
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start = null,
        string $window_end = null,
        array $berth_ids = null
    ): array {
        $nominationModel = $this->getForAnyUser($id);

        if ($berth_ids !== null) {
            $berth_ids = array_unique($berth_ids);
        }

        $this->validateInputs($email, $imo, $window_start, $window_end, $berth_ids);

        $nominationId = $this->commonUpdate(
            $nominationModel,
            $company_name,
            $email,
            $imo,
            $vessel_name,
            $window_start,
            $window_end,
            $berth_ids
        );

        return ["result" => "OK"];
    }

    public function delete(int $id): array
    {
        $repository = new NominationRepository();
        $nominationModel = $this->getForAnyUser($id);

        if ($nominationModel->nomination_status_id !== NominationStatusModel::id("open")) {
            throw new InvalidParameterException("Only open nominations can be deleted");
        }

        $repository->delete([$nominationModel->id]);

        return ["result" => "OK"];
    }

    public function get(int $id): NominationModel
    {
        $this->expireNominations();

        $nominationModel = $this->getForAnyUser($id);
        return $this->attachBerths($nominationModel);
    }

    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array {
        return $this->commonList($limit, $offset, $sort, $search, false);
    }
}
