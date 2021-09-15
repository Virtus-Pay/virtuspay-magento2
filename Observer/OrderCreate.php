<?php

namespace VirtusPay\Magento2\Observer;

use GumNet\AME\Helper\SensediaAPI;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderCreate implements ObserverInterface
{
    protected $_order;
    protected $_invoiceService;
    protected $_transactionFactory;
    protected $logger;
    protected $_orderRepository;
    protected $responseFactory;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_order = $order;
        $this->_invoiceService = $invoiceService;
        $this->_orderRepository = $orderRepository;
        $this->_transactionFactory = $transactionFactory;
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
    public function invoiceOrder($order)
    {
        $invoice = $this->_invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $transaction = $this->_transactionFactory->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transaction->save();
        $order->setState('processing')->setStatus('processing');
        $order->save();
    }
    public function cancelOrder($order)
    {
        $order->cancel()->save();
    }
}
