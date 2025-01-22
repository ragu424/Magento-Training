<?php
namespace Ragu\CustomOrderID\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * Extended Order Grid Collection to include Custom Order ID.
 */
class Collection extends OriginalCollection
{
    /**
     * Join custom data before rendering filters.
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('sales_order');
        $this->getSelect()->joinLeft(
            $joinTable,
            'main_table.entity_id = sales_order.entity_id',
            ['Custom_OrderID']
        );
        parent::_renderFiltersBefore();
    }
}
