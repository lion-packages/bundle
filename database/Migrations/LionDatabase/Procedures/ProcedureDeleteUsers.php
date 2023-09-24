<?php

declare(strict_types=1);

use App\Traits\Framework\Faker;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionDatabase\Drivers\MySQL\Schema;

return new class
{
	use Faker;

	private string $procedure = "delete_users";

	public function getMigration(): array
	{
		return ["type" => "PROCEDURE", "procedure" => $this->procedure, "connection" => env->DB_NAME];
	}

	public function execute(): object
	{
		return Schema::connection(env->DB_NAME)
			->procedure($this->procedure)
			->create()
			->groupQueryParams(function(Schema $schema) {
				$schema->in()->int("_idusers", 11);
			})
			->groupQueryBegin(function(DB $db) {
				$db
                    ->table("users")
                    ->delete()
                    ->where($db->equalTo("idusers"), "_idusers")
                    ->end();
			})
			->execute();
	}

	public function insert(): array
	{
		return ["rows" => []];
	}
};
