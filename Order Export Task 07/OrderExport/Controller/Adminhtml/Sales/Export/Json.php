<?php

namespace Ragu\OrderExport\Controller\Adminhtml\Sales\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Ragu\OrderExport\Model\OrderExport;

class Json extends Action
{
    protected $orderExport;
    protected $formKeyValidator;
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        OrderExport $orderExport,
        Validator $formKeyValidator,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->orderExport = $orderExport;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $request = $this->getRequest();

        if (!$this->formKeyValidator->validate($request)) {
            $this->messageManager->addErrorMessage(__('Invalid security or form key. Please refresh the page.'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }

        $selectedIds = $request->getParam('selected', []);

        if (empty($selectedIds)) {
            $this->messageManager->addErrorMessage(__('No orders selected.'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }

        return $this->orderExport->exportOrders($selectedIds, 'json');
    }
}
