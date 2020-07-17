<?php
declare (strict_types=1);

namespace sreeb\api;

use sreeb\Curl;
use sreeb\Result;

abstract class AbstractHuobiApi
{
    /**
     * CURL实例
     * @var Curl
     */
    public $curl;

    /**
     * accessKey
     * @var string
     */
    protected $AccessKey = '';

    /**
     * secretKey
     * @var string
     */
    protected $SecretKey = '';

    /**
     * 验签方法
     * @var string
     */
    protected $SignatureMethod = 'HmacSHA256';

    /**
     * 验签版本
     * @var int
     */
    protected $SignatureVersion = 2;

    /**
     * 请求接口
     * @var string
     */
    protected $ApiHost;

    /**
     * 请求结果是否返回数组
     * @var bool
     */
    protected $returnArray;

    /**
     * 返回数组字符型数据是否转化为字符串，避免被科学计数
     * @var bool
     */
    protected $toString;

    /**
     * 请求日志保存目录
     * @var string
     */
    protected $logPath;


    /**
     * 初始化，默认读取配置文件配置
     */
    public function init(): void
    {
        $config = include __DIR__ . '/../config.php';
        $this->setOptions($config);
    }

    /**
     * 设置配置参数
     * @param array $config
     */
    public function setOptions(array $config): void
    {
        if (!empty($config['AccessKey'])) {
            $this->AccessKey = $config['AccessKey'];
        }
        if (!empty($config['SecretKey'])) {
            $this->SecretKey = $config['SecretKey'];
        }
        if (!empty($config['SignatureMethod'])) {
            $this->SignatureMethod = $config['SignatureMethod'];
        }
        if (!empty($config['SignatureVersion'])) {
            $this->SignatureVersion = $config['SignatureVersion'];
        }
        if (!empty($config['ApiHost'])) {
            $this->ApiHost = $config['ApiHost'];
        }

        $this->curl = Curl::getInstance();
        if (isset($config['curlOptions']['timeOut']) && $config['curlOptions']['timeOut'] > 0) {
            $this->curl = $this->curl->setTimeout($config['curlOptions']['timeOut'], $config['curlOptions']['timeOut']);
        }
        if (isset($config['curlOptions']['verifySsl']) && $config['curlOptions']['verifySsl'] == 0) {
            $this->curl = $this->curl->setSSL(false);
        }
        if (isset($config['curlOptions']['openProxy']) && $config['curlOptions']['openProxy'] === true) {
            $this->curl = $this->curl->setProxy(
                $config['curlOptions']['proxy']['ip'] ?? '',
                $config['curlOptions']['proxy']['port'] ?? '',
                $config['curlOptions']['proxy']['username'] ?? '',
                $config['curlOptions']['proxy']['password'] ?? ''
            );
        }
        if (!empty($config['returnArray'])) {
            $this->returnArray = $config['returnArray'];
        }
        if (!empty($config['toString'])) {
            $this->toString = $config['toString'];
        }
        if (!empty($config['logPath'])) {
            $this->logPath = $config['logPath'];
        }
    }

    /**
     * 发起请求
     * @param RequestData $requestData
     * @return mixed
     */
    abstract protected function request(RequestData $requestData);

    /**
     * 计算签名
     * @param RequestData $requestData
     * @param array $parameters
     * @return string
     */
    abstract protected function signature(RequestData $requestData, array $parameters): string;

    /**
     * 记录日志
     * @param $requestData
     * @param Result $curlResult
     */
    protected function writeLog(RequestData $requestData, Result $curlResult)
    {
        if (!file_exists($this->logPath)) {
            return;
        }
        $log = date('Y-m-d H:i:s') . PHP_EOL;
        $log .= '请求接口：' . $curlResult->getInfo('url') . PHP_EOL;
        $log .= '请求类型：' . $requestData->getMethod() . PHP_EOL;
        $log .= '请求状态：' . $curlResult->getInfo('http_code') . PHP_EOL;
        $log .= '请求时长：' . $curlResult->getInfo('total_time') . PHP_EOL;
        $log .= '请求参数：' . var_export($requestData->getParameter(), true) . PHP_EOL;
        $log .= '响应数据：' . $curlResult->getBody() . PHP_EOL;
        $log .= '错误信息：' . $curlResult->getErrno() . ':' . $curlResult->getError() . PHP_EOL;
        $log .= PHP_EOL;
        file_put_contents($this->logPath . date('Ymd') . '.log', $log, FILE_APPEND);
    }

    /**
     * 返回请求结果
     * @param Result $result
     * @return false|mixed|string
     */
    protected function returnResult(Result $result)
    {
        if ($result->getErrno() == 0) {
            $body = $result->getBody();
            //返回数组格式
            if ($this->returnArray) {
                //转化数值为字符串
                if ($this->toString) {
                    $body = preg_replace('/:(-?\d+\.\d+|-?\d+)([,}])/', ':"$1"$2', $body);
                }
                return json_decode($body, true);
            }
            return $body;
        } else {
            throw new \Exception($result->getError());
        }
    }


}