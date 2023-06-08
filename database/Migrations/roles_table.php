<?php

use App\Traits\Framework\Faker;
use LionSQL\Drivers\MySQL\Schema;

return new class {

	use Faker;

	private string $table = "roles";

	public function getMigration(): array {
		return ["type" => "TABLE", "table" => $this->table, "connection" => env->DB_NAME];
	}

	public function execute(): object {
		return Schema::connection(env->DB_NAME)
			->table($this->table, true)
			->create()
			->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])
			->column('name', ['type' => 'varchar', 'null' => false, 'default' => '', 'comment' => "role name"])
            ->column('description', ['type' => 'varchar', 'lenght' => 255, 'null' => true, 'default' => '', 'comment' => "role description"])
			->execute();
	}

	public function insert(): array {
		return [
            "columns" => ["roles_name", "roles_description"],
            "rows" => [
                ["Administrator", "The administrator role has super permissions in the system"],
                ["Client", "The customer role has permissions to perform basic operations within the system"],
            ]
        ];
	}

};
