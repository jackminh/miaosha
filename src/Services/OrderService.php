<?php
namespace Jackminh\Miaosha\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderService
{


    protected array $config;

    public function __construct(array $config = []){
        $this->config = $config ?: config("miaosha",[]);
        
    }


    /**
     * 生成订单号
     *
     * @return string
     */
    public function generateOrderNo(): string
    {
        // 格式: 年月日 + 6位随机数 + 2位随机数
        return date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT) . mt_rand(10, 99);
    }

    
   
   
}