<?php

namespace VirtusPay\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;

class ObserverforDisabledFrontendPg implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;
    /**
     * @var
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_appState = $appState;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $method_instance = $observer->getEvent()->getMethodInstance();
        $quote = $observer->getEvent()->getQuote();
        if ($method_instance->getCode() == 'virtuspayboleto'
            && !$this->scopeConfig->getValue("payment/virtuspay/enable")) {
            $result->setData('is_available', false);
        }
    }

    /**
     * @return array
     */
    protected function getDisableAreas()
    {
        return [\Magento\Framework\App\Area::AREA_FRONTEND, \Magento\Framework\App\Area::AREA_WEBAPI_REST];
    }
}
