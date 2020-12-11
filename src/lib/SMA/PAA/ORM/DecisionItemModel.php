<?php
namespace SMA\PAA\ORM;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\DecisionModel;
use SMA\PAA\ORM\DecisionRepository;

class DecisionItemModel extends OrmModel
{
    const RESPONSE_NAME_ACCEPT = "Accept";
    const RESPONSE_NAME_REJECT = "Reject";

    const RESPONSE_TYPE_POSITIVE = "positive";
    const RESPONSE_TYPE_NEGATIVE = "negative";
    const RESPONSE_TYPE_NEUTRAL = "neutral";

    public $decision_id;
    public $label;
    public $response_name = null;
    public $response_type = null;
    public $response_options;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    private function validateResponse(string $responseName)
    {
        $validResponses = $this->getResponseOptionNames();

        if (!in_array($responseName, $validResponses)) {
            throw new InvalidParameterException("Invalid decision item response name: " . $responseName);
        }
    }

    private function validateDecisionStatus()
    {
        $decisionRepository = new DecisionRepository();
        $decision = $decisionRepository->getDecisionForItem($this);

        if ($decision->status === DecisionModel::STATUS_CLOSED) {
            throw new InvalidParameterException("Decision is closed: " . $decision->id);
        }
    }

    public function set(
        int $decision_id,
        string $label,
        string $responseName = null,
        string $responseType = null,
        string $responseOptions
    ) {
        $this->decision_id = $decision_id;
        $this->label = $label;
        $this->response_name = $responseName;
        $this->response_type = $responseType;
        $this->response_options = $responseOptions;
    }

    public function getResponseOptionNames(): array
    {
        $responseOptionsArray = json_decode($this->response_options, true);

        return array_keys($responseOptionsArray);
    }

    public function setResponse(string $responseName)
    {
        $this->validateDecisionStatus();

        if ($responseName !== "") {
            $this->validateResponse($responseName);

            $responseOptionsArray = json_decode($this->response_options, true);

            $this->response_name = $responseName;
            $this->response_type = $responseOptionsArray[$responseName]["type"];
        } else {
            $this->response_name = null;
            $this->response_type = null;
        }
    }
}
