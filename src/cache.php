<?php
namespace Miaosha;
use Redis;

class Cache {

	private $host = "127.0.0.1";
	private $port = 6379;
	private $redis = null;
	private $db_index = 0;

	private static $_instance = null;

	public static function getInstance() {
		if (null == self::$_instance) {
			self::$_instance = new self();
			return self::$_instance;
		} else {
			return self::$_instance;
		}

	}
	private function __clone() {}
	private function __construct() {}

	public function connect() {
		try {
			$this->redis = new Redis();
			$this->redis->connect($this->host, $this->port);
			$this->redis->select($this->db_index);
		} catch (\Exception $e) {
			die("Error: " . $e->getMessage());
		}
	}

	public function lpush($key, $value) {
		$this->redis->lPush($key, $value);
	}

	public function lpop($key) {
		return $this->redis->lPop($key);
	}

	public function rpop($key) {
		return $this->redis->rPop($key);
	}

	public function isExists($key) {
		return $this->redis->exists($key);
	}

	public function get($key) {
		return $this->redis->get($key);
	}

	public function set($key, $value) {
		$this->redis->set($key, $value);
	}

	public function setEx($key, $ex, $value) {
		$this->redis->setex($key, $ex, $value);
	}

	public function keys($pattern) {
		return $this->redis->keys($pattern);
	}
	public function mset($array) {
		return $this->redis->mset($array);
	}
	public function mget($array) {
		return $this->redis->mget($array);
	}
	/**
	 */
	public function lrange($key, $start, $stop) {
		return $this->redis->lRange($key, $start, $stop);
	}

	public function __destruct() {
		$this->redis->close();
	}

}