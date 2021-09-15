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
     * @return string
     */
    public function getResponse(): string;
}
