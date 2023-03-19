<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Miaosha\Activity;

$fp = fopen("./lock.txt", "r+");
if (flock($fp, LOCK_EX)) {
	handler();
	flock($fp, LOCK_UN);
} else {
	echo "Couldn't get the lock!";
}
fclose($fp);

function handler() {
	$user_id = isset($_GET['user_id']) && !empty($_GET['user_id']) ? $_GET['user_id'] : rand(1, 100000);
	$goods_id = isset($_GET['goods_id']) && !empty($_GET['goods_id']) ? $_GET['goods_id'] : 1;
	$post['user_id'] = $user_id;
	$post['goods_id'] = $goods_id;
	$activity = new Activity();
	$activity->handle($post);
}