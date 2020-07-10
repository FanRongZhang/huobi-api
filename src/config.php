<?php

return [
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
    "returnArray" => true,
    //返回结果数组数值类型转化为string类型，防止被科学计数。（returnArray=true时生效）
    "toString" => true,
    //请求日志保存目录，为空不保存
    "logPath" => __DIR__ . '/log/',
];