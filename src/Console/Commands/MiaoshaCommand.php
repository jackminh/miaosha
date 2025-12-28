<?php

namespace Jackminh\Miaosha\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

use Jackminh\Miaosha\Facades\Miaosha;
use Jackminh\Miaosha\Repositories\GoodsRepository;
use Jackminh\Miaosha\Contracts\GoodsRepositoryInterface;

use Jackminh\Miaosha\Contracts\UserRepositoryInterface;
use Jackminh\Miaosha\Repositories\UserRepository;

use Jackminh\Miaosha\Repositories\GoodsItemRepository;
use Jackminh\Miaosha\Contracts\GoodsItemRepositoryInterface;

class MiaoshaCommand extends Command
{
	
	protected $signature = 'jackminh:miaosha';
    protected $description = '初始化库存';

    protected GoodsRepositoryInterface $goodsRepository;
    protected UserRepositoryInterface  $userRepository;
    protected GoodsItemRepositoryInterface $goodsItemRepository;

    /**
     * 构造函数注入
     */
    public function __construct(GoodsRepositoryInterface $goodsRepository,
        UserRepositoryInterface $userRepository, GoodsItemRepositoryInterface $goodsItemRepository
    )
    {
        parent::__construct();
        $this->goodsRepository = $goodsRepository;
        $this->userRepository  = $userRepository;
        $this->goodsItemRepository = $goodsItemRepository;
    }

    public function handle(): int
    {
        //初始商品规格库存到redis
        $this->initStock();
        $this->info("####初始化库丰存完成####");
        return self::SUCCESS;
    }

    protected function initStock(): void 
    {
        $goodsId   = 10;
        $goodsItem = $this->goodsItemRepository->getGoodsItem($goodsId)->toArray();
        if(count($goodsItem) > 0){
            $datas = array_reduce($goodsItem, function($data,$item){
                $data[$item['id']] = [
                    'goods_id'  => $item['goods_id'],
                    'stock'     => $item['stock']
                ];
                $key = $item['id']."_".$item['goods_id']."_stock";
                $value = $item['stock'];
                if(Redis::get($key) === null){
                    Redis::set($key,$value);
                }
                return $data;
            },[]);
        }
    }


}