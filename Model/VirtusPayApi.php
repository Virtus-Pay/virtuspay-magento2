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

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        ApiResponseInterface $apiResponse
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->remoteAddress = $remoteAddress;
        $this->apiResponse = $apiResponse;
    }

    /**
     * @inheirtDoc
     */

    public function getQuote(): ApiResponseInterface
    {
        $return =  '{
  "preapproved": true,
  "id": "3810de8a-770c-46d8-a33e-80655753a550",
  "total_amount": 2000.1,
  "installments": [
    {
      "installment": 3,
      "down_payment": 702.95,
      "outstanding_balance": 702.95,
      "total": 2108.85,
      "interest": 5
    },
    {
      "installment": 6,
      "down_payment": 378.86,
      "outstanding_balance": 378.86,
      "total": 2273.16,
      "interest": 5
    },
    {
      "installment": 9,
      "down_payment": 271.7,
      "outstanding_balance": 271.7,
      "total": 2445.3,
      "interest": 5
    },
    {
      "installment": 12,
      "down_payment": 218.83,
      "outstanding_balance": 218.83,
      "total": 2625.96,
      "interest": 5
    }
  ],
  "cet": "77,61% a.a."
}';

        $this->apiResponse->setResponse($return);
        return $this->apiResponse;
        $quote = $this->checkoutSession->getQuote();
        $totalAmount = $quote->getGrandTotal();
        $taxvat = $quote->getCustomer()->getTaxvat();
        $telephone = $quote->getShippingAddress()->getTelephone();
        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment('homolog');

        $model = new \VirtusPay\ApiSDK\Model\PreAprovacao(
            $totalAmount,
            $taxvat,
            $telephone,
            $quote->getCustomer()->getEmail(),
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
