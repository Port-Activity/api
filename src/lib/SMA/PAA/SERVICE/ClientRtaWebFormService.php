<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\PortCallModel;
use SMA\PAA\TOOL\EmailTools;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\SERVICE\JwtService;
use SMA\PAA\SERVICE\EmailService;
use SMA\PAA\ORM\PortCallRepository;
use SMA\PAA\Session;

class ClientRtaWebFormService
{
    public function rtaWebForm(int $port_call_id, string $port, int $imo, string $rta, string $eta_min, string $eta_max)
    {
        $dateTools = new DateTools();
        if (!$dateTools->isValidIsoDateTime($rta)) {
            throw new InvalidParameterException("Given RTA is not in ISO format: " . $rta);
        }

        if (!$dateTools->isValidIsoDateTime($eta_min)) {
            throw new InvalidParameterException("Given ETA min is not in ISO format: " . $eta_min);
        }

        if (!$dateTools->isValidIsoDateTime($eta_max)) {
            throw new InvalidParameterException("Given ETA max is not in ISO format: " . $eta_max);
        }

        $portCallRepository = new PortCallRepository();
        $portCall = $portCallRepository->get($port_call_id);

        if (!$portCall || $portCall->status !== PortCallModel::STATUS_ARRIVING) {
            throw new InvalidParameterException(
                "Given port call is not as arriving status: " . $port_call_id
            );
        }


        $requiredValues = [
            "eta_form_email",
            "vessel_name",
            "loa",
            "beam",
            "draft",
            "berth"
        ];

        foreach ($requiredValues as $requiredValue) {
            if (empty($portCall->$requiredValue)) {
                throw new InvalidParameterException(
                    "Arriving status port call for given IMO " . $imo
                    . " does not have required value " . $requiredValue
                );
            }
        }

        $emailTools = new EmailTools();
        $emailsToArray = $emailTools->parseAndValidate($portCall->eta_form_email);
        if (!$emailsToArray) {
            throw new InvalidParameterException("Not valid email address: " . $portCall->eta_form_email);
        }

        $privateKey = json_decode(getenv("PRIVATE_KEY_JSON"));
        $publicKey = json_decode(getenv("PUBLIC_KEY_JSON"));

        $formUrl = getenv("BASE_URL") . "/agent/eta-form/rta/";
        $jwtService = new JwtService($privateKey, $publicKey);
        // Generate token
        $expiresIn = 6*60*60;
        $token = $jwtService->encode(
            ["port" => $port
            ,"rta" => $rta
            ,"eta_min" => $eta_min
            ,"eta_max" => $eta_max
            ,"email" => $portCall->eta_form_email
            ,"imo" => $imo
            ,"vessel_name" => $portCall->vessel_name
            ,"vessel_loa" => $portCall->loa
            ,"vessel_beam" => $portCall->beam
            ,"vessel_draft" => $portCall->draft
            ,"berth" => $portCall->berth],
            $expiresIn
        );
        $link = $formUrl . "?token=" . $token;
        $expiresDate = date(\DateTime::ATOM, time() + $expiresIn);
        $emailService = new EmailService();
        $subject = "Port Activity App / Link to RTA form";
        $text =
        "Please send your ETA to outer port area based on RTA given by port "
        . "with a form you can open with with this link: " . $link
        . "\n\nThe link expires in " . $expiresDate . "."
        . "\n\nYou can use the link as long as it is valid.";

        try {
            $emailService->sendEmail($portCall->eta_form_email, $subject, $text);
        } catch (\Exception $e) {
            error_log('Email sending exception: '. $e->getMessage());
            return ["result" => "ERROR"];
        }

        $session = new Session();
        $tools = new DateTools();
        $repository = new PortCallRepository();
        $repository->saveRta(
            $port_call_id,
            $rta,
            [
                "eta_min" => $eta_min,
                "eta_max" => $eta_max,
                "updated_at" => $tools->now(),
                "updated_by" => $session->userId()
            ]
        );

        return ["result" => "OK"];
    }
}
