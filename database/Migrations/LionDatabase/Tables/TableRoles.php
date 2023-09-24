<?php

declare(strict_types=1);

use App\Traits\Framework\Faker;
use LionDatabase\Drivers\MySQL\Schema;

return new class
{
	use Faker;

	private string $table = "roles";

	public function getMigration(): array
	{
		return ["type" => "TABLE", "table" => $this->table, "connection" => env->DB_NAME, "index" => 1];
	}

	public function execute(): object
	{
		return Schema::connection(env->DB_NAME)
			->table($this->table, true)
			->create()
			->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])
			->column('name', ['type' => 'varchar', 'length' => 45, 'null' => false, 'comment' => '', 'default' => ''])
			->column('description', ['type' => 'varchar', 'length' => 45, 'null' => true, 'comment' => '', 'default' => ''])
			->execute();
	}

	public function insert(): array
	{
		return [
			'columns' => [
				'idroles',
				'roles_name',
				'roles_description',
			],
 			'rows' => [
				[1,'Administrator',null],
				[2,'Client',null],
			]
		];
	}
};
