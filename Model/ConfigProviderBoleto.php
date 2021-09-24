<?php
namespace VirtusPay\Magento2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProviderBoleto extends \VirtusPay\Magento2\Model\ConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = "virtuspay";

    protected $method;
    protected $escaper;
    protected $scopeConfig;
    protected $customer;

    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->scopeConfig = $scopeConfig;
        $this->customer = $customer;
        parent::__construct($scopeConfig,$assetRepo,$storeManager);
    }

    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'virtuspay' => [
                    'fullname' => $this->getFullName(),
                    'taxvat' => $this->getTaxVat(),
                ],
            ],
        ] : [];
    }

    public function getFullName()
    {
        if ($this->customer->isLoggedIn()) {
            return $this->customer->getCustomer()->getName();
        }
        return "";
    }
    public function getTaxVat()
    {
        if ($this->customer->isLoggedIn()) {
            return $this->customer->getCustomer()->getTaxvat();
        }
        return "";
    }
}
