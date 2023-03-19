<?php
namespace Miaosha;
use mysqli;

class Db {

	private static $config = [
		'HOST' => '127.0.0.1',
		'PORT' => '3306',
		'USERNAME' => 'homestead',
		'PASSWORD' => 'secret',
		'DB' => 'demo',
	];
	private static $connection = null;
	private function __construct() {}
	private function __clone() {}

	private static $_instance = null;
	public static function getInstance() {
		if (null == self::$_instance) {
			self::$_instance = new self();
			return self::$_instance;
		} else {
			return self::$_instance;
		}
	}

	public function connect() {
		$host = self::$config['HOST'];
		$username = self::$config['USERNAME'];
		$password = self::$config['PASSWORD'];
		$db = self::$config['DB'];
		$port = self::$config['PORT'];
		$mysqli = new mysqli($host, $username, $password, $db, $port);
		if (mysqli_connect_errno($mysqli)) {
			throw new RuntimeException('mysqli connection error: ' . mysqli_connect_error());
		}
		self::$connection = $mysqli;
	}

	public function select($sql) {
		$result = mysqli_query(self::$connection, $sql, MYSQLI_USE_RESULT);
		$data = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = $row;
		}
		return $data;
	}

	public function handler_transaction($sqls = array(), $log = null) {
		try {
			//start the transaction
			$log->notice("开启事务");
			mysqli_begin_transaction(self::$connection);
			if (!empty($sqls)) {
				foreach ($sqls as $sql) {
					mysqli_query(self::$connection, $sql);
				}
			}
			//Committing the transaction
			$log->notice("提交事务");
			mysqli_commit(self::$connection);
		} catch (\Exception $e) {
			mysqli_rollback(self::$connection);
			mysqli_close(self::$connection);
			die("transaction error: " . $e->getMessage());
		}
		$log->notice("----------------------------end-------------------------------------");

	}

	public function insert($sql) {
		$result = mysqli_query(self::$connection, $sql);
		return mysqli_insert_id(self::$connection);
	}

	public function __destruct() {
		mysqli_close(self::$connection);
	}

}