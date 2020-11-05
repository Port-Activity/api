<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\PinnedVesselModel;
use SMA\PAA\ORM\PinnedVesselRepository;
use SMA\PAA\Session;

class PinnedVesselService
{
    public function getVesselIdsForUser($id)
    {
        if ($id) {
            $repository = new PinnedVesselRepository();
            $res = $repository->first(["user_id" => $id]);
            if ($res && $res->vessel_ids) {
                return json_decode($res->vessel_ids);
            }
        }
        return [];
    }
    public function getVesselIds()
    {
        $session = new Session();
        $user = $session->user();
        return $user ? $this->getVesselIdsForUser($user->id) : [];
    }

    public function setVesselIds($vessel_ids = [])
    {
        $session = new Session();
        $user = $session->user();
        if ($user) {
            $id = null;
            $repository = new PinnedVesselRepository();
            $model = $repository->first(["user_id" => $user->id]);
            if (!$model) {
                $model = new PinnedVesselModel();
            }
            $model->set($user->id, $vessel_ids);
            $id = $repository->save($model);

            $stateService = new StateService();
            $stateService->triggerPinnedVessels();

            return $this->getVesselIds($id);
        }
        return [];
    }
}
