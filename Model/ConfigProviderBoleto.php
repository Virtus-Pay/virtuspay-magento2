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
    protected $ccConfig;

    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\CcConfig $ccConfig
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->scopeConfig = $scopeConfig;
        $this->customer = $customer;
        $this->ccConfig = $ccConfig;
        parent::__construct($scopeConfig,$assetRepo,$storeManager);
    }

    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'virtuspay' => [
                    'fullname' => $this->getFullName(),
                    'taxvat' => $this->getTaxVat(),
                    'logo' => $this->getLogo()
                ],
            ],
        ] : [];
    }

    public function getLogo()
    {
        $asset = $this->ccConfig
            ->createAsset('VirtusPay_Magento2::images/logo_virtuspay_azulgrad_400.png');
        return $asset->getUrl();
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
