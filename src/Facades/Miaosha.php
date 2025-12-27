<?php
namespace Jackminh\Miaosha\Facades;

use Illuminate\Support\Facades\Facade;


class Miaosha extends Facade
{

	protected static function getFacadeAccessor()
    {
        return 'miaosha';
    }
    
}



