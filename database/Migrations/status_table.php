<?php

use App\Traits\Framework\Faker;
use LionSQL\Drivers\MySQL\Schema;

return new class {

	use Faker;

	private string $table = "process_states";

	public function getMigration(): array {
		return ["type" => "TABLE", "table" => $this->table, "connection" => env->DB_NAME];
	}

	public function execute(): object {
		return Schema::connection(env->DB_NAME)
			->table($this->table, true)
			->create()
			->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])
			->column('name', ['type' => 'varchar', 'null' => false, 'lenght' => 10, 'default' => ''])
			->execute();
	}

	public function insert(): array {
		return [
            "columns" => ["process_states_name"],
            "rows" => [
                ["Enabled"],
                ["Disabled"]
            ]
        ];
	}

};
