<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Checkout\Model;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Cart
{
    /** @var Data */
    protected $helper;

    /** @var Variables */
    protected $variables;

    /** @var Json */
    protected $serializer;

    /** @var Arrays */
    protected $arrays;

    public function __construct(Data $helper, Variables $variables, Json $serializer, Arrays $arrays)
    {
        $this->helper = $helper;
        $this->variables = $variables;
        $this->serializer = $serializer;
        $this->arrays = $arrays;
    }

    /**
     * @throws \Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null): array
    {
        if (is_array($requestInfo)) {
            if (array_key_exists(
                'offer',
                $requestInfo
            )) {
                $offerIds = $requestInfo[ 'offer' ];

                foreach ($offerIds as $offerId) {
                    $offerOptionData = $this->helper->getOfferData($this->variables->intValue($offerId));

                    foreach ($offerOptionData as $optionId => $optionValue) {
                        if ($optionValue != 0) {
                            $requestInfo[ 'options' ][ $optionId ] = $optionValue;
                        } elseif ($productInfo instanceof Product) {
                            $optionValueId = $this->arrays->getValue(
                                $requestInfo,
                                sprintf(
                                    'options:%s',
                                    $optionId
                                )
                            );

                            if (! $this->variables->isEmpty($optionValueId)) {
                                continue;
                            }

                            $productOption = $productInfo->getOptionById($optionId);

                            $values = $productOption->getValues();

                            if ($values) {
                                $optionType = $productOption->getType();
                                $isMultiple = $optionType === ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX ||
                                    $optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE;

                                $hasDefaultValue = false;

                                /** @var Value $value */
                                foreach ($values as $value) {
                                    if ($value->getData('default') == 1) {
                                        $requestInfo[ 'options' ][ $optionId ] =
                                            $isMultiple ? [$value->getOptionTypeId()] : $value->getOptionTypeId();

                                        $hasDefaultValue = true;
                                        break;
                                    }
                                }

                                if (! $hasDefaultValue) {
                                    $value = reset($values);

                                    $requestInfo[ 'options' ][ $optionId ] =
                                        $isMultiple ? [$value->getOptionTypeId()] : $value->getOptionTypeId();
                                }
                            }
                        }
                    }
                }
            }
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * @throws \Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function beforeUpdateItem(
        \Magento\Checkout\Model\Cart $subject,
        $itemId,
        $requestInfo = null,
        $updatingParams = null
    ): array {
        if ($requestInfo instanceof DataObject) {
            if ($requestInfo->hasData('offer')) {
                $offerIds = $requestInfo->getData('offer');

                foreach ($offerIds as $offerId) {
                    $offerOptionData = $this->helper->getOfferData($this->variables->intValue($offerId));

                    $options = $requestInfo->getData('options');

                    if (! is_array($options)) {
                        $options = [];
                    }

                    foreach ($offerOptionData as $optionId => $optionValue) {
                        if ($optionValue != 0) {
                            $options[ $optionId ] = $optionValue;
                        }
                    }

                    $requestInfo->setData(
                        'options',
                        $options
                    );
                }
            }
        }

        return [$itemId, $requestInfo, $updatingParams];
    }

    /**
     * @throws \Exception
     */
    public function afterUpdateItems(
        \Magento\Checkout\Model\Cart $subject,
        \Magento\Checkout\Model\Cart $result,
        array $data
    ): \Magento\Checkout\Model\Cart {
        foreach ($data as $itemId => $itemInfo) {
            if (! array_key_exists(
                'offer',
                $itemInfo
            )) {
                continue;
            }

            $offerIds = $itemInfo[ 'offer' ];

            if ($this->variables->isEmpty($offerIds)) {
                continue;
            }

            $item = $subject->getQuote()->getItemById($itemId);

            if (! $item) {
                continue;
            }

            $product = $item->getProduct();

            $offerOption = $item->getOptionByCode('offer_ids');

            if ($offerOption) {
                $currentOfferIds = explode(
                    ',',
                    $offerOption->getValue()
                );

                foreach ($offerIds as $offerId) {
                    if (! in_array(
                        $offerId,
                        $currentOfferIds
                    )) {
                        $currentOfferIds[] = $offerId;
                    }
                }

                $offerOption->setValue(
                    implode(
                        ',',
                        $currentOfferIds
                    )
                );
            } else {
                $item->addOption(
                    [
                        'code'  => 'offer_ids',
                        'value' => implode(
                            ',',
                            $offerIds
                        )
                    ]
                );

                $offerOption = $item->getOptionByCode('offer_ids');

                $offerOption->setProduct($product);
            }

            $optionsOption = $item->getOptionByCode('option_ids');

            $optionsOptionValue = $optionsOption ? explode(
                ',',
                $optionsOption->getValue()
            ) : [];

            foreach ($offerIds as $offerId) {
                $offerOptionData = $this->helper->getOfferData($this->variables->intValue($offerId));

                $buyRequest = $item->getOptionByCode('info_buyRequest');

                $buyRequestData = $buyRequest ? $this->serializer->unserialize($buyRequest->getValue()) : [];

                $options = $this->arrays->getValue(
                    $buyRequestData,
                    'options',
                    []
                );

                foreach ($offerOptionData as $optionId => $optionValue) {
                    if ($optionValue) {
                        continue;
                    }

                    $options[ $optionId ] = $optionValue;

                    if (is_array($optionValue)) {
                        $optionValue = implode(
                            ',',
                            $optionValue
                        );
                    }

                    $item->addOption(
                        [
                            'code'  => sprintf(
                                'option_%d',
                                $optionId
                            ),
                            'value' => $optionValue
                        ]
                    );

                    $optionOption = $item->getOptionByCode(
                        sprintf(
                            'option_%d',
                            $optionId
                        )
                    );

                    $optionOption->setProduct($product);

                    if (! in_array(
                        $optionId,
                        $optionsOptionValue
                    )) {
                        $optionsOptionValue[] = $optionId;
                    }
                }

                $buyRequestData[ 'options' ] = $options;

                $buyRequest->setValue($this->serializer->serialize($buyRequestData));

                $item->addOption(
                    [
                        'code'  => sprintf(
                            'offer_%d',
                            $offerId
                        ),
                        'value' => $this->serializer->serialize($offerOptionData)
                    ]
                );

                $offerOption = $item->getOptionByCode(
                    sprintf(
                        'offer_%d',
                        $offerId
                    )
                );

                $offerOption->setProduct($product);
            }

            $optionsOptionValue = array_unique($optionsOptionValue);

            if ($optionsOption) {
                $optionsOption->setValue(
                    implode(
                        ',',
                        $optionsOptionValue
                    )
                );
            } else {
                $item->addOption(
                    [
                        'code'  => 'option_ids',
                        'value' => implode(
                            ',',
                            $optionsOptionValue
                        )
                    ]
                );

                $optionsOption = $item->getOptionByCode('option_ids');

                $optionsOption->setProduct($product);
            }
        }

        return $result;
    }
}
