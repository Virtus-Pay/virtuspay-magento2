<?php

namespace VirtusPay\Magento2\Api;

interface ChangeNotificationStatusInterface
{

    /**
     * @api
     *
     * @param string $transactionId
     * @return mixed
     */
    public function execute(string $transactionId);
}
