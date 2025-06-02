<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Checkout\Block\Cart\Item;

use Infrangible\CatalogProductOptionOffer\Block\Cart\Item\Offers;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Infrangible\Core\Helper\Block;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Renderer
{
    /** @var Data */
    protected $helper;

    /** @var Block */
    protected $blockHelper;

    /** @var Json */
    protected $serializer;

    public function __construct(Data $helper, Block $blockHelper, Json $serializer)
    {
        $this->helper = $helper;
        $this->blockHelper = $blockHelper;
        $this->serializer = $serializer;
    }

    public function afterGetOptionList(\Magento\Checkout\Block\Cart\Item\Renderer $subject, array $result): array
    {
        $item = $subject->getItem();

        $offerIdsOption = $item->getOptionByCode('offer_ids');

        if ($offerIdsOption) {
            $offerIds = explode(
                ',',
                $offerIdsOption->getValue()
            );

            if ($offerIds) {
                foreach ($offerIds as $offerId) {
                    $offerOption = $item->getOptionByCode(
                        sprintf(
                            'offer_%d',
                            $offerId
                        )
                    );

                    if ($offerOption) {
                        $offerData = $this->serializer->unserialize($offerOption->getValue());

                        foreach (array_keys($offerData) as $offerOptionId) {
                            foreach ($result as $key => $optionData) {
                                if (! array_key_exists(
                                    'option_id',
                                    $optionData
                                )) {
                                    continue;
                                }

                                if ($offerOptionId == $optionData[ 'option_id' ]) {
                                    unset($result[ $key ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function afterGetActions(
        \Magento\Checkout\Block\Cart\Item\Renderer $subject,
        string $result,
        AbstractItem $item
    ): string {
        return $this->blockHelper->renderChildBlock(
            $subject,
            Offers::class,
            ['item' => $item, 'action_html' => $result]
        );
    }
}
