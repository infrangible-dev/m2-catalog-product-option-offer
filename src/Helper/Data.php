<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Helper;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Model\Offer;
use Infrangible\CatalogProductOptionOffer\Model\Offer\Option;
use Infrangible\CatalogProductOptionOffer\Model\OfferFactory;
use Infrangible\Core\Helper\Product;
use Infrangible\Core\Helper\Stores;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var OfferFactory */
    protected $offerFactory;

    /** @var \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\OfferFactory */
    protected $offerResourceFactory;

    /** @var Stores */
    protected $storeHelper;

    /** @var Product */
    protected $productHelper;

    /** @var Variables */
    protected $variables;

    public function __construct(
        OfferFactory $offerFactory,
        \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\OfferFactory $offerResourceFactory,
        Stores $storeHelper,
        Product $productHelper,
        Variables $variables
    ) {
        $this->offerFactory = $offerFactory;
        $this->offerResourceFactory = $offerResourceFactory;
        $this->storeHelper = $storeHelper;
        $this->productHelper = $productHelper;
        $this->variables = $variables;
    }

    /**
     * @throws \Exception
     */
    public function getOfferData(int $offerId): array
    {
        $offer = $this->offerFactory->create();

        $offerResource = $this->offerResourceFactory->create();

        $offerResource->load(
            $offer,
            $offerId
        );

        $offerOptionData = [];

        if ($offer->getId()) {
            $productId = $offer->getProductId();
            $storeId = $this->storeHelper->getStore()->getId();

            $product = $this->productHelper->loadProduct(
                $this->variables->intValue($productId),
                $this->variables->intValue($storeId)
            );

            /** @var Option $offerOption */
            foreach ($offer->getOptionsCollection() as $offerOption) {
                $offerOptionId = $offerOption->getOptionId();

                if ($offerOptionId) {
                    /** @var \Magento\Catalog\Model\Product\Option $option */
                    foreach ($product->getOptions() as $option) {
                        if ($option->getOptionId() == $offerOptionId) {
                            $optionValues = $option->getValues();

                            if ($optionValues) {
                                $optionType = $option->getType();
                                $offerOptionValueId = $offerOption->getOptionValueId();

                                if ($offerOptionValueId) {
                                    if (in_array(
                                        $optionType,
                                        ['drop_down', 'radio', 'select2']
                                    )) {
                                        $offerOptionData[ $offerOptionId ] = $offerOptionValueId;
                                    } else {
                                        $offerOptionData[ $offerOptionId ][] = $offerOptionValueId;
                                    }
                                } else {
                                    $offerOptionData[ $offerOptionId ] = 0;
                                }
                            } else {
                                $offerOptionData[ $offerOptionId ] = $offerOptionId;
                            }
                        }
                    }
                }
            }
        }

        return $offerOptionData;
    }

    public function getOfferOptionNames(
        Offer $offer,
        \Magento\Catalog\Model\Product $product,
        ?AbstractItem $item = null
    ): array {
        $optionNames = [];

        try {
            /** @var Option $offerOption */
            foreach ($offer->getOptionsCollection() as $offerOption) {
                $offerOptionId = $offerOption->getOptionId();

                if ($offerOptionId) {
                    $productOption = $product->getOptionById($offerOptionId);

                    if ($productOption) {
                        if ($item) {
                            $itemOption = $item->getOptionByCode(
                                sprintf(
                                    'option_%s',
                                    $productOption->getId()
                                )
                            );

                            if ($itemOption) {
                                try {
                                    $group = $productOption->groupFactory($productOption->getType());
                                } catch (LocalizedException $exception) {
                                    continue;
                                }

                                $group->setOption($productOption);
                                $group->setData(
                                    'configuration_item',
                                    $item
                                );
                                $group->setData(
                                    'configuration_item_option',
                                    $itemOption
                                );

                                $optionNames[ $productOption->getOptionId() ] = [
                                    'label' => $productOption->getTitle(),
                                    'value' => $group->getFormattedOptionValue($itemOption->getValue())
                                ];
                            }
                        } else {
                            $productOptionValues = $productOption->getValues();

                            if ($productOptionValues) {
                                $offerOptionValueId = $offerOption->getOptionValueId();

                                if ($offerOptionValueId) {
                                    /** @var Value $productOptionValue */
                                    foreach ($productOptionValues as $productOptionValue) {
                                        if ($productOptionValue->getId() == $offerOptionValueId) {
                                            if (array_key_exists(
                                                $productOption->getOptionId(),
                                                $optionNames
                                            )) {
                                                $optionNames[ $productOption->getOptionId() ][ 'value' ] = sprintf(
                                                    '%s, %s',
                                                    $optionNames[ $productOption->getOptionId() ][ 'value' ],
                                                    $productOptionValue->getTitle()
                                                );
                                            } else {
                                                $optionNames[ $productOption->getOptionId() ] = [
                                                    'label' => $productOption->getTitle(),
                                                    'value' => $productOptionValue->getTitle()
                                                ];
                                            }
                                        }
                                    }
                                } else {
                                    $optionNames[ $productOption->getOptionId() ] = [
                                        'label' => $productOption->getTitle(),
                                        'value' => __('Select any')
                                    ];
                                }
                            } else {
                                $optionNames[ $productOption->getOptionId() ] = [
                                    'label' => $productOption->getTitle(),
                                    'value' => ''
                                ];
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
        }

        usort(
            $optionNames,
            function (array $option1, array $option2) {
                $labelResult = strcasecmp(
                    $option1[ 'label' ],
                    $option2[ 'label' ]
                );

                return $labelResult === 0 ? strcasecmp(
                    $option1[ 'value' ],
                    $option2[ 'value' ]
                ) : $labelResult;
            }
        );

        return $optionNames;
    }
}
