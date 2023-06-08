<?php

use App\Traits\Framework\Faker;
use Carbon\Carbon;
use LionSecurity\Validation;
use LionSQL\Drivers\MySQL\Schema;

return new class {

	use Faker;

	private string $table = "users";

	public function getMigration(): array {
		return ["type" => "TABLE", "table" => $this->table, "connection" => env->DB_NAME];
	}

	public function execute(): object {
		return Schema::connection(env->DB_NAME)
			->table($this->table, true)
			->create()
			->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])
			->column('name', ['type' => 'varchar', 'lenght' => 25, 'null' => false, 'default' => "", 'comment' => "user name"])
            ->column('lastname', ['type' => 'varchar', 'lenght' => 25, 'null' => false, 'default' => "", 'comment' => "user lastname"])
            ->column('email', ['type' => 'varchar', 'lenght' => 255, 'null' => false, 'unique' => true, 'default' => '', 'comment' => "user email"])
            ->column('password', ['type' => 'blob', 'null' => false, 'default' => "", 'comment' => "user password"])
            ->column('code', ['type' => 'varchar', 'lenght' => 18, 'null' => false, 'unique' => true, 'default' => '', 'comment' => "unique user code"])
            ->column('create_at', ['type' => 'datetime', 'null' => false, 'default' => '', 'comment' => "creation date"])
			->execute();
	}

	public function insert(): array {
		return [
            "columns" => [
                "users_name", "users_lastname", "users_email",
                "users_password", "users_code", "users_create_at"
            ],
            "rows" => [
                [
                    "Sergio",
                    "Leon",
                    "dev@lion.com",
                    Validation::passwordHash(Validation::sha256("1212")),
                    uniqid("user-"),
                    Carbon::now()->format("Y-m-d H:i:s")
                ]
            ]
        ];
	}

};
