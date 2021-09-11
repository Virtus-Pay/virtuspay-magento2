<?php

namespace VirtusPay\Magento2\Api;

interface VirtusPayApiInterface
{
    /**
     * @return ApiResponseInterface
     */
    public function getQuote(): ApiResponseInterface;

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     */
    public function createOrder(\Magento\Quote\Api\Data\CartInterface $quote): string;
}
