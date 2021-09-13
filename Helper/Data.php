<?php

declare(strict_types=1);

namespace VirtusPay\Magento2\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->scopeConfig->getValue(
            'payment/virtuspay/token',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getEnvironment()
    {
        $environment = $this->scopeConfig->getValue(
            'payment/virtuspay/environment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($environment == 0) {
            return "homolog";
        } else {
            return "production";
        }
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->scopeConfig->getValue(
            'payment/virtuspay/street',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->scopeConfig->getValue(
            'payment/virtuspay/number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getComplement()
    {
        return $this->scopeConfig->getValue(
            'payment/virtuspay/complement',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->scopeConfig->getValue(
            'payment/virtuspay/district',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
