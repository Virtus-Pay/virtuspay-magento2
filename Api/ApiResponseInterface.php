<?php

namespace VirtusPay\Magento2\Api;

interface ApiResponseInterface
{
    /**
     * @param $response
     * @return bool
     */
    public function setResponse($response);

    /**
     * @return \stdClass
     */
    public function getResponse(): \stdClass;
}
