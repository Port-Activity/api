<?php
namespace SMA\PAA\ORM;

class TranslationRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getTranslation(string $namespace, string $language, string $key): ?TranslationModel
    {
        return $this->getWithQuery("SELECT * FROM {$this->table} "
        . "WHERE namespace=? and language=? and key=?", $namespace, $language, $key);
    }

    public function getTranslations(string $namespace, string $language): array
    {
        $res = $this->getMultipleWithQuery("SELECT * FROM {$this->table} "
        . "WHERE namespace=? and language=?", $namespace, $language);

        if (!isset($res)) {
            return [];
        }

        return $res;
    }

    public function exists(TranslationModel $needle): bool
    {
        return $this->first([
            "namespace" => $needle->namespace,
            "language" => $needle->language,
            "key" => $needle->key,
        ]) !== null;
    }
}
