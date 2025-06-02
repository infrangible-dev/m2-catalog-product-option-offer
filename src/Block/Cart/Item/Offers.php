<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Block\Cart\Item;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Infrangible\CatalogProductOptionOffer\Model\Offer;
use Infrangible\CatalogProductOptionOffer\Model\OfferFactory;
use Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer\CollectionFactory;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Offers extends Template
{
    /** @var OfferFactory */
    protected $offerFactory;

    /** @var \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\OfferFactory */
    protected $offerResourceFactory;

    /** @var Data */
    protected $helper;

    /** @var Configuration */
    protected $productConfigurationHelper;

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    /** @var \Magento\Catalog\Helper\Data */
    protected $catalogHelper;

    /** @var CollectionFactory */
    protected $offerCollectionFactory;

    /** @var Variables */
    protected $variables;

    /** @var array */
    private $appliedOffers;

    public function __construct(
        Template\Context $context,
        OfferFactory $offerFactory,
        \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\OfferFactory $offerResourceFactory,
        Data $helper,
        Configuration $productConfigurationHelper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Data $catalogHelper,
        CollectionFactory $collectionFactory,
        Variables $variables,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->offerFactory = $offerFactory;
        $this->offerResourceFactory = $offerResourceFactory;
        $this->helper = $helper;
        $this->productConfigurationHelper = $productConfigurationHelper;
        $this->priceCurrency = $priceCurrency;
        $this->catalogHelper = $catalogHelper;
        $this->offerCollectionFactory = $collectionFactory;
        $this->variables = $variables;
    }

    protected function _construct()
    {
        $this->setData(
            'template',
            $this->getTemplateName()
        );

        parent::_construct();
    }

    public function getTemplateName(): string
    {
        return 'Infrangible_CatalogProductOptionOffer::cart/item/offers.phtml';
    }

    public function getItem(): AbstractItem
    {
        return $this->getData('item');
    }

    public function getAppliedOffers(): array
    {
        if ($this->appliedOffers === null) {
            $item = $this->getItem();

            $offerIdsOption = $item->getOptionByCode('offer_ids');

            $this->appliedOffers = [];

            if ($offerIdsOption) {
                $offerIds = explode(
                    ',',
                    $offerIdsOption->getValue()
                );

                if ($offerIds) {
                    $offerResource = $this->offerResourceFactory->create();

                    foreach ($offerIds as $offerId) {
                        $offer = $this->offerFactory->create();

                        $offerResource->load(
                            $offer,
                            $offerId
                        );

                        if ($offer->getId()) {
                            $offerPrice = $offer->getPrice();

                            $offerPrice = $this->catalogHelper->getTaxPrice(
                                $item->getProduct(),
                                $offerPrice,
                                true
                            );

                            $this->appliedOffers[ $offerId ] = [
                                'name'        => $offer->getName(),
                                'description' => $offer->getDescription(),
                                'price'       => $this->priceCurrency->roundPrice($offerPrice * $item->getQty()),
                                'options'     => $this->helper->getOfferOptionNames(
                                    $offer,
                                    $item->getProduct(),
                                    $item
                                ),
                            ];
                        }
                    }
                }
            }
        }

        return $this->appliedOffers;
    }

    public function getFormatedOptionValue($optionValue): array
    {
        $params = [
            'max_length'   => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];

        return $this->productConfigurationHelper->getFormattedOptionValue(
            $optionValue,
            $params
        );
    }

    /**
     * @return Offer[]
     */
    public function getAvailableOffers(): array
    {
        $collection = $this->offerCollectionFactory->create();

        try {
            $collection->filterByProduct($this->variables->intValue($this->getItem()->getProduct()->getId()));
            $collection->filterByCart();

            $appliedOffers = $this->getAppliedOffers();
            $appliedOfferIds = array_keys($appliedOffers);

            $result = [];

            /** @var Offer $offer */
            foreach ($collection->getItems() as $offer) {
                if (! in_array(
                    $offer->getId(),
                    $appliedOfferIds
                )) {
                    $result[] = $offer;
                }
            }

            return $result;
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function getOfferOptionNames(Offer $offer): array
    {
        return $this->helper->getOfferOptionNames(
            $offer,
            $this->getItem()->getProduct()
        );
    }
}
