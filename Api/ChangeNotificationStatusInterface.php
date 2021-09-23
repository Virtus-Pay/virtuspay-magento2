<?php

namespace VirtusPay\Magento2\Api;

interface ChangeNotificationStatusInterface
{

    /**
     * @api
     *
     * @param string $transaction
     * @return mixed
     */
    public function execute(string $transaction);
}
