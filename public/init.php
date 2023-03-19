<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Miaosha\Activity;
$user_id = isset($_GET['user_id']) && !empty($_GET['user_id']) ? $_GET['user_id'] : rand(1, 100000);
$goods_id = isset($_GET['goods_id']) && !empty($_GET['goods_id']) ? $_GET['goods_id'] : 1;
$post['user_id'] = $user_id;
$post['goods_id'] = $goods_id;
$activity = new Activity();
var_dump($activity->init_goods_store_to_cache($post['goods_id']));