<?php

declare(strict_types=1);

namespace VirtusPay\Magento2\Model\Installments;

use Magento\Sales\Model\OrderFactory;

class Orders
{

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * Orders constructor.
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param int $customerId
     * @param int $month
     * @return int|void
     */
    public function getOrderIntervalMonth(int $customerId, int $month)
    {
        $orderFactory = $this->orderFactory->create();
        $collection = $orderFactory->getCollection()
            ->getSelect()
            ->where('customer_id = ?', $customerId)
            ->where('created_at BETWEEN CURDATE() - INTERVAL '.$month.' MONTH AND CURDATE()');

        return count($collection->query()->fetchAll());
    }

    /**
     * @param int $customerId
     * @param int $month
     * @return int|mixed
     */
    public function getAmountOrderIntervalMonth(int $customerId, int $month)
    {
        $amount = 0;
        $orderFactory = $this->orderFactory->create();
        $collection = $orderFactory->getCollection()
            ->getSelect()
            ->where('customer_id = ?', $customerId)
            ->where('created_at BETWEEN CURDATE() - INTERVAL '.$month.' MONTH AND CURDATE()');

        $datas = $collection->query()->fetchAll();

        if (!empty($datas)) {
            foreach ($datas as $data) {
                $amount += $data['grand_total'];
            }
        }

        return $amount;
    }

    /**
     * @param int $customerId
     * @param string $queryOrder
     * @return mixed|string
     * @throws \Zend_Db_Statement_Exception
     */
    public function getOrder(int $customerId, string $queryOrder = 'ASC')
    {
        $date = "";
        $orderFactory = $this->orderFactory->create();
        $collection = $orderFactory->getCollection()
            ->getSelect()
            ->where('customer_id = ?', $customerId);

        if ($queryOrder == 'DESC') {
            $collection = $collection->order('created_at DESC');
        }

        $data = $collection->query()->fetch();

        if (!empty($data)) {
            $date = $data['created_at'];
        }

        return $date;
    }

}
