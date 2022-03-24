<?php

namespace App\Models\Auth;

use App\Models\Model;
use LionSql\QueryBuilder as Builder;

class LogoutModel extends Model {

	public function __construct() {
		$this->init();
	}

}