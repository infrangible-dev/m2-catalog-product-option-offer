<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Catalog\Model\Product\Type;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AbstractType
{
    /** @var Data */
    protected $helper;

    /** @var Variables */
    protected $variables;

    /** @var Json */
    protected $serializer;

    public function __construct(Data $helper, Variables $variables, Json $serializer)
    {
        $this->helper = $helper;
        $this->variables = $variables;
        $this->serializer = $serializer;
    }

    /**
     * @throws \Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterPrepareForCartAdvanced(
        Product\Type\AbstractType $subject,
        $result,
        DataObject $buyRequest,
        Product $product
    ) {
        if (is_array($result)) {
            $offerIds = $buyRequest->getData('offer');

            if ($offerIds) {
                /** @var Product $cartCandidate */
                foreach ($result as $cartCandidate) {
                    $cartCandidate->addCustomOption(
                        'offer_ids',
                        implode(
                            ',',
                            $offerIds
                        )
                    );
                }

                foreach ($offerIds as $offerId) {
                    $offerOptionData = $this->helper->getOfferData($this->variables->intValue($offerId));

                    /** @var Product $cartCandidate */
                    foreach ($result as $cartCandidate) {
                        $cartCandidate->addCustomOption(
                            sprintf(
                                'offer_%d',
                                $offerId
                            ),
                            $this->serializer->serialize($offerOptionData)
                        );
                    }
                }
            }
        }

        return $result;
    }
}
