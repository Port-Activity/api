<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;
use SMA\PAA\ORM\SeaChartFixedVesselModel;
use SMA\PAA\SERVICE\StateService;

class SeaChartFixedVesselRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    private function triggerStateRebuild()
    {
        $stateService = new StateService();
        $stateService->rebuildSeaChartFixedVesselsLists();
    }

    public function save(OrmModel $model, bool $skipCrudLog = false): int
    {
        if (($model->imo === 0 && $model->mmsi === 0)
            || $model->vessel_type === 0) {
            throw new InvalidArgumentException(
                "Vessel identifiers and/or vessel type information missing"
            );
        }

        // First try with IMO if provided. In case DB entry with
        // matching MMSI but without IMO exists, look by MMSI as
        // fallback. This way adding IMO to existing MMSI based entry
        // succeeds (should e.g. fixed vessel without IMO get one).
        $dbModel = $model->imo !== null
            ? $this->first(["imo" => $model->imo])
            : null;
        if (!isset($dbModel) && $model->mmsi !== null) {
            $dbModel = $this->first(["mmsi" => $model->mmsi]);
        }

        if (isset($dbModel)) {
            $hasChanges = ($dbModel->imo !== $model->imo
                || $dbModel->mmsi !== $model->mmsi
                || $dbModel->vessel_type !== $model->vessel_type
                || $dbModel->vessel_name !== $model->vessel_name);

            if ($hasChanges) {
                $dbModel->imo = $model->imo;
                $dbModel->mmsi = $model->mmsi;
                $dbModel->vessel_type = $model->vessel_type;
                $dbModel->vessel_name = $model->vessel_name;
                $id = parent::save($dbModel, $skipCrudLog);
                $this->triggerStateRebuild();
                return $id;
            } else {
                return $dbModel->id;
            }
        } else {
            $id = parent::save($model, $skipCrudLog);
            $this->triggerStateRebuild();
            return $id;
        }
    }

    public function getFixedVessels(): array
    {
        return $this->getMultipleWithQuery("SELECT * FROM {$this->table}");
    }

    public function getFixedVesselByImo(int $imo): ?SeaChartFixedVesselModel
    {
        return $this->first(["imo" => $imo]);
    }

    public function getFixedVesselByMmsi(int $mmsi): ?SeaChartFixedVesselModel
    {
        return $this->first(["mmsi" => $mmsi]);
    }

    public function getFixedVesselByImoAndMmsi(int $imo, int $mmsi): ?SeaChartFixedVesselModel
    {
        return $this->first(["imo" => $imo, "mmsi" => $mmsi]);
    }
}
