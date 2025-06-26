<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Block\Quote\Item\Bundle\Products;

use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Offer extends \Infrangible\CatalogProductOptionOffer\Block\Product\View\Options\Offer
{
    public function getItem(): Item
    {
        return $this->registryHelper->registry('current_item');
    }

    public function getOfferOptionNames(\Infrangible\CatalogProductOptionOffer\Model\Offer $offer): array
    {
        return $this->helper->getOfferOptionNames(
            $offer,
            $this->getProduct(),
            $this->getItem(),
            false
        );
    }
}
