<?php

namespace VirtusPay\Magento2\Model;

use VirtusPay\Magento2\Api\ApiResponseInterface;
use VirtusPay\Magento2\Helper\Data as HelperData;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Directory\Model\RegionFactory;

class VirtusPayApi implements \VirtusPay\Magento2\Api\VirtusPayApiInterface
{

    const CALLBACK = "https://www.minhaloja.com.br/api2/virtus_callback";

    const RETURN_URL = "https://www.minhaloja.com.br/checkout?order=";

    protected $checkoutSession;

    protected $remoteAddress;

    protected $apiResponse;

    protected $othersInfo;

    protected $categoryRepositoryInterface;

    protected $regionFactory;

    protected $helperData;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \VirtusPay\Magento2\Model\Installments\OthersInfo $othersInfo,
        ApiResponseInterface $apiResponse,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        RegionFactory $regionFactory,
        HelperData $helperData
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->remoteAddress = $remoteAddress;
        $this->apiResponse = $apiResponse;
        $this->othersInfo = $othersInfo;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
    }

    /**
     * @inheirtDoc
     */

    public function getQuote(): ApiResponseInterface
    {

        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment($this->helperData->getEnvironment());

        $quote = $this->checkoutSession->getQuote();

        $totalAmount = $quote->getGrandTotal();
        $taxvat = $quote->getCustomer()->getTaxvat();
        $telephone = $quote->getShippingAddress()->getTelephone();
        $postcode = $quote->getShippingAddress()->getPostcode();

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

        $this->definePreapprovved($response);

        $this->apiResponse->setResponse($response);
        return $this->apiResponse;
    }

    private function definePreapprovved($response)
    {
        $data = json_decode($response, true);
        $id = $data['id'];
        $installments = $data['installments'];
        $inst = (is_null($installments)) ? 1 : $installments;

        $this->checkoutSession->setPreapproved($id);
        $this->checkoutSession->setInstallments($inst);
    }

    private function getCategoryDescription(int $id)
    {
        $category = $this->categoryRepositoryInterface->get($id);
        return $category->getName();
    }

    private function getRegionCodeById($regionId)
    {
        $region = $this->regionFactory->create()->load($regionId);
        return $region->getCode();
    }

    /**
     * @inheirtDoc
     */
    public function createOrder(): string
    {
        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment($this->helperData->getEnvironment());

        $itemSDK = [];
        $productsOrders = [];
        $quote = $this->checkoutSession->getQuote();
        $customer = $quote->getCustomer();
        $items = $quote->getItems();

        if (!empty($items)) {
            foreach ($items as $k => $item) {
                $itemSDK[$k]['product'] = $item->getName();
                $itemSDK[$k]['price'] = $item->getPrice();
                $itemSDK[$k]['detail'] = $item->getSku();
                $itemSDK[$k]['quantity'] = $item->getQty();
                $itemSDK[$k]['category'] = $this->getCategoryDescription((int) $item->getProduct()->getCategoryIds()[0]);
                $productsOrders[] = $item->getName();
            }

            $ordersProducts = implode(";", $productsOrders);
            $modelItems = new \VirtusPay\ApiSDK\Model\Items($itemSDK);
        }

        $billingAddres = $quote->getBillingAddress();
        $deliveryAddress = new \VirtusPay\ApiSDK\Model\DeliveryAddress(
            $this->getRegionCodeById($billingAddres->getRegionId()),
            $billingAddres->getCity(),
            $billingAddres->getStreet()[$this->helperData->getDistrict()],
            $billingAddres->getStreet()[$this->helperData->getStreet()],
            $billingAddres->getStreet()[$this->helperData->getNumber()],
            $billingAddres->getPostcode(),
            $billingAddres->getStreet()[$this->helperData->getComplement()]
        );


        $shippingAddress = $quote->getShippingAddress();
        $customerAddress = new \VirtusPay\ApiSDK\Model\CustomerAddress(
            $this->getRegionCodeById($shippingAddress->getRegionId()),
            $shippingAddress->getCity(),
            $shippingAddress->getStreet()[$this->helperData->getDistrict()],
            $shippingAddress->getStreet()[$this->helperData->getStreet()],
            $shippingAddress->getStreet()[$this->helperData->getNumber()],
            $shippingAddress->getPostcode(),
            $shippingAddress->getStreet()[$this->helperData->getComplement()]
        );

        $customer = new \VirtusPay\ApiSDK\Model\Customer(
            $customer->getFirstname() . " " . $customer->getLastname(),
            $customer->getTaxvat(),
            1500.00,
            $customer->getEmail(),
            $shippingAddress->getTelephone(),
            $quote->getCustomerDob(), $customerAddress
        );

        $return_url = self::RETURN_URL.$quote->getReservedOrderId()."&closed=true";

        $order = new \VirtusPay\ApiSDK\Model\Order(
            $quote->getReservedOrderId(), $customer, $deliveryAddress, $modelItems, $quote->getGrandTotal(),
            $this->checkoutSession->getInstallments(),
            $ordersProducts, self::CALLBACK,
            $return_url, "checkout",
            $this->checkoutSession->getPreapproved()
        );

        $gateway = new \VirtusPay\ApiSDK\Gateway\Order();
        $response = $gateway->save($order);

        return $response;
    }
}
