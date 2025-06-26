<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Observer;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductOptionCompositeQuoteItemBundleUpdateItemSelection implements ObserverInterface
{
    /** @var Data */
    protected $helper;

    /** @var Variables */
    protected $variables;

    /** @var Arrays */
    protected $arrays;

    public function __construct(Data $helper, Variables $variables, Arrays $arrays)
    {
        $this->helper = $helper;
        $this->variables = $variables;
        $this->arrays = $arrays;
    }

    /**
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        /** @var Http $request */
        $request = $observer->getData('request');

        $requestParams = $request->getParams();

        if (array_key_exists(
            'offer',
            $requestParams
        )) {
            $offerIds = $requestParams[ 'offer' ];

            /** @var Item $item */
            $item = $observer->getData('item');

            /** @var array $productOptions */
            $productOptions = $observer->getData('product_options');

            /** @var DataObject $cartDataObject */
            $cartDataObject = $observer->getData('cart_data');

            $itemId = $item->getId();
            $cartData = $cartDataObject->getData();

            foreach ($offerIds as $offerId) {
                $offerOptionData = $this->helper->getOfferData($this->variables->intValue($offerId));

                foreach ($offerOptionData as $optionId => $optionValue) {
                    $inProduct = false;

                    foreach ($productOptions as $productOption) {
                        if ($productOption->getId() == $optionId) {
                            $inProduct = true;
                            break;
                        }
                    }

                    if (! $inProduct) {
                        continue;
                    }

                    if ($optionValue != 0) {
                        $cartData[ $itemId ][ 'options' ][ $optionId ] = $optionValue;
                    } else {
                        $optionValueId = $this->arrays->getValue(
                            $cartData,
                            sprintf(
                                '%s:options:%s',
                                $itemId,
                                $optionId
                            )
                        );

                        if (! $this->variables->isEmpty($optionValueId)) {
                            continue;
                        }

                        foreach ($productOptions as $productOption) {
                            if ($productOption->getId() == $optionId) {
                                $values = $productOption->getValues();

                                if ($values) {
                                    $optionType = $productOption->getType();
                                    $isMultiple = $optionType === ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX ||
                                        $optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE;

                                    $hasDefaultValue = false;

                                    /** @var Value $value */
                                    foreach ($values as $value) {
                                        if ($value->getData('default') == 1) {
                                            $cartData[ $itemId ][ 'options' ][ $optionId ] =
                                                $isMultiple ? [$value->getOptionTypeId()] : $value->getOptionTypeId();

                                            $hasDefaultValue = true;
                                            break;
                                        }
                                    }

                                    if (! $hasDefaultValue) {
                                        $value = reset($values);

                                        $cartData[ $itemId ][ 'options' ][ $optionId ] =
                                            $isMultiple ? [$value->getOptionTypeId()] : $value->getOptionTypeId();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $cartDataObject->setData($cartData);
        }
    }
}
