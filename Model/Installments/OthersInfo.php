<?php

declare(strict_types=1);

namespace VirtusPay\Magento2\Model\Installments;

use Magento\Customer\Api\CustomerRepositoryInterface;
use VirtusPay\Magento2\Model\Installments\Orders;

class OthersInfo
{

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var \VirtusPay\Magento2\Model\Installments\Orders
     */
    private $orders;

    /**
     * OthersInfo constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param \VirtusPay\Magento2\Model\Installments\Orders $orders
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        Orders $orders
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->orders = $orders;
    }

    /**
     * @param string $email
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getOthersInfo(string $email)
    {
        $customer = $this->customerRepositoryInterface->get($email);
        $customerId = (int) $customer->getId();
        $date = [];

        $date['data_joined'] = $customer->getCreatedAt();
        $date['date_last_order'] = $this->orders->getOrder($customerId, 'DESC');
        $date['date_first_order'] = $this->orders->getOrder($customerId);
        $date['order_count_in_six_months'] = $this->orders->getOrderIntervalMonth($customerId, 6);
        $date['order_amount_in_six_months'] = $this->orders->getAmountOrderIntervalMonth($customerId, 6);
        $date['order_count_in_twelve_months'] = $this->orders->getOrderIntervalMonth($customerId, 12);
        $date['order_amount_in_twelve_months'] = $this->orders->getAmountOrderIntervalMonth($customerId, 12);
        $date['order_count_in_twenty_four_months'] = $this->orders->getOrderIntervalMonth($customerId, 24);
        $date['order_amount_in_twenty_four_months'] = $this->orders->getAmountOrderIntervalMonth($customerId, 24);
        $date['has_chargeback_in_two_months'] = false;

        return $date;
    }

}
