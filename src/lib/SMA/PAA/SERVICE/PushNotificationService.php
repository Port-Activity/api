<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\PushNotificationTokenRepository;
use SMA\PAA\ORM\UserRepository;
use SMA\PAA\Session;
use SMA\PAA\TOOL\DateTools;

class PushNotificationService
{
    public function sendNotification($data): void
    {
        $tokenList = [];
        $session = new Session();
        $currentUser = $session->user();

        // Get tokens and send to subscribers
        $pushRepository = new PushNotificationTokenRepository();
        $tokens = $pushRepository->listAll();
        if ($tokens && count($tokens)) {
            foreach ($tokens as $token) {
                if ($token->user_id === $currentUser->id) {
                    // Don't send to push notification to sender
                    continue;
                }
                if ($data->type == "ship") {
                    $pinnedVesselService = new PinnedVesselService();
                    $pinned = $pinnedVesselService->getVesselIdsForUser($token->user_id);
                    if (in_array($data->ship_imo, $pinned)) {
                        array_push($tokenList, $token->token);
                    }
                } else {
                    array_push($tokenList, $token->token);
                }
            }
            if ($data->type == "ship") {
                if ($data->ship && $data->ship->vessel_name) {
                    $title = $data->ship->vessel_name;
                } else {
                    $title = "Ship #" . $data->ship_imo;
                }
                $body = $data->message;
            } else {
                $title = "Port notification";
                $body = $data->message;
            }

            $redis = new RedisClient();
            $redis->rpush(
                "push-notification",
                array(json_encode([
                    "id" => time() . "-" . uniqid(),
                    "type" => "NOTIFICATION",
                    "tokens" => $tokenList,
                    "title" => $title,
                    "body" => $body,
                    "data" => $data,
                ]))
            );
        }
    }
    public function sendVessel(PortCallHelperModel $timestamp): void
    {
        $ns = getenv("NAMESPACE");
        $lng = getenv("LANGUAGE");
        $dateTools = new DateTools();
        $tokenList = [];
        $customBodies = [];
        $imo = $timestamp->imo();
        $vesselName = $timestamp->vesselName();

        // Get tokens and send to subscribers
        $pushRepository = new PushNotificationTokenRepository();
        $tokens = $pushRepository->listAll();
        if ($tokens && count($tokens)) {
            if ($vesselName) {
                $title = $vesselName;
            } else {
                $title = "Ship #" . $imo;
            }

            $text = $timestamp->timeType() . " " . str_replace("_", " ", $timestamp->state());
            $translations = new TranslationService();
            $translated = $translations->getValueFor($ns, $lng, $text);
            if (!$translated) {
                $translated = $text;
            }
            $notificationTemplate = [
                "type" => "ship",
                "ship_imo" => $imo, // for type=ship
                "id" => time() . "-" . uniqid(),
                "created_at" => $timestamp->createdAt(),
                "sender" => [
                  "email" => "system",
                ],
                "ship" => [
                  "imo" => $imo,
                  "vessel_name" => $vesselName,
                ],
            ];
            foreach ($tokens as $token) {
                $pinnedVesselService = new PinnedVesselService();
                $pinned = $pinnedVesselService->getVesselIdsForUser($token->user_id);
                if (in_array($imo, $pinned)) {
                    $userRepository = new UserRepository();
                    $time_zone = getenv("PORT_DEFAULT_TIME_ZONE") ?: "UTC";
                    $user = $userRepository->get($token->user_id);
                    if ($user && $user->time_zone) {
                        $time_zone = $user->time_zone;
                    }

                    $body = $translated . "\r\n" . $dateTools->localDate($timestamp->time(), $time_zone);

                    $notification = $notificationTemplate;
                    $notification["message"] = $body;

                    $tokenList[] = $token->token;
                    $customBodies[$token->token] = $body;
                    // Trigger also a notification
                    // NOTE: not stored in the notifications table
                    $sseService = new SseService();
                    $sseService->trigger("notifications", "changed-" . $token->user_id, $notification);
                }
            }

            $redis = new RedisClient();
            $redis->rpush(
                "push-notification",
                array(json_encode([
                    "id" => time() . "-" . uniqid(),
                    "type" => "VESSEL",
                    "tokens" => $tokenList,
                    "title" => $title,
                    "customBodies" => $customBodies,
                    "vessel_id" => $imo,
                    "data" => $notificationTemplate,
                ]))
            );
        }
    }
    public function sendLogistic($timestamp): void
    {
        $tokenList = [];

        // Get tokens and send to subscribers
        $pushRepository = new PushNotificationTokenRepository();
        $tokens = $pushRepository->listAll();
        if ($tokens && count($tokens)) {
            foreach ($tokens as $token) {
                // TODO: service for "pinning" trucks
                /*
                $pinnedLogisticService = new PinnedLogisticService();
                $pinned = $pinnedLogisticService->getTruckIdsForUser($token->user_id);
                if (in_array($timestamp->ship_imo, $pinned)) {
                */
                    array_push($tokenList, $token->token);
                //}
            }
            $title = $timestamp->checkpoint;
            // TODO: check all plates
            $regno = preg_replace("/(^\w{3})(\d{3})/", "$1-$2", $timestamp->front_license_plates[0]->number);
            $plates = array($regno);
            $nationality = $timestamp->front_license_plates[0]->nationality;
            if ($timestamp->direction === "Out") {
                $direction = "Departed";
            } else {
                $direction = "Arrived";
            }
            $body = $regno . "(" . $nationality . ") " . $direction;

            $redis = new RedisClient();
            $redis->rpush(
                "push-notification",
                array(json_encode([
                    "id" => time() . "-" . uniqid(),
                    "type" => "LOGISTIC",
                    "tokens" => $tokenList,
                    "title" => $title,
                    "body" => $body,
                    "license-plates" => $plates,
                ]))
            );
        }
    }
    public function sendGeneral(string $title, string $body, $data): void
    {
        $tokenList = [];

        // Get tokens and send to all devices
        $pushRepository = new PushNotificationTokenRepository();
        $tokens = $pushRepository->listAll();
        if ($tokens && count($tokens)) {
            foreach ($tokens as $token) {
                array_push($tokenList, $token->token);
            }
            // TODO: data TBD
            $redis = new RedisClient();
            $redis->rpush(
                "push-notification",
                array(json_encode([
                    "id" => time() . "-" . uniqid(),
                    "type" => "GENERAL",
                    "tokens" => $tokenList,
                    "title" => $title,
                    "body" => $body,
                    "data" => $data,
                ]))
            );
        }
    }
    public function removeStaleTokens(array $tokens): void
    {
        $pushRepository = new PushNotificationTokenRepository();
        foreach ($tokens as $token) {
            $pushRepository->deleteAll([
                "token" => $token,
            ]);
        }
    }
}
