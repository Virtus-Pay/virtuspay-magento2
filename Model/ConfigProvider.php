<?php
namespace VirtusPay\Magento2\Model;

class ConfigProvider
{
    protected $scopeConfig;
    protected $assetRepo;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
        $this->storeManager = $storeManager;
    }

    // Common functions
    public function getStaticUrl()
    {
        return $this->assetRepo->getUrl("VirtusPay_Magento2::images");
    }

}
