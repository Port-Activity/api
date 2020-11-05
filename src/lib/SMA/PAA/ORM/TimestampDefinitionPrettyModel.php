<?php
namespace SMA\PAA\ORM;

class TimestampDefinitionPrettyModel extends TimestampDefinitionModel
{
    public $time_type;
    public $state;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function setFromTimestampDefinition(TimestampDefinitionModel $timestampDefinitionModel)
    {
        $timeTypeRepository = new TimestampTimeTypeRepository();
        $timeTypes = $timeTypeRepository->getTimeTypeMappings();
        $stateRepository = new TimestampStateRepository();
        $states = $stateRepository->getStateMappings();

        $this->id = $timestampDefinitionModel->id;
        $this->name = $timestampDefinitionModel->name;
        $this->time_type_id = $timestampDefinitionModel->time_type_id;
        $this->state_id = $timestampDefinitionModel->state_id;
        $this->created_by = $timestampDefinitionModel->created_by;
        $this->created_at = $timestampDefinitionModel->created_at;
        $this->modified_by = $timestampDefinitionModel->modified_by;
        $this->modified_at = $timestampDefinitionModel->modified_at;

        $this->time_type = array_search($this->time_type_id, $timeTypes);
        $this->state = array_search($this->state_id, $states);
    }
}
