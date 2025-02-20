<?php

namespace Ragu\ExportOrders\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Magento\Framework\Filesystem;

/**
 * Class Export
 *
 * Controller for exporting orders in different formats (CSV, XLSX, JSON).
 */
class Export extends Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * Export constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->_filesystem = $filesystem;
    }

    /**
     * Execute the export action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $selectedIds = $this->getRequest()->getParam('selected');
        $format = $this->getRequest()->getParam('format', 'csv'); // Default to 'csv' if no format provided

        if (empty($selectedIds)) {
            $this->messageManager->addErrorMessage(__('No orders selected.'));
            return $this->_redirect('sales/order/index');
        }

        $orderCollection = $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $selectedIds]);

        $data = [];
        $headers = ['Order ID', 'Customer Name', 'Grand Total', 'Status'];

        foreach ($orderCollection as $order) {
            $data[] = [
                $order->getIncrementId(),
                $order->getCustomerName(),
                $order->getGrandTotal(),
                $order->getStatus()
            ];
        }

        switch ($format) {
            case 'csv':
                return $this->exportCsv($headers, $data);
            case 'xlsx':
                return $this->exportExcel($headers, $data);
            case 'json':
                return $this->exportJson($data);
            default:
                $this->messageManager->addErrorMessage(__('Invalid export format.'));
                return $this->_redirect('sales/order/index');
        }
    }

    /**
     * Export orders as CSV file.
     *
     * @param array $headers
     * @param array $data
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function exportCsv($headers, $data)
    {
        $fileName = 'orders_export.csv';
        $content = implode(',', $headers) . "\n";
        
        foreach ($data as $row) {
            $content .= implode(',', $row) . "\n";
        }

        return $this->fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR,
            'text/csv'
        );
    }

    /**
     * Export orders as XLSX file.
     *
     * @param array $headers
     * @param array $data
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function exportExcel($headers, $data)
    {
        $fileName = 'orders_export.xlsx';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray($data, null, 'A2');
    
        $writer = new Xlsx($spreadsheet);
    
        // Get the absolute path using the Filesystem
        $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath('export/' . $fileName);
        $writer->save($filePath);
    
        return $this->fileFactory->create(
            $fileName,
            [
                'type' => 'filename',
                'value' => 'export/' . $fileName,  // Relative path to the export directory
                'rm' => true  // Remove the file after download
            ],
            DirectoryList::VAR_DIR,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    /**
     * Export orders as JSON file.
     *
     * @param array $data
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function exportJson($data)
    {
        $fileName = 'orders_export.json';
        $content = json_encode($data, JSON_PRETTY_PRINT);

        return $this->fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR,
            'application/json'
        );
    }
}
