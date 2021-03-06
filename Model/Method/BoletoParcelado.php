<?php

namespace VirtusPay\Magento2\Model\Method;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Sales\Api\OrderRepositoryInterface;

class BoletoParcelado extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'virtuspay';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canAuthorize = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_countryFactory;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = ['BRL'];
    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
    protected $adminSession;
    protected $messageManager;
    protected $virtusPayApi;
    protected $logger;
    protected $_scopeConfig;
    protected $_invoiceService;
    protected $_transactionFactory;
    protected $statusCollectionFactory;
    protected $orderRepository;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \VirtusPay\Magento2\Api\VirtusPayApiInterface $virtusPayApi,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Psr\Log\LoggerInterface $mlogger,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->virtusPayApi = $virtusPayApi;
        $this->adminSession = $adminSession;
        $this->logger = $mlogger;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_invoiceService = $invoiceService;
        $this->_transactionFactory = $transactionFactory;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
    }
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->logger->info('Boleto parcelado create Order');
        $add = $payment->getAdditionalInformation();
        $order = $payment->getOrder();
        if (!$result = $this->virtusPayApi->createOrder($order, $add)) {
            $this->logger->info("API result: ".$result);
            $message = 'Houve um erro processando seu pedido. Por favor entre em contato conosco.';
            $this->messageManager
                ->addError($message);
            throw new \Magento\Framework\Validator\Exception(__($message));
        }
        $this->logger->info("API result: ".$result);
        $result = json_decode($result, true);

        if (!isset($result['status']) || $result['status'] !== "P"
            && $result['status'] !== "A") {
            $message = 'Houve um erro processando seu pedido. Por favor entre em contato conosco.';
            $this->messageManager->addError($message);
            throw new \Magento\Framework\Validator\Exception(__($message));
        }
        $this->updateOrderRaw($order->getIncrementId());

        $payment->setAdditionalInformation('link_virtus_pay', $result['links'][3]['href']);
        $payment->setAdditionalInformation('transaction', $result['transaction']);
        $payment->setTransactionId($result['transaction']);
//        if ($result['status'] == "A"){
//            $this->invoiceOrder($order);
//        }
        return $this;
    }
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if(!$this->_scopeConfig->getValue('payment/virtuspay/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
            return false;
        }
        $isAvailable = $this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        if(!$isAvailable) return false;
        return true;
    }
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }
        try {
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__('Payment refunding error.'));
        }
        $payment
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1);
        return $this;
    }
    public function updateOrderRaw($incrementId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('sales_order');
        $sql = "UPDATE " . $tableName . " SET status = 'pending', state = 'new' WHERE entity_id = " . $incrementId;
        $connection->query($sql);
    }

    public function invoiceOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $invoice = $this->_invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $transaction = $this->_transactionFactory->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transaction->save();
        $statusPaid = $this->_scopeConfig->getValue(
            'payment/virtuspay/status_paid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $order->setState('processing')->setStatus($statusPaid);
        $this->orderRepository->save($order);
    }
}
