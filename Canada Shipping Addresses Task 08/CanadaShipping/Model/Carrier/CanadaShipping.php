<?php
namespace Ragu\CanadaShipping\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

/**
 * Class CanadaShipping
 * Custom shipping method for Canada
 */
class CanadaShipping extends AbstractCarrier implements CarrierInterface
{
    /** @var string $_code Carrier code */
    protected $_code = 'custom';

    /** @var ResultFactory $rateResultFactory Factory for shipping rates */
    protected $rateResultFactory;

    /** @var MethodFactory $rateMethodFactory Factory for shipping methods */
    protected $rateMethodFactory;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Get allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['custom' => $this->getConfigData('name')];
    }

    /**
     * Collect shipping rates
     *
     * @param RateRequest $request
     * @return Result|false
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier('custom');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('custom');
        $method->setMethodTitle($this->getConfigData('name'));

        // Get cart subtotal
        $subtotal = $request->getBaseSubtotalInclTax(); // Use getBaseSubtotal() if tax is not included

        // Calculate 5% shipping charge
        $shippingAmount = $subtotal * 0.05;

        // Apply handling fee if configured
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingAmount);

        $method->setPrice($shippingPrice);
        $method->setCost($shippingAmount);

        $result->append($method);

        return $result;
    }
}
