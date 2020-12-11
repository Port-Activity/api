<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\Session;
use SMA\PAA\ORM\SeaChartFixedVesselRepository;

class StateService implements IStateService
{
    public function get(string $key)
    {
        $client = new RedisClient();
        $data = $client->get($key);
        if ($data) {
            return unserialize($data);
        }
        return null;
    }
    public function set(string $key, $data, int $expires = null)
    {
        $client = new RedisClient();
        $out = $client->set($key, serialize($data));
        if ($expires !== null) {
            $client->expire($key, $expires);
        }
        return $out;
    }
    public function getSet(string $key, callable $callback, int $expires = null)
    {
        $client = new RedisClient();
        $data = $client->get($key);
        if ($data) {
            $data = unserialize($data);
        } else {
            $data = call_user_func($callback);
            $client->set($key, serialize($data));
        }
        if ($expires) {
            $client->expire($key, $expires);
        }
        return $data;
    }
    public function delete(string $key)
    {
        $client = new RedisClient();
        $client->del($key);
    }
    private function rebuildActivePortCallLists()
    {
        $service = new PortCallService();
        $datas = $service->ongoingPortCallImoByStatusAndEta();
        $this->set(self::LATEST_PORT_CALL_STATUSES, $datas);
        $this->set(self::LATEST_PORT_CALL_IMOS, array_map(function ($data) {
            return $data["imo"];
        }, $datas));
        $this->set(self::LATEST_PORT_CALL_DETAILS, $datas);
    }
    public function rebuildInitialSharedData()
    {
        $this->rebuildSeaChartFixedVesselsLists();
        $this->rebuildActivePortCallLists();
    }
    public function rebuildSeaChartFixedVesselsLists()
    {
        $fixedVesselRepository = new SeaChartFixedVesselRepository();
        $fixedVessels = $fixedVesselRepository->getFixedVessels();
        $resultMappings = array();
        foreach ($fixedVessels as $fixedVessel) {
            if (!empty($fixedVessel->mmsi)) {
                $resultMappings[$fixedVessel->mmsi] = $fixedVessel->imo;
            }
        }
        $this->set(self::SEA_CHART_FIXED_VESSELS_MMSI_IMO_MAP, $resultMappings);
    }
    public function triggerPortCalls()
    {
        if ($this->get(self::LATEST_PORT_CALLS_LOCKED) === null) {
            $this->set(self::LATEST_PORT_CALLS_LOCKED, true, 60);
            $this->delete(self::LATEST_PORT_CALLS);
            $this->rebuildActivePortCallLists();
            $sse = new SseService();
            $sse->trigger("portcalls", "changed", []);
            $sse->trigger("queue-portcalls", "changed", []);
            $this->delete(self::LATEST_PORT_CALLS_LOCKED);
        }
    }
    public function triggerLogistics()
    {
        $this->delete(self::LATEST_LOGISTICS);
        $sse = new SseService();
        $sse->trigger("logistics", "changed", []);
    }
    public function triggerPinnedVessels()
    {
        $session = new Session();
        $user = $session->user();
        if ($user) {
            $this->delete(StateService::PINNED_VESSELS . "." . $user->id);
            $sse = new SseService();
            $sse->trigger("portcalls", "changed-" . $user->id, []);
        }
    }
}
