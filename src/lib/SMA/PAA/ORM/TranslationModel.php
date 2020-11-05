<?php
namespace SMA\PAA\ORM;

class TranslationModel extends OrmModel
{
    public $namespace;
    public $language;
    public $key;
    public $value;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $namespace,
        string $language,
        string $key,
        ?string $value
    ) {
        $this->namespace = $namespace;
        $this->language = $language;
        $this->key = $key;
        $this->value = $value;
    }
}
