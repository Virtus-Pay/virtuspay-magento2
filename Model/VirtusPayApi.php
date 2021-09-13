<?php

namespace VirtusPay\Magento2\Model;

use VirtusPay\Magento2\Api\ApiResponseInterface;

class VirtusPayApi implements \VirtusPay\Magento2\Api\VirtusPayApiInterface
{
    protected $token;

    protected $scopeConfig;

    protected $checkoutSession;

    protected $remoteAddress;

    protected $apiResponse;

    protected $othersInfo;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \VirtusPay\Magento2\Model\Installments\OthersInfo $othersInfo,
        ApiResponseInterface $apiResponse
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->remoteAddress = $remoteAddress;
        $this->apiResponse = $apiResponse;
        $this->othersInfo = $othersInfo;
    }

    /**
     * @inheirtDoc
     */

    public function getQuote(): ApiResponseInterface
    {
        $quote = $this->checkoutSession->getQuote();

        $totalAmount = $quote->getGrandTotal();
        $taxvat = $quote->getCustomer()->getTaxvat();
        $telephone = $quote->getShippingAddress()->getTelephone();
        $postcode = $quote->getShippingAddress()->getPostcode();
        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment('homolog');

        $othersInfo = $this->othersInfo->getOthersInfo($quote->getCustomer()->getEmail());

        $model = new \VirtusPay\ApiSDK\Model\PreAprovacao(
            $totalAmount,
            $taxvat,
            $telephone,
            $quote->getCustomer()->getEmail(),
            $this->remoteAddress->getRemoteAddress(),
            $othersInfo,
            $postcode
        );

        if (empty($taxvat)) {
            $data = [];
            $data['message'] = __('Taxvat is not empty.');
            $response = json_encode($data);
            $this->apiResponse->setResponse($response);
            return $this->apiResponse;
        }

        $gateway = new \VirtusPay\ApiSDK\Gateway\PreAprovacao();
        $response = $gateway->execute($model);

        $this->apiResponse->setResponse($response);
        return $this->apiResponse;

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
