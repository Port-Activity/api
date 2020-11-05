<?php
namespace SMA\PAA\ORM;

use SimpleXMLElement;

use SMA\PAA\ORM\VisVesselRepository;

class VisMessagePrettyModel extends VisMessageModel
{
    public $from_name;
    public $to_name;
    public $author;
    public $subject;
    public $body;

    private $visVesselRepository;

    public function __construct()
    {
        $this->from_name = "";
        $this->to_name = "";
        $this->author = "";
        $this->subject = "";
        $this->body = "";

        $this->visVesselRepository = new VisVesselRepository();

        parent::__construct(__CLASS__);
    }

    public function setFromVisMessage(VisMessageModel $visMessageModel)
    {
        $this->id = $visMessageModel->id;
        $this->time = $visMessageModel->time;
        $this->from_service_id = $visMessageModel->from_service_id;
        $this->to_service_id = $visMessageModel->to_service_id;
        $this->message_id = $visMessageModel->message_id;
        $this->message_type = $visMessageModel->message_type;
        $this->payload = $visMessageModel->payload;
        $this->ack = $visMessageModel->ack;
        $this->operational_ack = $visMessageModel->operational_ack;
        $this->created_by = $visMessageModel->created_by;
        $this->created_at = $visMessageModel->created_at;
        $this->modified_by = $visMessageModel->modified_by;
        $this->modified_at = $visMessageModel->modified_at;

        $this->from_name = $this->visVesselRepository->getVesselNameWithServiceId($visMessageModel->from_service_id);
        $this->to_name = $this->visVesselRepository->getVesselNameWithServiceId($visMessageModel->to_service_id);

        $payloadArray = json_decode($visMessageModel->payload, true);

        if (!isset($payloadArray["stmMessage"])) {
            return;
        }
        if (!isset($payloadArray["stmMessage"]["message"])) {
            return;
        }

        $message = $payloadArray["stmMessage"]["message"];

        libxml_use_internal_errors(true);
        try {
            $xmlMessage = new SimpleXMLElement($message);
        } catch (Exception $e) {
            return;
        }
        libxml_use_internal_errors(false);

        foreach ($xmlMessage->getDocNamespaces() as $prefix => $namespace) {
            if ($prefix === "") {
                $prefix="a";
            }
            $xmlMessage->registerXPathNamespace($prefix, $namespace);
        }

        $element = $xmlMessage->xpath('/a:textMessage/a:author');
        if (!empty($element)) {
            $this->author = (string)$element[0];
        }
        $element = $xmlMessage->xpath('/a:textMessage/a:subject');
        if (!empty($element)) {
            $this->subject= (string)$element[0];
        }
        $element = $xmlMessage->xpath('/a:textMessage/a:body');
        if (!empty($element)) {
            $this->body = (string)$element[0];
        }
    }
}
