<?php

namespace VirtusPay\Magento2\Api;

interface VirtusPayApiInterface
{
    /**
     * @return ApiResponseInterface
     */
    public function getQuote(): ApiResponseInterface;

    /**
     * @return string
     */
    public function createOrder(): string;
}
