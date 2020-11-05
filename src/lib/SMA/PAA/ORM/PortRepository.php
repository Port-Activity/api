<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\PortModel;

class PortRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $updateWhitelists = false, bool $skipCrudLog = false)
    {
        $portModel = $this->first(["service_id" => $model->service_id]);

        if (isset($portModel)) {
            $existingLocodes = json_decode($portModel->locodes, true);
            $newLocodes = json_decode($model->locodes, true);
            $locodes = array_unique(array_merge($existingLocodes, $newLocodes));
            $whitelistIn = $portModel->getIsWhiteListIn();
            $whitelistOut = $portModel->getIsWhiteListOut();
            if ($updateWhitelists) {
                $whitelistIn = $model->getIsWhiteListIn();
                $whitelistOut = $model->getIsWhiteListOut();
            }
            $portModel->set(
                $model->name,
                $portModel->service_id,
                $whitelistIn,
                $whitelistOut,
                $locodes
            );

            return parent::save($portModel);
        }

        return parent::save($model);
    }

    public function getByServiceId(string $serviceId): ?PortModel
    {
        return $this->first(["service_id" => $serviceId]);
    }

    public function getByLocode(string $locode): ?array
    {
        $query = <<<EOT
        SELECT * FROM {$this->table} WHERE locodes @> '["{$locode}"]'::jsonb;
EOT;
        $res = $this->getMultipleWithQuery($query);

        if (empty($res)) {
            return null;
        }

        return $res;
    }

    public function getNameWithServiceId(string $serviceId): string
    {
        $res = "";

        $model = $this->first(["service_id" => $serviceId]);

        if (isset($model)) {
            $res = $model->name;
        }

        return $res;
    }
}
