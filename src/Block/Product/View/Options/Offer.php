<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Block\Product\View\Options;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer\CollectionFactory;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Offer extends Template
{
    /** @var Variables */
    protected $variables;

    /** @var Registry */
    protected $registryHelper;

    /** @var CollectionFactory */
    protected $offerCollectionFactory;

    /** @var Data */
    protected $helper;

    /** @var \Magento\Catalog\Helper\Data */
    protected $catalogHelper;

    /** @var Json */
    protected $json;

    /*** @var Product */
    private $product;

    public function __construct(
        Template\Context $context,
        Variables $variables,
        Registry $registryHelper,
        CollectionFactory $collectionFactory,
        Data $helper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        Json $json,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->variables = $variables;
        $this->registryHelper = $registryHelper;
        $this->offerCollectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->catalogHelper = $catalogHelper;
        $this->json = $json;
    }

    public function getProduct(): Product
    {
        if (! $this->product) {
            $this->product = $this->registryHelper->registry('current_product');

            if (! $this->product) {
                throw new \LogicException('Product is not defined');
            }
        }

        return $this->product;
    }

    /**
     * @return \Infrangible\CatalogProductOptionOffer\Model\Offer[]
     */
    public function getOffers(): array
    {
        $collection = $this->offerCollectionFactory->create();

        try {
            $collection->filterByProduct($this->variables->intValue($this->getProduct()->getId()));
            $collection->filterByProductView();

            return $collection->getItems();
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function getOfferConfig(): string
    {
        $config = [];

        $product = $this->getProduct();

        foreach ($this->getOffers() as $offer) {
            $offerId = $offer->getId();

            $offerPrice = $offer->getPrice();

            try {
                $offerData = [
                    'prices'  => [
                        'basePrice'  => [
                            'amount' => $this->catalogHelper->getTaxPrice(
                                $product,
                                $offerPrice,
                                false,
                                null,
                                null,
                                null,
                                null,
                                null,
                                false
                            ),
                        ],
                        'finalPrice' => [
                            'amount' => $this->catalogHelper->getTaxPrice(
                                $product,
                                $offerPrice,
                                true,
                                null,
                                null,
                                null,
                                null,
                                null,
                                false
                            )
                        ]
                    ],
                    'options' => $this->helper->getOfferData($this->variables->intValue($offerId))
                ];
            } catch (\Exception $exception) {
                continue;
            }

            $config[ $offerId ] = $offerData;
        }

        return $this->json->encode($config);
    }

    public function getOfferOptionNames(\Infrangible\CatalogProductOptionOffer\Model\Offer $offer): array
    {
        return $this->helper->getOfferOptionNames(
            $offer,
            $this->getProduct()
        );
    }
}
