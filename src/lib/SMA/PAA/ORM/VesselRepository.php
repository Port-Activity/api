<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;
use SMA\PAA\Session;
use SMA\PAA\ORM\VesselModel;

class VesselRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        if ($model->imo !== 0) {
            return $this->saveValidImo($model, $skipCrudLog);
        } else {
            return $this->saveFakeImo($model, $skipCrudLog);
        }
    }

    public function saveValidImo(OrmModel $model, bool $skipCrudLog = false)
    {
        # If database ID is explicitly set
        # then allow normal modification
        # todo: This can lead to non unique IMO numbers or vessel names
        if (isset($model->id)) {
            return parent::save($model, $skipCrudLog);
        }

        # Check if IMO already exists in database
        $dbModel = $this->first(["imo" => $model->imo]);

        # If IMO is already stored, then just update the vessel name
        if (isset($dbModel)) {
            if ($model->vessel_name !== "" &&
                ($dbModel->vessel_name !== $model->vessel_name)) {
                $dbModel->vessel_name = $model->vessel_name;
                return parent::save($dbModel, $skipCrudLog);
            } else {
                return $dbModel->id;
            }
        }
        return parent::save($model, $skipCrudLog);
    }

    public function saveFakeImo(OrmModel $model, bool $skipCrudLog = false)
    {
        # Check if vessel name already exists in database
        $res = $this->first(["vessel_name" => ["LOWER" => $model->vessel_name]]);

        if (isset($res)) {
            $session = new Session();
            # Dummy update to get proper return value
            $model->id = $res->id;
            $model->imo = $res->imo;
            $model->setIsVisible($res->getIsVisible());
            # Note: we need explitly set created_at and by here since dummy update
            $dbts = gmdate("Y-m-d\TH:i:s\Z");
            $model->created_at = $dbts;
            $model->created_by = $session->userId();
        } else {
            # First save with 0 IMO
            parent::save($model, $skipCrudLog);

            # Query saved vessel to get unique ID
            $res = $this->first(["vessel_name" => ["LOWER" => $model->vessel_name]]);

            # Generate fake IMO based on unique ID and update
            if (isset($res)) {
                $model->id = $res->id;
                $model->imo = 100000000 + $res->id;
            } else {
                # Should not happen
                throw new InvalidArgumentException(
                    "Invalid vessel name: " . $model->vessel_name
                );
            }
        }

        return parent::save($model, $skipCrudLog);
    }

    public function getImo(string $vesselName): int
    {
        $res = $this->first(["vessel_name" => ["LOWER" => $vesselName]]);

        if (!isset($res)) {
            throw new InvalidArgumentException(
                "Invalid vessel name: " . $vesselName
            );
        }

        return $res->imo;
    }

    public function getImosWithVisibility(bool $visible): array
    {
        $res = [];

        $param = "f";
        if ($visible) {
            $param = "t";
        }

        $models = $this->listNoLimit(["visible" => $param], 0);

        foreach ($models as $model) {
            $res[] = $model->imo;
        }

        return $res;
    }

    public function getWithImo(int $imo): VesselModel
    {
        $res = $this->first(["imo" => $imo]);

        if (!isset($res)) {
            throw new InvalidArgumentException(
                "Invalid imo: " . $imo
            );
        }

        return $res;
    }
}
