<?php

declare(strict_types=1);

use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionDatabase\Drivers\MySQL\Schema;

return new class
{
	public function getMigration(): array
    {
		return ["type" => "VIEW", "connection" => env->DB_NAME];
	}

	public function execute(): object
    {
		return Schema::connection(env->DB_NAME)
			->view("read_users")
			->create()
			->groupQuery(function(DB $db) {
				$db->table($db->as("users", "usr"))
                    ->select(
                        $db->column("idusers", "usr"),
                        $db->column("idroles", "usr"),
                        $db->column("users_name", "usr"),
                        $db->column("users_last_name", "usr"),
                        $db->column("users_email", "usr"),
                        $db->column("users_code", "usr"),
                        $db->column("users_create_at", "usr"),
                        $db->column("roles_name", "rl")
                    )
                    ->inner()
                    ->join(
                        $db->as("roles", "rl"),
                        $db->column("idroles", "usr"),
                        $db->column("idroles", "rl")
                    );
			})
			->execute();
	}
};
