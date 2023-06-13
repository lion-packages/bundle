<?php

use LionSQL\Drivers\MySQL\MySQL as DB;
use LionSQL\Drivers\MySQL\Schema;

return new class {

	public function getMigration(): array {
		return ["type" => "VIEW", "connection" => env->DB_NAME];
	}

	public function execute(): object {
		return Schema::connection(env->DB_NAME)
			->view('read_users')
			->create()
			->groupQuery(function(DB $db) {
				$db->table($db->as("users", "usr"), true)->select(
					$db->column("idusers", "usr"),
					$db->column("idroles", "usr"),
					$db->column("roles_name", "rl"),
					$db->column("users_name", "usr"),
					$db->column("users_lastname", "usr"),
					$db->column("users_email", "usr"),
					$db->column("users_password", "usr"),
					$db->column("users_code", "usr"),
					$db->column("users_create_at", "usr"),
					$db->column("users_options", "usr")
				)
				->innerJoin(
					$db->as("roles", "rl"),
					$db->column("idroles", "usr"),
					$db->column("idroles", "rl")
				);
			})
			->execute();
	}

};