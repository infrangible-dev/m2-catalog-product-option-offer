<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Weee\Block\Item\Price;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Renderer
{
    public function afterGetUnitDisplayPriceInclTax(
        \Magento\Weee\Block\Item\Price\Renderer $subject,
        float $result
    ): float {
        $item = $subject->getItem();

        return $item->getData('offers_price_incl_tax') ? $result - $item->getData('offers_price_incl_tax') : $result;
    }

    public function afterGetUnitDisplayPriceExclTax(
        \Magento\Weee\Block\Item\Price\Renderer $subject,
        float $result
    ): float {
        $item = $subject->getItem();

        return $item->getData('offers_price') ? $result - $item->getData('offers_price') : $result;
    }

    public function afterGetRowDisplayPriceInclTax(
        \Magento\Weee\Block\Item\Price\Renderer $subject,
        float $result
    ): float {
        $item = $subject->getItem();

        return $item->getData('row_offers_price_incl_tax') ? $result - $item->getData('row_offers_price_incl_tax') :
            $result;
    }

    public function afterGetRowDisplayPriceExclTax(
        \Magento\Weee\Block\Item\Price\Renderer $subject,
        float $result
    ): float {
        $item = $subject->getItem();

        return $item->getData('row_offers_price') ? $result - $item->getData('row_offers_price') : $result;
    }
}
