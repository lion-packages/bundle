<?php

declare(strict_types=1);

use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionDatabase\Drivers\MySQL\Schema;

return new class
{
	private string $procedure = "create_users";

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
				$schema
					->in()->int('_idroles', 11)->end(',')
                    ->in()->varchar("_users_name", 45)->end(",")
                    ->in()->varchar("_users_last_name", 45)->end(",")
                    ->in()->varchar("_users_email", 255)->end(",")
                    ->in()->blob("_users_password")->end(",")
                    ->in()->varchar("_users_code", 25)->end(",")
                    ->in()->dateTime("_users_create_at");
			})
			->groupQueryBegin(function(DB $db) {
				$db->table("users")->insert([
                    "idroles" => "_idroles",
                    "users_name" => "_users_name",
                    "users_last_name" => "_users_last_name",
                    "users_email" => "_users_email",
                    "users_password" => "_users_password",
                    "users_code" => "_users_code",
                    "users_create_at" => "_users_create_at",
                ])->end();
			})
			->execute();
	}

	public function insert(): array
	{
		return ["rows" => []];
	}
};
