<?php

namespace VirtusPay\Magento2\Model;

class VirtusPayApi implements \VirtusPay\Magento2\Api\VirtusPayApiInterFace
{
    protected $token;
    protected $quote;
    protected $order;
    protected $scopeConfig;

    public function __construct(
        \VirtusPay\VirtusPaySdkPhp\Controller\Quote $quote,
        \VirtusPay\VirtusPaySdkPhp\Controller\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->quote = $quote;
        $this->order = $order;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheirtDoc
     */

    public function getQuote(\Magento\Quote\Api\Data\CartInterface $quote): string
    {
        $this->quote->setToken($this->getToken());
        return "";
    }

    /**
     * @inheirtDoc
     */
    public function createOrder(\Magento\Quote\Api\Data\CartInterface $quote): string
    {
        return "";
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->scopeConfig->getValue(
            'dev/debug/template_hints',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
