<?php
namespace Ragu\CustomOrderID\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Observer to add a random custom order ID to orders.
 */
class AddRandomOrderId implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Executes the observer logic to generate and add a custom order ID.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        $randomKey = $this->generateUniqueRandomKey();

        // Log the generated random key
        $this->logger->info('Generated Random Key: ' . $randomKey);

        // Save the random key to the custom field
        $order->setData('Custom_OrderID', $randomKey);

        $order->save();
    }

    /**
     * Generates a unique random key.
     *
     * @return string The unique random key.
     */
    protected function generateUniqueRandomKey()
    {
        $keyLength = 8;
        $randomKey = '';
        do {
            $randomKey = random_int(pow(10, $keyLength - 1), pow(10, $keyLength) - 1);
            $isUnique = $this->isRandomKeyExists($randomKey);
        } while ($isUnique);

        return $randomKey;
    }

    /**
     * Checks if a random key exists in the data set.
     *
     * @param string $randomKey The random key to check.
     * @return bool True if the random key exists, false otherwise.
     */
    protected function isRandomKeyExists($randomKey)
    {
        $existingOrder = $this->orderCollectionFactory->create()
            ->addFieldToFilter('Custom_OrderID', $randomKey)
            ->getFirstItem();

        return (bool)$existingOrder->getId();
    }
}
