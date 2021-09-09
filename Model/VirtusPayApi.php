<?php

namespace VirtusPay\Magento2\Model;

class VirtusPayApi implements \VirtusPay\Magento2\Api\VirtusPayApiInterface
{
    protected $token;
    protected $scopeConfig;
    protected $checkoutSession;
    protected $remoteAddress;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @inheirtDoc
     */

    public function getQuote(): string
    {
        $quote = $this->checkoutSession->getQuote();
        $totalAmount = $quote->getGrandTotal();
        $taxvat = $quote->getCustomer()->getTaxvat();
        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment('homolog');

        $telephone = $quote->getShippingAddress()->getTelephone()

        $model = new \VirtusPay\ApiSDK\Model\PreAprovacao(
            $totalAmount,
            $taxvat,
            $telephone,
            'ricardo@gmail.com',
            $this->remoteAddress->getRemoteAddress(),
            [],
            '05846050'
        );

        $gateway = new \VirtusPay\ApiSDK\Gateway\PreAprovacao();
        $response = $gateway->execute($model);

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
    protected function getToken()
    {
        return $this->scopeConfig->getValue(
            'payment/virtuspay/token',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
