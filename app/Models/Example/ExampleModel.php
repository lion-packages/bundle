<?php

namespace App\Models\Example;

use App\Models\Model;
use LionSql\Sql\QueryBuilder as Builder;

class ExampleModel extends Model {
	
	public function __construct() {
		$this->init();	
	}

	public function readComponentsDB(): array {
		return Builder::select('fetchAll', 'components', null);
	}

}