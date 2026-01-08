<?php

return [
	'default'	=> [
		'default_avatar'	=> '',
		'default_image'		=> ''
	],
	'seckill'		=> [
		'seckill_token_prefix' 	=> 'seckill:token:',
		'seckill_token_ttl'		=> 300   //秒杀令牌有效期
	],
	//用户行为配置
    'behavior' => [
        'max_records'       	=> 100,             // 最大记录条数
        'analyze_threshold' 	=> 50,              // 开始分析的阈值
        'time_window'       	=> 60,              // 时间窗口（秒）
        'max_frequency'     	=> 100,             // 最大请求频率（次/分钟）
    ],
    'robot_detection' => [
        'variance_threshold'        => 0.5,     	// 方差阈值，越小说明越均匀
        'std_dev_threshold'         => 100,     	// 标准差阈值（毫秒）
        'pattern_repeat_threshold'  => 5,       	// 模式重复次数
        'interval_consistency'      => 0.3,     	// 间隔一致性阈值（0-1）
    ],
    'blacklist' => [
        'ttl' => 3600,                  			// 黑名单有效期（秒）
        'prefix' => 'seckill:blacklist:',
    ]
    

];