<?php

namespace Ragu\OrderExport\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Filesystem\DirectoryList;

class OrderExport
{
    protected $orderCollectionFactory;
    protected $fileFactory;
    protected $json;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        FileFactory $fileFactory,
        Json $json
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->fileFactory = $fileFactory;
        $this->json = $json;
    }

    public function exportOrders($orderIds, $format)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $orderIds]);

        $data = [];

        foreach ($collection as $order) {
            $data[] = [
                'Order ID' => $order->getIncrementId(),
                'Customer Name' => $order->getCustomerName(),
                'Email' => $order->getCustomerEmail(),
                'Total' => $order->getGrandTotal(),
                'Status' => $order->getStatus(),
                'Created At' => $order->getCreatedAt()
            ];
        }

        switch ($format) {
            case 'csv':
                return $this->generateCsv($data);
            case 'json':
                return $this->generateJson($data);
            case 'excel':
                return $this->generateExcel($data);
        }
    }

    protected function generateCsv($data)
    {
        $filename = 'orders.csv';
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['Order ID', 'Customer Name', 'Email', 'Total', 'Status', 'Created At']);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $this->fileFactory->create(
            $filename,
            ['type' => 'string', 'value' => $content],
            DirectoryList::VAR_DIR
        );
    }

    protected function generateJson($data)
    {
        $filename = 'orders.json';
        $content = $this->json->serialize($data);

        return $this->fileFactory->create(
            $filename,
            ['type' => 'string', 'value' => $content],
            DirectoryList::VAR_DIR
        );
    }

    protected function generateExcel($data)
    {
        $filename = 'orders.xml';
        $xml = new \SimpleXMLElement('<orders/>');

        foreach ($data as $row) {
            $order = $xml->addChild('order');
            foreach ($row as $key => $value) {
                $order->addChild(str_replace(' ', '_', strtolower($key)), htmlspecialchars($value));
            }
        }

        return $this->fileFactory->create(
            $filename,
            ['type' => 'string', 'value' => $xml->asXML()],
            DirectoryList::VAR_DIR
        );
    }
}