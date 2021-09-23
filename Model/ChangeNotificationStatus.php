<?php

namespace VirtusPay\Magento2\Model;

use VirtusPay\Magento2\Api\ChangeNotificationStatusInterface;
use VirtusPay\Magento2\Helper\Data as HelperData;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class ChangeNotificationStatus implements ChangeNotificationStatusInterface
{

    const STATUS = [
        'P' => 'pending_payment',
        'N' => 'processing',
        'A' => 'approved',
        'R' => 'refused',
        'C' => 'canceled',
        'E' => 'complete'
    ];

    private $helperData;

    private $collectionFactory;

    private $orderRepositoryInterface;

    private $logger;

    public function __construct(
        HelperData $helperData,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepositoryInterface,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->logger = $logger;
    }

    public function execute(string $transaction)
    {

        try {
            $collection = $this->collectionFactory->create();
            $result = $collection->getSelect()
                ->where('txn_id = ?', $transaction);
            $transactionData = $result->query()->fetch();

            $orderId = $transactionData['order_id'];
            $data = $this->getOrderByTransactionSDK($transaction);

            $this->changeStatus($data['status'], $orderId);

        } catch (\Exception $e) {
             $this->logger->info("Error Status Webhook: " . $e);
        }
    }

    private function getOrderByTransactionSDK(string $transaction)
    {
        $response = "";
        $configuration = new \VirtusPay\ApiSDK\Configuration();
        $configuration->setEnvironment($this->helperData->getEnvironment());

        $gateway = new \VirtusPay\ApiSDK\Gateway\Order();
        $response = $gateway->getOrderByTransaction($transaction);
        $response = json_decode($response, true);

        if (!empty($response)) {
            return $response[0];
        }

        return $response;
    }

    private function changeStatus(string $status, string $orderId)
    {
        $order = $this->orderRepositoryInterface->get($orderId);
        $order->setStatus(self::STATUS[$status]);
        $order->setState(self::STATUS[$status]);

        return $this->orderRepositoryInterface->save($order);
    }

}
