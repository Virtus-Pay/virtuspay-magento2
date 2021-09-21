<?php

namespace VirtusPay\Magento2\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

/**
 * Class AddReceivedOrderStatus
 * @package Techflarestudio\Content\Setup\Patch\Data
 */
class AddReceivedOrderStatus implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    private function createStatus($code, $label)
    {
        $status = $this->statusFactory->create();

        $status->setData([
            'status' => $code,
            'label' => $label,
        ]);

        /**
         * Save the new status
         */
        $statusResource = $this->statusResourceFactory->create();
        $statusResource->save($status);

        /**
         * Assign status to state
         */
        $status->assignState($label, true, true);
    }



    /**
     * @inheritdoc
     */
    public function apply()
    {

        $this->createStatus('approved', 'Approved');
        $this->createStatus('refused', 'Refused');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
