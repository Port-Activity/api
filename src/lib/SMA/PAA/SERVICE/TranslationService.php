<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\TranslationModel;
use SMA\PAA\ORM\TranslationRepository;

class TranslationService
{
    public function get(string $ns, string $lng)
    {
        $result = [];
        $repository = new TranslationRepository();
        $translations = $repository->getTranslations($ns, $lng);
        foreach ($translations as $translation) {
            $result[$translation->key] = $translation->value;
        }
        return $result;
    }

    public function add(string $ns, string $lng, array $missing = [])
    {
        $repository = new TranslationRepository();
        foreach ($missing as $key => $value) {
            $TranslationModel = new TranslationModel();
            $TranslationModel->set($ns, $lng, $key, $value);
            if (!$repository->exists($TranslationModel)) {
                $repository->save($TranslationModel);
            }
        }
    }

    public function update(string $ns, string $lng, array $updated)
    {
        $repository = new TranslationRepository();
        foreach ($updated as $row) {
            if ($row['key']) {
                $TranslationModel = $repository->getTranslation($ns, $lng, $row['key']);
                $TranslationModel->set($ns, $lng, $row['key'], $row['value']);
                $repository->save($TranslationModel);
            }
        }
    }

    public function upload(string $ns, string $lng, array $translations)
    {
        $repository = new TranslationRepository();
        $repository->deleteAll([
            "namespace" => $ns,
            "language" => $lng,
        ]);
        foreach ($translations as $row) {
            $TranslationModel = new TranslationModel();
            $TranslationModel->set($ns, $lng, $row['key'], $row['value']);
            $repository->save($TranslationModel);
        }
    }
    public function getValueFor(string $ns, string $lng, string $key)
    {
        if (!$ns || !$lng || !$key) {
            return null;
        }
        $repository = new TranslationRepository();
        $res = $repository->first([
            "namespace" => $ns,
            "language" => $lng,
            "key" => $key,
        ]);
        if (isset($res)) {
            return $res->value;
        }
        return null;
    }
}
