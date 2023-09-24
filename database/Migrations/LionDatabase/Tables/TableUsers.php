<?php

declare(strict_types=1);

use App\Traits\Framework\Faker;
use LionDatabase\Drivers\MySQL\Schema;

return new class
{
	use Faker;

	private string $table = "users";

	public function getMigration(): array
	{
		return ["type" => "TABLE", "table" => $this->table, "connection" => env->DB_NAME, "index" => 2];
	}

	public function execute(): object
	{
		return Schema::connection(env->DB_NAME)
			->table($this->table, true)
			->create()
			->column('id', ['type' => 'int', 'primary-key' => true, 'lenght' => 11, 'null' => false, 'auto-increment' => true])
			->column('idroles', ['type' => 'int', 'null' => false, 'foreign-key' => ['table' => 'roles', 'column' => 'idroles'], 'comment' => '', 'default' => ''])
			->column('name', ['type' => 'varchar', 'length' => 45, 'null' => false, 'comment' => '', 'default' => ''])
			->column('last_name', ['type' => 'varchar', 'length' => 45, 'null' => false, 'comment' => '', 'default' => ''])
			->column('email', ['type' => 'varchar', 'length' => 45, 'null' => false, 'unique' => true, 'comment' => '', 'default' => ''])
			->column('password', ['type' => 'blob', 'null' => false, 'comment' => '', 'default' => ''])
			->column('code', ['type' => 'varchar', 'length' => 45, 'null' => false, 'unique' => true, 'comment' => '', 'default' => ''])
			->column('create_at', ['type' => 'datetime', 'null' => false, 'comment' => '', 'default' => ''])
			->execute();
	}

	public function insert(): array
	{
		return [
			'columns' => [
				'idusers',
				'idroles',
				'users_name',
				'users_last_name',
				'users_email',
				'users_password',
				'users_code',
				'users_create_at',
			],
 			'rows' => [
				[1,1,'Sergio','Leon','sleon@dev.com','0x24327924313024324f454755714b67356234687975463441337a55522e7874724b6e6f3658716f593269613350596e6c4455346673376b675355546d','user-6497cb44a9251','2023-06-25 00:06:12'],
			]
		];
	}
};
