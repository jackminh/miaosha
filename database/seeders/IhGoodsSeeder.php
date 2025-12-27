<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IhGoodsSeeder extends Seeder
{
    /**
     * 运行数据填充
     */
    public function run(): void
    {
        $goods = [];
        $now = Carbon::now();
        
        // 预设一些商品数据
        $productNames = [
            'iPhone 15 Pro Max', '华为 Mate 60 Pro', '小米14 Ultra',
            'MacBook Pro 16寸', '联想拯救者Y9000P', '戴尔XPS 13',
            '索尼 PlayStation 5', '任天堂 Switch OLED', 'Xbox Series X',
            '佳能 EOS R5', '尼康 Z9', '索尼 A7M4',
            '小米电视S Pro', '三星 QLED 电视', '海信 ULED 电视',
            '海尔冰箱', '美的空调', '格力空调',
            '戴森吹风机', '飞利浦剃须刀', '博朗电动牙刷'
        ];
        
        $productCategories = [
            '手机' => ['苹果', '华为', '小米', 'OPPO', 'vivo', '三星'],
            '电脑' => ['苹果', '联想', '戴尔', '惠普', '华硕', '微星'],
            '游戏机' => ['索尼', '任天堂', '微软'],
            '相机' => ['佳能', '尼康', '索尼', '富士'],
            '家电' => ['海尔', '美的', '格力', '海信', '创维', 'TCL']
        ];
        
        // 生成50个商品
        for ($i = 0; $i < 50; $i++) {
            // 随机选择分类和品牌
            $category = array_rand($productCategories);
            $brands = $productCategories[$category];
            $brand = $brands[array_rand($brands)];
            
            // 生成商品名称
            $modelNumber = rand(1000, 9999);
            $productName = $brand . ' ' . $this->generateModelName($category) . ' ' . $modelNumber;
            
            $goods[] = [
                'type' => rand(0, 1), // 0或1
                'name' => $productName,
                'code' => 'PROD' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'shop_id' => rand(1, 10), // 假设有10个商家
                'status' => rand(0, 1), // 0或1
                'image' => $this->generateProductImage($category),
                'stock' => rand(0, 500),
                'del' => rand(0, 10) > 1 ? 0 : 1, // 90%正常，10%删除
                'content' => $this->generateProductDescription($productName, $category, $brand),
                'created_at' => $now->subDays(rand(0, 365)),
                'updated_at' => $now,
                'deleted_at' => null,
            ];
            
            if ($goods[$i]['del'] == 1) {
                $goods[$i]['deleted_at'] = $now->copy()->subDays(rand(1, 30));
            }
        }
        
        // 批量插入
        DB::table('ih_goods')->insert($goods);
        
        $this->command->info('成功生成 ' . count($goods) . ' 个商品数据');
    }
    
    /**
     * 生成型号名称
     */
    private function generateModelName(string $category): string
    {
        $modelNames = [
            '手机' => ['Pro', 'Plus', 'Ultra', 'SE', '青春版'],
            '电脑' => ['Pro', 'Air', 'ThinkPad', 'Inspiron', 'Vostro'],
            '游戏机' => ['Slim', 'Pro', 'Lite', 'Standard'],
            '相机' => ['Mark', 'EOS', 'Alpha', 'Z', 'X-T'],
            '家电' => ['智能', '变频', '节能', '旗舰']
        ];
        
        return $modelNames[$category][array_rand($modelNames[$category])] ?? '标准版';
    }
    
    /**
     * 生成商品图片URL
     */
    private function generateProductImage(string $category): string
    {
        $imageBase = 'https://example.com/images/products/';
        $images = [
            '手机' => 'phone_' . rand(1, 5) . '.jpg',
            '电脑' => 'laptop_' . rand(1, 5) . '.jpg',
            '游戏机' => 'game_' . rand(1, 3) . '.jpg',
            '相机' => 'camera_' . rand(1, 4) . '.jpg',
            '家电' => 'appliance_' . rand(1, 6) . '.jpg',
        ];
        
        return $imageBase . ($images[$category] ?? 'default.jpg');
    }
    
    /**
     * 生成商品描述
     */
    private function generateProductDescription(string $name, string $category, string $brand): string
    {
        $descriptions = [
            '手机' => [
                "{$name} 采用最新的处理器，配备超视网膜XDR显示屏，带来流畅的使用体验。",
                "强大的拍照系统，夜景模式表现出色，满足您所有拍摄需求。",
                "超长续航，支持快充，让您告别电量焦虑。"
            ],
            '电脑' => [
                "高性能笔记本电脑，适合办公和娱乐，轻薄便携。",
                "专业级显卡，适合游戏和设计工作，散热性能优秀。",
                "超长续航时间，快速充电，随时随地保持高效工作。"
            ],
            '游戏机' => [
                "新一代游戏主机，支持4K游戏，流畅的游戏体验。",
                "丰富的游戏库，支持在线对战，与好友一起畅玩。",
                "高性能硬件，快速加载，沉浸式游戏体验。"
            ]
        ];
        
        $categoryDesc = $descriptions[$category] ?? [
            "{$brand}品牌出品，质量可靠，性能稳定。",
            "设计精美，操作简单，用户体验优秀。",
            "性价比高，售后服务完善，购买无忧。"
        ];
        
        $randomDesc = $categoryDesc[array_rand($categoryDesc)];
        
        return "<h2>产品介绍</h2>
<p>欢迎购买{$name}！</p>
<p>{$randomDesc}</p>
<h3>产品特点：</h3>
<ul>
<li>品牌：{$brand}</li>
<li>类型：{$category}</li>
<li>高质量材料制造</li>
<li>一年质保服务</li>
<li>全国联保</li>
</ul>
<h3>规格参数：</h3>
<ul>
<li>型号：{$name}</li>
<li>颜色：" . $this->randomColor() . "</li>
<li>重量：" . rand(100, 2000) . "g</li>
<li>尺寸：" . rand(10, 50) . "×" . rand(10, 50) . "×" . rand(1, 10) . "cm</li>
</ul>";
    }
    
    /**
     * 随机颜色
     */
    private function randomColor(): string
    {
        $colors = ['黑色', '白色', '银色', '金色', '蓝色', '红色', '绿色', '紫色'];
        return $colors[array_rand($colors)];
    }
}