<?php

use App\Traits\Framework\Faker;
use LionSQL\Drivers\MySQL\Schema;

return new class {

	use Faker;

	private string $table = "users";

	public function getMigration(): array {
		return ["type" => "TABLE", "table" => $this->table, "connection" => env->DB_NAME, "index" => 2];
	}

	public function execute(): object {
		return Schema::connection(env->DB_NAME)
			->table($this->table, true)
			->create()
			->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])
			->column('idroles', ['type' => 'int', 'null' => false, 'foreign-key' => ['table' => 'roles', 'column' => 'idroles'], 'default' => ''])
			->column('name', ['type' => 'varchar', 'length' => 45, 'null' => false, 'default' => ''])
			->column('lastname', ['type' => 'varchar', 'length' => 45, 'null' => false, 'default' => ''])
			->column('email', ['type' => 'varchar', 'length' => 45, 'null' => false, 'unique' => true, 'default' => ''])
			->column('password', ['type' => 'blob', 'null' => false, 'default' => ''])
			->column('code', ['type' => 'varchar', 'length' => 45, 'null' => false, 'unique' => true, 'default' => ''])
			->column('create_at', ['type' => 'datetime', 'null' => false, 'default' => ''])
			->execute();
	}

	public function insert(): array {
		return [
			'columns' => [
				'idusers',
				'users_idroles',
				'users_name',
				'users_lastname',
				'users_email',
				'users_password',
				'users_code',
				'users_create_at',
			],
 			'rows' => []
		];
	}

};
