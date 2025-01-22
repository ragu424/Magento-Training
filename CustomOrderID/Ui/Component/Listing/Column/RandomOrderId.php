<?php
namespace Ragu\CustomOrderID\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\OrderRepository;

/**
 * Class RandomOrderId
 * Custom UI Component for displaying custom order IDs in the order grid.
 */
class RandomOrderId extends Column
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * RandomOrderId constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepository $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepository $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source for UI Component.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $orderId = $item['entity_id'];
                $order = $this->orderRepository->get($orderId);
                $item[$this->getData('name')] = $order->getData('Custom_OrderID');
            }
        }

        return $dataSource;
    }
}
