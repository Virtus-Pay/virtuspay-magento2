<?php

declare(strict_types=1);

namespace VirtusPay\Magento2\Block;

class CcSuccess extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getPaymentMethod()
    {
        return "virtuspaycc";
    }

}
