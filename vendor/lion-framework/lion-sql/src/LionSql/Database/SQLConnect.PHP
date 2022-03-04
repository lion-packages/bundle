<?php

namespace LionSql\Database;

use \PDO;
use \PDOStatement;
use \PDOException;

class SQLConnect {
	
	private static PDO $conn;
	
	public function __construct() {
	}

	protected static function connectDatabase(array $config): void {
		try {
			self::$conn = new PDO(
				"mysql:host={$config['host']};dbname={$config['db_name']};charset={$config['charset']}", 
				$config['user'], 
				$config['password'], 
				isset($config['options']) ? $config['options'] : [
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_TIMEOUT => 5
				]
			);
		} catch (PDOException $e) {
			echo($e);
		}
	}

	protected static function bindValue(PDOStatement $stmt, array $data): PDOStatement {
		foreach ($data as $key => $dt) {
			if (isset($dt[1])) {
				$stmt->bindValue(($key + 1), strtolower($dt[1]) === "int" ? (int) $dt[0] : $dt[0], strtolower($dt[1]) === "int" ? PDO::PARAM_INT : PDO::PARAM_STR);
			} else {
				$stmt->bindValue(($key + 1), $dt[0], PDO::PARAM_STR);
			}
		}

		return $stmt;
	}

	protected static function prepare(string $query): PDOStatement {
		return self::$conn->prepare($query);
	}

	protected static function fetch(PDOStatement $stmt): array {
		if ($stmt->execute()) {
			$request = $stmt->fetch();

			return !$request ? ['status' => "success", 'message' => "No data available."] : $request;
		} else {
			return ['status' => "error", 'message' => "An unexpected error has occurred."];
		}
	}

	protected static function fetchAll(PDOStatement $stmt): array {
		if ($stmt->execute()) {
			$request = $stmt->fetchAll();

			return !$request ? ['status' => "success", 'message' => "No data available."] : $request;
		} else {
			return ['status' => "error", 'message' => "An unexpected error has occurred."];
		}
	}

}