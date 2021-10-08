<?php

namespace VirtusPay\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;


class OrderCreate implements ObserverInterface
{
    protected $_order;
    protected $logger;
    protected $_orderRepository;
    protected $responseFactory;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_order = $order;
        $this->_orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        //  Magento 2.2.* compatibility
        if (!$order) {
            $orderids = $observer->getEvent()->getOrderIds();
            foreach ($orderids as $orderid) {
                $order = $this->_order->load($orderid);
            }
        }
        $order = $this->_orderRepository->get($order->getId());
        $payment = $order->getPayment();
        $method = $payment->getMethod();
        if ($method=="virtuspay") {
            $order->setState('new')->setStatus('pending');
            $payment->setAdditionalInformation('order_created', '1');
            $order->save();
        }

        $link = $payment->getAdditionalInformation('link_virtus_pay');

        if (!empty($link)) {
            $this->responseFactory->create()->setRedirect($link)->sendResponse();
        }
    }
}
