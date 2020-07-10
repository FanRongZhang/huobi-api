<?php

namespace sreeb\api;

class RequestData
{
    private $method;
    private $apiPath;
    private $parameter;


    /**
     * RequestData constructor.
     * @param string $method
     * @param string $apiPath
     * @param array $parameter
     */
    public function __construct(string $method, string $apiPath, array $parameter)
    {
        $this->method = $method;
        $this->apiPath = $apiPath;
        $this->parameter = $parameter;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getApiPath(): string
    {
        return $this->apiPath;
    }

    /**
     * @return array
     */
    public function getParameter(): array
    {
        return $this->parameter;
    }

}