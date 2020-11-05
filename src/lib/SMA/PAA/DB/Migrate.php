<?php
namespace SMA\PAA\DB;

use Exception;

class Migrate
{
    private $name;
    private $migrateClosure;
    private $revertClosure;
    public function __construct(string $name, callable $migrate, callable $revert)
    {
        $this->name = basename($name);
        $this->migrateClosure = $migrate;
        $this->revertClosure = $revert;
    }
    public static function last(): ?string
    {
        $db = Connection::get();
        try {
            $data = $db->queryOne("SELECT name FROM migration ORDER BY name DESC LIMIT 1");
            return $data["name"];
        } catch (Exception $e) {
            $query = <<<EOT
            CREATE TABLE public.migration
            (
                id serial NOT NULL,
                name text COLLATE pg_catalog."default" NOT NULL,
                created_at timestamp with time zone NOT NULL DEFAULT CURRENT_DATE,
                CONSTRAINT migration_pkey PRIMARY KEY (id),
                CONSTRAINT uniq_name UNIQUE (name)
            );
EOT;
            $db->query($query);
        }
        return "";
    }
    private function markMigrated(): void
    {
        $db = Connection::get();
        $db->query("INSERT INTO public.migration (name) VALUES (?)", $this->name);
    }
    private function markReverted(): void
    {
        $db = Connection::get();
        $db->query("DELETE FROM public.migration WHERE name=?", $this->name);
    }
    public function migrate(): bool
    {
        $this->migrateClosure->call($this);
        $this->markMigrated();
        return true;
    }
    public function revert(): bool
    {
        $this->revertClosure->call($this);
        $this->markReverted();
        return true;
    }
    public function migrateOrRevert(): bool
    {
        if (getenv('PAA_MIGRATE') === "1") {
            return $this->migrate();
        }
        if (getenv('PAA_REVERT') === "1") {
            return $this->revert();
        }
        return false;
    }
}
