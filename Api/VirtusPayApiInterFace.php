<?php

namespace VirtusPay\Magento2\Api;

interface VirtusPayApiInterFace
{
    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     */
    public function getQuote(\Magento\Quote\Api\Data\CartInterface $quote): string;

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     */
    public function createOrder(\Magento\Quote\Api\Data\CartInterface $quote): string;
}
