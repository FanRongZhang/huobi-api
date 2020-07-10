## 项目介绍
简单易用的火币网交易包
[接口文档](https://huobiapi.github.io/docs/coin_margined_swap/v1/cn/#5ea2e0cde2)


## 使用方法(具体查看tests目录)

~~~
use sreeb\api\perpetual\HuobiPerprtualApi;
use sreeb\api\RequestData;
use sreeb\Huobi;

//创建永续合约类
$huobiPerprtualApi = Huobi::createInstance(HuobiPerprtualApi::class);

//设置配置,不设置默认读取 src/config.php配置
$huobiPerprtualApi->setOptions([
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

//请求接口数据（市场接口为market开头,合约资产为account开头，合约交易为contract开头）
$huobiPerprtualApi->marketSwapContractInfo('BTC-USD');
//对于多参数接口参数也可以采用数组参数
$huobiPerprtualApi->marketSwapFundingRate([
    'contract_code' => 'BTC-USD',
    'page_index' => 1,
    'page_size' => 20,
]);
//对于未封装接口,可先实例化RequestData类（请求方法，请求接口，请求参数），然后调用request进行请求。
$requestData = new RequestData('GET','/v1/account/accounts',[
    'symbol' => 'BTC',
    'contract_type' => 'next_week',
    'contract_code' => 20,
]);
$huobiPerprtualApi->request($requestData);
~~~