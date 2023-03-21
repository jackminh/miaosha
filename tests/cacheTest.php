<?php
namespace Miaosha\Tests;

use Miaosha\Cache;
use PHPUnit\Framework\TestCase;

class cacheTest extends TestCase {

	protected $redis;

	public function testUniqueness() {

		$firstCall = Cache::getInstance();
		$secondCall = Cache::getInstance();
		$this->assertInstanceOf(Cache::class, $firstCall);
		$this->assertSame($firstCall, $secondCall);
	}
}