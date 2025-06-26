<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Catalog\Model\Product\Option\Type;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\Option;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class DefaultType
{
    /** @var Json */
    protected $serializer;

    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    public function aroundGetOptionPrice(
        \Magento\Catalog\Model\Product\Option\Type\DefaultType $subject,
        callable $proceed,
        string $optionValue,
        float $basePrice
    ) {
        $itemOption = $subject->getData('configuration_item_option');

        if ($itemOption instanceof Option) {
            $item = $itemOption->getItem();

            $offerIdsOption = $item->getOptionByCode('offer_ids');

            if ($offerIdsOption) {
                $offerIds = explode(
                    ',',
                    $offerIdsOption->getValue()
                );

                if ($offerIds) {
                    $product = $itemOption->getProduct();

                    foreach ($offerIds as $offerId) {
                        $offerOption = $item->getOptionByCode(
                            sprintf(
                                'offer_%d',
                                $offerId
                            )
                        );

                        if ($offerOption) {
                            $offerData = $this->serializer->unserialize($offerOption->getValue());

                            /** @var \Magento\Catalog\Model\Product\Option $productOption */
                            foreach ($product->getProductOptionsCollection() as $productOption) {
                                $itemOptionId = substr(
                                    $itemOption->getCode(),
                                    7
                                );

                                if ($itemOptionId == $productOption->getId()) {
                                    $productOptionValues = $productOption->getValues();

                                    if ($productOptionValues) {
                                        if (array_key_exists(
                                            $itemOptionId,
                                            $offerData
                                        )) {
                                            $offerOptionValue = $offerData[ $itemOptionId ];

                                            if ($offerOptionValue == 0) {
                                                return 0;
                                            }

                                            $itemOptionValue = $itemOption->getValue();

                                            if (in_array(
                                                $productOption->getType(),
                                                ['drop_down', 'radio', 'select2']
                                            )) {
                                                if ($itemOptionValue == $offerOptionValue) {
                                                    return 0;
                                                }
                                            } else {
                                                $itemOptionValue = explode(
                                                    ',',
                                                    $itemOptionValue
                                                );

                                                if (array_diff(
                                                    $itemOptionValue,
                                                    $offerOptionValue
                                                )) {
                                                    return 0;
                                                }
                                            }
                                        }
                                    } else {
                                        if (array_key_exists(
                                            $itemOptionId,
                                            $offerData
                                        )) {
                                            return 0;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $proceed(
            $optionValue,
            $basePrice
        );
    }
}
