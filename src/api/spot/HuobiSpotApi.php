<?php


namespace sreeb\api\spot;


use sreeb\api\AbstractHuobiApi;
use sreeb\api\RequestData;

class HuobiSpotApi extends AbstractHuobiApi
{
    /**
     * HuobiSpotApi constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @param RequestData $requestData
     * @return false|mixed|string
     * @throws \Exception
     */
    protected function request(RequestData $requestData)
    {
        $parameters = [
            'AccessKeyId' => $this->AccessKey,
            'SignatureMethod' => $this->SignatureMethod,
            'SignatureVersion' => $this->SignatureVersion,
            'Timestamp' => gmdate('Y-m-d\TH:i:s'),
        ];
        //GET请求业务参数参与计算签名
        if ($requestData->getMethod() == 'GET' && !empty($requestData->getParameter())) {
            $parameters = array_merge($parameters, $requestData->getParameter());
        }

        //计算签名
        $parameters['Signature'] = $this->signature($requestData, $parameters);

        //POST请求需要把业务参数放到请求body
        if ($requestData->getMethod() == 'GET') {
            $res = $this->curl->request($this->ApiHost . $requestData->getApiPath() . '?' . http_build_query($parameters), $requestData->getMethod())->send(false);
        } else {
            $res = $this->curl->setHeader('Content-Type', 'application/json')->request($this->ApiHost . $requestData->getApiPath() . '?' . http_build_query($parameters), $requestData->getMethod(), $requestData->getParameter(), true)->send(false);
        }

        //记录日志
        $this->writeLog($requestData, $res);

        return $this->returnResult($res);

    }

    /**
     * @param RequestData $requestData
     * @param array $parameters
     * @return string
     */
    protected function signature(RequestData $requestData, array $parameters): string
    {
        ksort($parameters);
        $apiHost = substr($this->ApiHost, strpos($this->ApiHost, '//') + 2);
        $str = $requestData->getMethod() . "\n" . $apiHost . "\n" . $requestData->getApiPath() . "\n" . http_build_query($parameters, null, '&', PHP_QUERY_RFC3986);
        return base64_encode(hash_hmac('sha256', $str, $this->SecretKey, true));
    }
}