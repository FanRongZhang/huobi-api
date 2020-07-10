<?php

namespace sreeb;

use Exception;
use sreeb\api\perpetual\HuobiPerprtualApi;
use sreeb\api\spot\HuobiSpotApi;
use sreeb\api\swap\HuobiSwapApi;

class Huobi
{

    /**
     * @param string $class
     * @return HuobiPerprtualApi|HuobiSwapApi|HuobiSpotApi
     * @throws Exception
     */
    public static function createInstance(string $class)
    {
        if (class_exists($class)) {
            return new $class;
        } else {
            throw new Exception("Class '{$class}' not found");
        }
    }


}