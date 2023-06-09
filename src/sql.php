-- -- 数据库: `big` -- -- -------------------------------------------------------- -- -- 表的结构 `ih_goods` --

CREATE TABLE IF NOT EXISTS
`ih_goods` ( `goods_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`cat_id` int(11) NOT NULL,
`goods_name` varchar(255) NOT NULL,
PRIMARY KEY (`goods_id`) )
ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

INSERT INTO `ih_goods` (`goods_id`, `cat_id`, `goods_name`) VALUES (1, 0, '小米手机');

CREATE TABLE IF NOT EXISTS
`ih_log` ( `id` int(11) NOT NULL AUTO_INCREMENT,
`event` varchar(255) NOT NULL,
`type` tinyint(4) NOT NULL DEFAULT '0',
`addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS
`ih_order` ( `id` int(11) NOT NULL AUTO_INCREMENT, `order_sn` char(32) NOT NULL, `user_id` int(11) NOT NULL, `status` int(11) NOT NULL DEFAULT '0', `goods_id` int(11) NOT NULL DEFAULT '0', `sku_id` int(11) NOT NULL DEFAULT '0', `price` float NOT NULL, `addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单表' AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `ih_store` ( `id` int(11) NOT NULL AUTO_INCREMENT, `goods_id` int(11) NOT NULL, `sku_id` int(10) unsigned NOT NULL DEFAULT '0', `number` int(10) NOT NULL DEFAULT '0', `freez` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟库存', PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存' AUTO_INCREMENT=2 ;


INSERT INTO `ih_store` (`id`, `goods_id`, `sku_id`, `number`, `freez`) VALUES (1, 1, 11, 500, 0);