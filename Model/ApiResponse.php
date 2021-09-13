<?php

namespace VirtusPay\Magento2\Model;

use VirtusPay\Magento2\Api\ApiResponseInterface;

class ApiResponse implements ApiResponseInterface
{
    protected $response;

    public function setResponse($response): bool
    {
        $this->response = $response;
        return true;
    }
    public function getResponse(): \stdClass
    {
        return $this->response;
    }
}
