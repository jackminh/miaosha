<?php

namespace Jackminh\Miaosha\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IhGoodsItemSeeder extends Seeder
{
    /**
     * 运行数据填充
     */
    public function run(): void
    {
        // 首先获取已存在的商品ID
        $goodsIds = DB::table('ih_goods')->pluck('id')->toArray();
        if (empty($goodsIds)) {
            $this->command->error('请先运行商品Seeder生成商品数据！');
            return;
        }
        $items = [];
        $now = Carbon::now();
        $itemIndex = 1;
        
        // 为每个商品生成1-5个规格
        foreach ($goodsIds as $goodsId) {
            $specCount = rand(1, 5); // 每个商品的规格数量
            for ($i = 1; $i <= $specCount; $i++) {
                $priceData = $this->generatePriceData();
                
                $items[] = [
                    'stock' => $this->randomStock(),
                    'price' => $priceData['price'],
                    'market_price' => $priceData['market_price'],
                    'goods_id' => $goodsId,
                    'image' => $this->generateSkuImage($goodsId, $i),
                    'bar_code' => $this->generateBarCode($itemIndex),
                    'created_at' => $this->randomCreateTime($goodsId),
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
                
                $itemIndex++;
                
                // 每100条批量插入一次
                if ($itemIndex % 100 == 0) {
                    DB::table('ih_goods_item')->insert($items);
                    $items = [];
                    $this->command->info("已生成 {$itemIndex} 个商品规格");
                }
            }
        }
        
        // 插入剩余的数据
        if (!empty($items)) {
            DB::table('ih_goods_item')->insert($items);
            $this->command->info("总共生成 " . ($itemIndex - 1) . " 个商品规格");
        }
        
        // 创建一些特殊规格
        $this->createSpecialSpecs($goodsIds);
    }
    
    /**
     * 生成价格数据
     */
    private function generatePriceData(): array
    {
        $price = rand(100, 10000); // 价格在100-10000之间
        
        // 市场价通常是售价的1.2-2倍
        $marketPriceMultiplier = 1 + (rand(20, 100) / 100); // 1.2-2.0
        $marketPrice = round($price * $marketPriceMultiplier, 2);
        
        // 有时候会有促销价
        if (rand(1, 10) <= 3) { // 30%的概率有促销
            $promotionMultiplier = 1 - (rand(5, 30) / 100); // 0.7-0.95折
            $price = round($price * $promotionMultiplier, 2);
        }
        
        return [
            'price' => $price,
            'market_price' => $marketPrice
        ];
    }
    
    /**
     * 随机库存
     */
    private function randomStock(): int
    {
        $chance = rand(1, 100);
        
        if ($chance <= 10) {
            return 0; // 10% 无库存
        } elseif ($chance <= 30) {
            return rand(1, 10); // 20% 少量库存
        } elseif ($chance <= 70) {
            return rand(11, 100); // 40% 中等库存
        } elseif ($chance <= 90) {
            return rand(101, 500); // 20% 较多库存
        } else {
            return rand(501, 5000); // 10% 大量库存
        }
    }
    
    /**
     * 生成SKU图片
     */
    private function generateSkuImage(int $goodsId, int $specIndex): string
    {
        $imageBase = 'https://example.com/images/sku/';
        $colors = ['red', 'blue', 'green', 'black', 'white', 'gold', 'silver', 'pink', 'purple'];
        $sizes = ['s', 'm', 'l', 'xl', 'xxl', 'xxxl'];
        
        $color = $colors[array_rand($colors)];
        $size = $sizes[array_rand($sizes)];
        
        // 根据规格索引生成不同图片
        $imageTypes = [
            'product_' . $goodsId . '_' . $color . '.jpg',
            'product_' . $goodsId . '_' . $size . '.jpg',
            'product_' . $goodsId . '_v' . $specIndex . '.jpg',
            'product_' . $goodsId . '_variant_' . $specIndex . '.png'
        ];
        
        return $imageBase . $imageTypes[array_rand($imageTypes)];
    }
    
    /**
     * 生成条码
     */
    private function generateBarCode(int $index): string
    {
        $prefixes = [
            '690', // 中国大陆
            '691', // 中国大陆
            '692', // 中国大陆
            '693', // 中国大陆
            '694', // 中国大陆
            '695', // 中国大陆
            '880', // 韩国
            '885', // 泰国
            '888', // 新加坡
            '890', // 印度
            '893', // 越南
            '899', // 印度尼西亚
        ];
        
        $prefix = $prefixes[array_rand($prefixes)];
        $middle = date('ymd');
        $sequence = str_pad($index, 5, '0', STR_PAD_LEFT);
        
        // 计算校验位（简单模拟）
        $checkDigit = rand(0, 9);
        
        return $prefix . $middle . $sequence . $checkDigit;
    }
    
    /**
     * 随机创建时间（与商品创建时间关联）
     */
    private function randomCreateTime(int $goodsId): Carbon
    {
        // 获取商品的创建时间作为基准
        $goods = DB::table('ih_goods')->where('id', $goodsId)->first();
        
        if ($goods) {
            $baseTime = Carbon::parse($goods->created_at);
        } else {
            $baseTime = Carbon::now()->subDays(rand(0, 365));
        }
        
        // SKU可能在商品创建后的1-30天内添加
        return $baseTime->addDays(rand(0, 30))
                       ->addHours(rand(0, 23))
                       ->addMinutes(rand(0, 59));
    }
    
    /**
     * 创建特殊规格
     */
    private function createSpecialSpecs(array $goodsIds): void
    {
        $specialSpecs = [];
        $now = Carbon::now();
        
        // 为前5个商品创建特殊规格
        for ($i = 0; $i < min(5, count($goodsIds)); $i++) {
            $goodsId = $goodsIds[$i];
            
            // 1. 限量版规格
            $specialSpecs[] = [
                'stock' => rand(1, 100), // 限量库存
                'price' => rand(9999, 19999), // 高价
                'market_price' => rand(12999, 29999),
                'goods_id' => $goodsId,
                'image' => 'https://example.com/images/sku/limited_edition.jpg',
                'bar_code' => 'LTD' . date('Ymd') . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'created_at' => $now->subDays(rand(1, 30)),
                'updated_at' => $now,
            ];
            
            // 2. 特价促销规格
            $specialSpecs[] = [
                'stock' => rand(50, 200),
                'price' => rand(99, 499), // 特价
                'market_price' => rand(299, 999),
                'goods_id' => $goodsId,
                'image' => 'https://example.com/images/sku/promotion.jpg',
                'bar_code' => 'PROMO' . date('Ymd') . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'created_at' => $now->subDays(rand(1, 15)),
                'updated_at' => $now,
            ];
            
            // 3. 缺货规格
            $specialSpecs[] = [
                'stock' => 0, // 缺货
                'price' => rand(299, 1999),
                'market_price' => rand(399, 2999),
                'goods_id' => $goodsId,
                'image' => 'https://example.com/images/sku/out_of_stock.jpg',
                'bar_code' => 'OOS' . date('Ymd') . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'created_at' => $now->subDays(rand(31, 60)),
                'updated_at' => $now,
            ];
            
            // 4. 预售规格
            $specialSpecs[] = [
                'stock' => rand(100, 1000),
                'price' => rand(1999, 9999),
                'market_price' => rand(2999, 14999),
                'goods_id' => $goodsId,
                'image' => 'https://example.com/images/sku/preorder.jpg',
                'bar_code' => 'PRE' . date('Ymd') . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'created_at' => $now->addDays(1), // 未来时间
                'updated_at' => $now->addDays(1),
            ];
        }
        
        DB::table('ih_goods_item')->insert($specialSpecs);
    }
}