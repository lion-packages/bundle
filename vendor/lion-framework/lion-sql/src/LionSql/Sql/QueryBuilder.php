<?php

namespace LionSql\Sql;

use \PDO;
use \PDOStatement;
use \PDOException;
use LionSql\Database\SQLConnect;

class QueryBuilder extends SQLConnect {

	private static string $where = " WHERE";
	private static string $as = " AS";
	private static string $and = " AND";
	private static string $or = " OR";
	private static string $between = " BETWEEN";
	private static string $select = " SELECT";
	private static string $from = " FROM";
	private static string $join = " JOIN";
	private static string $on = " ON";
	private static string $left = " LEFT";
	private static string $right = " RIGHT";
	private static string $inner = " INNER";
	private static string $insert = " INSERT INTO";
	private static string $values = " VALUES";
	private static string $update = " UPDATE";
	private static string $set = " SET";
	private static string $delete = " DELETE";
	private static string $call = " CALL";
	private static string $like = " LIKE";
	
	public function __construct() {
		
	}

	public static function connect(array $config) {
		self::connectDatabase($config);
	}

	private static function addCharacter(array $files, int $count): string {
		$addValues = "";

		foreach ($files as $key => $file) {
			$addValues.= $key === ($count - 1) ? "?" : "?, ";
		}

		return $addValues;
	}

	public static function like(): string {
		return self::$like . " ?";
	}

	public static function call(string $call_name, array $files): array {
		try {
			$count = count($files);

			if ($count > 0) {
				$sql = self::$call . " {$call_name}(" . self::addCharacter($files, $count) . ")";
				self::bindValue(self::prepare($sql), $files)->execute();

				return ['status' => "success", 'message' => "execution finished."];
			} else {
				return ['status' => "warning", 'message' => "at least one row must be entered."];
			}
		} catch (PDOException $e) {
			return ['status' => "error", 'message' => $e];
		}
	}

	public static function delete(string $table, string $index, array $files): array {
		try {
			$sql = self::$delete . self::$from . " {$table} " . self::$where . " {$index}=?";
			self::bindValue(self::prepare($sql), [$files])->execute();

			return ['status' => "success", 'message' => "row deleted successfully."];
		} catch (PDOException $e) {
			return ['status' => "error", 'message' => $e];
		}
	}

	public static function update(string $table, string $columns, array $files = []): array {
		try {
			$columns = explode(":", $columns);
			$count = count($files);
			$addValues = "";

			if ($count > 0) {
				$sql = self::$update . " {$table} " . self::$set . " " . str_replace(",", "=?, ", $columns[0]) . "=? " . self::$where . " {$columns[1]}" . "=?";
				self::bindValue(self::prepare($sql), $files)->execute();

				return ['status' => "success", 'message' => "rows updated successfully."];
			} else {
				return ['status' => "warning", 'message' => "at least one row must be entered."];
			}
		} catch (PDOException $e) {
			return ['status' => "error", 'message' => $e];
		}
	}

	public static function insert(string $table, string $columns, array $files = []): array {
		try {
			$count = count($files);

			if ($count > 0) {
				$sql = self::$insert . " {$table} (" . str_replace(",", ", ", $columns) . ") " . self::$values . " (" . self::addCharacter($files, $count) . ")";
				self::bindValue(self::prepare($sql), $files)->execute();

				return ['status' => "success", 'message' => "rows inserted correctly."];
			} else {
				return ['status' => "warning", 'message' => "at least one row must be entered."];
			}	
		} catch (PDOException $e) {
			return ['status' => "error", 'message' => $e];
		}
	}

	public static function select(string $method, string $table, ?string $alias, string $columns, array $joins = [], array $files = []): array {
		try {
			$addJoins = "";
			if (count($joins) > 0) {
				foreach ($joins as $key => $join) {
					$addJoins.= "{$join} ";
				}
			}

			$sql = self::$select . " " . str_replace(",", ", ", $columns) . " " . self::$from . " {$table} " . ($alias != null ? self::$as . " {$alias} " : '') . " " . $addJoins;
			$prepare = self::prepare($sql);

			if (count($files) > 0) {
				$bind = self::bindValue($prepare, $files);
				return $method === 'fetch' ? self::fetch($bind) : self::fetchAll($bind);
			} else {
				return $method === 'fetch' ? self::fetch($prepare) : self::fetchAll($prepare);
			}
		} catch (PDOException $e) {
			return ['status' => "error", 'message' => $e];
		}
	}

	public static function where(string $column, ?string $operator = null): string {
		return self::$where . " {$column}" . ($operator != null ? "{$operator}?" : '');
	}

	public static function and(string $column, string $operator): string {
		return self::$and . " {$column}{$operator}?";
	}

	public static function or(string $column, string $operator): string {
		return self::$or . " {$column}{$operator}?";
	}

	public static function between(): string {
		return self::$between . " ? " . self::$and . " ? ";
	}

	public static function join(string $type, string $table, ?string $alias, string $condition): string {
		return "{$type} " . self::$join . " " . ($table) . " " . ($alias != null ? self::$as . " {$alias} " : '') . " " . self::$on . " " . ($condition);
	}
	
}