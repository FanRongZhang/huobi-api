<?php

use sreeb\api\RequestData;
use sreeb\api\spot\HuobiSpotApi;
use sreeb\Huobi;

//创建现货类
$huobiSpotApi = Huobi::createInstance(HuobiSpotApi::class);

//设置配置,不设置默认读取 src/config.php配置
$huobiSpotApi->setOptions([
    "AccessKey" => "",
    "SecretKey" => "",
    "SignatureMethod" => "HmacSHA256",
    "SignatureVersion" => 2,
    //接口请求网关
    "ApiHost" => "https://api.hbdm.vn",
    //curl请求配置项
    "curlOptions" => [
        //请求超时时间
        "timeOut" => 5,
        //SSL验证
        "verifySsl" => false,
        //开启代理
        "openProxy" => false,
        //代理设置
        "proxy" => [
            'ip' => '',
            'port' => '',
            'username' => '',
            'password' => '',
        ]
    ],
    //返回结果是否为数组
    "returnArray" => false,
    //返回结果数组数值类型转化为string类型，防止被科学计数。（returnArray=true时生效）
    "toString" => true,
    //请求日志保存目录，为空不保存
    "logPath" => __DIR__ . '/../src/log/'
]);

//现货类接口暂未封装，可采用下面请求方式
//对于未封装接口,可先实例化RequestData类（请求方法，请求接口，请求参数），然后调用request进行请求。
$requestData = new RequestData('GET','/v1/account/accounts',[
    'symbol' => 'BTC',
    'contract_type' => 'next_week',
    'contract_code' => 20,
]);
$huobiSpotApi->request($requestData);