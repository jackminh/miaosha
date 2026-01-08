<?php
namespace Jackminh\Miaosha\Facades;

use Illuminate\Support\Facades\Facade;


class Consumer extends Facade
{

	protected static function getFacadeAccessor()
    {
        return 'consumer';
    }
    
}