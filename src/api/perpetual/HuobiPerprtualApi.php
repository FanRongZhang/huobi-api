<?php

namespace sreeb\api\perpetual;

use Exception;
use sreeb\api\AbstractHuobiApi;
use sreeb\api\RequestData;

class HuobiPerprtualApi extends AbstractHuobiApi
{

    /**
     * HuobiPerprtualApi constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 发起请求
     * @param RequestData $requestData
     * @return false|mixed|string
     * @throws Exception
     */
    public function request(RequestData $requestData)
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
     * 计算签名
     * @param RequestData $requestData
     * @param array $parameters
     * @return string
     */
    protected function signature(RequestData $requestData,array $parameters): string
    {
        ksort($parameters);
        $apiHost = substr($this->ApiHost, strpos($this->ApiHost, '//') + 2);
        $str = $requestData->getMethod() . "\n" . $apiHost . "\n" . $requestData->getApiPath() . "\n" . http_build_query($parameters, null, '&', PHP_QUERY_RFC3986);
        return base64_encode(hash_hmac('sha256', $str, $this->SecretKey, true));
    }

    /**
     * 获取合约信息
     * @param mixed $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapContractInfo($contractCode = [])
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_contract_info', $requestData);
        return $this->request($requestData);
    }


    /**
     * 获取合约指数信息
     * @param mixed $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapIndex($contractCode = [])
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_index', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取合约指最高限价和最低限价
     * @param mixed $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapPriceLimit($contractCode = [])
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取当前可用合约总持仓量
     * @param mixed $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapOpenInterest($contractCode = [])
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_open_interest', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取行情深度数据
     * @param mixed $contractCode
     * @param string $type
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketDepth($contractCode, string $type = '')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "type" => $type,
            ];
        }
        $requestData = new RequestData('GET', '/swap-ex/market/depth', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取K线数据
     * @param $contractCode
     * @param string $period
     * @param int $size
     * @param int $from
     * @param int $to
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketKline($contractCode, string $period = '', int $size = 150, int $from = 0, int $to = 0)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "period" => $period,
                "size" => $size,
                "from" => $from,
                "to" => $to,
            ];
        }
        $requestData = new RequestData('GET', '/swap-ex/market/history/kline', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取聚合行情
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketMerged($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-ex/market/detail/merged', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取市场最近成交记录
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketTrade($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-ex/market/trade', $requestData);
        return $this->request($requestData);
    }

    /**
     * 批量获取最近的交易记录
     * @param $contractCode
     * @param int $size
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketTrades($contractCode, int $size = 1)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "size" => $size,
            ];
        }
        $requestData = new RequestData('GET', '/swap-ex/market/history/trade', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询合约风险准备金月和预估分摊比例
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapRiskInfo($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_risk_info', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询合约风险准备金余额历史数据
     * @param $contractCode
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapInsuranceFund($contractCode, int $pageIndex = 1, int $pageSize = 100)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_insurance_fund', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询平台阶梯调整系数
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapAdjustfactor($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_adjustfactor', $requestData);
        return $this->request($requestData);
    }

    /**
     * 平台持仓量查询
     * @param $contractCode
     * @param string $period
     * @param int $size
     * @param int $amountType
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapHisOpenInterest($contractCode, string $period = '60min', int $size = 48, int $amountType = 1)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "period" => $period,
                "size" => $size,
                "amount_type" => $amountType,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_his_open_interest', $requestData);
        return $this->request($requestData);
    }

    /**
     * 精英账户多空持仓对比-账户数
     * @param $contractCode
     * @param string $period
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapEliteAccountRatio($contractCode, string $period = '60min')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "period" => $period,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_elite_account_ratio', $requestData);
        return $this->request($requestData);
    }

    /**
     * 精英账户多空持仓对比-持仓量
     * @param $contractCode
     * @param string $period
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapElitePositionRatio($contractCode, string $period = '60min')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "period" => $period,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_elite_position_ratio', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询系统状态
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapApiState($contractCode = [])
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_api_state', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取合约的资金费率
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapFundingRate($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_funding_rate', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取合约的历史资金费率
     * @param $contractCode
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapHistoricalFundingRate($contractCode, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_historical_funding_rate', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取强平订单
     * @param $contractCode
     * @param int $tradeType
     * @param int $createDate
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapLiquidationOrders($contractCode, int $tradeType = 0, int $createDate = 7, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "trade_type" => $tradeType,
                "create_date" => $createDate,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('GET', '/swap-api/v1/swap_liquidation_orders', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取合约的溢价指数K线
     * @param $contractCode
     * @param string $period
     * @param int $size
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapPremiumIndexKline($contractCode, string $period = '15min', int $size = 100)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "period" => $period,
                "size" => $size,
            ];
        }
        $requestData = new RequestData('GET', '/index/market/history/swap_premium_index_kline', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取实时预测资金费率的K线数据
     * @param $contractCode
     * @param string $period
     * @param int $size
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapEstimatedRateKline($contractCode, string $period = '15min', int $size = 100)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "period" => $period,
                "size" => $size,
            ];
        }
        $requestData = new RequestData('GET', '/index/market/history/swap_estimated_rate_kline', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取基差数据
     * @param $contractCode
     * @param string $period
     * @param string $basisPriceType
     * @param int $size
     * @return false|mixed|string
     * @throws Exception
     */
    public function marketSwapBasis($contractCode, string $period = '15min', string $basisPriceType = 'open', int $size = 100)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "period" => $period,
                "basis_price_type" => $basisPriceType,
                "size" => $size,
            ];
        }
        $requestData = new RequestData('GET', '/index/market/history/swap_basis', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取用户账户信息
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapAccountInfo($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_account_info', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取用户持仓信息
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapPositionInfo($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_position_info', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询用户账户和持仓信息
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapAccountPositionInfo($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_account_position_info', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询母账户下所有子账户资产信息
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapSubAccountList($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_sub_account_list', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询单个子账户资产信息
     * @param $contractCode
     * @param int $subUid
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapSubAccountInfo($contractCode, $subUid = 0)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "sub_uid" => $subUid,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_sub_account_info', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询单个子账户持仓信息
     * @param $contractCode
     * @param int $subUid
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapSubPositionInfo($contractCode, $subUid = 0)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "sub_uid" => $subUid,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_sub_position_info', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询用户财务记录
     * @param $contractCode
     * @param string $type
     * @param int $createDate
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapFinancialRecord($contractCode, string $type = '', int $createDate = 30, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "type" => $type,
                "create_date" => $createDate,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_financial_record', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询用户当前的下单量限制
     * @param $contractCode
     * @param string $orderPriceType
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapOrderLimit($contractCode, string $orderPriceType = 'limit')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "order_price_type" => $orderPriceType,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_order_limit', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询用户当前手续费费率
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapFee($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_fee', $requestData);
        return $this->request($requestData);
    }

    /**
     * 查询用户当前的划转限制
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapTransferLimit($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_transfer_limit', $requestData);
        return $this->request($requestData);
    }

    /**
     * 用户持仓量限制的查询
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapPositionLimit($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_position_limit', $requestData);
        return $this->request($requestData);
    }

    /**
     * 母子账户划转
     * @param $subUid
     * @param string $contractCode
     * @param float|int $amount
     * @param string $type
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapMasterSubTransfer($subUid, string $contractCode = 'BTC-USD', float $amount = 0, string $type = 'master_to_sub')
    {
        if (is_array($subUid)) {
            $requestData = $subUid;
        } else {
            $requestData = [
                "sub_uid" => $subUid,
                "contract_code" => $contractCode,
                "amount" => $amount,
                "type" => $type,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_master_sub_transfer', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取母账户下的所有母子账户划转记录
     * @param $contractCode
     * @param string $transferType
     * @param int $createDate
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapMasterSubTransferRecord($contractCode, string $transferType = '34', int $createDate = 30, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "transfer_type" => $transferType,
                "create_date" => $createDate,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_master_sub_transfer_record', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取用户的API指标禁用信息
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountSwapApiTradingStatus()
    {
        $requestData = [];
        $requestData = new RequestData('GET', '/swap-api/v1/swap_api_trading_status', $requestData);
        return $this->request($requestData);
    }

    /**
     * 合约下单
     * @param $contractCode
     * @param int $clientOrderId
     * @param float|int $price
     * @param int $volume
     * @param string $direction
     * @param string $offset
     * @param int $leverRate
     * @param string $orderPriceType
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapOrder($contractCode, int $clientOrderId = 0, float $price = 0, int $volume = 0, string $direction = 'buy', string $offset = 'open', int $leverRate = 10, string $orderPriceType = 'limit')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "client_order_id" => $clientOrderId,
                "price" => $price,
                "volume" => $volume,
                "direction" => $direction,
                "offset" => $offset,
                "lever_rate" => $leverRate,
                "order_price_type" => $orderPriceType,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_order', $requestData);
        return $this->request($requestData);
    }

    /**
     * 合约批量下单
     * @param array $orderData
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapBatchOrder(array $orderData)
    {
        $requestData = new RequestData('POST', '/swap-api/v1/swap_batchorder', $orderData);
        return $this->request($requestData);
    }

    /**
     * 撤销订单
     * @param $contractCode
     * @param string $orderId
     * @param string $clientOrderId
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapCancel($contractCode, string $orderId = '', string $clientOrderId = '')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "order_id" => $orderId,
                "client_order_id" => $clientOrderId,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_cancel', $requestData);
        return $this->request($requestData);
    }

    /**
     * 全部撤单
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapCancelAll($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_cancelall', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取合约订单信息
     * @param $contractCode
     * @param string $orderId
     * @param string $clientOrderId
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapOrderInfo($contractCode, string $orderId = '', string $clientOrderId = '')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "order_id" => $orderId,
                "client_order_id" => $clientOrderId,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_order_info', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取订单明细信息
     * @param $contractCode
     * @param string $orderId
     * @param int $createdAt
     * @param int $OrderType
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapOrderDetail($contractCode, string $orderId = '', int $createdAt = 0, int $OrderType = 1, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "order_id" => $orderId,
                "created_at" => $createdAt,
                "order_type" => $OrderType,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_order_detail', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取合约当前未成交委托
     * @param $contractCode
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapOrderOpenOrders($contractCode, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_openorders', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取合约历史委托
     * @param $contractCode
     * @param int $tradeType
     * @param int $type
     * @param string $status
     * @param int $creatDate
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapHisOrders($contractCode, int $tradeType = 0, int $type = 1, string $status = '0', int $creatDate = 30, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "trade_type" => $tradeType,
                "type" => $type,
                "status" => $status,
                "create_date" => $creatDate,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_hisorders', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取历史成交记录
     * @param $contractCode
     * @param int $tradeType
     * @param int $creatDate
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapMatchresults($contractCode, int $tradeType = 0, int $creatDate = 30, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "trade_type" => $tradeType,
                "create_date" => $creatDate,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_matchresults', $requestData);
        return $this->request($requestData);
    }

    /**
     * 闪电平仓下单
     * @param $contractCode
     * @param int $volume
     * @param string $direction
     * @param int $clientOrderId
     * @param string $orderPriceType
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapLightningClosePosition($contractCode, int $volume = 0, string $direction = 'buy', int $clientOrderId = 0, string $orderPriceType = 'lightning')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "volume" => $volume,
                "direction" => $direction,
                "client_order_id" => $clientOrderId,
                "order_price_type" => $orderPriceType,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_lightning_close_position', $requestData);
        return $this->request($requestData);
    }

    /**
     * 合约计划委托下单
     * @param $contractCode
     * @param string $triggerType
     * @param float|int $triggerPrice
     * @param float|int $orderPrice
     * @param string $orderPriceType
     * @param int $volume
     * @param string $direction
     * @param string $offset
     * @param int $leverRate
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapTriggerOrder($contractCode, string $triggerType = 'ge', float $triggerPrice = 0, float $orderPrice = 0, string $orderPriceType = 'limit', int $volume = 0, string $direction = 'buy', string $offset = 'open', int $leverRate = 0)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "trigger_type" => $triggerType,
                "trigger_price" => $triggerPrice,
                "order_price" => $orderPrice,
                "order_price_type" => $orderPriceType,
                "volume" => $volume,
                "direction" => $direction,
                "offset" => $offset,
                "lever_rate" => $leverRate,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_trigger_order', $requestData);
        return $this->request($requestData);
    }

    /**
     * 合约计划委托撤单
     * @param $contractCode
     * @param string $orderId
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapTriggerCancel($contractCode, string $orderId = '')
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "order_id" => $orderId,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_trigger_cancel', $requestData);
        return $this->request($requestData);
    }

    /**
     * 合约计划全部撤单
     * @param $contractCode
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapTriggerCancelAll($contractCode)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_trigger_cancelall', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取计划委托当前委托
     * @param $contractCode
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapTriggerOpenOrders($contractCode, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_trigger_openorders', $requestData);
        return $this->request($requestData);
    }

    /**
     * 获取计划委托历史委托
     * @param $contractCode
     * @param int $tradeType
     * @param string $status
     * @param int $createDate
     * @param int $pageIndex
     * @param int $pageSize
     * @return false|mixed|string
     * @throws Exception
     */
    public function contractSwapTriggerHisOrders($contractCode, int $tradeType = 0, string $status = '0', int $createDate = 30, int $pageIndex = 1, int $pageSize = 20)
    {
        if (is_array($contractCode)) {
            $requestData = $contractCode;
        } else {
            $requestData = [
                "contract_code" => $contractCode,
                "trade_type" => $tradeType,
                "status" => $status,
                "create_date" => $createDate,
                "page_index" => $pageIndex,
                "page_size" => $pageSize,
            ];
        }
        $requestData = new RequestData('POST', '/swap-api/v1/swap_trigger_hisorders', $requestData);
        return $this->request($requestData);
    }

    /**
     * 现货-永续合约账户间进行资金的划转
     * @param $from
     * @param string $to
     * @param string $currency
     * @param float|int $amount
     * @return false|mixed|string
     * @throws Exception
     */
    public function accountTransfer($from, string $to = '', string $currency = '', float $amount = 0)
    {
        if (is_array($from)) {
            $requestData = $from;
        } else {
            $requestData = [
                "from" => $from,
                "to" => $to,
                "currency" => $currency,
                "amount" => $amount,
            ];
        }
        $requestData = new RequestData('POST', '/v2/account/transfer', $requestData);
        return $this->request($requestData);
    }
}