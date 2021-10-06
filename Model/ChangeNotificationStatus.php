<?php

namespace VirtusPay\Magento2\Model;

use VirtusPay\Magento2\Model\Method\BoletoParcelado;
use VirtusPay\Magento2\Api\ChangeNotificationStatusInterface;
use VirtusPay\Magento2\Helper\Data as HelperData;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderManagementInterface;

class ChangeNotificationStatus implements ChangeNotificationStatusInterface
{
/*
-- "Status VirtusPay" [Status Loja]
- *P*endente [ ... ]
- *E*fetivado [ Processando]
- *C*ancelada [Cancelamento]
- *R*ecusada [ ... ]
- *A*nalisada [ ... ]
 */
    const STATUS = [
        'P' => 'pending_payment',
        'N' => 'pending_payment',
        'A' => 'pending_payment',
        'R' => 'canceled',
        'C' => 'canceled',
        'E' => 'processing'
    ];

    private $boletoParcelado;

    private $helperData;

    private $collectionFactory;

    private $orderRepositoryInterface;

    private $logger;

    private $converterOrder;

    private $orderManagementInterface;

    public function __construct(
        BoletoParcelado $boletoParcelado,
        HelperData $helperData,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepositoryInterface,
        LoggerInterface $logger,
        \Magento\Sales\Model\Convert\Order $converterOrder,
        OrderManagementInterface $orderManagementInterface
    ) {
        $this->boletoParcelado = $boletoParcelado;
        $this->helperData = $helperData;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->logger = $logger;
        $this->converterOrder = $converterOrder;
        $this->orderManagementInterface = $orderManagementInterface;
    }

    private function createShip($order)
    {
        $shipment = $this->converterOrder->toShipment($order);
        // Loop through order items
        foreach ($order->getAllItems() AS $orderItem) {
            // Check if order item has qty to ship or is virtual
            if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
            $qtyShipped = $orderItem->getQtyToShip();
            // Create shipment item with qty
            $shipmentItem = $this->converterOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            // Add shipment item to shipment
            $shipment->addItem($shipmentItem);
        }

        // Register shipment
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);

        try {
            // Save created shipment and order
            $shipment->save();
            $shipment->getOrder()->save();

        } catch (\Exception $e) {
            echo "Shipment Not Created". $e->getMessage(); exit;
        }
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
        $configuration->setEnvironment($this->helperData->getEnvironment(),
            $this->helperData->getToken());

        $gateway = new \VirtusPay\ApiSDK\Gateway\Order();
        $response = $gateway->getOrderByTransaction($transaction, $configuration);
        $response = json_decode($response, true);

        if (!empty($response)) {
            return $response[0];
        }

        return $response;
    }

    private function changeStatus(string $status, string $orderId)
    {
        $order = $this->orderRepositoryInterface->get($orderId);

        if (self::STATUS[$status] == "processing") {
            $this->boletoParcelado->invoiceOrder($order);
        }

        if (self::STATUS[$status] == "canceled") {
//            $order->setStatus(self::STATUS[$status]);
//            $order->setState(self::STATUS[$status]);
//            $this->orderRepositoryInterface->save($order);
            $this->orderManagementInterface->cancel($orderId);
        }

//        if (self::STATUS[$status] == "complete") {
//            $this->createShip($order);
//        }
    }

}
