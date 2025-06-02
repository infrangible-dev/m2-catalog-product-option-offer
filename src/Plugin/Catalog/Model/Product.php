<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Catalog\Model;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Magento\Framework\DataObject;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Product
{
    /** @var Data */
    protected $helper;

    /** @var Variables */
    protected $variables;

    public function __construct(Data $helper, Variables $variables)
    {
        $this->helper = $helper;
        $this->variables = $variables;
    }

    /**
     * @throws \Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterProcessBuyRequest(
        \Magento\Catalog\Model\Product $subject,
        DataObject $result,
        DataObject $buyRequest
    ): DataObject {
        $offerIds = $buyRequest->getData('offer');

        if ($offerIds) {
            $options = $result->getData('options');

            if (! is_array($options)) {
                $options = [];
            }

            foreach ($offerIds as $offerId) {
                $offerData = $this->helper->getOfferData($this->variables->intValue($offerId));

                if ($offerData) {
                    foreach (array_keys($offerData) as $offerOptionId) {
                        if (array_key_exists(
                            $offerOptionId,
                            $options
                        )) {
                            unset($options[ $offerOptionId ]);
                        }
                    }
                }
            }

            $result->setData(
                'options',
                $options
            );

            $result->setData(
                'offer',
                $offerIds
            );
        }

        return $result;
    }
}
