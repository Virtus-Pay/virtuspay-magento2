<?php

namespace VirtusPay\Magento2\Model;

use VirtusPay\Magento2\Api\ApiResponseInterface;
use VirtusPay\Magento2\Helper\Data as HelperData;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Store\Model\StoreManagerInterface;

class VirtusPayApi implements \VirtusPay\Magento2\Api\VirtusPayApiInterface
{

    protected $checkoutSession;

    protected $remoteAddress;

    protected $apiResponse;

    protected $othersInfo;

    protected $categoryRepositoryInterface;

    protected $regionFactory;

    protected $helperData;

    protected $storedManager;

    protected $logger;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \VirtusPay\Magento2\Model\Installments\OthersInfo $othersInfo,
        ApiResponseInterface $apiResponse,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        RegionFactory $regionFactory,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->remoteAddress = $remoteAddress;
        $this->apiResponse = $apiResponse;
        $this->othersInfo = $othersInfo;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
        $this->storedManager = $storeManager;
        $this->logger = $logger;
        $this->checkoutSession->setPreapproved(null);
    }

    /**
     * @inheirtDoc
     */

    public function getQuote(): ApiResponseInterface
    {

        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment($this->helperData->getEnvironment(),
            $this->helperData->getToken());
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
            str_replace("-","",$postcode)
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

        $this->definePreApproved($response);

        $this->apiResponse->setResponse($response);
        return $this->apiResponse;
    }

    private function definePreApproved($response)
    {
        $data = json_decode($response, true);
        $id = $data['id'];
        $installments = $data['installments'];
        $inst = ($installments === null) ? 1 : $installments;
        if ($data['preapproved']) {
            $this->checkoutSession->setPreapproved($id);
        }
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
    public function createOrder($order, $payment): string
    {
        if($payment['preapproved'] !== "1") {
            $message = 'O pagamento não foi pré-aprovado';
            throw new \Magento\Framework\Validator\Exception(__($message));
        }
        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment($this->helperData->getEnvironment(),
            $this->helperData->getToken());

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
                $itemSDK[$k]['category'] =
                    $this->getCategoryDescription((int) $item->getProduct()->getCategoryIds()[0]);
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
            str_replace("-", "", $billingAddres->getPostcode()),
            $billingAddres->getStreet()[$this->helperData->getComplement()]
        );

        $shippingAddress = $quote->getShippingAddress();
        $customerAddress = new \VirtusPay\ApiSDK\Model\CustomerAddress(
            $this->getRegionCodeById($shippingAddress->getRegionId()),
            $shippingAddress->getCity(),
            $shippingAddress->getStreet()[$this->helperData->getDistrict()],
            $shippingAddress->getStreet()[$this->helperData->getStreet()],
            $shippingAddress->getStreet()[$this->helperData->getNumber()],
            str_replace("-", "", $billingAddres->getPostcode()),
            $shippingAddress->getStreet()[$this->helperData->getComplement()]
        );
        $dob = $quote->getCustomerDob();
        /* DOB DISABLED
//        $this->logger->info("DOB: ". $dob);
                if (!$dob) {
                    $dob = $payment['dob'];
                    $this->logger->info("DOB: ". $dob);
                    if ($dob) {
                        $dob = substr($dob, 6, 4)
                            . "-" . substr($dob, 3, 2) . "-" . substr($dob, 0, 2);
                    }
                }

//        $this->logger->info("DOB: ". $dob);
        if (!$dob) {
            $dob = "1900-01-01";
        }
//        $this->logger->info("DOB: ". $dob);
        */
        $customer = new \VirtusPay\ApiSDK\Model\Customer(
            $customer->getFirstname() . " " . $customer->getLastname(),
            $customer->getTaxvat(),
            1500.00,
            $customer->getEmail(),
            $shippingAddress->getTelephone(),
            $customerAddress
        );

        $url = $this->storedManager->getStore()->getBaseUrl();

        $return_url = $url."checkout/onepage/success/";
        $callback_url = $url."rest/V1/virtuspay/change-notification-status";
        $installments = 1;
        if ($payment['installments']) {
            $installments = $payment['installments'];
        }

        $orderSDK = new \VirtusPay\ApiSDK\Model\Order(
            $order->getIncrementId(),
            $customer,
            $deliveryAddress,
            $modelItems,
            $order->getGrandTotal(),
            $installments,
            $ordersProducts,
            $callback_url,
            $return_url,
            "checkout",
            $payment['quoteid']
        );
//        $this->checkoutSession->getPreapproved()
        $gateway = new \VirtusPay\ApiSDK\Gateway\Order();
        return $gateway->save($orderSDK);
    }
}
