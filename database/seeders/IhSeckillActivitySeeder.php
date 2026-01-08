<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IhSeckillActivitySeeder extends Seeder
{
    /**
     * 运行数据填充
     */
    public function run(): void
    {
        $goods = $this->getSeckillGoods(10);

        $seckillActivity[] = [
            'name'              => "新年秒杀活动",
            'goods_id'          => $goods['goodsId'], // 商品
            'total_stock'       => $goods['totalStock'], //总库存
            'available_stock'   => $goods['availableStock'], //可用库存
            'original_price'    => $goods['originalPrice'], //原价
            'seckill_price'     => $goods['seckillPrice'], // 秒杀价
            'start_time'        => $goods['startTime'],    //开始时间
            'end_time'          => $goods['endTime'],     //结束时间
            'limit_per_user'    => $goods['limitPerUser'], //每人限购数
        ];
        // 批量插入
        DB::table('ih_seckill_activity')->insert($seckillActivity);
        
        $this->command->info('成功生成 ' . count($seckillActivity) . ' 个秒杀活动');
    }

    private function getSeckillGoods($goodsId=10): array 
    {
        $products = DB::table('ih_goods')->where('id',$goodsId)->first();
        if(empty((array)$products)) {
            $this->command->error('请先运行商品Seeder生成商品数据！');
            return [];
        }
        $originalPrice = DB::table('ih_goods_item')->where('goods_id',$goodsId)->avg('price');
        $seckillPrice = $originalPrice * 0.8;
        $goods = [
            'goodsId'       => $goodsId,
            'totalStock'    => $products->stock,
            'availableStock'=> $products->stock,
            'originalPrice' => $originalPrice,
            'seckillPrice'  => $seckillPrice,
            'startTime'     => Carbon::today(),
            'endTime'       => Carbon::tomorrow(),
            'limitPerUser'  => 10,
        ];
        return [
            'goodsId'          => $goods['goodsId'], // 商品
            'totalStock'       => $goods['totalStock'], //总库存
            'availableStock'   => $goods['availableStock'], //可用库存
            'originalPrice'    => $goods['originalPrice'], //原价
            'seckillPrice'     => $goods['seckillPrice'], // 秒杀价
            'startTime'        => $goods['startTime'],    //开始时间
            'endTime'          => $goods['endTime'],     //结束时间
            'limitPerUser'     => $goods['limitPerUser'], //每人限购数
        ];
    }
    


    
}