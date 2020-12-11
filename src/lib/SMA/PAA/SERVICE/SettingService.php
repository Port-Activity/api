<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\TOOL\EmailTools;
use SMA\PAA\ORM\SettingRepository;

class SettingService
{
    private function validateInput(string $name, string $value)
    {
        $valid = true;
        if (in_array($name, array(
            "activity_module",
            "logistics_module",
            "queue_module",
            "map_module",
            "codeless_registration_module"
            ))) {
            if ($value !== "enabled" && $value !== "disabled") {
                $valid = false;
            }
        } elseif ($name === "queue_travel_duration_to_berth" ||
                  $name === "queue_rta_window_duration" ||
                  $name === "queue_laytime_buffer_duration" ||
                  $name === "queue_live_eta_alert_buffer_duration" ||
                  $name === "queue_live_eta_alert_delay_duration") {
            $dateTools = new DateTools();
            if (!$dateTools->isValidIsoDuration($value)) {
                $valid = false;
            }
        } elseif ($name === "port_operator_emails") {
            if ($value !== "empty") {
                $emailTools = new EmailTools();
                $emails = str_replace(",", " ", $value);
                if (!$emailTools->parseAndValidate($emails)) {
                    $valid = false;
                }
            }
        }

        if (!$valid) {
            throw new InvalidParameterException("Invalid value: " . $value . " for setting: " . $name);
        }
    }

    public function getAll(): array
    {
        $res = [];

        $repository = new SettingRepository();
        $models = $repository->listAll();
        if (!empty($models)) {
            foreach ($models as $model) {
                $innerRes = [];
                $innerRes["id"] = $model->id;
                $innerRes[$model->name] = $model->value;

                $res[] = $innerRes;
            }
        }

        return $res;
    }

    public function getByName(string $name): array
    {
        $res = [];

        $repository = new SettingRepository();
        $model = $repository->getSetting($name);
        if ($model === null) {
            throw new InvalidParameterException("Invalid setting name: " . $name);
        }

        $res["id"] = $model->id;
        $res[$model->name] = $model->value;

        return $res;
    }

    public function update(string $name, string $value): array
    {
        $res = [];

        $repository = new SettingRepository();
        $model = $repository->getSetting($name);
        if ($model === null) {
            throw new InvalidParameterException("Invalid setting name: " . $name);
        }

        $this->validateInput($name, $value);

        if ($value === "empty") {
            $value = "";
        }

        $model->setValue($value);
        $repository->save($model);

        $res[$model->name] = $model->value;

        return $res;
    }

    public function getPublicSettingByName(string $name): array
    {
        $res = [];

        $validNames = ["codeless_registration_module"];

        if (!in_array($name, $validNames)) {
            throw new InvalidParameterException("Invalid setting name: " . $name);
        }

        $repository = new SettingRepository();
        $model = $repository->getSetting($name);
        if ($model === null) {
            throw new InvalidParameterException("Invalid setting name: " . $name);
        }

        $res[$model->name] = $model->value;

        return $res;
    }
}
